<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CabinBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'cabin_id',
        'doctor_id',
        'booking_type',
        'shift_key',
        'booking_date',
        'start_time',
        'end_time',
        'total_hours',
        'base_amount',
        'gst_percent',
        'gst_amount',
        'total_amount',
        'payment_choice',
        'payment_mode',
        'payment_status',
        'transaction_reference',
        'paid_amount',
        'paid_on',
        'status',
        'notes',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'total_hours' => 'decimal:2',
        'base_amount' => 'decimal:2',
        'gst_percent' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'paid_on' => 'datetime',
    ];

    public function cabin()
    {
        return $this->belongsTo(Cabin::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
