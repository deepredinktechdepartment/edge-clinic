<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'service_id',
        'service_name',

        'quantity',
        'rate',
        'amount',

        'cgst_percent',
        'sgst_percent',
        'igst_percent',

        'cgst_amount',
        'sgst_amount',
        'igst_amount',

        'total_amount'
    ];

    // ================= RELATIONSHIP =================

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}