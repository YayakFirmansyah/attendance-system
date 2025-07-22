<?php
// app/Http/Controllers/DashboardController.php - Role-Based Update

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\ClassModel;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Role-based dashboard
        if ($user->isAdmin()) {
            return $this->adminDashboard();
        } else {
            return $this->dosenDashboard();
        }
    }

    private function adminDashboard()
    {
        // Set timezone
        Carbon::setLocale('id');
        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();
        
        // Admin Statistics
        $totalStudents = Student::where('status', 'active')->count();
        $totalUsers = User::where('status', 'active')->count();
        $totalClasses = ClassModel::where('status', 'active')->count();
        $todayAttendances = Attendance::whereDate('date', $today)->count();
        $todayLogs = AttendanceLog::whereDate('timestamp', $today)->count() ?? 0;

        // Recent activity
        $recentLogs = AttendanceLog::with(['student', 'classModel.course'])
            ->whereDate('timestamp', $today)
            ->orderBy('timestamp', 'desc')
            ->limit(10)
            ->get() ?? collect();

        // Today's classes
        $currentDay = strtolower($now->format('l'));
        $todayClasses = ClassModel::with(['course', 'room'])
            ->where('status', 'active')
            ->where('day', 'like', '%' . $currentDay . '%')
            ->orderBy('start_time')
            ->get();

        // Class attendance stats
        $classAttendanceStats = [];
        foreach ($todayClasses as $class) {
            $attendanceCount = Attendance::where('class_id', $class->id)
                ->whereDate('date', $today)
                ->count();
                
            $classAttendanceStats[] = [
                'class' => $class,
                'attendance_count' => $attendanceCount,
                'capacity' => $class->capacity ?? 0,
                'percentage' => isset($class->capacity) && $class->capacity > 0 ? 
                    round(($attendanceCount / $class->capacity) * 100, 1) : 0
            ];
        }

        return view('dashboard.admin', compact(
            'totalStudents',
            'totalUsers',
            'totalClasses', 
            'todayAttendances',
            'todayLogs',
            'recentLogs',
            'todayClasses',
            'classAttendanceStats',
            'now'
        ));
    }

    private function dosenDashboard()
    {
        // Set timezone
        Carbon::setLocale('id');
        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();
        
        // Dosen Statistics (filtered by their classes)
        // TODO: Add relationship between User and Classes for dosen
        // For now, show general stats
        $todayAttendances = Attendance::whereDate('date', $today)->count();
        $todayLogs = AttendanceLog::whereDate('timestamp', $today)->count() ?? 0;

        // Today's classes (all for now, filter by dosen later)
        $currentDay = strtolower($now->format('l'));
        $todayClasses = ClassModel::with(['course', 'room'])
            ->where('status', 'active')
            ->where('day', 'like', '%' . $currentDay . '%')
            ->orderBy('start_time')
            ->get();

        // Recent activity from classes
        $recentLogs = AttendanceLog::with(['student', 'classModel.course'])
            ->whereDate('timestamp', $today)
            ->orderBy('timestamp', 'desc')
            ->limit(5)
            ->get() ?? collect();

        return view('dashboard.dosen', compact(
            'todayAttendances',
            'todayLogs',
            'todayClasses',
            'recentLogs',
            'now'
        ));
    }

    public function apiStatus()
    {
        try {
            $apiUrl = config('app.python_api_url', 'http://localhost:5000');
            
            $response = Http::timeout(5)->get($apiUrl . '/api/health');
            
            if ($response->successful()) {
                return response()->json([
                    'status' => 'connected',
                    'data' => $response->json()
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'API responded with status: ' . $response->status()
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Connection failed: ' . $e->getMessage()
            ], 500);
        }
    }
}