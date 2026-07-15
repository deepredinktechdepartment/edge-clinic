<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CabinInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'doctor_id',
        'cabin_id',
        'billing_type',
        'period_start',
        'period_end',
        'invoice_date',
        'due_date',
        'subtotal',
        'gst_percent',
        'gst_amount',
        'total_amount',
        'status',
        'sent_via',
        'notes',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'gst_percent' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function cabin()
    {
        return $this->belongsTo(Cabin::class);
    }

    public function items()
    {
        return $this->hasMany(CabinInvoiceItem::class);
    }
}
