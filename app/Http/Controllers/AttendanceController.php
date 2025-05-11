<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with('student');
        
        // Filter by date
        if ($request->has('date')) {
            $query->where('date', $request->date);
        } else {
            $query->where('date', now()->format('Y-m-d'));
        }
        
        // Filter by section through student relation
        if ($request->has('section')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('section', $request->section);
            });
        }
        
        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $attendances = $query->get();
        
        return response()->json([
            'attendances' => $attendances
        ]);
    }
    
    public function store(Request $request)
    {
        // Validate inputs
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'date' => 'required|date',
            'status' => 'required|in:Present,Absent,Late',
            'reason' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Check if record already exists
        $existing = Attendance::where('student_id', $request->student_id)
                             ->where('date', $request->date)
                             ->first();
        
        if ($existing) {
            $existing->update([
                'status' => $request->status,
                'reason' => $request->reason,
            ]);
            
            $message = 'Attendance updated successfully';
            $attendance = $existing;
        } else {
            // Create new attendance record
            $attendance = Attendance::create([
                'student_id' => $request->student_id,
                'date' => $request->date,
                'status' => $request->status,
                'reason' => $request->reason,
            ]);
            
            $message = 'Attendance recorded successfully';
        }
        
        return response()->json([
            'message' => $message,
            'attendance' => $attendance
        ]);
    }
    
    public function storeMultiple(Request $request)
    {
        // Validate inputs
        $validator = Validator::make($request->all(), [
            'attendances' => 'required|array',
            'attendances.*.student_id' => 'required|exists:students,id',
            'attendances.*.date' => 'required|date',
            'attendances.*.status' => 'required|in:Present,Absent,Late',
            'attendances.*.reason' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $createdCount = 0;
        $updatedCount = 0;
        
        foreach ($request->attendances as $item) {
            // Check if record already exists
            $existing = Attendance::where('student_id', $item['student_id'])
                                 ->where('date', $item['date'])
                                 ->first();
            
            if ($existing) {
                $existing->update([
                    'status' => $item['status'],
                    'reason' => $item['reason'] ?? null,
                ]);
                $updatedCount++;
            } else {
                // Create new attendance record
                Attendance::create([
                    'student_id' => $item['student_id'],
                    'date' => $item['date'],
                    'status' => $item['status'],
                    'reason' => $item['reason'] ?? null,
                ]);
                $createdCount++;
            }
        }
        
        return response()->json([
            'message' => "Attendance processed successfully: $createdCount created, $updatedCount updated"
        ]);
    }
    
    public function getBySection(Request $request)
    {
        // Validate inputs
        $validator = Validator::make($request->all(), [
            'section' => 'required|string',
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Get students in the section
        $students = Student::where('section', $request->section)->get();
        
        // Get attendance records for the date
        $attendances = Attendance::whereIn('student_id', $students->pluck('id'))
                                ->where('date', $request->date)
                                ->get()
                                ->keyBy('student_id');
        
        // Prepare response with student info and attendance status
        $results = $students->map(function($student) use ($attendances, $request) {
            $attendance = $attendances->get($student->id);
            
            return [
                'student_id' => $student->id,
                'lrn' => $student->lrn,
                'name' => $student->name,
                'status' => $attendance ? $attendance->status : 'Not Marked',
                'reason' => $attendance ? $attendance->reason : null,
                'date' => $request->date,
            ];
        });
        
        return response()->json([
            'attendances' => $results,
            'section' => $request->section,
            'date' => $request->date,
            'summary' => [
                'total' => $students->count(),
                'present' => $attendances->where('status', 'Present')->count(),
                'absent' => $attendances->where('status', 'Absent')->count(),
                'late' => $attendances->where('status', 'Late')->count(),
                'not_marked' => $students->count() - $attendances->count(),
            ]
        ]);
    }
    
    public function getReport(Request $request)
    {
        // Validate inputs
        $validator = Validator::make($request->all(), [
            'section' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
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
        
        // Get attendance records in date range
        $attendances = Attendance::whereIn('student_id', $students->pluck('id'))
                                ->whereBetween('date', [$request->start_date, $request->end_date])
                                ->get();
        
        // Calculate summary
        $totalDays = now()->parse($request->start_date)->diffInDays(now()->parse($request->end_date)) + 1;
        $totalExpectedAttendances = $students->count() * $totalDays;
        
        $presentCount = $attendances->where('status', 'Present')->count();
        $absentCount = $attendances->where('status', 'Absent')->count();
        $lateCount = $attendances->where('status', 'Late')->count();
        $notMarkedCount = $totalExpectedAttendances - $attendances->count();
        
        // Attendance by day
        $attendanceByDay = $attendances->groupBy('date')
                                      ->map(function($items, $date) {
                                          return [
                                              'date' => $date,
                                              'present' => $items->where('status', 'Present')->count(),
                                              'absent' => $items->where('status', 'Absent')->count(),
                                              'late' => $items->where('status', 'Late')->count(),
                                          ];
                                      })
                                      ->values();
        
        // Attendance by student
        $attendanceByStudent = $students->map(function($student) use ($attendances, $totalDays) {
            $studentAttendances = $attendances->where('student_id', $student->id);
            
            return [
                'student_id' => $student->id,
                'name' => $student->name,
                'present' => $studentAttendances->where('status', 'Present')->count(),
                'absent' => $studentAttendances->where('status', 'Absent')->count(),
                'late' => $studentAttendances->where('status', 'Late')->count(),
                'not_marked' => $totalDays - $studentAttendances->count(),
                'attendance_rate' => $totalDays > 0 
                    ? round(($studentAttendances->where('status', 'Present')->count() / $totalDays) * 100, 1) 
                    : 0,
            ];
        });
        
        return response()->json([
            'summary' => [
                'total_students' => $students->count(),
                'total_days' => $totalDays,
                'total_expected_attendances' => $totalExpectedAttendances,
                'present' => $presentCount,
                'absent' => $absentCount,
                'late' => $lateCount,
                'not_marked' => $notMarkedCount,
                'attendance_rate' => $totalExpectedAttendances > 0 
                    ? round(($presentCount / $totalExpectedAttendances) * 100, 1) 
                    : 0,
            ],
            'by_day' => $attendanceByDay,
            'by_student' => $attendanceByStudent,
            'section' => $request->section ?? 'All',
            'date_range' => [
                'start' => $request->start_date,
                'end' => $request->end_date,
            ]
        ]);
    }
}