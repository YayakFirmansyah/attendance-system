<?php
// app/Http/Controllers/AttendanceController.php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\ClassModel;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        $classes = ClassModel::with('course')->where('status', 'active')->get();
        return view('attendance.index', compact('classes'));
    }

    public function classAttendance(ClassModel $class)
    {
        $today = today();
        $attendances = Attendance::with('student')
            ->where('class_id', $class->id)
            ->whereDate('date', $today)
            ->get();

        return view('attendance.class', compact('class', 'attendances', 'today'));
    }

    public function scanner(ClassModel $class)
    {
        return view('attendance.scanner', compact('class'));
    }

    public function processAttendance(Request $request)
    {
        try {
            // Use new Face Verification API
            $response = Http::timeout(30)->post(config('app.python_api_url') . '/api/verify-face', [
                'image' => $request->image
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                if ($result['success'] && !empty($result['results'])) {
                    $attendanceRecords = [];
                    
                    foreach ($result['results'] as $face) {
                        if ($face['verified']) {
                            // Record attendance for verified students
                            $attendanceRecord = $this->recordAttendance(
                                $face['student_name'],
                                $request->class_id,
                                $face['similarity'],
                                $face
                            );
                            
                            if ($attendanceRecord) {
                                $attendanceRecords[] = $attendanceRecord;
                            }
                        }
                    }
                    
                    // Enhance response with attendance records
                    $result['attendance_records'] = $attendanceRecords;
                    $result['total_verified'] = count($attendanceRecords);
                }
                
                return response()->json($result);
            }

            return response()->json([
                'success' => false,
                'message' => 'Verification API failed',
                'error_details' => $response->body()
            ], 500);

        } catch (\Exception $e) {
            Log::error('Face verification error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Face verification failed: ' . $e->getMessage()
            ], 500);
        }
    }

    private function recordAttendance($studentName, $classId, $similarity, $faceData)
    {
        try {
            // Find student by name (you might want to map student_id from verification result)
            $student = Student::where('name', $studentName)->first();
            
            if (!$student) {
                Log::warning("Student not found in database: {$studentName}");
                return null;
            }

            $today = Carbon::today();
            
            // Check if attendance already recorded today
            $existingAttendance = Attendance::where('student_id', $student->id)
                ->where('class_id', $classId)
                ->whereDate('date', $today)
                ->first();

            if ($existingAttendance) {
                Log::info("Attendance already recorded for {$studentName} today");
                return [
                    'student_name' => $studentName,
                    'status' => 'already_recorded',
                    'existing_time' => $existingAttendance->check_in,
                    'similarity' => $similarity
                ];
            }

            // Create new attendance record
            $attendance = Attendance::create([
                'student_id' => $student->id,
                'class_id' => $classId,
                'date' => $today,
                'check_in' => Carbon::now()->format('H:i:s'),
                'status' => 'present',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            // Log attendance with verification details
            AttendanceLog::create([
                'student_id' => $student->id,
                'class_id' => $classId,
                'timestamp' => Carbon::now(),
                'confidence_score' => $similarity,
                'verification_method' => 'face_verification',
                'bounding_box' => json_encode($faceData['bounding_box'] ?? null),
                'mtcnn_confidence' => $faceData['mtcnn_confidence'] ?? null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            Log::info("Attendance recorded for {$studentName} with {$similarity}% similarity");

            return [
                'student_name' => $studentName,
                'student_id' => $student->id,
                'attendance_id' => $attendance->id,
                'status' => 'recorded',
                'time' => $attendance->check_in,
                'similarity' => $similarity,
                'verification_threshold' => $faceData['verification_threshold'] ?? 0.75
            ];

        } catch (\Exception $e) {
            Log::error("Error recording attendance for {$studentName}: " . $e->getMessage());
            return null;
        }
    }

    public function addStudent(Request $request)
    {
        try {
            $request->validate([
                'student_name' => 'required|string|max:255',
                'nim' => 'required|string|unique:students,nim',
                'class' => 'required|string',
                'images' => 'required|array|min:3',
                'images.*' => 'required|string' // base64 images
            ]);

            // Add student to verification database via API
            $response = Http::timeout(60)->post(config('app.python_api_url') . '/api/add-student', [
                'student_name' => $request->student_name,
                'nim' => $request->nim,
                'class' => $request->class,
                'images' => $request->images
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                if ($result['success']) {
                    // Also create student in Laravel database
                    $student = Student::create([
                        'name' => $request->student_name,
                        'nim' => $request->nim,
                        'email' => $request->email ?? null,
                        'phone' => $request->phone ?? null,
                        'status' => 'active',
                        'face_encoding_count' => $result['student_info']['encoding_count'] ?? 0
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Student added successfully',
                        'student' => $student,
                        'verification_info' => $result['student_info']
                    ]);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to add student to verification system'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Add student error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error adding student: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getVerificationStats()
    {
        try {
            $response = Http::timeout(10)->get(config('app.python_api_url') . '/api/verification-stats');

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to get verification stats'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting stats: ' . $e->getMessage()
            ], 500);
        }
    }

    public function migrateToVerification()
    {
        try {
            $response = Http::timeout(120)->post(config('app.python_api_url') . '/api/migrate-to-verification');

            if ($response->successful()) {
                $result = $response->json();
                
                if ($result['success']) {
                    // Update students table with verification info
                    foreach ($result['migration_details'] ?? [] as $studentName => $details) {
                        Student::where('name', $studentName)->update([
                            'face_encoding_count' => $details['encoding_count'] ?? 0,
                            'verification_migrated' => true,
                            'updated_at' => Carbon::now()
                        ]);
                    }
                }
                
                return response()->json($result);
            }

            return response()->json([
                'success' => false,
                'message' => 'Migration failed'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Migration error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testVerification()
    {
        try {
            $response = Http::timeout(60)->post(config('app.python_api_url') . '/api/test-verification');

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'success' => false,
                'message' => 'Verification test failed'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Test error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reports()
    {
        $classes = ClassModel::with('course')->where('status', 'active')->get();
        return view('attendance.reports', compact('classes'));
    }

    public function generateReport(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $class = ClassModel::with('course')->findOrFail($request->class_id);
        
        $attendances = Attendance::with('student')
            ->where('class_id', $request->class_id)
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->orderBy('date', 'desc')
            ->get();

        $students = Student::whereHas('attendances', function($query) use ($request) {
            $query->where('class_id', $request->class_id)
                  ->whereBetween('date', [$request->start_date, $request->end_date]);
        })->get();

        // Add verification statistics to report
        $verificationStats = [];
        try {
            $response = Http::timeout(10)->get(config('app.python_api_url') . '/api/verification-stats');
            if ($response->successful()) {
                $verificationStats = $response->json();
            }
        } catch (\Exception $e) {
            Log::warning('Could not fetch verification stats for report');
        }

        return view('attendance.report-result', compact(
            'class', 
            'attendances', 
            'students', 
            'request', 
            'verificationStats'
        ));
    }

    // Legacy method - kept for backward compatibility
    public function retrainModel()
    {
        try {
            // For face verification, we don't need retraining
            // Instead, we can run a verification test
            $response = Http::timeout(60)->post(config('app.python_api_url') . '/api/test-verification');

            if ($response->successful()) {
                $result = $response->json();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Verification system tested successfully',
                    'test_results' => $result
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to test verification system'
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error testing verification system: ' . $e->getMessage()
            ], 500);
        }
    }
}