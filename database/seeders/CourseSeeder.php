<?php
// database/seeders/CourseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\User;

class CourseSeeder extends Seeder
{
    public function run()
    {
        $lecturerId = User::where('email', 'dosen@presensi.com')->value('id')
            ?? User::where('role', 'dosen')->orderBy('id')->value('id');

        if (!$lecturerId) {
            $this->command->warn('CourseSeeder: no dosen user found, courses will be created without lecturer assignment.');
        }

        $courses = [
            [
                'course_code' => 'TIF001',
                'course_name' => 'Algoritma dan Pemrograman',
                'credits' => 3,
                'faculty' => 'Fakultas Teknik',
                'lecturer_id' => $lecturerId,
            ],
            [
                'course_code' => 'TIF002',
                'course_name' => 'Basis Data',
                'credits' => 3,
                'faculty' => 'Fakultas Teknik',
                'lecturer_id' => $lecturerId,
            ],
            [
                'course_code' => 'TIF003',
                'course_name' => 'Kecerdasan Buatan',
                'credits' => 3,
                'faculty' => 'Fakultas Teknik',
                'lecturer_id' => $lecturerId,
            ],
        ];

        foreach ($courses as $course) {
            Course::updateOrCreate(
                ['course_code' => $course['course_code']],
                $course
            );
        }
    }
}
