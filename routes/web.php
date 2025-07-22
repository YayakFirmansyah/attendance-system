<?php
// routes/web.php - COMPLETE ROUTES WITH ATTENDANCE HISTORY

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceHistoryController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ClassController;

// AUTH ROUTES
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// PROTECTED ROUTES - butuh login
Route::middleware(['auth'])->group(function () {

    // DASHBOARD - accessible by admin & dosen
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ADMIN ONLY ROUTES
    Route::middleware(['role:admin'])->group(function () {
        // User management
        Route::resource('users', UserController::class);

        // Room management
        Route::resource('rooms', RoomController::class);

        // Course management
        Route::resource('courses', CourseController::class);

        // Class schedule management
        Route::resource('classes', ClassController::class);

        // Students management
        Route::resource('students', StudentController::class);
        Route::get('students/{student}/faces', [StudentController::class, 'manageFaces'])->name('students.faces');
        Route::post('students/{student}/faces', [StudentController::class, 'uploadFaces'])->name('students.upload-faces');
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
        });

        // Attendance History routes
        Route::prefix('attendance/history')->name('attendance.history.')->group(function () {
            Route::get('/', [AttendanceHistoryController::class, 'index'])->name('index');
            Route::get('/create', [AttendanceHistoryController::class, 'create'])->name('create');
            Route::post('/', [AttendanceHistoryController::class, 'store'])->name('store');
            Route::get('/{attendance}', [AttendanceHistoryController::class, 'show'])->name('show');
            Route::get('/{attendance}/edit', [AttendanceHistoryController::class, 'edit'])->name('edit');
            Route::put('/{attendance}', [AttendanceHistoryController::class, 'update'])->name('update');
            Route::post('/bulk-edit', [AttendanceHistoryController::class, 'bulkEdit'])->name('bulk-edit');
            Route::get('/reports/generate', [AttendanceHistoryController::class, 'reports'])->name('reports');
        });
    });

    // API Routes for AJAX
    Route::prefix('api')->name('api.')->group(function () {
        // Face Registration API - menggunakan StudentController
        Route::get('model-info', [StudentController::class, 'getModelInfo'])->name('model-info');
        Route::get('students/{student}/face-status', [StudentController::class, 'getFaceStatus'])->name('students.face-status');
        Route::post('refresh-model-cache', [StudentController::class, 'refreshModelCache'])->name('refresh-model-cache');

        // Attendance API routes
        Route::post('attendance/process', [AttendanceController::class, 'processAttendance'])->name('attendance.process');
        Route::get('attendance/today/{class}', [AttendanceController::class, 'getTodayAttendance'])->name('attendance.today');
        Route::get('attendance/logs/{class}', [AttendanceController::class, 'getAttendanceLogs'])->name('attendance.logs');

        // Dashboard status API
        Route::get('status', [DashboardController::class, 'apiStatus'])->name('status');

        // Student face status API
        Route::post('students/{student}/refresh-face-status', [StudentController::class, 'refreshFaceStatus'])->name('students.refresh-face-status');
        Route::get('flask-status', [StudentController::class, 'getApiStatus'])->name('flask-status');
    });
});