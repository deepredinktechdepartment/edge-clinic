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
use App\Services\RegistrationFeeService;
use App\Services\FollowupEligibilityService;

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
        $pageTitle = 'Book Appointment' . ($patient ? ' for ' . $patient->name : '');
        return view('patients.doctor-slot-select', compact('doctors','pageTitle','patient', 'sources'));
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
    $drKey  = $doctor->drKey;

    $dates = app(\App\Http\Controllers\DoctorController::class)
        ->_getDoctorCalendar($doctor->id);

    if (empty(data_get($dates, 'slots.location1')) && !empty($drKey)) {
        $mocdocResponse = app(\App\Http\Controllers\MocDocController::class)
            ->_getDoctorCalendar(new Request(['drKey' => $drKey]));

        $mocdocPayload = $mocdocResponse->getData(true);

        if (($mocdocPayload['status'] ?? 0) === 200 && !empty($mocdocPayload['data'])) {
            $dates = $mocdocPayload['data'];
        }
    }

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
            'payment_choice'=> 'required|in:pay_now,pay_later,free_booking',
            'payment_mode'  => 'nullable|in:cash,upi',
            'upi_ref'       => 'required_if:payment_mode,upi'
        ]);

        $patient = Patient::findOrFail($request->patientId);
        $doctor  = Doctor::findOrFail($request->doctor_id);

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
        $isPayLater = $request->payment_choice === 'pay_later' || $isFreeBooking;

        if (!$isPayLater && empty($request->payment_mode)) {
            return back()
                ->withInput()
                ->with('error', 'Please select a payment mode.');
        }

        $paymentMode = $isFreeBooking ? 'free_booking' : ($isPayLater ? 'pay_later' : $request->payment_mode);
        $paymentStatus = $isFreeBooking ? 'Authorized' : ($isPayLater ? 'Pending' : 'Authorized');

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
            'aptDate'          => $request->date,
            'aptTime'          => $request->time,
            'doctor_id'        => $doctor->id,
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
                                        'apt_date'  => $request->date,
                                        'apt_time'  => $request->time,
                                    ]),
            'ip_address'        => $request->ip(),
            'user_agent'        => $request->userAgent(),
            'referrer'          => $request->headers->get('referer'),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        /* ===============================
           BOOK APPOINTMENT IN MOCDOC
        =============================== */

        $details = [
            'payment_id' => $paymentId,
            'amount'     => $calculatedAmount,
            'currency'   => 'INR',
            'status'     => $paymentStatus,
            'first_name' => $nameParts[0] ?? '',
            'last_name'  => $nameParts[1] ?? '',
            'email'      => $patient->email,
            'phone'      => $patient->mobile,
            'patient_id' => $patient->id,
            'user_id'    => $patient->user_id,
            'doctor_id'  => $doctor->id,
            'dr'         => $doctor->drKey,
            'date'       => $request->date,
            'start'      => $request->time,
            'end'        => \Carbon\Carbon::createFromFormat('H:i', $request->time)
                                ->addMinutes(10)
                                ->format('H:i'),
            'notes'      => [],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];

        $razorpayController = app(\App\Http\Controllers\RazorpayController::class);
        $mocdocResponse = $razorpayController->bookMocdocAppointment($details);

        if (empty($mocdocResponse['apptkey'])) {
            throw new \Exception('MocDoc booking failed');
        }

        /* ===============================
           UPDATE PAYMENT WITH MOCDOC DATA
        =============================== */
        DB::table('payments')
            ->where('payment_id', $paymentId)
            ->update([
                'mocdoc_apptkey'  => $mocdocResponse['apptkey'],
                'mocdoc_response' => json_encode($mocdocResponse),
                'updated_at'      => now(),
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
