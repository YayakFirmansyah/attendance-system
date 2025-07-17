<?php
// app/Services/FaceRecognitionService.php - FIXED ERROR HANDLING

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use Carbon\Carbon;

class FaceRecognitionService
{
    private $apiUrl;
    private $recentDetections = [];

    public function __construct()
    {
        $this->apiUrl = config('app.python_api_url', 'http://localhost:5000');
        $this->similarityThreshold = config('app.face_similarity_threshold', 0.2); // Default 50%
    }

    public function checkApiHealth()
    {
        try {
            $response = Http::timeout(10)->get($this->apiUrl . '/api/health');
            
            if ($response->successful()) {
                $data = $response->json();
                return [
                    'status' => 'healthy',
                    'data' => $data
                ];
            }
            
            return [
                'status' => 'error',
                'message' => 'API responded with status: ' . $response->status()
            ];
            
        } catch (\Exception $e) {
            Log::error('API Health Check Failed', [
                'error' => $e->getMessage(),
                'api_url' => $this->apiUrl
            ]);
            
            return [
                'status' => 'error',
                'message' => 'API connection failed: ' . $e->getMessage()
            ];
        }
    }

    public function recognizeFace($base64Image, $classId, $deviceInfo = null)
    {
        try {
            // Validate inputs
            if (empty($base64Image) || empty($classId)) {
                return $this->errorResponse('Invalid input parameters');
            }

            Log::info('Face recognition request', [
                'class_id' => $classId,
                'api_url' => $this->apiUrl,
                'image_size' => strlen($base64Image)
            ]);

            // Call Flask API dengan error handling yang lebih baik
            $response = Http::timeout(60)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->post($this->apiUrl . '/api/verify-face', [
                    'image' => $base64Image
                ]);

            // Check response status
            if (!$response->successful()) {
                $errorMsg = $this->parseErrorResponse($response);
                Log::error('Flask API Error', [
                    'status' => $response->status(),
                    'response_body' => $response->body(),
                    'error_message' => $errorMsg
                ]);
                
                return $this->errorResponse($errorMsg);
            }

            // Parse JSON response
            $result = $response->json();
            
            if (!$result || !isset($result['success'])) {
                return $this->errorResponse('Invalid API response format');
            }

            if (!$result['success']) {
                return $this->errorResponse($result['message'] ?? 'Face recognition failed');
            }

            // Process results
            return $this->processResults($result, $classId, $deviceInfo);

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('HTTP Request Exception', [
                'error' => $e->getMessage(),
                'api_url' => $this->apiUrl
            ]);
            return $this->errorResponse('Connection timeout - Flask API may be loading');
            
        } catch (\Exception $e) {
            Log::error('Face Recognition Service Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse('Service temporarily unavailable');
        }
    }

    private function parseErrorResponse($response)
    {
        $status = $response->status();
        $body = $response->body();
        
        // Check if response is HTML (common for server errors)
        if (strpos($body, '<!DOCTYPE') !== false || strpos($body, '<html') !== false) {
            switch ($status) {
                case 500:
                    return 'Flask API internal server error - check your model files';
                case 404:
                    return 'Flask API endpoint not found';
                case 502:
                    return 'Flask API is not running';
                case 503:
                    return 'Flask API service unavailable';
                default:
                    return "Flask API error (HTTP {$status})";
            }
        }
        
        // Try to parse JSON error
        try {
            $json = json_decode($body, true);
            if (isset($json['message'])) {
                return $json['message'];
            }
        } catch (\Exception $e) {
            // Ignore JSON parse errors
        }
        
        return "HTTP {$status} - " . substr($body, 0, 100);
    }

    private function processResults($apiResult, $classId, $deviceInfo)
    {
        $recognizedStudents = [];
        $today = Carbon::today();

        // Get today's attendance
        $todayAttendance = Attendance::where('class_id', $classId)
            ->whereDate('date', $today)
            ->pluck('student_id')
            ->toArray();

        foreach ($apiResult['results'] as $faceResult) {
            // Skip if not verified or low confidence (UBAH THRESHOLD JADI DYNAMIC)
            $similarity = $faceResult['similarity'] ?? 0;
            if (!($faceResult['verified'] ?? false) || $similarity < $this->similarityThreshold) {
                Log::info('Face rejected due to low confidence', [
                    'similarity' => $similarity,
                    'threshold' => $this->similarityThreshold,
                    'verified' => $faceResult['verified'] ?? false
                ]);
                continue;
            }

            $student = Student::where('name', $faceResult['student_name'])
                ->where('status', 'active')
                ->first();

            if (!$student) continue;

            // Skip if already attended today
            if (in_array($student->id, $todayAttendance)) {
                continue;
            }

            // Check duplicate detection cache
            $logKey = $classId . '_' . $student->id;
            $now = time();
            
            if (isset($this->recentDetections[$logKey])) {
                if ($now - $this->recentDetections[$logKey] < 30) {
                    continue;
                }
            }

            // Record attendance
            $attendance = Attendance::create([
                'student_id' => $student->id,
                'class_id' => $classId,
                'date' => $today,
                'check_in' => Carbon::now()->format('H:i:s'),
                'status' => 'present',
                'similarity_score' => $faceResult['similarity'] ?? 0,
                'notes' => 'Auto-recognized via face recognition'
            ]);

            // Record log
            AttendanceLog::create([
                'student_id' => $student->id,
                'class_id' => $classId,
                'timestamp' => Carbon::now(),
                'confidence_score' => $faceResult['similarity'] ?? 0,
                'is_verified' => true,
                'device_info' => $deviceInfo
            ]);

            // Update cache
            $this->recentDetections[$logKey] = $now;

            $recognizedStudents[] = [
                'student' => $student,
                'attendance' => $attendance,
                'confidence' => $faceResult['similarity'] ?? 0,
                'status' => 'new_attendance'
            ];
        }

        return [
            'success' => true,
            'recognized_students' => $recognizedStudents,
            'total_faces_detected' => count($apiResult['results']),
            'total_recognized' => count($recognizedStudents),
            'message' => count($recognizedStudents) > 0 ? 'Face recognition successful' : 'No new attendance recorded'
        ];
    }

    private function errorResponse($message)
    {
        return [
            'success' => false,
            'message' => $message,
            'recognized_students' => [],
            'total_faces_detected' => 0,
            'total_recognized' => 0
        ];
    }
}