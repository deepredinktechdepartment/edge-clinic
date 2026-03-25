<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultationPrescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'consultation_id',
        'medicine_id',
        'medicine_name',
        'pack',
        'frequency',
        'duration',
        'instruction',
        'details',
        'sort_order',
    ];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
}
