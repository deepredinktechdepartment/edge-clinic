<?php

namespace App\Http\Controllers\MocDoc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MocDocService;

class AvailabilityController extends Controller
{
    public function getAvailability(Request $request, MocDocService $moc)
    {
        $request->validate([
            'doctor_id' => 'required',
            'date'      => 'required|date'
        ]);

        $data = $moc->getAvailability($request->doctor_id, $request->date);

        return response()->json($data);
    }
}
