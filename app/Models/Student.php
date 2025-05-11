<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Student extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'lrn',
        'name',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'grade',
        'section',
        'gender',
        'birthdate',
        'address',
        'parent_name',
        'parent_contact',
        'parent_email',
        'status',
        'date_enrolled',
        'teacher_assigned',
        'height',
        'weight',
        'bmi',
        'nutritional_status',
        'vision',
        'hearing',
        'vaccinations',
        'dental_health'
    ];
    
    // We can add an accessor to format health data in a consistent way
    protected function health(): Attribute
    {
        return Attribute::make(
            get: function () {
                return [
                    'height' => $this->height,
                    'weight' => $this->weight,
                    'bmi' => $this->bmi,
                    'nutritionalStatus' => $this->nutritional_status,
                    'vision' => $this->vision,
                    'hearing' => $this->hearing,
                    'vaccinations' => $this->vaccinations,
                    'dentalHealth' => $this->dental_health
                ];
            },
        );
    }
}