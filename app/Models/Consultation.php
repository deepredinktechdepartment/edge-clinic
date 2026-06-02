<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'payment_id',
        'patient_id',
        'doctor_id',
        'created_by',
        'source',
        'status',
        'visit_date',
        'visit_time',
        'token_number',
        'chief_complaint_duration_value',
        'chief_complaint_duration_unit',
        'history_of_present_illness',
        'chief_complaints',
        'aggravating_factors',
        'relieving_factors',
        'associated_symptoms',
        'general_appearance',
        'follow_up_label',
        'follow_up_date',
        'referral_department',
        'referral_note',
        'investigation_instructions',
        'advice',
        'doctor_note',
        'bp_systolic',
        'bp_diastolic',
        'heart_rate',
        'spo2',
        'temperature',
        'weight',
        'height',
        'bmi',
        'respiratory_rate',
        'grbs',
        'waist_circumference',
        'pain_score',
        'gcs',
        'case_sheet_front_path',
        'case_sheet_back_path',
        'receptionist_updated_by',
        'receptionist_updated_at',
        'finalized_by',
        'finalized_at',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'follow_up_date' => 'date',
        'receptionist_updated_at' => 'datetime',
        'finalized_at' => 'datetime',
        'chief_complaints' => 'array',
        'aggravating_factors' => 'array',
        'relieving_factors' => 'array',
        'associated_symptoms' => 'array',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function diagnoses()
    {
        return $this->hasMany(ConsultationDiagnosis::class)->orderBy('sort_order');
    }

    public function examinations()
    {
        return $this->hasMany(ConsultationExamination::class)->orderBy('sort_order');
    }

    public function investigations()
    {
        return $this->hasMany(ConsultationInvestigation::class)->orderBy('sort_order');
    }

    public function prescriptions()
    {
        return $this->hasMany(ConsultationPrescription::class)->orderBy('sort_order');
    }
}
