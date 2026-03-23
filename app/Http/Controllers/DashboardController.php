<?php
// app/Http/Controllers/DashboardController.php - FIXED

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\ClassModel;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user && $user->role === 'admin') {
            return $this->adminDashboard();
        } else {
            return $this->dosenDashboard();
        }
    }

    private function adminDashboard()
    {
        Carbon::setLocale('id');
        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();

        // Admin Statistics
        $totalStudents = Student::where('status', 'active')->count();
        $totalUsers = User::where('status', 'active')->count();
        $totalClasses = ClassModel::where('status', 'active')->count();
        $todayAttendances = Attendance::whereDate('date', $today)->count();
        $todayLogs = AttendanceLog::whereDate('timestamp', $today)->count() ?? 0;

        $todayAttendanceList = Attendance::with(['student:id,name', 'classModel.course:id,course_name'])
            ->whereDate('date', $today)
            ->orderByDesc('check_in')
            ->limit(50)
            ->get();

        // Recent activity
        $recentLogs = AttendanceLog::with(['student', 'classModel.course'])
            ->whereDate('timestamp', $today)
            ->orderBy('timestamp', 'desc')
            ->limit(10)
            ->get() ?? collect();

        // FIXED: Today's classes dengan proper eager loading
        $currentDay = strtolower($now->format('l'));
        $todayClasses = ClassModel::with(['course.lecturer', 'room', 'cohort'])
            ->where('status', 'active')
            ->where('day', $currentDay)
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
            'todayAttendanceList',
            'todayLogs',
            'recentLogs',
            'todayClasses',
            'classAttendanceStats',
            'now'
        ));
    }

    private function dosenDashboard()
    {
        Carbon::setLocale('id');
        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();
        $dosenId = Auth::id();
        $currentDay = strtolower($now->format('l'));

        $todayClasses = ClassModel::with(['course', 'room', 'cohort'])
            ->where('status', 'active')
            ->where('day', $currentDay)
            ->whereHas('course', function ($query) use ($dosenId) {
                $query->where('lecturer_id', $dosenId);
            })
            ->orderBy('start_time')
            ->get();

        $todayClassIds = $todayClasses->pluck('id');

        $todayAttendances = Attendance::whereDate('date', $today)
            ->whereIn('class_id', $todayClassIds)
            ->count();

        $todayLogs = AttendanceLog::whereDate('timestamp', $today)
            ->whereIn('class_id', $todayClassIds)
            ->count() ?? 0;

        $todayAttendanceList = Attendance::with(['student:id,name', 'classModel.course:id,course_name'])
            ->whereDate('date', $today)
            ->whereIn('class_id', $todayClassIds)
            ->orderByDesc('check_in')
            ->limit(50)
            ->get();

        // Recent activity from classes
        $recentLogs = AttendanceLog::with(['student', 'classModel.course'])
            ->whereDate('timestamp', $today)
            ->whereIn('class_id', $todayClassIds)
            ->orderBy('timestamp', 'desc')
            ->limit(5)
            ->get() ?? collect();

        return view('dashboard.dosen', compact(
            'todayAttendances',
            'todayAttendanceList',
            'todayLogs',
            'todayClasses',
            'recentLogs',
            'now'
        ));
    }

    public function mySchedules()
    {
        $dosenId = Auth::id();
        
        $classes = ClassModel::with(['course.lecturer', 'room', 'cohort'])
            ->where('status', 'active')
            ->whereHas('course', function ($query) use ($dosenId) {
                $query->where('lecturer_id', $dosenId);
            })
            ->orderByRaw("FIELD(day, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
            ->orderBy('start_time')
            ->get();
            
        return view('dosen.schedules', compact('classes'));
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
