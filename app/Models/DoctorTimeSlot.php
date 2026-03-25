<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorTimeSlot extends Model
{
    protected $fillable = [
        'doctor_id',
        'day_of_week',
        'slot_time',
        'session_type',
        'is_reserved',
        'is_weekly_off',
    ];

    protected $casts = [
        'is_reserved'   => 'boolean',
        'is_weekly_off' => 'boolean',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public static function dayNames(): array
    {
        return ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    }
}
