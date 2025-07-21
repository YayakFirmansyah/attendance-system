<?php
// app/Http/Controllers/ClassController.php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Models\Room;


class ClassController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('role:admin');
    // }

    public function index()
    {
        $classes = ClassModel::with('course')->orderBy('day')->orderBy('start_time')->paginate(15);
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
            'room_id' => 'required|exists:rooms,id',
            'class_code' => 'required|string|max:10',
            'day' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'status' => 'required|in:active,inactive'
        ]);

        // Check for duplicate class (same course, class_code, day, time)
        $duplicateClass = ClassModel::where('course_id', $validated['course_id'])
            ->where('class_code', $validated['class_code'])
            ->where('day', $validated['day'])
            ->where('status', 'active')
            ->exists();

        if ($duplicateClass) {
            return back()->withErrors(['error' => 'Class with same course, class code, and day already exists']);
        }

        // Check for room schedule conflicts
        $conflict = ClassModel::where('room_id', $validated['room_id'])
            ->where('day', $validated['day'])
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
            return back()->withErrors(['error' => 'Room schedule conflict detected for this time']);
        }

        ClassModel::create($validated);
        return redirect()->route('classes.index')->with('success', 'Class schedule created successfully');
    }

    public function edit(ClassModel $class)
    {
        $courses = Course::where('status', 'active')
            ->with('lecturer')
            ->whereNotNull('lecturer_id')
            ->get();
        $rooms = Room::where('status', 'active')->get();
        return view('classes.edit', compact('class', 'courses', 'rooms'));
    }

    public function update(Request $request, ClassModel $class)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'room_id' => 'required|exists:rooms,id',
            'class_code' => 'required|string|max:10',
            'day' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'status' => 'required|in:active,inactive'
        ]);

        // Check for duplicate class (exclude current class)
        $duplicateClass = ClassModel::where('course_id', $validated['course_id'])
            ->where('class_code', $validated['class_code'])
            ->where('day', $validated['day'])
            ->where('status', 'active')
            ->where('id', '!=', $class->id)
            ->exists();

        if ($duplicateClass) {
            return back()->withErrors(['error' => 'Class with same course, class code, and day already exists']);
        }

        // Check room conflicts (exclude current class)
        $conflict = ClassModel::where('room_id', $validated['room_id'])
            ->where('day', $validated['day'])
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
            return back()->withErrors(['error' => 'Room schedule conflict detected for this time']);
        }

        $class->update($validated);
        return redirect()->route('classes.index')->with('success', 'Class schedule updated successfully');
    }

    public function destroy(ClassModel $class)
    {
        $class->delete();
        return redirect()->route('classes.index')->with('success', 'Class schedule deleted successfully');
    }
}