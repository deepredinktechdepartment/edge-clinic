<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $table ="appointments";
    protected $guarded = ['id'];

    public $timestamps = true;

    public function consultation()
{
    return $this->hasOne(Consultation::class, 'payment_id', 'payment_id');
}
}
