<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HealthRecord;
use App\Models\Student;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon; // Added missing Carbon import

class HealthRecordController extends Controller
{
    public function store(Request $request)
    {
        // Validate inputs
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'check_date' => 'required|date',
            'height' => 'required|numeric',
            'weight' => 'required|numeric',
            'vision' => 'nullable|string',
            'hearing' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Calculate BMI
        $heightInMeters = $request->height / 100;
        $bmi = round($request->weight / ($heightInMeters * $heightInMeters), 1);
        
        // Determine nutritional status
        $nutritionalStatus = "Normal";
        if ($bmi < 14) $nutritionalStatus = "Severely Underweight";
        else if ($bmi < 15) $nutritionalStatus = "Underweight";
        else if ($bmi < 18.5) $nutritionalStatus = "Normal";
        else if ($bmi < 21) $nutritionalStatus = "Overweight";
        else $nutritionalStatus = "Obese";
        
        // Create health record
        $record = HealthRecord::create([
            'student_id' => $request->student_id,
            'check_date' => $request->check_date,
            'height' => $request->height,
            'weight' => $request->weight,
            'bmi' => $bmi,
            'nutritional_status' => $nutritionalStatus,
            'vision' => $request->vision,
            'hearing' => $request->hearing,
            'notes' => $request->notes,
        ]);
        
        // Update student's current health info
        $student = Student::find($request->student_id);
        if ($student) {
            $student->update([
                'height' => $request->height,
                'weight' => $request->weight,
                'bmi' => $bmi,
                'nutritional_status' => $nutritionalStatus,
                'vision' => $request->vision,
                'hearing' => $request->hearing,
            ]);
        }
        
        return response()->json([
            'message' => 'Health record created successfully',
            'record' => $record
        ], 201);
    }
    
    public function getByStudent($studentId)
    {
        $student = Student::findOrFail($studentId);
        $records = HealthRecord::where('student_id', $studentId)
                              ->orderBy('check_date', 'desc')
                              ->get();
        
        return response()->json([
            'student' => $student,
            'records' => $records
        ]);
    }
    
    public function getReport(Request $request)
    {
        // Validate inputs
        $validator = Validator::make($request->all(), [
            'section' => 'nullable|string',
            'check_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Build query to get students
        $studentsQuery = Student::query();
        
        if ($request->has('section') && $request->section) {
            $studentsQuery->where('section', $request->section);
        }
        
        $students = $studentsQuery->get();
        $studentsCount = $students->count();
        
        // Check if there are any students
        if ($studentsCount === 0) {
            return response()->json([
                'total_students' => 0,
                'nutritional_status' => [],
                'vision_status' => [],
                'hearing_status' => [],
                'height_weight_by_age' => [],
                'section' => $request->section ?? 'All',
            ]);
        }
        
        // Get health status summary
        $nutritionalStatus = $students->groupBy('nutritional_status')
                                     ->map(function($items, $status) use ($studentsCount) {
                                         return [
                                             'status' => $status ?: 'Not Assessed',
                                             'count' => $items->count(),
                                             'percentage' => round(($items->count() / $studentsCount) * 100, 1),
                                         ];
                                     })
                                     ->values();
        
        // Vision status
        $visionStatus = $students->groupBy('vision')
                                ->map(function($items, $status) use ($studentsCount) {
                                    return [
                                        'status' => $status ?: 'Not Assessed',
                                        'count' => $items->count(),
                                        'percentage' => round(($items->count() / $studentsCount) * 100, 1)
                                    ];
                                })
                                ->values();
        
        // Hearing status
        $hearingStatus = $students->groupBy('hearing')
                                 ->map(function($items, $status) use ($studentsCount) {
                                     return [
                                         'status' => $status ?: 'Not Assessed',
                                         'count' => $items->count(),
                                         'percentage' => round(($items->count() / $studentsCount) * 100, 1)
                                     ];
                                 })
                                 ->values();
        
        // Height and weight averages by age
        $heightWeightByAge = $students->where('birthdate', '!=', null)
                                     ->groupBy(function($student) {
                                         return Carbon::parse($student->birthdate)->age;
                                     })
                                     ->map(function($items, $age) {
                                         $avgHeight = $items->avg('height');
                                         $avgWeight = $items->avg('weight');
                                         $avgBmi = $items->avg('bmi');
                                         
                                         return [
                                             'age' => $age,
                                             'count' => $items->count(),
                                             'avg_height' => $avgHeight ? round($avgHeight, 1) : 0,
                                             'avg_weight' => $avgWeight ? round($avgWeight, 1) : 0,
                                             'avg_bmi' => $avgBmi ? round($avgBmi, 1) : 0,
                                         ];
                                     })
                                     ->sortBy('age')
                                     ->values();
        
        return response()->json([
            'total_students' => $studentsCount,
            'nutritional_status' => $nutritionalStatus,
            'vision_status' => $visionStatus,
            'hearing_status' => $hearingStatus,
            'height_weight_by_age' => $heightWeightByAge,
            'section' => $request->section ?? 'All',
        ]);
    }
}