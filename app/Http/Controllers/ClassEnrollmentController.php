<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Student;
use Illuminate\Http\Request;

class ClassEnrollmentController extends Controller
{
    /**
     * Display the cohort-based participant list for a specific class.
     */
    public function manage(ClassModel $class)
    {
        $students = Student::where('status', 'active')
            ->where('cohort_id', $class->cohort_id)
            ->orderBy('name')
            ->get();

        return view('classes.enrollment', compact('class', 'students'));
    }

    /**
     * Cohort membership is the source of truth, so manual add is disabled.
     */
    public function store(Request $request, ClassModel $class)
    {
        return redirect()->route('classes.cohort-members', $class)
            ->with('info', 'Keanggotaan kelas mengikuti cohort, jadi tidak perlu penambahan manual.');
    }

    /**
     * Manual removal is disabled because the class follows the cohort.
     */
    public function drop(Request $request, ClassModel $class, Student $student)
    {
        return redirect()->route('classes.cohort-members', $class)
            ->with('info', 'Keanggotaan kelas mengikuti cohort, jadi tidak bisa dikeluarkan manual.');
    }
}
