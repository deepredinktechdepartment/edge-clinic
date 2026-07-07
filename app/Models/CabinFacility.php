<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CabinFacility extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'pricing_type',
        'rate',
        'charge_label',
        'payment_note',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
    ];

    public function cabins()
    {
        return $this->belongsToMany(Cabin::class, 'cabin_facility_links')
            ->withTimestamps();
    }
}
