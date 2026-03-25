<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorSession extends Model
{
    protected $fillable = [
        'doctor_id',
        'session_type',
        'start_time',
        'end_time',
        'break_enabled',
        'break_start',
        'break_end',
    ];

    protected $casts = [
        'break_enabled' => 'boolean',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /** Minutes since midnight */
    public function getStartMinutesAttribute(): int
    {
        [$h, $m] = explode(':', $this->start_time);
        return (int)$h * 60 + (int)$m;
    }

    public function getEndMinutesAttribute(): int
    {
        [$h, $m] = explode(':', $this->end_time);
        return (int)$h * 60 + (int)$m;
    }

    public function getBreakStartMinutesAttribute(): int
    {
        if (!$this->break_start) return 0;
        [$h, $m] = explode(':', $this->break_start);
        return (int)$h * 60 + (int)$m;
    }

    public function getBreakEndMinutesAttribute(): int
    {
        if (!$this->break_end) return 0;
        [$h, $m] = explode(':', $this->break_end);
        return (int)$h * 60 + (int)$m;
    }
}
