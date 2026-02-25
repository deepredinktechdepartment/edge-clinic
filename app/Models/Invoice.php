<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'appointment_id',
        'patient_id',
        'invoice_date',
        'due_date',

        'sub_total',

        'total_cgst',
        'total_sgst',
        'total_igst',

        'tax_total',
        'grand_total',

        'paid_amount',
        'balance_amount',

        'status',
        'notes'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date'     => 'date',
    ];

    // ================= RELATIONSHIPS =================

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function logs()
    {
        return $this->hasMany(InvoiceLog::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    public function payments()
{
    return $this->hasMany(InvoicePayment::class);
}
}