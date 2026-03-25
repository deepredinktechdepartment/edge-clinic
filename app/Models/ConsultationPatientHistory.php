<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultationPatientHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'past_medical_history',
        'surgical_history',
        'family_history',
        'drug_allergies',
        'chronic_conditions',
        'ongoing_medications',
    ];

    protected $casts = [
        'past_medical_history' => 'array',
        'surgical_history' => 'array',
        'family_history' => 'array',
        'drug_allergies' => 'array',
        'chronic_conditions' => 'array',
        'ongoing_medications' => 'array',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
