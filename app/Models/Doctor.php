<?php

namespace App\Models;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'name',
        'slug',
        'designation',
        'qualification',
        'experience',
        'educational_qualification',
        'expertise',
        'sort_order',
        'slots',
        'online_payment',
        'awards',
        'bio',
        'is_active',
        'photo',
        'created_by'
    ];
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
