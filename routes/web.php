<?php
// routes/web.php - UPDATE dengan UserController

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController; // ← TAMBAH INI

// AUTH ROUTES
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// PROTECTED ROUTES - butuh login
Route::middleware(['auth'])->group(function () {
    
    // DASHBOARD - bisa diakses admin & dosen
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // ADMIN ONLY ROUTES
    Route::middleware(['role:admin'])->group(function () {
        // Students management
        Route::resource('students', StudentController::class);
        Route::get('students/{student}/faces', [StudentController::class, 'manageFaces'])->name('students.faces');
        Route::post('students/{student}/faces', [StudentController::class, 'uploadFaces'])->name('students.upload-faces');
        
        // User management (admin & dosen)
        Route::resource('users', UserController::class); // ← TAMBAH INI
    });
    
    // ADMIN & DOSEN ROUTES  
    Route::middleware(['role:admin,dosen'])->group(function () {
        // Attendance routes
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
    });
    
    // API Routes for AJAX
    Route::prefix('api')->name('api.')->group(function () {
        Route::post('attendance/process', [AttendanceController::class, 'processAttendance'])->name('attendance.process');
        Route::get('attendance/today/{class}', [AttendanceController::class, 'getTodayAttendance'])->name('attendance.today');
        Route::get('attendance/logs/{class}', [AttendanceController::class, 'getAttendanceLogs'])->name('attendance.logs');
        Route::get('status', [DashboardController::class, 'apiStatus'])->name('status');
    });
});