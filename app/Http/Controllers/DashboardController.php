<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\ClassModel;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        $today = today();
        
        // Statistics
        $totalStudents = Student::where('status', 'active')->count();
        $totalClasses = ClassModel::where('status', 'active')->count();
        $todayAttendances = Attendance::whereDate('date', $today)->count();
        $todayLogs = AttendanceLog::whereDate('timestamp', $today)->count();

        // Recent activity
        $recentLogs = AttendanceLog::with(['student', 'classModel.course'])
            ->whereDate('timestamp', $today)
            ->orderBy('timestamp', 'desc')
            ->limit(10)
            ->get();

        // Today's classes
        $todayClasses = ClassModel::with('course')
            ->where('status', 'active')
            ->where('day', strtolower(Carbon::now()->format('l')))
            ->orderBy('start_time')
            ->get();

        // Attendance stats by class
        $classAttendanceStats = [];
        foreach ($todayClasses as $class) {
            $attendanceCount = Attendance::where('class_id', $class->id)
                ->whereDate('date', $today)
                ->count();
                
            $classAttendanceStats[] = [
                'class' => $class,
                'attendance_count' => $attendanceCount,
                'percentage' => $class->capacity > 0 ? round(($attendanceCount / $class->capacity) * 100, 1) : 0
            ];
        }

        return view('dashboard', compact(
            'totalStudents',
            'totalClasses', 
            'todayAttendances',
            'todayLogs',
            'recentLogs',
            'todayClasses',
            'classAttendanceStats'
        ));
    }

    public function apiStatus()
    {
        try {
            $apiUrl = config('app.python_api_url');
            
            if (!$apiUrl) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Python API URL not configured'
                ], 500);
            }

            Log::info('Checking API status at: ' . $apiUrl);
            
            $response = Http::timeout(5)->get($apiUrl . '/api/health');
            
            if ($response->successful()) {
                return response()->json([
                    'status' => 'connected',
                    'data' => $response->json()
                ]);
            } else {
                Log::error('API responded with status: ' . $response->status());
                return response()->json([
                    'status' => 'error',
                    'message' => 'API responded with status: ' . $response->status()
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('API connection failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Connection failed: ' . $e->getMessage()
            ], 500);
        }
    }
}