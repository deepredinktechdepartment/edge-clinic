<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cabin extends Model
{
    use HasFactory;

    protected $fillable = [
        'cabin_code',
        'name',
        'cabin_type',
        'floor_name',
        'room_number',
        'capacity',
        'booking_mode',
        'hourly_rate',
        'monthly_rate',
        'status',
        'available_from',
        'operating_start_time',
        'operating_end_time',
        'notes',
    ];

    protected $casts = [
        'available_from' => 'date',
    ];

    public function bookings()
    {
        return $this->hasMany(CabinBooking::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(CabinSubscription::class);
    }

    public function facilities()
    {
        return $this->belongsToMany(CabinFacility::class, 'cabin_facility_links')
            ->withTimestamps()
            ->orderBy('name');
    }
}
