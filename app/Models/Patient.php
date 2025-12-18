<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable; // ← change here
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Patient extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
   
        'name',
        'gender',
        'dob',
        'mobile',
        'email',
        'address',
        'age',
        'ipAddress',
        'password',
        'country_code',
        'bookingfor',
        'other_reason'
    ];

    protected $hidden = [
        'password',
    ];
}
