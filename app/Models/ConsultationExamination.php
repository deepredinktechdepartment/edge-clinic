<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultationExamination extends Model
{
    use HasFactory;

    protected $fillable = [
        'consultation_id',
        'system_name',
        'finding_status',
        'notes',
        'sort_order',
    ];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }
}
