<?php
// database/seeders/ClassSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClassModel;
use App\Models\Course;

class ClassSeeder extends Seeder
{
    public function run()
    {
        $courses = Course::all();
        
        $classes = [
            [
                'course_id' => $courses->where('course_code', 'TIF001')->first()->id,
                'class_code' => 'A',
                'semester' => '2024/2025 Genap',
                'day' => 'monday',
                'start_time' => '08:00',
                'end_time' => '10:30',
                'room' => 'Lab Komputer 1',
                'capacity' => 30
            ],
            [
                'course_id' => $courses->where('course_code', 'TIF002')->first()->id,
                'class_code' => 'A',
                'semester' => '2024/2025 Genap',
                'day' => 'tuesday',
                'start_time' => '10:30',
                'end_time' => '13:00',
                'room' => 'Lab Komputer 2',
                'capacity' => 25
            ],
            [
                'course_id' => $courses->where('course_code', 'TIF003')->first()->id,
                'class_code' => 'A',
                'semester' => '2024/2025 Genap',
                'day' => 'wednesday',
                'start_time' => '13:00',
                'end_time' => '15:30',
                'room' => 'Lab AI',
                'capacity' => 20
            ]
        ];

        foreach ($classes as $class) {
            ClassModel::create($class);
        }
    }
}