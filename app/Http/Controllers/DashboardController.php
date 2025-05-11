<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function adminDashboard()
    {
        // Get total students
        $totalStudents = Student::count();
        
        // Get students by section
        $studentsBySection = Student::select('section', DB::raw('count(*) as count'))
                                    ->groupBy('section')
                                    ->get();
        
        // Get teachers count                            
        $totalTeachers = Teacher::count();
        
        // Get attendance summary
        $presentCount = Attendance::where('status', 'Present')
                                 ->where('date', now()->format('Y-m-d'))
                                 ->count();
        $absentCount = Attendance::where('status', 'Absent')
                                ->where('date', now()->format('Y-m-d'))
                                ->count();
        $attendanceRate = $totalStudents > 0 
                        ? round(($presentCount / $totalStudents) * 100, 1) 
                        : 0;
        
        return response()->json([
            'totalStudents' => $totalStudents,
            'studentsBySection' => $studentsBySection,
            'totalTeachers' => $totalTeachers,
            'attendanceSummary' => [
                'present' => $presentCount,
                'absent' => $absentCount,
                'rate' => $attendanceRate,
            ]
        ]);
    }
    
    public function teacherDashboard(Request $request)
    {
        // Get teacher's section
        $section = $request->section;
        
        if (!$section) {
            return response()->json(['message' => 'Section is required'], 400);
        }
        
        // Get students in the section
        $students = Student::where('section', $section)->get();
        $totalStudents = $students->count();
        
        // Get gender distribution
        $maleCount = $students->where('gender', 'Male')->count();
        $femaleCount = $students->where('gender', 'Female')->count();
        
        // Get nutritional status
        $nutritionalStatus = $students->groupBy('nutritional_status')
                                      ->map(function ($items, $status) {
                                          return [
                                              'status' => $status ?: 'Not Assessed',
                                              'count' => $items->count()
                                          ];
                                      })
                                      ->values();
        
        // Get today's attendance
        $studentIds = $students->pluck('id')->toArray();
        $today = now()->format('Y-m-d');
        
        $presentCount = Attendance::whereIn('student_id', $studentIds)
                                 ->where('date', $today)
                                 ->where('status', 'Present')
                                 ->count();
        
        $absentCount = Attendance::whereIn('student_id', $studentIds)
                                ->where('date', $today)
                                ->where('status', 'Absent')
                                ->count();
        
        $attendanceRate = $totalStudents > 0 
                        ? round(($presentCount / $totalStudents) * 100, 1) 
                        : 0;
        
        return response()->json([
            'totalStudents' => $totalStudents,
            'genderDistribution' => [
                ['name' => 'Male', 'value' => $maleCount],
                ['name' => 'Female', 'value' => $femaleCount]
            ],
            'nutritionalStatus' => $nutritionalStatus,
            'attendanceSummary' => [
                'present' => $presentCount,
                'absent' => $absentCount,
                'rate' => $attendanceRate,
            ]
        ]);
    }
}