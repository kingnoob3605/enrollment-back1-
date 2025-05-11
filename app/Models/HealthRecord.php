<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'check_date', 'height', 'weight', 'bmi', 
        'nutritional_status', 'vision', 'hearing', 'notes'
    ];

    protected $casts = [
        'check_date' => 'date',
        'height' => 'float',
        'weight' => 'float',
        'bmi' => 'float',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}