<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\FaceEncoding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class StudentController extends Controller
{
    private $apiUrl;

    public function __construct()
    {
        $this->apiUrl = config('app.python_api_url', 'http://localhost:5000');
    }

    /**
     * Optimized index method with better performance
     */
    public function index(Request $request)
    {
        $query = Student::select([
            'id', 'student_id', 'name', 'email', 'program_study',
            'faculty', 'semester', 'phone', 'status', 'profile_photo'
        ]);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('semester') && $request->semester) {
            $query->where('semester', $request->semester);
        }

        if ($request->has('program') && $request->program) {
            $query->where('program_study', 'like', "%{$request->program}%");
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $students = $query->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('students.index', compact('students'));
    }

    /**
     * Optimized show method
     */
    public function show(Student $student)
    {
        $student->load([
            'attendances' => function ($query) {
                $query->with('classModel.course')
                    ->orderBy('date', 'desc')
                    ->limit(10);
            }
        ]);
        return view('students.show', compact('student'));
    }

    public function create()
    {
        return view('students.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|string|unique:students',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:students',
            'program_study' => 'required|string|max:255',
            'faculty' => 'required|string|max:255',
            'semester' => 'required|integer|min:1|max:8',
            'phone' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive,graduated',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('students', 'public');
            $validated['profile_photo'] = basename($path);
        }

        $student = Student::create($validated);

        return redirect()->route('students.show', $student)
            ->with('success', 'Student created successfully.');
    }

    public function edit(Student $student)
    {
        return view('students.edit', compact('student'));
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'student_id' => 'required|string|unique:students,student_id,' . $student->id,
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email,' . $student->id,
            'program_study' => 'required|string|max:255',
            'faculty' => 'required|string|max:255',
            'semester' => 'required|integer|min:1|max:8',
            'phone' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive,graduated',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('profile_photo')) {
            if ($student->profile_photo) {
                Storage::disk('public')->delete('students/' . $student->profile_photo);
            }
            $path = $request->file('profile_photo')->store('students', 'public');
            $validated['profile_photo'] = basename($path);
        }

        $student->update($validated);

        return redirect()->route('students.show', $student)
            ->with('success', 'Student updated successfully.');
    }

    public function destroy(Student $student)
    {
        try {
            if ($student->profile_photo) {
                Storage::disk('public')->delete('students/' . $student->profile_photo);
            }
            $student->faceEncodings()->delete();
            $student->delete();

            return redirect()->route('students.index')
                ->with('success', 'Student deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('students.index')
                ->with('error', 'Failed to delete student. Please try again.');
        }
    }

    public function manageFaces(Student $student)
    {
        return view('students.faces', compact('student'));
    }

    // ========== API-BASED FACE REGISTRATION METHODS ==========

    /**
     * Get registered classes from Flask API
     */
    private function getRegisteredClasses()
    {
        try {
            return Cache::remember('face_model_classes', 300, function () {
                $response = Http::timeout(10)->get($this->apiUrl . '/api/model-info');
                if ($response->successful()) {
                    $data = $response->json();
                    return $data['model_info']['classes'] ?? [];
                }
                Log::warning('Failed to fetch model info', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return [];
            });
        } catch (\Exception $e) {
            Log::error('Error fetching model classes', [
                'error' => $e->getMessage(),
                'api_url' => $this->apiUrl
            ]);
            return [];
        }
    }

    /**
     * Check if student name is registered in face model
     *
     * @param string $studentName
     * @return bool
     */
    public function isStudentRegistered($studentName)
    {
        $registeredClasses = $this->getRegisteredClasses();
        $normalizedStudentName = strtolower(trim($studentName));
        foreach ($registeredClasses as $className) {
            if (strtolower(trim($className)) === $normalizedStudentName) {
                return true;
            }
        }
        return false;
    }

    // ========== API ENDPOINTS ==========

    /**
     * API: Get model info
     */
    public function getModelInfo()
    {
        try {
            $classes = $this->getRegisteredClasses();
            return response()->json([
                'success' => true,
                'model_info' => [
                    'classes' => $classes,
                    'total_registered' => count($classes),
                    'last_updated' => Cache::has('face_model_classes') ?
                        'From cache (updated within 5 minutes)' :
                        'Just fetched from API'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch model info: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get face registration status for individual student (API endpoint)
     */
    public function getFaceStatus(Student $student)
    {
        try {
            $status = $student->face_registration_status;

            return response()->json([
                'success' => true,
                'student_id' => $student->id,
                'student_name' => $student->name,
                'status' => $status['status'],
                'message' => $status['message']
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to get face status', [
                'student_id' => $student->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Failed to check face status'
            ], 500);
        }
    }

    /**
     * API: Refresh model cache
     */
    public function refreshModelCache()
    {
        try {
            Cache::forget('face_model_classes');
            $classes = $this->getRegisteredClasses();
            return response()->json([
                'success' => true,
                'message' => 'Model cache refreshed successfully',
                'total_classes' => count($classes),
                'classes' => $classes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh cache: ' . $e->getMessage()
            ], 500);
        }
    }

    // Legacy method untuk backward compatibility
    public function uploadFaces(Request $request, Student $student)
    {
        return response()->json([
            'success' => false,
            'message' => 'Face registration is now managed through the training pipeline. Please contact administrator.'
        ], 400);
    }

    /**
     * Refresh face registration status (clear cache)
     */
    public function refreshFaceStatus(Student $student)
    {
        try {
            Cache::forget("student_face_registered_{$student->id}");
            $status = $student->face_registration_status;
            return response()->json([
                'success' => true,
                'message' => 'Face status refreshed',
                'status' => $status
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to refresh face status', [
                'student_id' => $student->id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh face status'
            ], 500);
        }
    }

    /**
     * Get API status like dashboard
     */
    public function getApiStatus()
    {
        try {
            $apiUrl = config('app.python_api_url', 'http://localhost:5000');
            $response = Http::timeout(3)->get($apiUrl . '/api/health');
            if ($response->successful()) {
                return response()->json([
                    'status' => 'connected',
                    'message' => 'API Flask running',
                    'data' => $response->json()
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'API responded with status: ' . $response->status()
                ], 500);
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'status' => 'offline',
                'message' => 'API Flask tidak berjalan - start Flask server terlebih dahulu'
            ], 503);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Connection failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all students with their face registration status
     */
    public function getStudentsWithFaceStatus()
    {
        try {
            $registeredClasses = $this->getRegisteredClasses();
            $students = Student::select(['id', 'name'])->get();
            
            $studentsWithStatus = $students->map(function ($student) use ($registeredClasses) {
                $normalizedStudentName = strtolower(trim($student->name));
                $isRegistered = false;
                
                foreach ($registeredClasses as $className) {
                    if (strtolower(trim($className)) === $normalizedStudentName) {
                        $isRegistered = true;
                        break;
                    }
                }
                
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'status' => $isRegistered ? 'registered' : 'not_registered',
                    'message' => $isRegistered ? 'Face registered in model' : 'Face not registered'
                ];
            });
            
            return response()->json([
                'success' => true,
                'students' => $studentsWithStatus,
                'total_registered_classes' => count($registeredClasses),
                'api_status' => 'connected'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting students face status', [
                'error' => $e->getMessage(),
                'api_url' => $this->apiUrl
            ]);
            
            // Return all students with API error status
            $students = Student::select(['id', 'name'])->get();
            $studentsWithStatus = $students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'status' => 'api_error',
                    'message' => 'Unable to check face registration status'
                ];
            });
            
            return response()->json([
                'success' => false,
                'students' => $studentsWithStatus,
                'api_status' => 'error',
                'message' => 'Failed to fetch model info: ' . $e->getMessage()
            ], 500);
        }
    }
}
