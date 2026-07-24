<?php
namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\RegistrationFee;
use App\Models\Source;
use App\Models\DoctorSession;
use App\Models\DoctorSlotSetting;
use App\Models\DoctorTimeSlot;
use App\Models\DoctorNonPracticeDay;
use App\Services\RegistrationFeeService;
use App\Services\FollowupEligibilityService;
use App\Services\AppointmentPaymentStateService;
use Carbon\Carbon;

class AppointmentController extends Controller
{
public function patientCreate(Request $request)
{
    // flag tells where this form is used
    $action = $request->get('action', 'default');
    $pageTitle="Book an appointment";
        return view('patients.create', compact('pageTitle','action'));
    }


    public function slotChoose(Request $request,$patientId)
    {

         $patient = $patientId ? Patient::findOrFail($patientId) : null;

        $doctors = Doctor::with('department')
            ->orderByRaw("TRIM(REPLACE(name, 'Dr. ', '')) ASC")
            ->get();
    // Page title as a string
        $sources = Source::where('status', true)
            ->orderBy('name')
            ->get();
        $paymentRuleLabels = app(AppointmentPaymentStateService::class)->ruleOptions();
        $pageTitle = 'Book Appointment' . ($patient ? ' for ' . $patient->name : '');
        return view('patients.doctor-slot-select', compact('doctors','pageTitle','patient', 'sources', 'paymentRuleLabels'));
    }
    // Step 2: Load dates & slots for selected doctor (AJAX)
//   public function ajaxSlots($doctorId)
// {
//     \Log::info('ajaxSlots Called', [
//         'doctor_id' => $doctorId
//     ]);

//     $doctor = Doctor::findOrFail($doctorId);

//     \Log::info('Doctor Loaded', [
//         'id' => $doctor->id,
//         'name' => $doctor->name,
//         'drKey' => $doctor->drKey,
//         'appointment_fee' => $doctor->appointment_fee
//     ]);

//     $drKey = $doctor->drKey;

//     $dates = app(\App\Http\Controllers\DoctorController::class)
//            ->_getDoctorCalendar($drKey);

//     \Log::info('Calendar Data Returned', [
//         'doctor_id' => $doctor->id,
//         'dates' => $dates
//     ]);

//     return response()->json([
//         'doctor_id' => $doctor->id,
//         'doctor_name' => $doctor->name,
//         'appointment_fee'  => (float) $doctor->appointment_fee,
//         'dates' => $dates,
//     ]);
// }

public function ajaxSlots($doctorId, Request $request)
{
    \Log::info('ajaxSlots Called', [
        'doctor_id'  => $doctorId,
        'patient_id' => $request->patientId
    ]);

    $doctor = Doctor::findOrFail($doctorId);

    $dates = app(\App\Http\Controllers\DoctorController::class)
        ->_getDoctorCalendar($doctor->id);

    $patientId = $request->patientId;

    $followup = app(FollowupEligibilityService::class)
        ->check($doctorId, $patientId ? (int) $patientId : null, (int) $doctor->followup_days);

    $doctorFee = $followup['eligible']
        ? 0
        : (float) $doctor->appointment_fee;

    return response()->json([
        'doctor_id'        => $doctor->id,
        'doctor_name'      => $doctor->name,
        'appointment_fee'  => $doctorFee,
        'is_followup'      => $followup['eligible'],
        'followup_days'    => $doctor->followup_days,
        'last_visit'       => $followup['last_visit'],
        'valid_till'       => $followup['valid_till'],
        'followup_count'   => $followup['followup_count'],
        'last_followup'    => $followup['last_followup'],
        'dates'            => $dates,
    ]);
}

/**
 * Returns the first valid walk-in time after the doctor's configured hours.
 * This is deliberately separate from the normal slot calendar: reception can
 * record a genuine late walk-in without opening normal appointment slots.
 */
public function afterSlotWindow($doctorId, Request $request)
{
    $request->validate(['date' => 'required']);
    $doctor = Doctor::findOrFail($doctorId);

    try {
        $date = $this->appointmentDate($request->date);
    } catch (\Throwable $e) {
        return response()->json(['available' => false, 'message' => 'Choose a valid appointment date.'], 422);
    }

    if ($date->lt(Carbon::today())) {
        return response()->json(['available' => false, 'message' => 'Past dates cannot be used for an after-slot walk-in.'], 422);
    }

    $window = $this->afterSlotWindowForDate($doctor->id, $date);

    return response()->json([
        'doctor_id' => $doctor->id,
        'date' => $date->format('Ymd'),
        'available' => $window !== null,
        'end_time' => $window['end_time'] ?? null,
        'message' => $window
            ? 'Choose a time at or after ' . Carbon::createFromFormat('H:i', $window['end_time'])->format('h:i A') . '.'
            : 'This doctor has no working hours configured for the selected date.',
    ]);
}

public function confirm(Request $request)
{
    try {

        /* ===============================
           VALIDATION
        =============================== */
        $validated = $request->validate([
            'patientId'     => 'required|exists:patients,id',
            'doctor_id'     => 'required|exists:doctors,id',
            'date'          => 'required',
            'time'          => 'required',
            'amount'        => 'required|numeric|min:0',
            'source_id'     => 'required|exists:sources,id',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'after_slot_walk_in' => 'nullable|boolean',
            'payment_choice'=> 'required|in:pay_now,pay_later,free_booking,no_payment_required',
            'payment_mode'  => 'nullable|in:cash,upi',
            'upi_ref'       => 'required_if:payment_mode,upi'
        ]);

        $patient = Patient::findOrFail($request->patientId);
        $doctor  = Doctor::findOrFail($request->doctor_id);
        $appointmentDate = $this->appointmentDate($request->date);
        $appointmentDateKey = $appointmentDate->format('Ymd');
        $isAfterSlotWalkIn = $request->boolean('after_slot_walk_in');
        $afterSlotStartTime = null;

        if ($isAfterSlotWalkIn) {
            $window = $this->afterSlotWindowForDate($doctor->id, $appointmentDate);
            $selectedTime = Carbon::createFromFormat('H:i', Carbon::parse($request->time)->format('H:i'));

            if (!$window || $selectedTime->lt(Carbon::createFromFormat('H:i', $window['end_time']))) {
                return back()->withInput()->withErrors([
                    'after_slot_walk_in' => 'Choose a time at or after the doctor\'s configured closing time for this date.',
                ]);
            }

            if ($appointmentDate->isToday() && $selectedTime->lt(now())) {
                return back()->withInput()->withErrors([
                    'time' => 'A past time cannot be used for today\'s after-slot walk-in.',
                ]);
            }

            $afterSlotStartTime = $window['end_time'];
        }

        /* ===============================
           FEE VALIDATION
        =============================== */
        $regData = app(RegistrationFeeService::class)->check($patient->id);


        /* ===============================
        FOLLOW-UP CHECK (FINAL AUTH)
        ================================ */

        $followup = app(FollowupEligibilityService::class)
            ->check($doctor->id, $patient->id, (int) $doctor->followup_days);

        $doctorFee = $followup['eligible']
            ? 0
            : (float) ($doctor->appointment_fee ?? 0);
        $isFollowup = $followup['eligible'] ? 1 : 0;
        $mainVisitId = $followup['eligible'] ? $followup['main_visit_id'] : null;


        $registrationFee = (float) ($regData['amount'] ?? 0);
        $grossAmount = $doctorFee + $registrationFee;
        $discountPercentage = round((float) ($request->discount_percentage ?? 0), 2);
        $discountAmount = round(($grossAmount * $discountPercentage) / 100, 2);
        $calculatedAmount = round(max($grossAmount - $discountAmount, 0), 2);

        if ((float) $request->amount !== (float) $calculatedAmount) {
            abort(403, 'Payment amount mismatch');
        }

        /* ===============================
           GENERATE IDS
        =============================== */
        $timeKey     = str_replace(':', '', $request->time);
        $randomPart  = Str::random(8);

        $paymentId   = 'pay_' . $randomPart . substr($timeKey, -4);
        $orderId     = 'OFF_' . strtoupper(Str::random(6)) . substr($timeKey, -4);

        $isFreeBooking = (float) $calculatedAmount <= 0 || $request->payment_choice === 'free_booking';
        $isNoPaymentRequired = $request->payment_choice === 'no_payment_required';
        $isPayLater = $request->payment_choice === 'pay_later' || $isFreeBooking;
        $isPayNow = ! $isFreeBooking && ! $isPayLater && ! $isNoPaymentRequired;

        if ($isPayNow && empty($request->payment_mode)) {
            return back()
                ->withInput()
                ->with('error', 'Please select a payment mode.');
        }

        $paymentMode = $isNoPaymentRequired
            ? 'no_payment_required'
            : ($isFreeBooking ? 'free_booking' : ($isPayLater ? 'pay_later' : $request->payment_mode));
        $paymentStatus = $isNoPaymentRequired
            ? 'No Payment Required'
            : ($isFreeBooking ? 'Authorized' : ($isPayLater ? 'Pending' : 'Authorized'));
        $appointmentPaymentStatus = ($isPayLater && ! $isNoPaymentRequired)
            ? 'initiated'
            : 'success';
        $paymentDate = ($isPayLater || $isNoPaymentRequired) ? null : now();

        $resolvedPaymentState = app(AppointmentPaymentStateService::class)->resolve(
            (int) $request->source_id,
            null,
            $paymentStatus,
            $appointmentPaymentStatus,
            $paymentDate
        );
        $paymentStatus = $resolvedPaymentState['payment_status'];
        $appointmentPaymentStatus = $resolvedPaymentState['appointment_payment_status'];
        $paymentDate = $resolvedPaymentState['payment_date'];

        $referenceNo = null;

        if ($paymentMode === 'cash') {
            $referenceNo = 'CASH_' . $patient->user_id . '_' . $patient->id . '_' . $timeKey;
        } elseif ($paymentMode === 'upi') {
            $referenceNo = $request->upi_ref;
        }

        DB::beginTransaction();

        /* ===============================
           INSERT PAYMENT
        =============================== */
        DB::table('payments')->insert([
            'patient_id'       => $patient->id,
            'payment_id'       => $paymentId,
            'order_id'         => $orderId,
            'reference_no'     => $referenceNo,
            'payment_mode'     => $paymentMode,
            'doctor_fee'       => $doctorFee,
            'source_id'        => $request->source_id,
            'is_followup'      => $isFollowup,
            'main_visit_id'    => $mainVisitId,
            'registration_fee' => $registrationFee,
            'discount_percentage' => $discountPercentage,
            'discount_amount'  => $discountAmount,
            'amount'           => $calculatedAmount,
            'currency'         => 'INR',
            'status'           => $paymentStatus,
            'type'             => 'appointment',
            'email'            => $patient->email,
            'phone'            => $patient->mobile,
            'aptDate'          => $appointmentDateKey,
            'aptTime'          => $request->time,
            'doctor_id'        => $doctor->id,
            'is_after_slot'    => $isAfterSlotWalkIn,
            'after_slot_start_time' => $afterSlotStartTime,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        /* ===============================
           INSERT ORDER (OFFLINE)
        =============================== */
        $nameParts = explode(' ', $patient->name, 2);

        DB::table('orders')->insert([
            'user_id'           => $patient->user_id ?? 0,
            'patient_id'        => $patient->id,
            'order_id'          => $orderId,
            'first_name'        => $nameParts[0] ?? '',
            'last_name'         => $nameParts[1] ?? '',
            'email'             => $patient->email,
            'phone'             => $patient->mobile,
            'doctor_fee'        => $doctorFee,
            'registration_fee'  => $registrationFee,
            'amount'            => $calculatedAmount,
            'currency'          => 'INR',
            'status'            => 'created',
            'notes'             => json_encode([
                                        'source_id'  => $request->source_id,
                                        'discount_percentage' => $discountPercentage,
                                        'discount_amount' => $discountAmount,
                                        'gross_amount' => $grossAmount,
                                        'doctor_id' => $doctor->id,
                                        'doctor_key'=> $doctor->drKey,
                                        'apt_date'  => $appointmentDateKey,
                                        'apt_time'  => $request->time,
                                        'after_slot_walk_in' => $isAfterSlotWalkIn,
                                        'after_slot_start_time' => $afterSlotStartTime,
                                    ]),
            'ip_address'        => $request->ip(),
            'user_agent'        => $request->userAgent(),
            'referrer'          => $request->headers->get('referer'),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        /* ===============================
           CREATE LOCAL APPOINTMENT
        =============================== */
        $existingSlot = DB::table('appointments')
            ->where('doctor_id', $doctor->id)
            ->where('date', $appointmentDateKey)
            ->where('time_slot', $request->time)
            ->exists();

        if ($existingSlot) {
            throw new \Exception('Selected slot already booked. Please choose another slot.');
        }

        $appointmentNo = 'APT' . now()->format('YmdHis') . strtoupper(Str::random(3));
        $appointmentId = DB::table('appointments')->insertGetId([
            'appointment_no' => $appointmentNo,
            'doctor_id'      => $doctor->id,
            'patient_id'     => $patient->id,
            'date'           => $appointmentDateKey,
            'time_slot'      => $request->time,
            'fee'            => $calculatedAmount,
            'payment_id'     => $paymentId,
            'payment_status' => $appointmentPaymentStatus,
            'payment_method' => $paymentMode,
            'currency'       => 'INR',
            'payment_date'   => $paymentDate,
            'appointment_status' => 'Scheduled',
            'is_after_slot'  => $isAfterSlotWalkIn,
            'after_slot_start_time' => $afterSlotStartTime,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        DB::table('appointment_status_logs')->insert([
            'appointment_no' => $appointmentNo,
            'appointment_id' => $appointmentId,
            'to_status'      => 'Booked',
            'changed_by'     => auth()->id(),
            'changedName'    => auth()->user()->name ?? 'Reception',
            'ip_address'     => $request->ip(),
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        /* ===============================
           UPDATE PAYMENT WITH LOCAL APPOINTMENT DATA
        =============================== */
        DB::table('payments')
            ->where('payment_id', $paymentId)
            ->update([
                'mocdoc_apptkey'      => $appointmentNo,
                'appointment_status'  => 'Scheduled',
                'mocdoc_response'     => json_encode([
                    'status' => 'local',
                    'appointment_no' => $appointmentNo,
                    'appointment_id' => $appointmentId,
                ]),
                'updated_at'          => now(),
            ]);

        /* ===============================
           UPDATE REGISTRATION VALIDITY
        =============================== */
        if ($regData['apply']) {
            $validityDays = RegistrationFee::where('is_active', 1)
                ->value('validity_days');

            Patient::where('id', $patient->id)->update([
                'registration_valid_till' => now()->addDays($validityDays),
            ]);
        }

        DB::commit();

        return redirect()
            ->to(url('admin/appointments-report'))
            ->with('success', 'Appointment booked successfully');

    } catch (\Throwable $e) {

        DB::rollBack();

        Log::error('Offline Appointment confirm failed', [
            'error' => $e->getMessage(),
            'data'  => $request->all()
        ]);

        return back()
            ->withInput()
            ->with('error', 'Failed to book appointment. Please try again.');
    }
}

/** Normalise both existing Ymd values and browser date input values. */
private function appointmentDate(string $value): Carbon
{
    return preg_match('/^\d{8}$/', $value)
        ? Carbon::createFromFormat('Ymd', $value)->startOfDay()
        : Carbon::parse($value)->startOfDay();
}

/**
 * Get the last configured slot end for one doctor and date. A weekly-off or
 * non-practice day deliberately has no after-slot booking window.
 */
private function afterSlotWindowForDate(int $doctorId, Carbon $date): ?array
{
    $dateKey = $date->format('Ymd');
    $isNonPracticeDay = DoctorNonPracticeDay::where('doctor_id', $doctorId)
        ->whereDate('marked_date', $date->toDateString())
        ->exists();

    if ($isNonPracticeDay) {
        return null;
    }

    $dayMap = [6 => 0, 0 => 1, 1 => 2, 2 => 3, 3 => 4, 4 => 5, 5 => 6];
    $dayOfWeek = $dayMap[$date->dayOfWeek];
    $weeklySlots = DoctorTimeSlot::where('doctor_id', $doctorId)
        ->where('day_of_week', $dayOfWeek)
        ->get();

    if ($weeklySlots->contains(fn ($slot) => (bool) $slot->is_weekly_off)) {
        return null;
    }

    if ($weeklySlots->isNotEmpty()) {
        $lastSlot = $weeklySlots
            ->where('is_weekly_off', false)
            ->max('slot_time');

        if (!$lastSlot) {
            return null;
        }

        $duration = (int) (DoctorSlotSetting::where('doctor_id', $doctorId)->value('slot_duration') ?: 15);
        return ['end_time' => Carbon::parse($lastSlot)->addMinutes($duration)->format('H:i')];
    }

    $endTime = DoctorSession::where('doctor_id', $doctorId)->max('end_time');
    return $endTime ? ['end_time' => Carbon::parse($endTime)->format('H:i')] : null;
}

public function checkRegistrationFee(
    ?int $patientId,
    RegistrationFeeService $service
) {
    return response()->json(
        $service->check($patientId)
    );
}

public function printInvoice($paymentId)
{
    $payment = DB::table('payments as p')
        ->leftJoin('patients as pat', 'pat.id', '=', 'p.patient_id')
        ->leftJoin('doctors as d', 'd.id', '=', 'p.doctor_id')
        ->select([
            'p.id',
            'p.payment_id',
            'p.mocdoc_apptkey',
            'p.amount',
            'p.doctor_fee',
            'p.registration_fee',
            'p.discount_percentage',
            'p.discount_amount',
            'p.status',
            'p.payment_mode',
            'p.email',
            'p.phone',
            'p.aptDate',
            'p.aptTime',
            'p.created_at',

            // Doctor
            'd.name as doctor_name',

            // Patient
            'pat.name as patient_name',
            'pat.mobile as patient_phone',
            'pat.registration_valid_till',
        ])
        ->where('p.payment_id', $paymentId)
        ->first();

    if (!$payment) {
        abort(404);
    }

    return view('invoices.appointment', compact('payment'));
}

}
