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
use App\Http\Controllers\CohortController;
use App\Http\Controllers\SettingController;

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
        Route::get('classes/{class}/enrollments', [\App\Http\Controllers\ClassEnrollmentController::class, 'manage'])->name('classes.enrollments');
        Route::post('classes/{class}/enrollments', [\App\Http\Controllers\ClassEnrollmentController::class, 'store'])->name('classes.enrollments.store');
        Route::delete('classes/{class}/enrollments/{student}', [\App\Http\Controllers\ClassEnrollmentController::class, 'drop'])->name('classes.enrollments.drop');

        // Students management
        Route::resource('students', StudentController::class);
        Route::get('students/{student}/faces', [StudentController::class, 'manageFaces'])->name('students.faces');
        Route::post('students/{student}/faces', [StudentController::class, 'uploadFaces'])->name('students.upload-faces');

        // Cohorts management
        Route::resource('cohorts', CohortController::class);
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::get('students/{student}/faces', [StudentController::class, 'manageFaces'])->name('students.faces');
        Route::post('students/{student}/faces', [StudentController::class, 'uploadFaces'])->name('students.upload-faces');
    });

    // DOSEN ONLY ROUTES - Attendance Management
    Route::middleware(['role:dosen'])->group(function () {
        // Attendance scanning - HANYA DOSEN
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/scanner/{class}', [AttendanceController::class, 'scanner'])->name('scanner');
        });

        // Attendance CRUD - HANYA DOSEN
        Route::prefix('attendance/manage')->name('attendance.')->group(function () {
            Route::get('/', [AttendanceController::class, 'index'])->name('index');
            Route::get('/class/{class}', [AttendanceController::class, 'classAttendance'])->name('class');
            Route::get('/create', [AttendanceController::class, 'create'])->name('create');
            Route::post('/', [AttendanceController::class, 'store'])->name('store');
            Route::get('/{attendance}/edit', [AttendanceController::class, 'edit'])->name('edit');
            Route::put('/{attendance}', [AttendanceController::class, 'update'])->name('update');
            Route::delete('/{attendance}', [AttendanceController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-update', [AttendanceController::class, 'bulkUpdate'])->name('bulk-update');
        });
    });

    // ADMIN & DOSEN ROUTES - Reports (Read Only for Admin)
    Route::middleware(['role:admin,dosen'])->group(function () {
        // Reports - Both can view
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [AttendanceController::class, 'reports'])->name('index');
            Route::get('/class/{class}', [AttendanceController::class, 'classReport'])->name('class');
            Route::get('/student/{student}', [AttendanceController::class, 'studentReport'])->name('student');
            Route::post('/generate', [AttendanceController::class, 'generateReport'])->name('generate');
            Route::get('/export/{class}', [AttendanceController::class, 'exportReport'])->name('export');
        });

        // Attendance History - View only for both
        Route::prefix('attendance/history')->name('attendance.history.')->group(function () {
            Route::get('/', [AttendanceHistoryController::class, 'index'])->name('index');
            Route::get('/{attendance}', [AttendanceHistoryController::class, 'show'])->name('show');
        });
    });

    // API Routes for AJAX
    Route::prefix('api')->name('api.')->group(function () {
        // Dashboard status API - Both Admin & Dosen
        Route::get('status', [DashboardController::class, 'apiStatus'])->name('status');

        // ADMIN ONLY API
        Route::middleware(['role:admin'])->group(function () {
            // Face Registration API - Admin only
            Route::get('model-info', [StudentController::class, 'getModelInfo'])->name('model-info');
            Route::get('students/{student}/face-status', [StudentController::class, 'getFaceStatus'])->name('students.face-status');
            Route::post('students/{student}/refresh-face-status', [StudentController::class, 'refreshFaceStatus'])->name('students.refresh-face-status');
            Route::post('refresh-model-cache', [StudentController::class, 'refreshModelCache'])->name('refresh-model-cache');
            Route::get('flask-status', [StudentController::class, 'getApiStatus'])->name('flask-status');
        });

        // DOSEN ONLY API
        Route::middleware(['role:dosen'])->group(function () {
            // Attendance processing - Dosen only
            Route::post('attendance/mark', [AttendanceController::class, 'markAttendance'])->name('attendance.mark');
            Route::post('attendance/session/open', [AttendanceController::class, 'openSession'])->name('attendance.session.open');
            Route::post('attendance/session/close', [AttendanceController::class, 'closeSession'])->name('attendance.session.close');
            Route::get('attendance/today/{class}', [AttendanceController::class, 'getTodayAttendance'])->name('attendance.today');
            Route::get('attendance/logs/{class}', [AttendanceController::class, 'getAttendanceLogs'])->name('attendance.logs');
        });

        // BOTH ADMIN & DOSEN API
        Route::middleware(['role:admin,dosen'])->group(function () {
            // Reports API
            Route::get('reports/class-statistics/{class}', [AttendanceController::class, 'getClassStatistics'])->name('reports.class-stats');
            Route::get('reports/student-summary/{student}', [AttendanceController::class, 'getStudentSummary'])->name('reports.student-summary');
        });
    });
});
