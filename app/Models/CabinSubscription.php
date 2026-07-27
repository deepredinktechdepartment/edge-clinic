<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CabinSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'cabin_id',
        'doctor_id',
        'start_date',
        'end_date',
        'subscription_start_time',
        'subscription_end_time',
        'subscription_days',
        'monthly_rate',
        'gst_percent',
        'gst_amount',
        'total_amount',
        'payment_choice', 'payment_mode', 'payment_status', 'transaction_reference', 'paid_amount', 'paid_on',
        'invoice_day',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'subscription_days' => 'array',
        'monthly_rate' => 'decimal:2',
        'gst_percent' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2', 'paid_on' => 'datetime',
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
