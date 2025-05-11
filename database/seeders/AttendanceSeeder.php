<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::all();
        
        // Create attendance records for the past 30 days
        for ($day = 0; $day < 30; $day++) {
            // Skip weekends
            $date = Carbon::now()->subDays($day);
            if ($date->isWeekend()) continue;
            
            foreach ($students as $student) {
                // Randomize attendance status with 85% present
                $rand = rand(1, 100);
                if ($rand <= 85) {
                    $status = 'Present';
                    $reason = null;
                } else if ($rand <= 95) {
                    $status = 'Absent';
                    $reasons = ['Sick', 'Family Emergency', 'Medical Appointment', 'Other'];
                    $reason = $reasons[rand(0, 3)];
                } else {
                    $status = 'Late';
                    $reason = null;
                }
                
                // Create attendance record
                Attendance::create([
                    'student_id' => $student->id,
                    'date' => $date->format('Y-m-d'),
                    'status' => $status,
                    'reason' => $reason,
                ]);
            }
        }
    }
}