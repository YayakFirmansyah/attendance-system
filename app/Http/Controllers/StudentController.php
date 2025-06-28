<?php
// app/Http/Controllers/StudentController.php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\FaceEncoding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with('faceEncodings')->paginate(15);
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
            'student_id' => 'required|unique:students',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:students',
            'program_study' => 'required|string|max:255',
            'faculty' => 'required|string|max:255',
            'semester' => 'required|integer|min:1|max:8',
            'phone' => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|max:2048'
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
            'student_id' => 'required|unique:students,student_id,' . $student->id,
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email,' . $student->id,
            'program_study' => 'required|string|max:255',
            'faculty' => 'required|string|max:255',
            'semester' => 'required|integer|min:1|max:8',
            'phone' => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('profile_photo')) {
            // Delete old photo
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
        // Delete profile photo
        if ($student->profile_photo) {
            Storage::disk('public')->delete('students/' . $student->profile_photo);
        }

        $student->delete();

        return redirect()->route('students.index')
            ->with('success', 'Student deleted successfully.');
    }

    public function manageFaces(Student $student)
    {
        return view('students.faces', compact('student'));
    }

    public function uploadFaces(Request $request, Student $student)
    {
        $request->validate([
            'faces' => 'required|array|min:1|max:5',
            'faces.*' => 'required|string' // base64 images
        ]);

        try {
            // Send to Python API
            $response = Http::post(config('app.python_api_url') . '/api/students/' . $student->id . '/faces', [
                'images' => $request->faces
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                if ($result['success']) {
                    return response()->json([
                        'success' => true,
                        'message' => $result['message']
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => $result['message']
                    ], 400);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to communicate with face recognition API'
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error uploading faces: ' . $e->getMessage()
            ], 500);
        }
    }
}