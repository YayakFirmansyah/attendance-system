<?php
// app/Http/Controllers/CourseController.php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use App\Models\User;

class CourseController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('role:admin');
    // }

    public function index()
    {
        $courses = Course::with('lecturer')->orderBy('course_name')->paginate(10);
        return view('courses.index', compact('courses'));
    }

    public function create()
    {
        $lecturers = User::where('role', 'dosen')->where('status', 'active')->get();
        return view('courses.create', compact('lecturers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_code' => 'required|unique:courses',
            'course_name' => 'required|max:255',
            'credits' => 'required|integer|min:1|max:6',
            'faculty' => 'required|max:255',
            'lecturer_id' => 'required|exists:users,id',
            'description' => 'nullable',
            'status' => 'required|in:active,inactive'
        ]);

        Course::create($validated);
        return redirect()->route('courses.index')->with('success', 'Course created successfully');
    }

    public function edit(Course $course)
    {
        $lecturers = User::where('role', 'dosen')->where('status', 'active')->get();
        return view('courses.edit', compact('course', 'lecturers'));
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'course_code' => 'required|unique:courses,course_code,' . $course->id,
            'course_name' => 'required|max:255',
            'credits' => 'required|integer|min:1|max:6',
            'faculty' => 'required|max:255',
            'lecturer_id' => 'required|exists:users,id',
            'description' => 'nullable',
            'status' => 'required|in:active,inactive'
        ]);

        $course->update($validated);
        return redirect()->route('courses.index')->with('success', 'Course updated successfully');
    }

    public function destroy(Course $course)
    {
        $course->delete();
        return redirect()->route('courses.index')->with('success', 'Course deleted successfully');
    }
}