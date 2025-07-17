<?php
// app/Http/Controllers/AttendanceController.php - FIXED JSON RESPONSE

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Attendance;
use App\Services\FaceRecognitionService;
use Illuminate\Http\Request;
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
        return view('attendance.scanner', compact('class', 'apiHealth'));
    }

    public function processAttendance(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'image' => 'required|string',
                'class_id' => 'required|exists:classes,id'
            ]);

            Log::info('Processing attendance request', [
                'class_id' => $validated['class_id'],
                'image_size' => strlen($validated['image']),
                'user_agent' => $request->header('User-Agent')
            ]);

            // Process face recognition
            $result = $this->faceRecognitionService->recognizeFace(
                $validated['image'],
                $validated['class_id'],
                $request->header('User-Agent')
            );

            // Always return JSON response
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'] ?? ($result['success'] ? 'Processing completed' : 'Processing failed'),
                'recognized_students' => $result['recognized_students'] ?? [],
                'total_faces_detected' => $result['total_faces_detected'] ?? 0,
                'total_recognized' => $result['total_recognized'] ?? 0,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation failed', ['errors' => $e->errors()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . collect($e->errors())->flatten()->first(),
                'recognized_students' => [],
                'total_faces_detected' => 0,
                'total_recognized' => 0,
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Attendance processing error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'System error occurred. Please try again.',
                'recognized_students' => [],
                'total_faces_detected' => 0,
                'total_recognized' => 0
            ], 500);
        }
    }

    public function getTodayAttendance(ClassModel $class)
    {
        try {
            $today = Carbon::today();
            $attendances = Attendance::with('student')
                ->where('class_id', $class->id)
                ->whereDate('date', $today)
                ->orderBy('check_in', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'attendances' => $attendances->map(function ($attendance) {
                    return [
                        'id' => $attendance->id,
                        'student_name' => $attendance->student->name,
                        'student_id' => $attendance->student->student_id,
                        'check_in' => $attendance->check_in,
                        'status' => $attendance->status,
                        'similarity_score' => $attendance->similarity_score,
                        'notes' => $attendance->notes
                    ];
                }),
                'total' => $attendances->count(),
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