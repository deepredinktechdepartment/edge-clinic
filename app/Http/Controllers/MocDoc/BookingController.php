<?php

namespace App\Http\Controllers\MocDoc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MocDocService;

class BookingController extends Controller
{
    public function book(Request $request, MocDocService $moc)
    {
        $request->validate([
            'doctor_id'  => 'required',
            'patient_name' => 'required',
            'mobile'     => 'required',
            'date'       => 'required|date',
            'slot_time'  => 'required'
        ]);

        $data = [
            "DoctorID"    => $request->doctor_id,
            "PatientName" => $request->patient_name,
            "MobileNo"    => $request->mobile,
            "Date"        => $request->date,
            "SlotTime"    => $request->slot_time
        ];

        return response()->json($moc->bookAppointment($data));
    }

    public function cancel(Request $request, MocDocService $moc)
    {
        $request->validate([
            'booking_id' => 'required'
        ]);

        return response()->json($moc->cancelAppointment($request->booking_id));
    }

    public function getBooking(Request $request, MocDocService $moc)
    {
        $request->validate([
            'booking_id' => 'required'
        ]);

        return response()->json($moc->getBooking($request->booking_id));
    }
}
