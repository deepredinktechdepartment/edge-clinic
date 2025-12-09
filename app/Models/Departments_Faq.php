<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Departments_Faq extends Model{
    use HasFactory;
    protected $table ="faqs";
    protected $fillable = [
        'department_id', 'faq_question', 'faq_answer', 'is_active' , 'created_at' , 'updated_at'];
}
