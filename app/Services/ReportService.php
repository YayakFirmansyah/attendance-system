<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\ClassModel;
use App\Models\Student;
use App\Models\Course;
use App\Enums\AttendanceStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Generate attendance report for a class
     */
    public function generateClassReport(int $classId, Carbon $startDate, Carbon $endDate): array
    {
        $class = ClassModel::with(['course', 'room', 'enrollments.student'])->findOrFail($classId);

        $attendances = Attendance::with('student')
            ->where('class_id', $classId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        // Group by student
        $studentAttendances = $attendances->groupBy('student_id');

        $reportData = [];
        foreach ($class->enrollments as $enrollment) {
            $student = $enrollment->student;
            $studentRecords = $studentAttendances->get($student->id, collect());

            $present = $studentRecords->where('status', AttendanceStatus::PRESENT->value)->count();
            $late = $studentRecords->where('status', AttendanceStatus::LATE->value)->count();
            $absent = $studentRecords->where('status', AttendanceStatus::ABSENT->value)->count();
            $excused = $studentRecords->where('status', AttendanceStatus::EXCUSED->value)->count();

            $totalSessions = $this->countClassSessions($class, $startDate, $endDate);
            $attended = $present + $late;
            $attendanceRate = $totalSessions > 0 ? round(($attended / $totalSessions) * 100, 2) : 0;

            $reportData[] = [
                'student' => $student,
                'statistics' => [
                    'total_sessions' => $totalSessions,
                    'present' => $present,
                    'late' => $late,
                    'absent' => $absent,
                    'excused' => $excused,
                    'attended' => $attended,
                    'attendance_rate' => $attendanceRate,
                ],
                'details' => $studentRecords,
            ];
        }

        // Sort by student name
        usort($reportData, function ($a, $b) {
            return strcmp($a['student']->name, $b['student']->name);
        });

        return [
            'class' => $class,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'students' => $reportData,
            'summary' => $this->calculateClassSummary($reportData),
        ];
    }

    /**
     * Generate student attendance report across all classes
     */
    public function generateStudentReport(int $studentId, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $student = Student::with(['enrollments.classModel.course'])->findOrFail($studentId);

        $query = Attendance::with('classModel.course')
            ->where('student_id', $studentId);

        if ($startDate) {
            $query->whereDate('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('date', '<=', $endDate);
        }

        $attendances = $query->get();

        // Group by class
        $classSummaries = [];
        foreach ($student->enrollments as $enrollment) {
            $classId = $enrollment->class_id;
            $classAttendances = $attendances->where('class_id', $classId);

            $present = $classAttendances->where('status', AttendanceStatus::PRESENT->value)->count();
            $late = $classAttendances->where('status', AttendanceStatus::LATE->value)->count();
            $absent = $classAttendances->where('status', AttendanceStatus::ABSENT->value)->count();
            $excused = $classAttendances->where('status', AttendanceStatus::EXCUSED->value)->count();

            $total = $present + $late + $absent + $excused;
            $attended = $present + $late;
            $attendanceRate = $total > 0 ? round(($attended / $total) * 100, 2) : 0;

            $classSummaries[] = [
                'class' => $enrollment->classModel,
                'statistics' => [
                    'total' => $total,
                    'present' => $present,
                    'late' => $late,
                    'absent' => $absent,
                    'excused' => $excused,
                    'attended' => $attended,
                    'attendance_rate' => $attendanceRate,
                ],
            ];
        }

        // Overall statistics
        $overallStats = [
            'total' => $attendances->count(),
            'present' => $attendances->where('status', AttendanceStatus::PRESENT->value)->count(),
            'late' => $attendances->where('status', AttendanceStatus::LATE->value)->count(),
            'absent' => $attendances->where('status', AttendanceStatus::ABSENT->value)->count(),
            'excused' => $attendances->where('status', AttendanceStatus::EXCUSED->value)->count(),
        ];
        $overallStats['attended'] = $overallStats['present'] + $overallStats['late'];
        $overallStats['attendance_rate'] = $overallStats['total'] > 0
            ? round(($overallStats['attended'] / $overallStats['total']) * 100, 2)
            : 0;

        return [
            'student' => $student,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'class_summaries' => $classSummaries,
            'overall_statistics' => $overallStats,
            'recent_attendances' => $attendances->sortByDesc('date')->take(10),
        ];
    }

    /**
     * Generate monthly report summary
     */
    public function generateMonthlyReport(int $month, int $year): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $attendances = Attendance::with(['student', 'classModel.course'])
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        // Statistics by class
        $classSummaries = [];
        $classes = ClassModel::with('course')->whereHas('attendances', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        })->get();

        foreach ($classes as $class) {
            $classAttendances = $attendances->where('class_id', $class->id);

            $classSummaries[] = [
                'class' => $class,
                'statistics' => [
                    'total' => $classAttendances->count(),
                    'present' => $classAttendances->where('status', AttendanceStatus::PRESENT->value)->count(),
                    'late' => $classAttendances->where('status', AttendanceStatus::LATE->value)->count(),
                    'absent' => $classAttendances->where('status', AttendanceStatus::ABSENT->value)->count(),
                    'excused' => $classAttendances->where('status', AttendanceStatus::EXCUSED->value)->count(),
                ],
            ];
        }

        // Overall statistics
        $overallStats = [
            'total' => $attendances->count(),
            'present' => $attendances->where('status', AttendanceStatus::PRESENT->value)->count(),
            'late' => $attendances->where('status', AttendanceStatus::LATE->value)->count(),
            'absent' => $attendances->where('status', AttendanceStatus::ABSENT->value)->count(),
            'excused' => $attendances->where('status', AttendanceStatus::EXCUSED->value)->count(),
            'unique_students' => $attendances->pluck('student_id')->unique()->count(),
            'unique_classes' => $attendances->pluck('class_id')->unique()->count(),
        ];

        return [
            'period' => [
                'month' => $month,
                'year' => $year,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'class_summaries' => $classSummaries,
            'overall_statistics' => $overallStats,
        ];
    }

    /**
     * Get attendance trends (for charts)
     */
    public function getAttendanceTrends(int $classId, int $days = 30): array
    {
        $startDate = now()->subDays($days);

        $attendances = Attendance::where('class_id', $classId)
            ->whereDate('date', '>=', $startDate)
            ->select(
                DB::raw('DATE(date) as attendance_date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present'),
                DB::raw('SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late'),
                DB::raw('SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent')
            )
            ->groupBy('attendance_date')
            ->orderBy('attendance_date')
            ->get();

        $dates = [];
        $presentData = [];
        $lateData = [];
        $absentData = [];

        foreach ($attendances as $record) {
            $dates[] = Carbon::parse($record->attendance_date)->format('d M');
            $presentData[] = $record->present;
            $lateData[] = $record->late;
            $absentData[] = $record->absent;
        }

        return [
            'labels' => $dates,
            'datasets' => [
                'present' => $presentData,
                'late' => $lateData,
                'absent' => $absentData,
            ],
        ];
    }

    /**
     * Count expected class sessions in date range
     */
    private function countClassSessions(ClassModel $class, Carbon $startDate, Carbon $endDate): int
    {
        $count = 0;
        $currentDate = $startDate->copy();
        $classDay = strtolower($class->day);

        while ($currentDate <= $endDate) {
            if (strtolower($currentDate->format('l')) === $classDay) {
                $count++;
            }
            $currentDate->addDay();
        }

        return $count;
    }

    /**
     * Calculate summary statistics for class report
     */
    private function calculateClassSummary(array $reportData): array
    {
        $totalStudents = count($reportData);
        $totalPresent = 0;
        $totalLate = 0;
        $totalAbsent = 0;
        $totalExcused = 0;
        $totalAttendanceRate = 0;

        foreach ($reportData as $data) {
            $stats = $data['statistics'];
            $totalPresent += $stats['present'];
            $totalLate += $stats['late'];
            $totalAbsent += $stats['absent'];
            $totalExcused += $stats['excused'];
            $totalAttendanceRate += $stats['attendance_rate'];
        }

        return [
            'total_students' => $totalStudents,
            'average_attendance_rate' => $totalStudents > 0 ? round($totalAttendanceRate / $totalStudents, 2) : 0,
            'total_present' => $totalPresent,
            'total_late' => $totalLate,
            'total_absent' => $totalAbsent,
            'total_excused' => $totalExcused,
        ];
    }
}
