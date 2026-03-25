<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\ConsultationPatientHistory;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Icd10Code;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ConsultationController extends Controller
{
    public function create(Request $request)
    {
        $payment = null;
        $consultation = null;
        $patient = null;

        if ($request->filled('payment_id')) {
            $payment = Payment::with(['patient', 'doctor', 'consultation'])
                ->findOrFail($request->integer('payment_id'));

            if ($payment->consultation) {
                return redirect()->route('consultations.edit', $payment->consultation);
            }

            $patient = $payment->patient;
        }

        if (! $patient && $request->filled('patient_id')) {
            $patient = Patient::findOrFail($request->integer('patient_id'));
        }

        abort_unless($patient, 404, 'Patient not found.');

        $doctorId = $payment?->doctor_id
            ?? ($this->isDoctorUser() ? Auth::user()->doctor_id : null)
            ?? Payment::where('patient_id', $patient->id)->latest('id')->value('doctor_id');

        $doctor = $doctorId ? Doctor::find($doctorId) : null;

        $consultation = new Consultation([
            'patient_id' => $patient->id,
            'payment_id' => $payment?->id,
            'doctor_id' => $doctor?->id,
            'source' => $payment ? 'appointment' : 'patient',
            'visit_date' => $this->resolveVisitDate($payment),
            'visit_time' => $payment?->aptTime,
            'status' => 'draft',
            'follow_up_label' => '4 Weeks',
            'general_appearance' => 'well',
        ]);

        return view('consultations.window', $this->buildViewData($consultation, $patient, $doctor, $payment));
    }

    public function edit(Consultation $consultation)
    {
        $consultation->load([
            'patient.consultationHistory',
            'doctor',
            'payment',
            'diagnoses',
            'examinations',
            'investigations',
            'prescriptions',
        ]);

        return view('consultations.window', $this->buildViewData(
            $consultation,
            $consultation->patient,
            $consultation->doctor,
            $consultation->payment
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'consultation_id' => 'nullable|exists:consultations,id',
            'payment_id' => 'nullable|exists:payments,id',
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'status' => 'required|in:draft,finalized',
            'visit_date' => 'nullable|date',
            'visit_time' => 'nullable|string|max:20',
            'token_number' => 'nullable|string|max:50',
            'duration_value' => 'nullable|string|max:30',
            'duration_unit' => 'nullable|string|max:20',
            'history_of_present_illness' => 'nullable|string',
            'general_appearance' => 'nullable|string|max:30',
            'follow_up_label' => 'nullable|string|max:50',
            'follow_up_date' => 'nullable|date',
            'referral_department' => 'nullable|string|max:255',
            'referral_note' => 'nullable|string',
            'investigation_instructions' => 'nullable|string',
            'advice' => 'nullable|string',
            'doctor_note' => 'nullable|string',
            'bp_systolic' => 'nullable|string|max:20',
            'bp_diastolic' => 'nullable|string|max:20',
            'heart_rate' => 'nullable|string|max:20',
            'spo2' => 'nullable|string|max:20',
            'temperature' => 'nullable|string|max:20',
            'weight' => 'nullable|string|max:20',
            'height' => 'nullable|string|max:20',
            'bmi' => 'nullable|string|max:20',
            'respiratory_rate' => 'nullable|string|max:20',
            'grbs' => 'nullable|string|max:20',
            'waist_circumference' => 'nullable|string|max:20',
            'pain_score' => 'nullable|string|max:20',
            'gcs' => 'nullable|string|max:20',
            'chief_complaints_json' => 'nullable|string',
            'aggravating_factors_json' => 'nullable|string',
            'relieving_factors_json' => 'nullable|string',
            'associated_symptoms_json' => 'nullable|string',
            'past_medical_history_json' => 'nullable|string',
            'surgical_history_json' => 'nullable|string',
            'family_history_json' => 'nullable|string',
            'drug_allergies_json' => 'nullable|string',
            'chronic_conditions_json' => 'nullable|string',
            'ongoing_medications_json' => 'nullable|string',
            'diagnoses_json' => 'nullable|string',
            'examinations_json' => 'nullable|string',
            'investigations_json' => 'nullable|string',
            'prescriptions_json' => 'nullable|string',
        ]);

        $patient = Patient::findOrFail($validated['patient_id']);
        $payment = ! empty($validated['payment_id'])
            ? Payment::findOrFail($validated['payment_id'])
            : null;

        $consultation = DB::transaction(function () use ($validated, $patient, $payment) {
            $consultation = null;

            if (! empty($validated['consultation_id'])) {
                $consultation = Consultation::findOrFail($validated['consultation_id']);
            } elseif ($payment && $payment->consultation) {
                $consultation = $payment->consultation;
            } else {
                $consultation = new Consultation();
            }

            $doctorId = $validated['doctor_id']
                ?? $payment?->doctor_id
                ?? ($this->isDoctorUser() ? Auth::user()->doctor_id : null);

            $consultation->fill([
                'payment_id' => $payment?->id,
                'patient_id' => $patient->id,
                'doctor_id' => $doctorId,
                'created_by' => Auth::id(),
                'source' => $payment ? 'appointment' : 'patient',
                'status' => $validated['status'],
                'visit_date' => $validated['visit_date'] ?? now()->toDateString(),
                'visit_time' => $validated['visit_time'] ?? $payment?->aptTime,
                'token_number' => $validated['token_number'] ?? $payment?->mocdoc_apptkey,
                'chief_complaint_duration_value' => $validated['duration_value'] ?? null,
                'chief_complaint_duration_unit' => $validated['duration_unit'] ?? null,
                'history_of_present_illness' => $validated['history_of_present_illness'] ?? null,
                'chief_complaints' => $this->decodeTagList($validated['chief_complaints_json'] ?? null),
                'aggravating_factors' => $this->decodeTagList($validated['aggravating_factors_json'] ?? null),
                'relieving_factors' => $this->decodeTagList($validated['relieving_factors_json'] ?? null),
                'associated_symptoms' => $this->decodeTagList($validated['associated_symptoms_json'] ?? null),
                'general_appearance' => $validated['general_appearance'] ?? 'well',
                'follow_up_label' => $validated['follow_up_label'] ?? null,
                'follow_up_date' => $validated['follow_up_date'] ?? null,
                'referral_department' => $validated['referral_department'] ?? null,
                'referral_note' => $validated['referral_note'] ?? null,
                'investigation_instructions' => $validated['investigation_instructions'] ?? null,
                'advice' => $validated['advice'] ?? null,
                'doctor_note' => $validated['doctor_note'] ?? null,
                'bp_systolic' => $validated['bp_systolic'] ?? null,
                'bp_diastolic' => $validated['bp_diastolic'] ?? null,
                'heart_rate' => $validated['heart_rate'] ?? null,
                'spo2' => $validated['spo2'] ?? null,
                'temperature' => $validated['temperature'] ?? null,
                'weight' => $validated['weight'] ?? null,
                'height' => $validated['height'] ?? null,
                'bmi' => $validated['bmi'] ?? null,
                'respiratory_rate' => $validated['respiratory_rate'] ?? null,
                'grbs' => $validated['grbs'] ?? null,
                'waist_circumference' => $validated['waist_circumference'] ?? null,
                'pain_score' => $validated['pain_score'] ?? null,
                'gcs' => $validated['gcs'] ?? null,
                'finalized_at' => $validated['status'] === 'finalized' ? now() : null,
            ]);
            $consultation->save();

            ConsultationPatientHistory::updateOrCreate(
                ['patient_id' => $patient->id],
                [
                    'past_medical_history' => $this->decodeTagList($validated['past_medical_history_json'] ?? null),
                    'surgical_history' => $this->decodeTagList($validated['surgical_history_json'] ?? null),
                    'family_history' => $this->decodeTagList($validated['family_history_json'] ?? null),
                    'drug_allergies' => $this->decodeTagList($validated['drug_allergies_json'] ?? null),
                    'chronic_conditions' => $this->decodeTagList($validated['chronic_conditions_json'] ?? null),
                    'ongoing_medications' => $this->decodeTagList($validated['ongoing_medications_json'] ?? null),
                ]
            );

            $consultation->diagnoses()->delete();
            foreach ($this->decodeRowList($validated['diagnoses_json'] ?? null) as $index => $diagnosis) {
                if (blank($diagnosis['diagnosis_name'] ?? null)) {
                    continue;
                }

                $consultation->diagnoses()->create([
                    'icd10_code_id' => $diagnosis['icd10_code_id'] ?? null,
                    'diagnosis_name' => $diagnosis['diagnosis_name'],
                    'icd10_code' => $diagnosis['icd10_code'] ?? null,
                    'diagnosis_type' => $diagnosis['diagnosis_type'] ?? 'provisional',
                    'clinical_status' => $diagnosis['clinical_status'] ?? 'active',
                    'sort_order' => $index,
                ]);
            }

            $consultation->examinations()->delete();
            foreach ($this->decodeRowList($validated['examinations_json'] ?? null) as $index => $examination) {
                if (blank($examination['system_name'] ?? null)) {
                    continue;
                }

                $consultation->examinations()->create([
                    'system_name' => $examination['system_name'],
                    'finding_status' => $examination['finding_status'] ?? 'normal',
                    'notes' => $examination['notes'] ?? null,
                    'sort_order' => $index,
                ]);
            }

            $consultation->investigations()->delete();
            foreach ($this->decodeRowList($validated['investigations_json'] ?? null) as $index => $investigation) {
                $testName = is_array($investigation) ? ($investigation['test_name'] ?? null) : $investigation;

                if (blank($testName)) {
                    continue;
                }

                $consultation->investigations()->create([
                    'test_name' => $testName,
                    'sort_order' => $index,
                ]);
            }

            $consultation->prescriptions()->delete();
            foreach ($this->decodeRowList($validated['prescriptions_json'] ?? null) as $index => $prescription) {
                if (blank($prescription['medicine_name'] ?? null)) {
                    continue;
                }

                $consultation->prescriptions()->create([
                    'medicine_id' => $prescription['medicine_id'] ?? null,
                    'medicine_name' => $prescription['medicine_name'],
                    'pack' => $prescription['pack'] ?? null,
                    'frequency' => $prescription['frequency'] ?? null,
                    'duration' => $prescription['duration'] ?? null,
                    'instruction' => $prescription['instruction'] ?? null,
                    'details' => $prescription['details'] ?? null,
                    'sort_order' => $index,
                ]);
            }

            if ($payment) {
                $payment->appointment_status = $validated['status'] === 'finalized'
                    ? 'Completed'
                    : 'In-Consultation';
                $payment->save();
            }

            return $consultation;
        });

        $message = $validated['status'] === 'finalized'
            ? 'Consultation finalized successfully.'
            : 'Consultation draft saved successfully.';

        if ($validated['status'] === 'finalized' && $request->boolean('print_after_save')) {
            return redirect()->route('consultations.print', $consultation)
                ->with('success', $message);
        }

        return redirect()->route('consultations.edit', $consultation)
            ->with('success', $message);
    }

    public function print(Consultation $consultation)
    {
        $consultation->load([
            'patient.consultationHistory',
            'doctor',
            'payment',
            'diagnoses',
            'examinations',
            'investigations',
            'prescriptions',
        ]);

        return view('consultations.print', [
            'pageTitle' => 'Consultation Print',
            'consultation' => $consultation,
        ]);
    }

    public function pdf(Consultation $consultation)
    {
        $consultation->load([
            'patient.consultationHistory',
            'doctor',
            'payment',
            'diagnoses',
            'examinations',
            'investigations',
            'prescriptions',
        ]);

        return Pdf::loadView('consultations.pdf', [
            'consultation' => $consultation,
        ])->download('consultation-' . $consultation->id . '.pdf');
    }

    public function email(Consultation $consultation)
    {
        $consultation->load(['patient', 'doctor', 'diagnoses', 'prescriptions']);

        abort_if(blank($consultation->patient?->email), 422, 'Patient email is not available.');

        $diagnoses = $consultation->diagnoses->pluck('diagnosis_name')->filter()->implode(', ');
        $medicines = $consultation->prescriptions->pluck('medicine_name')->filter()->implode(', ');

        Mail::raw(
            "Consultation summary for {$consultation->patient->name}\nDiagnosis: {$diagnoses}\nMedicines: {$medicines}\nAdvice: {$consultation->advice}",
            function ($message) use ($consultation) {
                $message->to($consultation->patient->email)
                    ->subject('Consultation Summary - Edge Clinic');
            }
        );

        return back()->with('success', 'Consultation summary emailed successfully.');
    }

    public function searchIcd10(Request $request)
    {
        $term = trim((string) $request->get('q', ''));

        $results = Icd10Code::query()
            ->when($term !== '', function ($query) use ($term) {
                $query->where(function ($subQuery) use ($term) {
                    $subQuery->where('full_code', 'like', '%' . $term . '%')
                        ->orWhere('short_description', 'like', '%' . $term . '%')
                        ->orWhere('long_description', 'like', '%' . $term . '%');
                });
            })
            ->limit(15)
            ->get(['id', 'full_code', 'short_description', 'long_description']);

        return response()->json($results->map(function ($code) {
            return [
                'id' => $code->id,
                'code' => $code->full_code,
                'name' => $code->short_description ?: $code->long_description,
            ];
        }));
    }

    public function searchMedicines(Request $request)
    {
        $term = trim((string) $request->get('q', ''));

        $results = Medicine::query()
            ->when($term !== '', function ($query) use ($term) {
                $query->where('name', 'like', '%' . $term . '%');
            })
            ->limit(15)
            ->get([
                'id',
                'name',
                'pack_size_label',
                'manufacturer_name',
                'short_composition1',
                'short_composition2',
            ]);

        return response()->json($results->map(function ($medicine) {
            return [
                'id' => $medicine->id,
                'name' => $medicine->name,
                'pack' => $medicine->pack_size_label,
                'manufacturer' => $medicine->manufacturer_name,
                'details' => collect([
                    $medicine->short_composition1,
                    $medicine->short_composition2,
                ])->filter()->implode(', '),
            ];
        }));
    }

    protected function buildViewData(Consultation $consultation, Patient $patient, ?Doctor $doctor, ?Payment $payment): array
    {
        $history = $patient->consultationHistory()->firstOrCreate(['patient_id' => $patient->id]);

        $consultation->loadMissing(['diagnoses', 'examinations', 'investigations', 'prescriptions']);

        $pastConsultations = Consultation::with('doctor')
            ->where('patient_id', $patient->id)
            ->when($consultation->exists, fn ($query) => $query->where('id', '!=', $consultation->id))
            ->latest('visit_date')
            ->limit(10)
            ->get();

        $latestPayment = $payment ?: Payment::with('doctor')
            ->where('patient_id', $patient->id)
            ->latest('id')
            ->first();

        return [
            'pageTitle' => $consultation->exists ? 'Edit Current Visit' : 'Current Visit',
            'consultation' => $consultation,
            'patient' => $patient,
            'doctor' => $doctor,
            'payment' => $payment,
            'latestPayment' => $latestPayment,
            'history' => $history,
            'pastConsultations' => $pastConsultations,
            'departments' => Department::orderBy('dept_name')->get(),
            'doctors' => Doctor::orderByRaw("TRIM(REPLACE(name, 'Dr. ', '')) ASC")->get(['id', 'name']),
            'defaultFollowUpDate' => now()->addWeeks(4)->toDateString(),
        ];
    }

    protected function decodeTagList(?string $value): array
    {
        $decoded = json_decode($value ?: '[]', true);

        return collect(is_array($decoded) ? $decoded : [])
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();
    }

    protected function decodeRowList(?string $value): array
    {
        $decoded = json_decode($value ?: '[]', true);

        return is_array($decoded) ? array_values($decoded) : [];
    }

    protected function resolveVisitDate(?Payment $payment): string
    {
        if ($payment && filled($payment->aptDate)) {
            return Carbon::createFromFormat('Ymd', $payment->aptDate)->toDateString();
        }

        return now()->toDateString();
    }

    protected function isDoctorUser(): bool
    {
        return Auth::check() && (int) Auth::user()->role === 5;
    }
}

