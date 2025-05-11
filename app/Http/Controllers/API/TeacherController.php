<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Student;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index()
    {
        $teachers = Teacher::all();
        
        return response()->json([
            'teachers' => $teachers
        ]);
    }

    public function store(Request $request)
    {
        // Validate request
        $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'grade' => 'required|string|max:10',
            'section' => 'required|string|max:10',
            'email' => 'required|email|max:255|unique:teachers,email',
            'phone' => 'nullable|string|max:20',
            'subjects' => 'nullable|array',
        ]);
        
        // Create teacher
        $teacher = Teacher::create($request->all());
        
        return response()->json([
            'message' => 'Teacher created successfully',
            'teacher' => $teacher
        ], 201);
    }

    public function show($id)
    {
        $teacher = Teacher::findOrFail($id);
        
        // Get student count for this teacher's section
        $studentCount = Student::where('section', $teacher->section)->count();
        
        // Add the student count to the teacher object
        $teacher->students_count = $studentCount;
        
        return response()->json([
            'teacher' => $teacher
        ]);
    }

    public function update(Request $request, $id)
    {
        $teacher = Teacher::findOrFail($id);
        
        // Validate request
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'position' => 'sometimes|required|string|max:255',
            'grade' => 'sometimes|required|string|max:10',
            'section' => 'sometimes|required|string|max:10',
            'email' => 'sometimes|required|email|max:255|unique:teachers,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'subjects' => 'nullable|array',
        ]);
        
        $teacher->update($request->all());
        
        return response()->json([
            'message' => 'Teacher updated successfully',
            'teacher' => $teacher
        ]);
    }

    public function destroy($id)
    {
        $teacher = Teacher::findOrFail($id);
        $teacher->delete();
        
        return response()->json([
            'message' => 'Teacher deleted successfully'
        ]);
    }
    
    // Get teacher performance metrics
    public function getPerformanceMetrics(Request $request)
    {
        $teachers = Teacher::all();
        
        $performanceData = $teachers->map(function ($teacher) {
            // Get student count
            $studentCount = Student::where('section', $teacher->section)->count();
            
            // For demo purposes, we'll generate random metrics
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
            'teacherPerformance' => $performanceData
        ]);
    }
}