<?php

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
                'course_id' => optional($courses->where('course_code', 'TIF001')->first())->id,
                'class_code' => 'A',
                'semester' => '2024/2025 Genap',
                'day' => 'monday',
                'start_time' => '08:00:00',
                'end_time' => '10:30:00',
                'room_id' => null
            ],
            [
                'course_id' => optional($courses->where('course_code', 'TIF002')->first())->id,
                'class_code' => 'A',
                'semester' => '2024/2025 Genap',
                'day' => 'tuesday',
                'start_time' => '10:30:00',
                'end_time' => '13:00:00',
                'room_id' => null
            ],
            [
                'course_id' => optional($courses->where('course_code', 'TIF003')->first())->id,
                'class_code' => 'A',
                'semester' => '2024/2025 Genap',
                'day' => 'wednesday',
                'start_time' => '13:00:00',
                'end_time' => '15:30:00',
                'room_id' => null
            ]
        ];

        foreach ($classes as $class) {
            ClassModel::create($class);
        }
    }
}