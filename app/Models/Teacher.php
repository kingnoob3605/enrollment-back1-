<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'position',
        'grade',
        'section',
        'email',
        'phone',
        'subjects'
    ];
    
    protected $casts = [
        'subjects' => 'array',
    ];
    
    // Add a relationship to students
    public function students()
    {
        return $this->hasMany(Student::class, 'section', 'section');
    }
}