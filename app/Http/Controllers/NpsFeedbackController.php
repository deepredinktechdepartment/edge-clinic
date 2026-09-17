<?php

namespace App\Http\Controllers;

use App\Models\NpsFeedback;
use App\Models\Payment;
use Illuminate\Http\Request;

class NpsFeedbackController extends Controller
{
    public function create(Request $request, Payment $payment)
    {
        return view('feedback.nps', ['payment' => $payment->load(['patient', 'doctor'])]);
    }

    public function store(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'score' => 'required|integer|min:0|max:10',
            'comment' => 'nullable|string|max:1000',
        ]);

        NpsFeedback::updateOrCreate(['payment_id' => $payment->id], [
            'patient_id' => $payment->patient_id,
            'doctor_id' => $payment->doctor_id,
            'score' => $validated['score'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return view('feedback.thank-you');
    }
}
