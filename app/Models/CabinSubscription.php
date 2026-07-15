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
        'monthly_rate',
        'gst_percent',
        'gst_amount',
        'total_amount',
        'invoice_day',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'monthly_rate' => 'decimal:2',
        'gst_percent' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
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
