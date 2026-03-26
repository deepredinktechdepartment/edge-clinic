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
use App\Services\RegistrationFeeService;

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
    $pageTitle = 'Book Appointment' . ($patient ? ' for ' . $patient->name : '');
        return view('patients.doctor-slot-select', compact('doctors','pageTitle','patient'));
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
        ->_getDoctorCalendar($doctorId);

    $patientId = $request->patientId;

    $doctorFee = (float) $doctor->appointment_fee;
    $applyFee  = true;

    $lastVisitFormatted     = null;
    $validTillFormatted     = null;
    $followupCount          = 0;
    $lastFollowupFormatted  = null;

    if ($patientId && $doctor->followup_days > 0) {

        /* ===============================
           FETCH LAST MAIN VISIT
        =============================== */

        $lastMainVisit = Payment::where('doctor_id', $doctorId)
            ->where('patient_id', $patientId)
            ->where('type', 'appointment')
            ->where('is_followup', 0)
            ->where('appointment_status', 'Checked-In')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastMainVisit && $lastMainVisit->aptDate) {

            $mainVisitDate = \Carbon\Carbon::createFromFormat(
                'Ymd',
                $lastMainVisit->aptDate
            );

            $lastVisitFormatted = $mainVisitDate->format('d M Y');

            $validTill = $mainVisitDate->copy()
                ->addDays($doctor->followup_days);

            $validTillFormatted = $validTill->format('d M Y');

            /* ===============================
               FETCH FOLLOWUPS UNDER MAIN VISIT
            =============================== */

            $followups = Payment::where('doctor_id', $doctorId)
                ->where('patient_id', $patientId)
                ->where('is_followup', 1)
                ->where('main_visit_id', $lastMainVisit->id)
                ->where('appointment_status', 'Checked-In')
                ->orderBy('id', 'asc')
                ->get();

            $followupCount = $followups->count();

            if ($followupCount > 0) {
                $lastFollowup = $followups->last();

                $lastFollowupFormatted = \Carbon\Carbon::createFromFormat(
                    'Ymd',
                    $lastFollowup->aptDate
                )->format('d M Y');
            }

            /* ===============================
               FOLLOWUP VALIDATION
            =============================== */

            if (\Carbon\Carbon::today()->lessThanOrEqualTo($validTill)) {
                $applyFee  = false;
                $doctorFee = 0;
            }
        }
    }

    return response()->json([
        'doctor_id'        => $doctor->id,
        'doctor_name'      => $doctor->name,
        'appointment_fee'  => $doctorFee,
        'is_followup'      => !$applyFee,
        'followup_days'    => $doctor->followup_days,
        'last_visit'       => $lastVisitFormatted,
        'valid_till'       => $validTillFormatted,
        'followup_count'   => $followupCount,
        'last_followup'    => $lastFollowupFormatted,
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
            'payment_mode'  => 'nullable|in:cash,upi',
            'upi_ref'       => 'required_if:payment_mode,upi',
        ]);


        if ((float) $request->amount == 0) {
            $request->merge([
                'payment_mode' => null,
                'upi_ref'      => null
            ]);
        }

        $patient = Patient::findOrFail($request->patientId);
        $doctor  = Doctor::findOrFail($request->doctor_id);

        /* ===============================
           FEE VALIDATION
        =============================== */
        $regData = app(RegistrationFeeService::class)->check($patient->id);


        /* ===============================
        FOLLOW-UP CHECK (FINAL AUTH)
        ================================ */

        $doctorFee = (float) ($doctor->appointment_fee ?? 0);
        $isFollowup = 0;
        $mainVisitId = null;

        $lastAppointment = Payment::where('doctor_id', $doctor->id)
            ->where('patient_id', $patient->id)
            ->where('appointment_status', 'Checked-In')
            ->where('type', 'appointment')
            ->orderBy('aptDate', 'desc')
            ->first();

        if ($lastAppointment && $lastAppointment->aptDate) {

            $lastVisit = \Carbon\Carbon::createFromFormat('Ymd', $lastAppointment->aptDate);

            $validTill = $lastVisit->copy()
                ->addDays($doctor->followup_days);

            if (\Carbon\Carbon::today()->lessThanOrEqualTo($validTill)) {

                // ✅ FREE FOLLOW-UP
                $doctorFee = 0;
                $isFollowup = 1;

                // If previous was already follow-up, keep original main visit
                $mainVisitId = $lastAppointment->main_visit_id
                    ?? $lastAppointment->id;
            }
        }


        $registrationFee = (float) ($regData['amount'] ?? 0);
        $calculatedAmount = $doctorFee + $registrationFee;

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

        $referenceNo = $request->payment_mode === 'cash'
            ? 'CASH_' . $patient->user_id . '_' . $patient->id . '_' . $timeKey
            : $request->upi_ref;

        DB::beginTransaction();

        /* ===============================
           INSERT PAYMENT
        =============================== */
        DB::table('payments')->insert([
            'patient_id'       => $patient->id,
            'payment_id'       => $paymentId,
            'order_id'         => $orderId,
            'reference_no'     => $referenceNo,
            'payment_mode'     => $request->payment_mode ?? 'free',
            'doctor_fee'       => $doctorFee,
            'is_followup'      => $isFollowup,
            'main_visit_id'    => $mainVisitId,
            'registration_fee' => $registrationFee,
            'amount'           => $calculatedAmount,
            'currency'         => 'INR',
            'status'           => 'Authorized',
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
            'status'     => 'Authorized',
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

        /* ===============================
        CREATE APPOINTMENT (REPLACES MOCDOC)
        =============================== */

        $exists = DB::table('appointments')
            ->where('doctor_id', $doctor->id)
            ->where('date', $request->date)
            ->where('time_slot', $request->time)
            ->exists();

        if ($exists) {
            throw new \Exception('Slot already booked');
        }

        $appointmentNo = 'APT' . now()->format('YmdHis') . strtoupper(\Str::random(3));

        $appointmentId = DB::table('appointments')->insertGetId([
            'appointment_no' => $appointmentNo,
            'doctor_id'      => $doctor->id,
            'patient_id'     => $patient->id,
            'date'           => $request->date,
            'time_slot'      => $request->time,
            'fee'            => $calculatedAmount,
            'payment_id'     => $paymentId,
            'payment_status' => 'success',
            'payment_method' => $request->payment_mode,
            'currency'       => 'INR',
        ]);

        DB::table('appointment_status_logs')->insert([
            'appointment_no' => $appointmentNo,
            'appointment_id' => $appointmentId,
            'to_status'      => 'Booked',
            'changed_by'     => auth()->id(),
            'changedName'    => auth()->user()->name ?? 'Admin',
            'created_at'     => now(),
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
        ->leftJoin('appointments as a', 'a.payment_id', '=', 'p.payment_id') // ✅ IMPORTANT
        ->leftJoin('patients as pat', 'pat.id', '=', 'p.patient_id')
        ->leftJoin('doctors as d', 'd.id', '=', 'p.doctor_id')
        ->select([
            'p.id',
            'p.payment_id',

            // ✅ APPOINTMENT DATA
            'a.appointment_no',
            'a.date as appointment_date',
            'a.time_slot as appointment_time',

            // PAYMENT
            'p.amount',
            'p.doctor_fee',
            'p.registration_fee',
            'p.status',
            'p.payment_mode',
            'p.email',
            'p.phone',
            'p.created_at',

            // DOCTOR
            'd.name as doctor_name',

            // PATIENT
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
