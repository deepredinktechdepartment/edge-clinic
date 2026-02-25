<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'action',
        'remarks',
        'created_by'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}