<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'youtube_url',
        'description',
    ];
}
