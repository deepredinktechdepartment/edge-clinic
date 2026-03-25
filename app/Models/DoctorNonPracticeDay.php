<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorNonPracticeDay extends Model
{
    protected $fillable = [
        'doctor_id',
        'marked_date',
        'type',
    ];

    protected $casts = [
        'marked_date' => 'date',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
