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
        $classes = ClassModel::with(['course.lecturer', 'room', 'cohort'])
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();

        return view('classes.index', compact('classes'));
    }

    public function create()
    {
        $courses = Course::where('status', 'active')
            ->with('lecturer')
            ->whereNotNull('lecturer_id')
            ->get();

        $rooms = Room::where('status', 'active')->get();
        $cohorts = \App\Models\Cohort::orderBy('name')->get();

        return view('classes.create', compact('courses', 'rooms', 'cohorts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'cohort_id' => 'required|exists:cohorts,id',
            'day' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'room_id' => 'required|exists:rooms,id',
            'status' => 'required|in:active,inactive',
        ]);

        // Check for duplicate class on same course and cohort
        $duplicate = ClassModel::where('course_id', $validated['course_id'])
            ->where('cohort_id', $validated['cohort_id'])
            ->exists();

        if ($duplicate) {
            return back()->withErrors(['error' => 'Schedule for this course and cohort already exists']);
        }

        // Check for room schedule conflicts on same day and time
        $conflict = ClassModel::where('room_id', $validated['room_id'])
            ->where('day', $validated['day'])
            ->where('status', 'active')
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('start_time', '<=', $validated['start_time'])
                            ->where('end_time', '>=', $validated['end_time']);
                    });
            })
            ->exists();

        if ($conflict) {
            return back()->withErrors(['error' => 'Room schedule conflict detected for this time range']);
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
        $cohorts = \App\Models\Cohort::orderBy('name')->get();

        return view('classes.edit', compact('class', 'courses', 'rooms', 'cohorts'));
    }

    public function update(Request $request, ClassModel $class)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'cohort_id' => 'required|exists:cohorts,id',
            'day' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'room_id' => 'required|exists:rooms,id',
            'status' => 'required|in:active,inactive'
        ]);

        // Check for duplicate class (exclude current class)
        $duplicate = ClassModel::where('course_id', $validated['course_id'])
            ->where('cohort_id', $validated['cohort_id'])
            ->where('id', '!=', $class->id)
            ->exists();

        if ($duplicate) {
            return back()->withErrors(['error' => 'Schedule for this course and cohort already exists']);
        }

        // Check for room schedule conflicts (exclude current class)
        $conflict = ClassModel::where('room_id', $validated['room_id'])
            ->where('day', $validated['day'])
            ->where('status', 'active')
            ->where('id', '!=', $class->id)
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('start_time', '<=', $validated['start_time'])
                            ->where('end_time', '>=', $validated['end_time']);
                    });
            })
            ->exists();

        if ($conflict) {
            return back()->withErrors(['error' => 'Room schedule conflict detected for this time range']);
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
