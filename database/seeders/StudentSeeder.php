<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\HealthRecord;
use Carbon\Carbon;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $sections = ['A', 'B', 'C', 'D', 'E'];
        $startLRN = 120001001001;
        
        foreach ($sections as $section) {
            // Create 10 students per section
            for ($i = 0; $i < 10; $i++) {
                $lrn = (string)($startLRN + (array_search($section, $sections) * 100) + $i);
                
                // Randomize gender
                $gender = rand(0, 1) ? 'Male' : 'Female';
                
                // Random birthdate (6-7 years old)
                $birthdate = Carbon::now()->subYears(rand(6, 7))->subMonths(rand(0, 11))->subDays(rand(0, 30));
                
                // Random height and weight based on age and gender
                if ($gender == 'Male') {
                    $height = rand(110, 125); // cm
                    $weight = rand(18, 25); // kg
                } else {
                    $height = rand(108, 123); // cm
                    $weight = rand(17, 24); // kg
                }
                
                // Calculate BMI
                $heightInMeters = $height / 100;
                $bmi = round($weight / ($heightInMeters * $heightInMeters), 1);
                
                // Determine nutritional status
                $nutritionalStatus = "Normal";
                if ($bmi < 14) $nutritionalStatus = "Severely Underweight";
                else if ($bmi < 15) $nutritionalStatus = "Underweight";
                else if ($bmi < 18.5) $nutritionalStatus = "Normal";
                else if ($bmi < 21) $nutritionalStatus = "Overweight";
                else $nutritionalStatus = "Obese";
                
                // First and last names
                $firstNames = ['John', 'Maria', 'Carlos', 'Sofia', 'Miguel', 'Ana', 'Jose', 'Luisa', 'Gabriel', 'Isabella'];
                $lastNames = ['Cruz', 'Reyes', 'Santos', 'Garcia', 'Mendoza', 'Lim', 'Tan', 'Gonzales', 'Bautista', 'Aquino'];
                
                $firstName = $firstNames[rand(0, count($firstNames) - 1)];
                $lastName = $lastNames[rand(0, count($lastNames) - 1)];
                $middleName = substr($lastNames[rand(0, count($lastNames) - 1)], 0, 1) . '.';
                
                // Create student
                $student = Student::create([
                    'lrn' => $lrn,
                    'name' => "{$firstName} {$middleName} {$lastName}",
                    'first_name' => $firstName,
                    'middle_name' => $middleName,
                    'last_name' => $lastName,
                    'grade' => '1',
                    'section' => $section,
                    'birthdate' => $birthdate,
                    'gender' => $gender,
                    'address' => rand(100, 999) . ' ' . ['Rizal', 'Mabini', 'Bonifacio', 'Aguinaldo', 'Luna'][rand(0, 4)] . ' St., Barangay ' . ['San Miguel', 'Santa Cruz', 'San Jose', 'San Antonio', 'San Pedro'][rand(0, 4)] . ', City',
                    'parent_name' => $gender == 'Male' ? "{$lastName}, {$firstNames[rand(0, count($firstNames) - 1)]}" : "{$lastNames[rand(0, count($lastNames) - 1)]}, {$firstName}",
                    'parent_contact' => '09' . rand(100000000, 999999999),
                    'status' => 'Enrolled',
                    'date_enrolled' => Carbon::now()->subMonths(rand(1, 3)),
                    'teacher_assigned' => 'Teacher ' . $section,
                    'height' => $height,
                    'weight' => $weight,
                    'bmi' => $bmi,
                    'nutritional_status' => $nutritionalStatus,
                    'vision' => rand(0, 10) < 8 ? 'Normal' : 'Needs Correction', // 80% normal
                    'hearing' => rand(0, 10) < 9 ? 'Normal' : 'Needs Assistance', // 90% normal
                    'vaccinations' => rand(0, 10) < 7 ? 'Complete' : 'Incomplete', // 70% complete
                ]);
                
                // Create health record
                HealthRecord::create([
                    'student_id' => $student->id,
                    'check_date' => Carbon::now()->subMonths(rand(0, 2)),
                    'height' => $height,
                    'weight' => $weight,
                    'bmi' => $bmi,
                    'nutritional_status' => $nutritionalStatus,
                    'vision' => $student->vision,
                    'hearing' => $student->hearing,
                ]);
            }
        }
    }
}