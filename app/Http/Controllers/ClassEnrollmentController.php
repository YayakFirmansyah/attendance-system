<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Student;
use App\Models\ClassEnrollment;
use Illuminate\Http\Request;

class ClassEnrollmentController extends Controller
{
    /**
     * Display the enrollment page for a specific class.
     */
    public function manage(ClassModel $class)
    {
        // Get currently enrolled students
        $enrollments = ClassEnrollment::with('student')
            ->where('class_id', $class->id)
            ->where('status', 'active')
            ->get();

        // Get all students for the dropdown/selection (excluding already enrolled)
        $enrolledIds = $enrollments->pluck('student_id')->toArray();

        $availableStudents = Student::where('status', 'active')
            ->whereNotIn('id', $enrolledIds)
            ->orderBy('name')
            ->get();

        return view('classes.enrollment', compact('class', 'enrollments', 'availableStudents'));
    }

    /**
     * Add a student to the class.
     */
    public function store(Request $request, ClassModel $class)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        // Check if student is already enrolled (maybe inactive/dropped)
        $existingEnrollment = ClassEnrollment::where('class_id', $class->id)
            ->where('student_id', $request->student_id)
            ->first();

        if ($existingEnrollment) {
            $existingEnrollment->update(['status' => 'active']);
            return redirect()->back()->with('success', 'Student re-enrolled successfully.');
        }

        // Create new enrollment
        ClassEnrollment::create([
            'class_id' => $class->id,
            'student_id' => $request->student_id,
            'status' => 'active',
            'enrolled_at' => now()
        ]);

        return redirect()->back()->with('success', 'Student enrolled successfully.');
    }

    /**
     * Remove/Drop a student from the class.
     */
    public function drop(Request $request, ClassModel $class, Student $student)
    {
        $enrollment = ClassEnrollment::where('class_id', $class->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        // Instead of hard delete, we mark as dropped
        $enrollment->update(['status' => 'dropped']);

        return redirect()->back()->with('success', 'Student removed from this class.');
    }
}
