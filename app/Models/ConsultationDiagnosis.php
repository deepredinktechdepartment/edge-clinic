<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultationDiagnosis extends Model
{
    use HasFactory;

    protected $fillable = [
        'consultation_id',
        'icd10_code_id',
        'diagnosis_name',
        'icd10_code',
        'diagnosis_type',
        'clinical_status',
        'sort_order',
    ];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function icd10Code()
    {
        return $this->belongsTo(Icd10Code::class);
    }
}
