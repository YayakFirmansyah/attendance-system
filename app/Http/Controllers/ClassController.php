<?php
// app/Http/Controllers/ClassController.php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Models\Room;

class ClassController extends Controller
{
    public function index()
    {
        $classes = ClassModel::with(['course.lecturer', 'room'])
            ->orderBy('semester', 'desc')
            ->orderBy('day')
            ->orderBy('start_time')
            ->paginate(10);
        
        return view('classes.index', compact('classes'));
    }

    public function create()
    {
        $courses = Course::where('status', 'active')
            ->with('lecturer')
            ->whereNotNull('lecturer_id')
            ->get();
        
        $rooms = Room::where('status', 'active')->get();
        
        return view('classes.create', compact('courses', 'rooms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'class_code' => 'required|string|max:10',
            'semester' => 'required|string|max:20',
            'day' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'room_id' => 'required|exists:rooms,id',
            'status' => 'required|in:active,inactive',
        ]);

        // Check for duplicate class on same course, code, and semester
        $duplicate = ClassModel::where('course_id', $validated['course_id'])
            ->where('class_code', $validated['class_code'])
            ->where('semester', $validated['semester'])
            ->exists();

        if ($duplicate) {
            return back()->withErrors(['error' => 'Class with this course, code, and semester already exists']);
        }

        // Check for room schedule conflicts on same day and time
        $conflict = ClassModel::where('room_id', $validated['room_id'])
            ->where('day', $validated['day'])
            ->where('semester', $validated['semester']) // TAMBAHAN CHECK SEMESTER
            ->where('status', 'active')
            ->where(function($query) use ($validated) {
                $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                      ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                      ->orWhere(function($q) use ($validated) {
                          $q->where('start_time', '<=', $validated['start_time'])
                            ->where('end_time', '>=', $validated['end_time']);
                      });
            })
            ->exists();

        if ($conflict) {
            return back()->withErrors(['error' => 'Room schedule conflict detected for this time and semester']);
        }

        ClassModel::create($validated);
        return redirect()->route('classes.index')->with('success', 'Class schedule created successfully');
    }

    public function show(ClassModel $class)
    {
        $class->load(['course.lecturer']);
        return view('classes.show', compact('class'));
    }

    public function edit(ClassModel $class)
    {
        $courses = Course::where('status', 'active')
            ->with('lecturer')
            ->whereNotNull('lecturer_id')
            ->get();
        
        $rooms = \App\Models\Room::where('status', 'active')->get();
        
        return view('classes.edit', compact('class', 'courses', 'rooms'));
    }

    public function update(Request $request, ClassModel $class)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'class_code' => 'required|string|max:10',
            'semester' => 'required|string|max:20', // TAMBAHAN VALIDATION SEMESTER
            'day' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'room_id' => 'required|exists:rooms,id', // PERBAIKAN: room_id bukan room
            'status' => 'required|in:active,inactive'
        ]);

        // Check for duplicate class (exclude current class)
        $duplicate = ClassModel::where('course_id', $validated['course_id'])
            ->where('class_code', $validated['class_code'])
            ->where('semester', $validated['semester'])
            ->where('id', '!=', $class->id)
            ->exists();

        if ($duplicate) {
            return back()->withErrors(['error' => 'Class with this course, code, and semester already exists']);
        }

        // Check for room schedule conflicts (exclude current class)
        $conflict = ClassModel::where('room_id', $validated['room_id'])
            ->where('day', $validated['day'])
            ->where('semester', $validated['semester']) // TAMBAHAN CHECK SEMESTER
            ->where('status', 'active')
            ->where('id', '!=', $class->id)
            ->where(function($query) use ($validated) {
                $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                      ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                      ->orWhere(function($q) use ($validated) {
                          $q->where('start_time', '<=', $validated['start_time'])
                            ->where('end_time', '>=', $validated['end_time']);
                      });
            })
            ->exists();

        if ($conflict) {
            return back()->withErrors(['error' => 'Room schedule conflict detected for this time and semester']);
        }

        $class->update($validated);
        return redirect()->route('classes.index')->with('success', 'Class schedule updated successfully');
    }

    public function destroy(ClassModel $class)
    {
        // Check if class has attendance records
        if ($class->attendances()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete class with existing attendance records']);
        }

        $class->delete();
        return redirect()->route('classes.index')->with('success', 'Class schedule deleted successfully');
    }
}