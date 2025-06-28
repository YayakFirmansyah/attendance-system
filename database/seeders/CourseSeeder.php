<?php
// database/seeders/CourseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    public function run()
    {
        $courses = [
            [
                'course_code' => 'TIF001',
                'course_name' => 'Algoritma dan Pemrograman',
                'credits' => 3,
                'faculty' => 'Fakultas Teknik',
                'lecturer_name' => 'Dr. Ir. John Doe, M.Kom'
            ],
            [
                'course_code' => 'TIF002',
                'course_name' => 'Basis Data',
                'credits' => 3,
                'faculty' => 'Fakultas Teknik',
                'lecturer_name' => 'Prof. Dr. Jane Smith, M.T'
            ],
            [
                'course_code' => 'TIF003',
                'course_name' => 'Kecerdasan Buatan',
                'credits' => 3,
                'faculty' => 'Fakultas Teknik',
                'lecturer_name' => 'Dr. Michael Johnson, M.Kom'
            ]
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }
    }
}