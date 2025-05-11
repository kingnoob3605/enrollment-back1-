<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Teacher;
use Illuminate\Support\Facades\Validator;

class TeacherController extends Controller
{
    public function index()
    {
        $teachers = Teacher::all();
        
        return response()->json([
            'teachers' => $teachers
        ]);
    }
    
    public function show($id)
    {
        $teacher = Teacher::findOrFail($id);
        
        // Count students assigned to this teacher
        $studentsCount = \App\Models\Student::where('teacher_assigned', $teacher->name)
                                          ->where('section', $teacher->section)
                                          ->count();
        
        $teacher->students_count = $studentsCount;
        $teacher->save();
        
        return response()->json([
            'teacher' => $teacher
        ]);
    }
    
    public function store(Request $request)
    {
        // Validate inputs
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'grade' => 'required|string',
            'section' => 'required|string',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Create teacher
        $teacher = Teacher::create([
            'name' => $request->name,
            'position' => $request->position,
            'grade' => $request->grade,
            'section' => $request->section,
            'email' => $request->email,
            'phone' => $request->phone,
            'subjects' => $request->subjects,
        ]);
        
        return response()->json([
            'message' => 'Teacher created successfully',
            'teacher' => $teacher
        ], 201);
    }
    
    public function update(Request $request, $id)
    {
        $teacher = Teacher::findOrFail($id);
        
        // Validate inputs
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'grade' => 'required|string',
            'section' => 'required|string',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Track if section changed
        $sectionChanged = $teacher->section !== $request->section;
        $oldSection = $teacher->section;
        
        // Update teacher
        $teacher->update([
            'name' => $request->name,
            'position' => $request->position,
            'grade' => $request->grade,
            'section' => $request->section,
            'email' => $request->email,
            'phone' => $request->phone,
            'subjects' => $request->subjects,
        ]);
        
        // If section changed, update students
        if ($sectionChanged) {
            // Find students with old teacher and section
            $students = \App\Models\Student::where('teacher_assigned', $teacher->name)
                                          ->where('section', $oldSection)
                                          ->get();
            
            // Update them with new section
            foreach ($students as $student) {
                $student->section = $request->section;
                $student->save();
            }
        }
        
        return response()->json([
            'message' => 'Teacher updated successfully',
            'teacher' => $teacher
        ]);
    }
    
    public function destroy($id)
    {
        $teacher = Teacher::findOrFail($id);
        
        // Check if teacher has students
        $studentsCount = \App\Models\Student::where('teacher_assigned', $teacher->name)
                                           ->where('section', $teacher->section)
                                           ->count();
        
        if ($studentsCount > 0) {
            return response()->json([
                'message' => 'Cannot delete teacher with assigned students. Please reassign students first.'
            ], 422);
        }
        
        $teacher->delete();
        
        return response()->json(['message' => 'Teacher deleted successfully']);
    }
    
    public function getMetrics()
    {
        $totalTeachers = Teacher::count();
        $teachersByGrade = Teacher::select('grade', \DB::raw('count(*) as count'))
                                 ->groupBy('grade')
                                 ->get();
        
        return response()->json([
            'totalTeachers' => $totalTeachers,
            'teachersByGrade' => $teachersByGrade
        ]);
    }
}