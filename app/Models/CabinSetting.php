<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CabinSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_open_time',
        'clinic_close_time',
        'min_booking_duration_minutes',
        'buffer_minutes',
        'default_gst_percent',
        'monthly_invoice_day',
        'payment_due_days',
        'invoice_delivery_mode',
        'clinic_gstin',
        'standard_hourly_rate',
        'premium_hourly_rate',
        'procedure_hourly_rate',
        'standard_monthly_rate',
        'premium_monthly_rate',
        'procedure_monthly_rate',
    ];

    protected $casts = [
        'default_gst_percent' => 'decimal:2',
        'standard_hourly_rate' => 'decimal:2',
        'premium_hourly_rate' => 'decimal:2',
        'procedure_hourly_rate' => 'decimal:2',
        'standard_monthly_rate' => 'decimal:2',
        'premium_monthly_rate' => 'decimal:2',
        'procedure_monthly_rate' => 'decimal:2',
    ];
}
