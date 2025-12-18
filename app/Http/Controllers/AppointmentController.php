<?php
namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use Illuminate\Http\Request;

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
}
