<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Procedure extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'name',
        'slug',
        'about_procedure',
        'preparation_time',
        'post_procedure_care',
        'procedure_duration',
        'back_to_work',
        'approximate_cost',
        'est_recovery_period',
    ];
}
