<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Student;
use App\Models\ClassModel;
use App\Enums\AttendanceStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\ClassEnrollment;

class AttendanceService
{
    public function recordAttendance(int $sessionId, array $students): array
    {
        $session = \App\Models\AttendanceSession::findOrFail($sessionId);
        if ($session->status !== 'active') {
            throw new \Exception('Sesi absensi sudah ditutup.');
        }

        $results = [];

        foreach ($students as $studentData) {
            $studentName = $studentData['student_name'];
            $confidence = $studentData['confidence'];

            // Cek Student berdasarkan name
            $student = \App\Models\Student::where('name', $studentName)
                ->where('status', 'active')
                ->first();

            if (!$student) {
                $student = \App\Models\Student::whereRaw('LOWER(name) = ?', [strtolower($studentName)])
                    ->where('status', 'active')
                    ->first();

                if (!$student) {
                    $searchName = str_replace([' ', "'", '"'], '%', $studentName);
                    $student = \App\Models\Student::where('name', 'LIKE', "%{$searchName}%")
                        ->where('status', 'active')
                        ->first();
                }
            }

            if (!$student) {
                $results[] = [
                    'student_name' => $studentName,
                    'status' => 'not_found',
                    'confidence' => $confidence
                ];
                continue;
            }

            $studentId = $student->id;

            $classModel = \App\Models\ClassModel::find($session->class_id);
            $isEnrolled = $classModel && $this->isStudentEnrolledInClass($studentId, $classModel);

            if (!$isEnrolled) {
                $results[] = [
                    'student_name' => $student->name,
                    'status' => 'not_enrolled',
                    'confidence' => $confidence
                ];
                continue;
            }

            // Lock row for safe concurrent inserting
            DB::beginTransaction();
            try {
                $attendance = Attendance::where('attendance_session_id', $session->id)
                    ->where('student_id', $studentId)
                    ->lockForUpdate()
                    ->first();

                if ($attendance) {
                    $results[] = [
                        'student_name' => $attendance->student->name,
                        'status' => 'already_attended',
                        'confidence' => $confidence
                    ];
                } else {
                    Attendance::create([
                        'student_id' => $studentId,
                        'class_id' => $session->class_id,
                        'attendance_session_id' => $session->id,
                        'date' => Carbon::today(),
                        'check_in' => Carbon::now(),
                        'status' => 'present',
                        'similarity_score' => $confidence,
                        'notes' => 'Face recognition verification'
                    ]);

                    $results[] = [
                        'student_name' => $student->name,
                        'status' => 'new_attendance',
                        'confidence' => $confidence
                    ];
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                \Illuminate\Support\Facades\Log::error("Failed to record attendance", ['error' => $e->getMessage()]);
            }
        }

        return $results;
    }

    public function autoMarkAbsent(\App\Models\AttendanceSession $session): void
    {
        if ($session->status !== 'active') return;

        DB::beginTransaction();
        try {
            $session->update([
                'status' => 'closed',
                'closed_at' => now(),
            ]);

            $classModel = \App\Models\ClassModel::find($session->class_id);
            if (!$classModel) {
                DB::rollBack();
                return;
            }

            $enrolledStudentIds = $this->getEnrolledStudentIds($classModel);

            $attendedStudentIds = Attendance::where('attendance_session_id', $session->id)
                ->pluck('student_id')
                ->toArray();

            $absentStudentIds = array_diff($enrolledStudentIds, $attendedStudentIds);

            $absentRecords = [];
            foreach ($absentStudentIds as $id) {
                $absentRecords[] = [
                    'student_id' => $id,
                    'class_id' => $session->class_id,
                    'attendance_session_id' => $session->id,
                    'date' => Carbon::today(),
                    'status' => 'absent',
                    'notes' => 'Auto-alpha on session close',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            if (!empty($absentRecords)) {
                Attendance::insert($absentRecords);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function isStudentEnrolledInClass(int $studentId, ClassModel $classModel): bool
    {
        $hasEnrollment = ClassEnrollment::where('class_id', $classModel->id)
            ->where('status', 'active')
            ->exists();

        if ($hasEnrollment) {
            return ClassEnrollment::where('class_id', $classModel->id)
                ->where('student_id', $studentId)
                ->where('status', 'active')
                ->exists();
        }

        // Fallback for legacy data where enrollments were never seeded.
        return Student::where('id', $studentId)
            ->where('cohort_id', $classModel->cohort_id)
            ->where('status', 'active')
            ->exists();
    }

    private function getEnrolledStudentIds(ClassModel $classModel): array
    {
        $enrollmentQuery = ClassEnrollment::where('class_id', $classModel->id)
            ->where('status', 'active');

        if ($enrollmentQuery->exists()) {
            return $enrollmentQuery->pluck('student_id')->toArray();
        }

        // Fallback for legacy data where enrollments were never seeded.
        return Student::where('cohort_id', $classModel->cohort_id)
            ->where('status', 'active')
            ->pluck('id')
            ->toArray();
    }

    /**
     * Record manual attendance entry
     */
    public function recordManualAttendance(array $data): Attendance
    {
        DB::beginTransaction();
        try {
            // Handle file upload if exists
            if (isset($data['attachment'])) {
                $data['attachment_path'] = $data['attachment']->store('attendance-attachments', 'public');
                unset($data['attachment']);
            }

            // Set manual flag
            $data['is_manual'] = true;
            $data['recorded_by'] = Auth::id();

            // Create attendance record
            $attendance = Attendance::create($data);

            // Create log entry
            AttendanceLog::create([
                'student_id' => $data['student_id'],
                'class_id' => $data['class_id'],
                'timestamp' => now(),
                'confidence_score' => 1.0, // Manual entry = 100% confidence
                'is_verified' => true,
                'device_info' => 'Manual Entry by ' . (Auth::user()?->name ?? 'Unknown User'),
            ]);

            DB::commit();
            return $attendance;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update attendance record
     */
    public function updateAttendance(Attendance $attendance, array $data): Attendance
    {
        DB::beginTransaction();
        try {
            // Handle file upload if exists
            if (isset($data['attachment'])) {
                // Delete old attachment if exists
                if ($attendance->attachment_path) {
                    Storage::disk('public')->delete($attendance->attachment_path);
                }
                $data['attachment_path'] = $data['attachment']->store('attendance-attachments', 'public');
                unset($data['attachment']);
            }

            // Update recorded_by
            $data['updated_by'] = Auth::id();

            // Update attendance
            $attendance->update($data);

            DB::commit();
            return $attendance->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Bulk update attendance status
     */
    public function bulkUpdateStatus(array $attendanceIds, string $status, ?string $notes = null): int
    {
        return Attendance::whereIn('id', $attendanceIds)
            ->update([
                'status' => $status,
                'notes' => $notes,
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]);
    }

    /**
     * Get attendance statistics for a class
     */
    public function getClassStatistics(int $classId, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = Attendance::where('class_id', $classId);

        if ($startDate) {
            $query->whereDate('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('date', '<=', $endDate);
        }

        $total = $query->count();
        $present = $query->clone()->where('status', AttendanceStatus::PRESENT->value)->count();
        $late = $query->clone()->where('status', AttendanceStatus::LATE->value)->count();
        $absent = $query->clone()->where('status', AttendanceStatus::ABSENT->value)->count();
        $excused = $query->clone()->where('status', AttendanceStatus::EXCUSED->value)->count();

        $attended = $present + $late;
        $attendanceRate = $total > 0 ? round(($attended / $total) * 100, 2) : 0;

        return [
            'total' => $total,
            'present' => $present,
            'late' => $late,
            'absent' => $absent,
            'excused' => $excused,
            'attended' => $attended,
            'attendance_rate' => $attendanceRate,
        ];
    }

    /**
     * Get student attendance summary
     */
    public function getStudentSummary(int $studentId, ?int $classId = null): array
    {
        $query = Attendance::where('student_id', $studentId);

        if ($classId) {
            $query->where('class_id', $classId);
        }

        $total = $query->count();
        $present = $query->clone()->where('status', AttendanceStatus::PRESENT->value)->count();
        $late = $query->clone()->where('status', AttendanceStatus::LATE->value)->count();
        $absent = $query->clone()->where('status', AttendanceStatus::ABSENT->value)->count();
        $excused = $query->clone()->where('status', AttendanceStatus::EXCUSED->value)->count();

        $attended = $present + $late;
        $attendanceRate = $total > 0 ? round(($attended / $total) * 100, 2) : 0;

        return [
            'total' => $total,
            'present' => $present,
            'late' => $late,
            'absent' => $absent,
            'excused' => $excused,
            'attended' => $attended,
            'attendance_rate' => $attendanceRate,
        ];
    }

    /**
     * Get today's attendance for a class
     */
    public function getTodayAttendance(int $classId): \Illuminate\Database\Eloquent\Collection
    {
        return Attendance::with('student')
            ->where('class_id', $classId)
            ->whereDate('date', today())
            ->orderBy('check_in', 'desc')
            ->get();
    }

    /**
     * Check if student already attended today
     */
    public function hasAttendedToday(int $studentId, int $classId): bool
    {
        return Attendance::where('student_id', $studentId)
            ->where('class_id', $classId)
            ->whereDate('date', today())
            ->exists();
    }

    /**
     * Get attendance history for a class
     */
    public function getClassHistory(int $classId, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Attendance::with(['student', 'classModel.course'])
            ->where('class_id', $classId);

        // Apply filters
        if (isset($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        return $query->orderBy('date', 'desc')
            ->orderBy('check_in', 'desc')
            ->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Get students with low attendance
     */
    public function getLowAttendanceStudents(int $classId, float $threshold = 75.0): array
    {
        $students = Student::whereHas('attendances', function ($query) use ($classId) {
            $query->where('class_id', $classId);
        })->with(['attendances' => function ($query) use ($classId) {
            $query->where('class_id', $classId);
        }])->get();

        $lowAttendance = [];

        foreach ($students as $student) {
            $summary = $this->getStudentSummary($student->id, $classId);

            if ($summary['attendance_rate'] < $threshold) {
                $lowAttendance[] = [
                    'student' => $student,
                    'statistics' => $summary,
                ];
            }
        }

        // Sort by attendance rate (lowest first)
        usort($lowAttendance, function ($a, $b) {
            return $a['statistics']['attendance_rate'] <=> $b['statistics']['attendance_rate'];
        });

        return $lowAttendance;
    }

    /**
     * Delete attendance record
     */
    public function deleteAttendance(Attendance $attendance): bool
    {
        DB::beginTransaction();
        try {
            // Delete attachment if exists
            if ($attendance->attachment_path) {
                Storage::disk('public')->delete($attendance->attachment_path);
            }

            $attendance->delete();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
