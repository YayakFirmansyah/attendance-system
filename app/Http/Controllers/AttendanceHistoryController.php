<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceHistoryController extends Controller
{
    /**
     * Index - Tampilkan history presensi dengan filter
     */
    public function index(Request $request)
    {
        $query = Attendance::with(['student', 'classModel.course']);
        
        // Filter berdasarkan request
        if ($request->class_id) {
            $query->where('class_id', $request->class_id);
        }
        
        if ($request->date) {
            $query->whereDate('date', $request->date);
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->student_search) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->student_search}%")
                  ->orWhere('student_id', 'like', "%{$request->student_search}%");
            });
        }
        
        $attendances = $query->orderBy('date', 'desc')
                           ->orderBy('check_in', 'desc')
                           ->paginate(20)
                           ->withQueryString();
        
        // Data untuk filter dropdown
        $classes = ClassModel::with('course')->where('status', 'active')->get();
        
        // Statistics
        $stats = [
            'total' => $query->count(),
            'present' => (clone $query)->where('status', 'present')->count(),
            'late' => (clone $query)->where('status', 'late')->count(),
            'absent' => (clone $query)->where('status', 'absent')->count()
        ];
        
        return view('attendance.history.index', compact('attendances', 'classes', 'stats'));
    }
    
    /**
     * Show - Detail attendance record
     */
    public function show(Attendance $attendance)
    {
        $attendance->load(['student', 'classModel.course.lecturer']);
        return view('attendance.history.show', compact('attendance'));
    }
    
    /**
     * Edit - Form edit manual attendance
     */
    public function edit(Attendance $attendance)
    {
        $attendance->load(['student', 'classModel.course']);
        return view('attendance.history.edit', compact('attendance'));
    }
    
    /**
     * Update - Update attendance record
     */
    public function update(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'status' => 'required|in:present,late,absent',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i|after:check_in',
            'notes' => 'nullable|string|max:255'
        ]);
        
        // Convert time to full datetime
        if ($validated['check_in']) {
            $validated['check_in'] = Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $validated['check_in']);
        }
        
        if ($validated['check_out']) {
            $validated['check_out'] = Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $validated['check_out']);
        }
        
        $attendance->update($validated);
        
        return redirect()->route('attendance.history.index')
                        ->with('success', 'Attendance updated successfully');
    }
    
    /**
     * Bulk Edit - Edit multiple attendance records
     */
    public function bulkEdit(Request $request)
    {
        $validated = $request->validate([
            'attendance_ids' => 'required|array',
            'attendance_ids.*' => 'exists:attendances,id',
            'bulk_action' => 'required|in:present,late,absent,delete',
            'bulk_notes' => 'nullable|string|max:255'
        ]);
        
        $attendances = Attendance::whereIn('id', $validated['attendance_ids']);
        
        if ($validated['bulk_action'] === 'delete') {
            $attendances->delete();
            $message = 'Attendance records deleted successfully';
        } else {
            $updateData = ['status' => $validated['bulk_action']];
            if ($validated['bulk_notes']) {
                $updateData['notes'] = $validated['bulk_notes'];
            }
            
            $attendances->update($updateData);
            $message = 'Attendance records updated successfully';
        }
        
        return redirect()->route('attendance.history.index')->with('success', $message);
    }
    
    /**
     * Reports - Generate attendance reports
     */
    public function reports(Request $request)
    {
        $classes = ClassModel::with('course')->where('status', 'active')->get();
        
        if (!$request->has('generate')) {
            return view('attendance.history.reports', compact('classes'));
        }
        
        $validated = $request->validate([
            'report_type' => 'required|in:summary,detailed,by_student,by_class',
            'class_id' => 'nullable|exists:classes,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'format' => 'required|in:view,excel,pdf'
        ]);
        
        $data = $this->generateReportData($validated);
        
        if ($validated['format'] === 'view') {
            return view('attendance.history.report-result', $data);
        }
        
        // Export logic would go here for Excel/PDF
        return $this->exportReport($data, $validated['format']);
    }
    
    /**
     * Create Manual Attendance
     */
    public function create(Request $request)
    {
        $classes = ClassModel::with('course')->where('status', 'active')->get();
        $students = Student::where('status', 'active')->get();
        
        return view('attendance.history.create', compact('classes', 'students'));
    }
    
    /**
     * Store Manual Attendance
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date',
            'status' => 'required|in:present,late,absent',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i|after:check_in',
            'notes' => 'nullable|string|max:255'
        ]);
        
        // Check if attendance already exists
        $exists = Attendance::where('student_id', $validated['student_id'])
                           ->where('class_id', $validated['class_id'])
                           ->whereDate('date', $validated['date'])
                           ->exists();
        
        if ($exists) {
            return back()->withErrors(['date' => 'Attendance record already exists for this student, class, and date.']);
        }
        
        // Convert times
        if ($validated['check_in']) {
            $validated['check_in'] = Carbon::parse($validated['date'] . ' ' . $validated['check_in']);
        }
        
        if ($validated['check_out']) {
            $validated['check_out'] = Carbon::parse($validated['date'] . ' ' . $validated['check_out']);
        }
        
        Attendance::create($validated);
        
        return redirect()->route('attendance.history.index')
                        ->with('success', 'Manual attendance created successfully');
    }
    
    /**
     * Generate report data based on type
     */
    private function generateReportData($params)
    {
        $query = Attendance::with(['student', 'classModel.course'])
                          ->whereBetween('date', [$params['date_from'], $params['date_to']]);
        
        if ($params['class_id']) {
            $query->where('class_id', $params['class_id']);
        }
        
        switch ($params['report_type']) {
            case 'summary':
                return $this->getSummaryReport($query);
            case 'detailed':
                return $this->getDetailedReport($query);
            case 'by_student':
                return $this->getStudentReport($query);
            case 'by_class':
                return $this->getClassReport($query);
        }
    }
    
    private function getSummaryReport($query)
    {
        $total = $query->count();
        $present = (clone $query)->where('status', 'present')->count();
        $late = (clone $query)->where('status', 'late')->count();
        $absent = (clone $query)->where('status', 'absent')->count();
        
        return [
            'type' => 'summary',
            'data' => compact('total', 'present', 'late', 'absent'),
            'attendance_rate' => $total > 0 ? round(($present + $late) / $total * 100, 2) : 0
        ];
    }
    
    private function getDetailedReport($query)
    {
        return [
            'type' => 'detailed',
            'data' => $query->orderBy('date', 'desc')->get()
        ];
    }
    
    private function getStudentReport($query)
    {
        $students = $query->select('student_id')
                         ->selectRaw('COUNT(*) as total')
                         ->selectRaw('SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present')
                         ->selectRaw('SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late')
                         ->selectRaw('SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent')
                         ->with('student')
                         ->groupBy('student_id')
                         ->get();
        
        return [
            'type' => 'by_student',
            'data' => $students
        ];
    }
    
    private function getClassReport($query)
    {
        $classes = $query->select('class_id')
                        ->selectRaw('COUNT(*) as total')
                        ->selectRaw('SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present')
                        ->selectRaw('SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late')
                        ->selectRaw('SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent')
                        ->with('classModel.course')
                        ->groupBy('class_id')
                        ->get();
        
        return [
            'type' => 'by_class',
            'data' => $classes
        ];
    }
    
    private function exportReport($data, $format)
    {
        // Implement export logic here
        return response()->json(['message' => 'Export functionality not implemented yet']);
    }
}