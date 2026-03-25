<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultationInvestigation extends Model
{
    use HasFactory;

    protected $fillable = [
        'consultation_id',
        'test_name',
        'sort_order',
    ];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }
}
