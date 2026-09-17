<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NpsFeedback extends Model
{
    protected $fillable = ['payment_id', 'patient_id', 'doctor_id', 'score', 'comment'];
}
