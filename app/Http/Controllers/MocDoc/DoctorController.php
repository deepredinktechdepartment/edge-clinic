<?php

namespace App\Http\Controllers\MocDoc;

use App\Http\Controllers\Controller;
use App\Services\MocDocService;

class DoctorController extends Controller
{
    public function list(MocDocService $moc)
    {
        $doctors = $moc->getDoctors();
        return response()->json($doctors);
    }

    public function detail($doctorId, MocDocService $moc)
    {
        $doctor = $moc->getDoctorDetail($doctorId);
        return response()->json($doctor);
    }
}
