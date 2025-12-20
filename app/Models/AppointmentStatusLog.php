<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// App\Models\AppointmentStatusLog.php
class AppointmentStatusLog extends Model
{
    protected $fillable = [
        'appointment_no',
        'appointment_id',
        'from_status',
        'to_status',
        'remarks',
        'changed_by',
        'changedName'
    ];
}