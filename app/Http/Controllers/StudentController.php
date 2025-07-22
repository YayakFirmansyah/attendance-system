<?php
// app/Http/Controllers/StudentController.php - UPDATED WITH API-BASED FACE REGISTRATION

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

    public function index()
    {
        $students = Student::orderBy('name')->paginate(15);
        return view('students.index', compact('students'));
    }

    public function show(Student $student)
    {
        $student->load(['faceEncodings', 'attendances.classModel.course']);
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
            // Delete old photo if exists
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
            // Delete profile photo if exists
            if ($student->profile_photo) {
                Storage::disk('public')->delete('students/' . $student->profile_photo);
            }

            // Delete related face encodings (cascade should handle this, but just in case)
            $student->faceEncodings()->delete();
            
            // Delete student (attendances will be cascade deleted)
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
     */
    public function isStudentRegistered($studentName)
    {
        $registeredClasses = $this->getRegisteredClasses();
        
        // Normalize names untuk pencocokan yang lebih akurat
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
     * API: Get face status for specific student
     */
    public function getFaceStatus(Student $student)
    {
        try {
            $isRegistered = $this->isStudentRegistered($student->name);
            
            return response()->json([
                'success' => true,
                'is_registered' => $isRegistered,
                'student_name' => $student->name,
                'registration_status' => $isRegistered ? 'registered' : 'not_registered'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check face status: ' . $e->getMessage()
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
}