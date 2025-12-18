<?php
namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
  public function ajaxSlots($doctorId)
{
  
    $doctor = Doctor::findOrFail($doctorId);
    $drKey  = $doctor->drKey;

    // Get available dates & time slots

   $dates = app(\App\Http\Controllers\DoctorController::class)
           ->_getDoctorCalendar($drKey);

    // Return JSON data only
    return response()->json([
        'doctor_id' => $doctor->id,
        'doctor_name' => $doctor->name,
        'dates' => $dates,
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
            'payment_mode'  => 'required|in:cash,upi',
            'upi_ref'       => 'required_if:payment_mode,upi'
        ]);

        /* ===============================
           FETCH DATA
        =============================== */
        $patient = Patient::findOrFail($request->patientId);
        $doctor  = Doctor::findOrFail($request->doctor_id);

        /* ===============================
           GENERATE 18-CHAR PAYMENT ID
           pay_XXXXXXXXXXTTTT
        =============================== */
        $timeKey     = str_replace(':', '', $request->time); // eg 1030
        $randomPart  = Str::random(10);                      // 10 chars
        $paymentId   = 'pay_' . $randomPart . substr($timeKey, -4); // 18 chars

        /* ===============================
           PAYMENT REFERENCE
        =============================== */
        if ($request->payment_mode === 'cash') {
            $referenceNo = 'CASH_' . $patient->user_id . '_' . $patient->id . '_' . $timeKey;
        } else {
            $referenceNo = $request->upi_ref;
        }

        /* ===============================
           APPOINTMENT KEY
           APTID.user_id.patient.time
        =============================== */
        $apptKey = 'APTID_' . $patient->user_id . '_' . $patient->id . '_' . $timeKey;

        DB::beginTransaction();

        /* ===============================
           INSERT PAYMENT
        =============================== */
        DB::table('payments')->insert([
            'patient_id'     => $patient->id,
            'payment_id'     => $paymentId,
            'reference_no'   => $referenceNo,
            'payment_mode'   => $request->payment_mode,
            'order_id'       => null,
            'amount'         => $request->amount,
            'currency'       => 'INR',
            'status'         => 'Authorized',
            'email'          => $patient->email,
            'phone'          => $patient->mobile,
            'aptDate'        => $request->date,
            'aptTime'        => $request->time,
            'doctor_id'      => $doctor->id,
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
            'referrer'       => $request->headers->get('referer'),
            'response'       => json_encode($request->all()),
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        /* ===============================
           UPDATE APPOINTMENT
        =============================== */
        DB::table('payments')
            ->where('patient_id', $patient->id)
            ->where('doctor_id', $doctor->id)
            ->where('aptDate', $request->date)
            ->where('aptTime', $request->time)
            ->update([
                'mocdoc_apptkey' => $apptKey,                           
                'updated_at'     => now(),
            ]);

        DB::commit();

        /* ===============================
           REDIRECT SUCCESS
        =============================== */
        return redirect()
            ->to(url('admin/appointments-report'))
            ->with('success', 'Appointment booked successfully');

    } catch (\Throwable $e) {

        DB::rollBack();

        \Log::error('Appointment confirm failed', [
            'error' => $e->getMessage(),
            'data'  => $request->all()
        ]);

        return back()
            ->withInput()
            ->with('error', 'Failed to book appointment. Please try again.');
    }
}
}
