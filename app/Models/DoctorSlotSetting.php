<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorSlotSetting extends Model
{
    protected $fillable = [
        'doctor_id',
        'slot_duration',
        'advance_booking_days',
        'slots_private',
    ];

    protected $casts = [
        'slots_private' => 'boolean',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
