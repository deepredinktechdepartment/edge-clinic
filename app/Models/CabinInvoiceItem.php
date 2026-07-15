<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CabinInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cabin_invoice_id',
        'description',
        'reference_type',
        'reference_id',
        'quantity',
        'unit_rate',
        'line_total',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_rate' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(CabinInvoice::class, 'cabin_invoice_id');
    }
}
