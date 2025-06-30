<?php
// app/Http/Controllers/AttendanceController.php - CLEANED VERSION

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\ClassModel;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Models\AttendanceLog;

class AttendanceController extends Controller
{
    public function index()
    {
        $classes = ClassModel::with('course')->where('status', 'active')->get();
        return view('attendance.index', compact('classes'));
    }

    public function scanner(ClassModel $class)
    {
        return view('attendance.scanner', compact('class'));
    }

    /**
     * Process attendance with complete logging
     */
    public function processAttendance(Request $request)
    {
        try {
            // Validate input
            if (empty($request->image)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No image provided'
                ], 400);
            }

            if (empty($request->class_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No class ID provided'
                ], 400);
            }

            // Check API URL configuration
            $apiUrl = config('app.python_api_url');
            if (!$apiUrl) {
                Log::error('Python API URL not configured');
                return response()->json([
                    'success' => false,
                    'message' => 'Python API URL not configured'
                ], 500);
            }

            Log::info('Processing attendance request', [
                'class_id' => $request->class_id,
                'image_size' => strlen($request->image ?? ''),
                'device_info' => $request->device_info
            ]);

            // Call face verification API
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->post($apiUrl . '/api/verify-face', [
                    'image' => $request->image
                ]);

            if ($response->successful()) {
                $result = $response->json();
                
                Log::info('API response received', [
                    'success' => $result['success'] ?? false,
                    'results_count' => count($result['results'] ?? [])
                ]);
                
                // Save captured image and log all detections
                $capturedImagePath = $this->saveCapturedImage($request->image);
                
                if ($result['success'] && !empty($result['results'])) {
                    $attendanceRecords = [];
                    
                    foreach ($result['results'] as $faceResult) {
                        // Log every face detection (verified or not)
                        $this->logFaceDetection(
                            $faceResult,
                            $request->class_id,
                            $capturedImagePath,
                            $request->device_info
                        );
                        
                        // Record attendance only for verified faces
                        if ($faceResult['verified'] ?? false) {
                            $attendanceRecord = $this->recordAttendance(
                                $faceResult['student_name'],
                                $request->class_id,
                                $faceResult['similarity'] ?? 0,
                                $capturedImagePath
                            );
                            
                            if ($attendanceRecord) {
                                $attendanceRecords[] = $attendanceRecord;
                            }
                        }
                    }
                    
                    $result['attendance_records'] = $attendanceRecords;
                    $result['total_recorded'] = count($attendanceRecords);
                } else {
                    // Log failed detection
                    $this->logFailedDetection(
                        $request->class_id,
                        $capturedImagePath,
                        $request->device_info,
                        $result['message'] ?? 'No faces detected'
                    );
                }
                
                return response()->json($result);
            }

            // Log API failure
            Log::error('Python API request failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Face verification API failed',
                'error_details' => [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]
            ], 500);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Connection error to Python API: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Cannot connect to Python API. Please check if the Python service is running.'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Unexpected error in processAttendance: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'System error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add new student
     */
    public function addStudent(Request $request)
    {
        try {
            $request->validate([
                'student_name' => 'required|string|max:255',
                'nim' => 'required|string|unique:students,nim',
                'class' => 'required|string',
                'images' => 'required|array|min:3',
                'images.*' => 'required|string'
            ]);

            $response = Http::timeout(60)->post(config('app.python_api_url') . '/api/add-student', [
                'student_name' => $request->student_name,
                'nim' => $request->nim,
                'class' => $request->class,
                'images' => $request->images
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                if ($result['success']) {
                    $student = Student::create([
                        'name' => $request->student_name,
                        'nim' => $request->nim,
                        'email' => $request->email ?? null,
                        'phone' => $request->phone ?? null,
                        'status' => 'active'
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Student added successfully',
                        'student' => $student
                    ]);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to add student'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Add student error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error adding student'
            ], 500);
        }
    }

    /**
     * Health check for Python API
     */
    public function checkApiHealth()
    {
        try {
            $response = Http::timeout(10)->get(config('app.python_api_url') . '/api/health');

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'success' => false,
                'message' => 'API health check failed'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'API connection error'
            ], 500);
        }
    }

    public function testApiConnection()
    {
        try {
            $apiUrl = config('app.python_api_url');
            
            if (!$apiUrl) {
                return response()->json([
                    'success' => false,
                    'message' => 'Python API URL not configured in .env'
                ]);
            }

            // Test health endpoint
            $response = Http::timeout(10)->get($apiUrl . '/api/health');
            
            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'success' => true,
                    'message' => 'Python API connection successful',
                    'api_url' => $apiUrl,
                    'api_data' => $data
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Python API responded with error',
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot connect to Python API: ' . $e->getMessage(),
                'api_url' => config('app.python_api_url')
            ]);
        }
    }

    public function classAttendance(ClassModel $class)
    {
        $today = today();
        $attendances = Attendance::with('student')
            ->where('class_id', $class->id)
            ->whereDate('date', $today)
            ->orderBy('check_in', 'desc')
            ->get();

        return view('attendance.class', compact('class', 'attendances', 'today'));
    }

    /**
     * Get today's attendance for AJAX
     */
    public function getTodayAttendance(ClassModel $class)
    {
        try {
            $today = today();
            $attendances = Attendance::with('student')
                ->where('class_id', $class->id)
                ->whereDate('date', $today)
                ->orderBy('check_in', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'attendances' => $attendances,
                'total' => $attendances->count(),
                'date' => $today->format('Y-m-d')
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading today attendance: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading attendance data'
            ], 500);
        }
    }

    /**
     * Save captured image to storage
     */
    private function saveCapturedImage($base64Image)
    {
        try {
            // Remove data URL prefix if present
            if (strpos($base64Image, ',') !== false) {
                $base64Image = explode(',', $base64Image)[1];
            }
            
            $imageData = base64_decode($base64Image);
            $fileName = 'attendance_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.jpg';
            $path = 'attendance_logs/' . $fileName;
            
            Storage::disk('public')->put($path, $imageData);
            
            return $fileName;
            
        } catch (\Exception $e) {
            Log::error('Error saving captured image: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Log face detection (both verified and unverified)
     */
    private function logFaceDetection($faceResult, $classId, $capturedImagePath, $deviceInfo)
    {
        try {
            $studentId = null;
            
            // Find student if verified
            if ($faceResult['verified'] ?? false) {
                $student = Student::where('name', $faceResult['student_name'])->first();
                $studentId = $student ? $student->id : null;
            }
            
            AttendanceLog::create([
                'student_id' => $studentId,
                'class_id' => $classId,
                'timestamp' => now(),
                'captured_image' => $capturedImagePath,
                'confidence_score' => $faceResult['similarity'] ?? 0,
                'detected_faces' => json_encode([
                    'face_id' => $faceResult['face_id'] ?? 0,
                    'student_name' => $faceResult['student_name'] ?? null,
                    'verified' => $faceResult['verified'] ?? false,
                    'similarity' => $faceResult['similarity'] ?? 0,
                    'mtcnn_confidence' => $faceResult['mtcnn_confidence'] ?? 0,
                    'bounding_box' => $faceResult['bounding_box'] ?? null,
                    'top_matches' => $faceResult['top_matches'] ?? []
                ]),
                'device_info' => $deviceInfo
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error logging face detection: ' . $e->getMessage());
        }
    }

    /**
     * Log failed detection
     */
    private function logFailedDetection($classId, $capturedImagePath, $deviceInfo, $errorMessage)
    {
        try {
            AttendanceLog::create([
                'student_id' => null,
                'class_id' => $classId,
                'timestamp' => now(),
                'captured_image' => $capturedImagePath,
                'confidence_score' => 0,
                'detected_faces' => json_encode([
                    'error' => $errorMessage,
                    'faces_count' => 0
                ]),
                'device_info' => $deviceInfo
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error logging failed detection: ' . $e->getMessage());
        }
    }

    /**
     * Record attendance for verified student
     */
    private function recordAttendance($studentName, $classId, $similarity, $capturedImagePath = null)
    {
        try {
            $student = Student::where('name', $studentName)->first();
            
            if (!$student) {
                Log::warning("Student not found: {$studentName}");
                return null;
            }

            // Check if already recorded today
            $today = today();
            $existing = Attendance::where('student_id', $student->id)
                ->where('class_id', $classId)
                ->whereDate('date', $today)
                ->first();

            if ($existing) {
                Log::info("Attendance already recorded for {$studentName} today");
                return [
                    'student_name' => $studentName,
                    'student_id' => $student->id,
                    'status' => 'already_recorded',
                    'existing_time' => $existing->check_in,
                    'similarity' => $similarity
                ];
            }

            $attendance = Attendance::create([
                'student_id' => $student->id,
                'class_id' => $classId,
                'date' => $today,
                'check_in' => now(),
                'status' => 'present',
                'similarity_score' => $similarity,
                'notes' => $capturedImagePath ? "Image: {$capturedImagePath}" : null
            ]);

            Log::info("Attendance recorded for {$studentName} with {$similarity}% similarity");

            return [
                'student_name' => $studentName,
                'student_id' => $student->id,
                'attendance_id' => $attendance->id,
                'status' => 'recorded',
                'time' => $attendance->check_in,
                'similarity' => $similarity
            ];

        } catch (\Exception $e) {
            Log::error("Error recording attendance for {$studentName}: " . $e->getMessage());
            return null;
        }
    }
}