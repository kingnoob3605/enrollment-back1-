<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Teacher;

class TeachersSeeder extends Seeder
{
    public function run(): void
    {
        // Create teachers for each section
        $sections = ['A', 'B', 'C', 'D', 'E'];
        
        foreach ($sections as $section) {
            Teacher::create([
                'name' => 'Teacher ' . $section,
                'position' => 'Class Advisor',
                'grade' => '1',
                'section' => $section,
                'email' => 'teacher' . strtolower($section) . '@school.edu',
                'phone' => '123-456-789' . array_search($section, $sections),
                'subjects' => ['Mathematics', 'English', 'Science'],
            ]);
        }
    }
}