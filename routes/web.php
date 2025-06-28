<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AttendanceController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Students
Route::resource('students', StudentController::class);
Route::get('students/{student}/faces', [StudentController::class, 'manageFaces'])->name('students.faces');
Route::post('students/{student}/faces', [StudentController::class, 'uploadFaces'])->name('students.upload-faces');

// Attendance
Route::prefix('attendance')->name('attendance.')->group(function () {
    Route::get('/', [AttendanceController::class, 'index'])->name('index');
    Route::get('/class/{class}', [AttendanceController::class, 'classAttendance'])->name('class');
    Route::get('/scanner/{class}', [AttendanceController::class, 'scanner'])->name('scanner');
    Route::get('/reports', [AttendanceController::class, 'reports'])->name('reports');
    Route::post('/generate-report', [AttendanceController::class, 'generateReport'])->name('generate-report');
    Route::post('/attendance/add-student', [AttendanceController::class, 'addStudent']);
    Route::get('/attendance/verification-stats', [AttendanceController::class, 'getVerificationStats']);
    Route::post('/attendance/migrate-verification', [AttendanceController::class, 'migrateToVerification']);
    Route::post('/attendance/test-verification', [AttendanceController::class, 'testVerification']);
});

// API Routes for AJAX
Route::prefix('api')->name('api.')->group(function () {
    Route::post('attendance/process', [AttendanceController::class, 'processAttendance'])->name('attendance.process');
    Route::post('classifier/retrain', [AttendanceController::class, 'retrainModel'])->name('retrain');
    Route::get('status', [DashboardController::class, 'apiStatus'])->name('status');
});