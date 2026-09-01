<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerWebhookDeliveryLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_webhook_integration_id',
        'payment_id',
        'appointment_id',
        'event',
        'payload',
        'response_status',
        'response_body',
        'error_message',
        'delivered_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'delivered_at' => 'datetime',
    ];

    public function integration()
    {
        return $this->belongsTo(PartnerWebhookIntegration::class, 'partner_webhook_integration_id');
    }
}
