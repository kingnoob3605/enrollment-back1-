<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        // Get real data from database
        $totalStudents = Student::count();
        
        $studentsBySection = Student::select('section', DB::raw('count(*) as count'))
            ->groupBy('section')
            ->get()
            ->map(function ($item) {
                return [
                    'section' => $item->section,
                    'count' => $item->count
                ];
            });
        
        return response()->json([
            'totalStudents' => $totalStudents,
            'studentsBySection' => $studentsBySection
        ]);
    }
    
    public function reports()
    {
        // Get teacher performance data
        $teachers = Teacher::all();
        
        $teacherPerformance = $teachers->map(function ($teacher) {
            // Get student count
            $studentCount = Student::where('section', $teacher->section)->count();
            
            // For demo purposes, we'll generate random metrics
            // In a real app, you would calculate these based on actual data
            $attendanceRate = rand(85, 98);
            $performanceScore = rand(80, 95);
            
            return [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'section' => $teacher->section,
                'students' => $studentCount,
                'attendance' => $attendanceRate,
                'performance' => $performanceScore,
            ];
        });
        
        return response()->json([
            'reportTypes' => ['enrollment', 'attendance', 'teachers'],
            'teachers' => $teacherPerformance
        ]);
    }
    
    public function settings()
    {
        // Return settings data
        return response()->json([
            'schoolInfo' => [
                'name' => 'Elementary School',
                'address' => 'Saavedra St, Zamboanga, 7000 Zamboanga del Sur',
                'phone' => '(123) 456-7890',
                'email' => 'school@example.edu',
                'principal' => 'Miss Principal',
                'schoolYear' => '2024-2025',
            ],
            'userAccounts' => [
                [
                    'id' => 1,
                    'username' => 'admin',
                    'role' => 'admin',
                    'name' => 'Admin User',
                    'email' => 'admin@school.edu',
                ],
                [
                    'id' => 2,
                    'username' => 'teacher1',
                    'role' => 'teacher',
                    'name' => 'Adam Teacher',
                    'email' => 'adam@school.edu',
                ],
                // Add more users as needed
            ]
        ]);
    }
}