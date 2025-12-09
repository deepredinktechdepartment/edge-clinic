<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'dept_name', 'dept_slug', 'dept_description', 'sort_order' ,'is_active' , 'dept_icon' , 'dept_banner' , 'about_dept','about_procedure','procedure_banner','our_approach','tech_facility'
    ];
}
