<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        // Get query parameters
        $section = $request->input('section');
        $teacher = $request->input('teacher');
        $status = $request->input('status');
        $search = $request->input('search');
        
        // Start with a base query
        $query = Student::query();
        
        // Apply filters
        if ($section) {
            $query->where('section', $section);
        }
        
        if ($teacher) {
            $query->where('teacher_assigned', $teacher);
        }
        
        if ($status) {
            $query->where('status', $status);
        }
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('lrn', 'LIKE', "%{$search}%");
            });
        }
        
        // Get students with pagination
        $students = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return response()->json([
            'students' => $students->items(),
            'pagination' => [
                'total' => $students->total(),
                'per_page' => $students->perPage(),
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage()
            ]
        ]);
    }

    public function store(Request $request)
    {
        // Validate request
        $request->validate([
            'name' => 'required|string|max:255',
            'lrn' => 'required|string|max:12|unique:students,lrn',
            'grade' => 'required|string|max:10',
            'section' => 'required|string|max:10',
            'gender' => 'required|in:Male,Female',
            'birthdate' => 'nullable|date',
            'address' => 'nullable|string',
            'parent_name' => 'nullable|string|max:255',
            'parent_contact' => 'nullable|string|max:20',
            'parent_email' => 'nullable|email|max:255',
            'height' => 'nullable|numeric',
            'weight' => 'nullable|numeric',
        ]);
        
        // Calculate BMI if height and weight are provided
        $bmi = null;
        $nutritionalStatus = null;
        
        if ($request->height && $request->weight) {
            $heightInMeters = $request->height / 100;
            $bmi = round($request->weight / ($heightInMeters * $heightInMeters), 1);
            
            // Determine nutritional status based on BMI
            if ($bmi < 14) $nutritionalStatus = "Severely Underweight";
            else if ($bmi < 15) $nutritionalStatus = "Underweight";
            else if ($bmi < 18.5) $nutritionalStatus = "Normal";
            else if ($bmi < 21) $nutritionalStatus = "Overweight";
            else $nutritionalStatus = "Obese";
        }
        
        // Create student
        $student = Student::create([
            'lrn' => $request->lrn,
            'name' => $request->name,
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'suffix' => $request->suffix,
            'grade' => $request->grade,
            'section' => $request->section,
            'gender' => $request->gender,
            'birthdate' => $request->birthdate,
            'address' => $request->address,
            'parent_name' => $request->parent_name,
            'parent_contact' => $request->parent_contact,
            'parent_email' => $request->parent_email,
            'status' => 'Enrolled',
            'date_enrolled' => now(),
            'teacher_assigned' => $request->teacher_assigned ?? "Teacher {$request->section}",
            'height' => $request->height,
            'weight' => $request->weight,
            'bmi' => $bmi,
            'nutritional_status' => $nutritionalStatus,
            'vision' => $request->vision ?? 'Normal',
            'hearing' => $request->hearing ?? 'Normal',
            'vaccinations' => $request->vaccinations ?? 'Complete',
            'dental_health' => $request->dental_health ?? 'Good',
        ]);
        
        return response()->json([
            'message' => 'Student created successfully',
            'student' => $student
        ], 201);
    }

    public function show($id)
    {
        $student = Student::findOrFail($id);
        
        return response()->json([
            'student' => $student
        ]);
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        
        // Validate request
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'lrn' => 'sometimes|required|string|max:12|unique:students,lrn,' . $id,
            'grade' => 'sometimes|required|string|max:10',
            'section' => 'sometimes|required|string|max:10',
            'gender' => 'sometimes|required|in:Male,Female',
            'birthdate' => 'nullable|date',
            'address' => 'nullable|string',
            'parent_name' => 'nullable|string|max:255',
            'parent_contact' => 'nullable|string|max:20',
            'parent_email' => 'nullable|email|max:255',
            'height' => 'nullable|numeric',
            'weight' => 'nullable|numeric',
        ]);
        
        // Calculate BMI if height and weight are provided
        if ($request->has('height') && $request->has('weight')) {
            $heightInMeters = $request->height / 100;
            $bmi = round($request->weight / ($heightInMeters * $heightInMeters), 1);
            
            // Determine nutritional status based on BMI
            if ($bmi < 14) $nutritionalStatus = "Severely Underweight";
            else if ($bmi < 15) $nutritionalStatus = "Underweight";
            else if ($bmi < 18.5) $nutritionalStatus = "Normal";
            else if ($bmi < 21) $nutritionalStatus = "Overweight";
            else $nutritionalStatus = "Obese";
            
            $request->merge([
                'bmi' => $bmi,
                'nutritional_status' => $nutritionalStatus
            ]);
        }
        
        $student->update($request->all());
        
        return response()->json([
            'message' => 'Student updated successfully',
            'student' => $student
        ]);
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();
        
        return response()->json([
            'message' => 'Student deleted successfully'
        ]);
    }
    
    // API endpoint for dashboard metrics
    public function getDashboardMetrics(Request $request)
    {
        // Get query parameters
        $section = $request->input('section');
        
        // Base query
        $query = Student::query();
        
        // Apply section filter if specified
        if ($section) {
            $query->where('section', $section);
        }
        
        // Total students
        $totalStudents = $query->count();
        
        // Students by gender
        $maleCount = (clone $query)->where('gender', 'Male')->count();
        $femaleCount = (clone $query)->where('gender', 'Female')->count();
        
        // Students by section
        $studentsBySection = Student::select('section', DB::raw('count(*) as count'))
            ->groupBy('section')
            ->get()
            ->map(function ($item) {
                return [
                    'section' => $item->section,
                    'count' => $item->count
                ];
            });
        
        // Students by nutritional status (BMI)
        $bmiCategories = Student::select('nutritional_status', DB::raw('count(*) as count'))
            ->whereNotNull('nutritional_status')
            ->groupBy('nutritional_status')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->nutritional_status,
                    'value' => $item->count
                ];
            });
        
        return response()->json([
            'totalStudents' => $totalStudents,
            'genderDistribution' => [
                ['name' => 'Male', 'value' => $maleCount],
                ['name' => 'Female', 'value' => $femaleCount],
            ],
            'studentsBySection' => $studentsBySection,
            'nutritionalStatus' => $bmiCategories
        ]);
    }
}