<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerWebhookIntegration extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_id',
        'partner_name',
        'is_enabled',
        'webhook_url',
        'basic_auth_username',
        'basic_auth_password',
        'timeout_seconds',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'basic_auth_password' => 'encrypted',
    ];

    public function source()
    {
        return $this->belongsTo(Source::class);
    }

    public function deliveryLogs()
    {
        return $this->hasMany(PartnerWebhookDeliveryLog::class);
    }
}
