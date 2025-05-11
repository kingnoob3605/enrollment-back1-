<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\HealthRecordController;
use App\Http\Controllers\DashboardController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Admin Dashboard
    Route::get('/admin/dashboard', [DashboardController::class, 'adminDashboard']);
    
    // Student Management
    Route::prefix('admin/students')->group(function () {
        Route::get('/', [StudentController::class, 'index']);
        Route::post('/', [StudentController::class, 'store']);
        Route::get('/{id}', [StudentController::class, 'show']);
        Route::put('/{id}', [StudentController::class, 'update']);
        Route::delete('/{id}', [StudentController::class, 'destroy']);
    });
    
    // Teacher Management
    Route::prefix('admin/teachers')->group(function () {
        Route::get('/', [TeacherController::class, 'index']);
        Route::post('/', [TeacherController::class, 'store']);
        Route::get('/{id}', [TeacherController::class, 'show']);
        Route::put('/{id}', [TeacherController::class, 'update']);
        Route::delete('/{id}', [TeacherController::class, 'destroy']);
    });
    
    // Student Metrics
    Route::get('/student-metrics', [StudentController::class, 'getMetrics']);
    
    // Teacher Metrics
    Route::get('/teacher-metrics', [TeacherController::class, 'getMetrics']);
    
    // Teacher Dashboard
    Route::get('/teacher/dashboard', [DashboardController::class, 'teacherDashboard']);
    
    // Attendance Management
    Route::prefix('attendance')->group(function () {
        Route::get('/', [AttendanceController::class, 'index']);
        Route::post('/', [AttendanceController::class, 'store']);
        Route::post('/multiple', [AttendanceController::class, 'storeMultiple']);
        Route::get('/section', [AttendanceController::class, 'getBySection']);
        Route::get('/report', [AttendanceController::class, 'getReport']);
    });
    
    // Health Records
    Route::prefix('health-records')->group(function () {
        Route::post('/', [HealthRecordController::class, 'store']);
        Route::get('/student/{id}', [HealthRecordController::class, 'getByStudent']);
        Route::get('/report', [HealthRecordController::class, 'getReport']);
    });
});