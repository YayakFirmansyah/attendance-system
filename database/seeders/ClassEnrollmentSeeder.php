<?php

namespace Database\Seeders;

use App\Models\ClassEnrollment;
use App\Models\ClassModel;
use App\Models\Student;
use Illuminate\Database\Seeder;

class ClassEnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $classes = ClassModel::where('status', 'active')->get();

        if ($classes->isEmpty()) {
            $this->command->warn('ClassEnrollmentSeeder skipped: no active classes found.');
            return;
        }

        foreach ($classes as $class) {
            $students = Student::where('status', 'active')
                ->where('cohort_id', $class->cohort_id)
                ->pluck('id');

            foreach ($students as $studentId) {
                ClassEnrollment::updateOrCreate(
                    [
                        'class_id' => $class->id,
                        'student_id' => $studentId,
                    ],
                    [
                        'status' => 'active',
                        'enrolled_at' => now()->toDateString(),
                    ]
                );
            }
        }
    }
}
