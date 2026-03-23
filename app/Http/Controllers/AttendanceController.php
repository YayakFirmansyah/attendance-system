<?php
// app/Http/Controllers/AttendanceController.php - FIXED JSON RESPONSE

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Attendance;
use App\Models\Setting;
use App\Services\FaceRecognitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    private $faceRecognitionService;

    public function __construct(FaceRecognitionService $faceRecognitionService)
    {
        $this->faceRecognitionService = $faceRecognitionService;
    }

    public function index()
    {
        $classes = ClassModel::with('course')->where('status', 'active')->get();
        return view('attendance.index', compact('classes'));
    }

    public function scanner(ClassModel $class)
    {
        $apiHealth = $this->faceRecognitionService->checkApiHealth();
        $activeSession = \App\Models\AttendanceSession::where('class_id', $class->id)
            ->where('status', 'active')
            ->first();

        $confidenceThreshold = (float) Setting::getValue(
            'face_similarity_threshold',
            config('app.face_similarity_threshold', 0.5)
        );

        return view('attendance.scanner', compact('class', 'apiHealth', 'activeSession', 'confidenceThreshold'));
    }

    public function markAttendance(Request $request)
    {
        try {
            $validated = $request->validate([
                'session_id' => 'required|exists:attendance_sessions,id',
                'students' => 'required|array',
                'students.*.student_name' => 'required|string',
                'students.*.confidence' => 'required|numeric'
            ]);

            $results = app(\App\Services\AttendanceService::class)
                ->recordAttendance($validated['session_id'], $validated['students']);

            return response()->json([
                'success' => true,
                'message' => 'Attendance recorded successfully',
                'results' => $results
            ]);
        } catch (\Exception $e) {
            Log::error('Attendance marking error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'System error occurred. Please try again.',
                'results' => []
            ], 500);
        }
    }

    public function openSession(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id'
        ]);

        $activeSession = \App\Models\AttendanceSession::where('class_id', $validated['class_id'])
            ->where('status', 'active')
            ->first();

        if ($activeSession) {
            return response()->json(['success' => false, 'message' => 'Ada sesi yang masih aktif.']);
        }

        $session = \App\Models\AttendanceSession::create([
            'class_id' => $validated['class_id'],
            'status' => 'active',
            'created_by' => Auth::id()
        ]);

        return response()->json(['success' => true, 'session_id' => $session->id]);
    }

    public function closeSession(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|exists:attendance_sessions,id'
        ]);

        $session = \App\Models\AttendanceSession::findOrFail($validated['session_id']);

        if ($session->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Sesi sudah ditutup.']);
        }

        try {
            app(\App\Services\AttendanceService::class)->autoMarkAbsent($session);
            return response()->json(['success' => true, 'message' => 'Sesi absensi ditutup dan data alpha disimpan.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menutup sesi.'], 500);
        }
    }

    public function getTodayAttendance(ClassModel $class)
    {
        try {
            $today = Carbon::today();

            // Get all enrolled students
            $enrollments = \App\Models\ClassEnrollment::with('student')
                ->where('class_id', $class->id)
                ->where('status', 'active')
                ->get();

            // Get today's attendance records
            $attendances = Attendance::where('class_id', $class->id)
                ->whereDate('date', $today)
                ->get()
                ->keyBy('student_id');

            $result = $enrollments->map(function ($enrollment) use ($attendances) {
                $student = $enrollment->student;
                $attendance = $attendances->get($student->id);

                return [
                    'id' => $attendance ? $attendance->id : null,
                    'student_name' => $student->name,
                    'student_id' => $student->student_id,
                    'check_in' => $attendance ? $attendance->check_in : null,
                    'status' => $attendance ? $attendance->status : 'absent',
                    'similarity_score' => $attendance ? $attendance->similarity_score : 0,
                    'notes' => $attendance ? $attendance->notes : null
                ];
            });

            return response()->json([
                'success' => true,
                'attendances' => $result,
                'total_enrolled' => $enrollments->count(),
                'total_present' => $attendances->count(),
                'date' => $today->format('Y-m-d')
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading attendance', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load attendance data',
                'attendances' => [],
                'total' => 0
            ], 500);
        }
    }

    public function reports()
    {
        $classes = ClassModel::with('course')->where('status', 'active')->get();
        return view('attendance.reports', compact('classes'));
    }
}
