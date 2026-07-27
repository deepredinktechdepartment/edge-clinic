<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class PrescriptionShareController extends Controller
{
    public function show(Request $request, Consultation $consultation)
    {
        abort_if(blank($consultation->case_sheet_front_path), 404, 'Prescription not found.');

        return view('consultations.shared-prescription', [
            'consultation' => $consultation->load(['patient', 'doctor']),
            'frontUrl' => URL::temporarySignedRoute('prescriptions.shared-file', now()->addDays(30), ['consultation' => $consultation->id, 'side' => 'front']),
            'backUrl' => $consultation->case_sheet_back_path
                ? URL::temporarySignedRoute('prescriptions.shared-file', now()->addDays(30), ['consultation' => $consultation->id, 'side' => 'back'])
                : null,
        ]);
    }

    public function file(Request $request, Consultation $consultation, string $side)
    {
        $path = $consultation->{$side === 'back' ? 'case_sheet_back_path' : 'case_sheet_front_path'};
        abort_if(blank($path) || ! Storage::disk('public')->exists($path), 404, 'Prescription file not found.');

        return response()->file(Storage::disk('public')->path($path));
    }
}
