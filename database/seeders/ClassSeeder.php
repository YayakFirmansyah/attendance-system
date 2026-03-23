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

        $room1 = \App\Models\Room::where('room_code', 'LAB-01')->first()->id;
        $room2 = \App\Models\Room::where('room_code', 'T-301')->first()->id;
        $cohort = \App\Models\Cohort::first()->id;

        $classes = [
            [
                'course_id' => optional($courses->where('course_code', 'TIF001')->first())->id,
                'cohort_id' => $cohort,
                'room_id' => $room1,
                'day' => 'monday',
                'start_time' => '08:00:00',
                'end_time' => '10:30:00',
                'status' => 'active'
            ],
            [
                'course_id' => optional($courses->where('course_code', 'TIF002')->first())->id,
                'cohort_id' => $cohort,
                'room_id' => $room2,
                'day' => 'tuesday',
                'start_time' => '10:30:00',
                'end_time' => '13:00:00',
                'status' => 'active'
            ],
            [
                'course_id' => optional($courses->where('course_code', 'TIF003')->first())->id,
                'cohort_id' => $cohort,
                'room_id' => $room1,
                'day' => 'wednesday',
                'start_time' => '13:00:00',
                'end_time' => '15:30:00',
                'status' => 'active'
            ]
        ];

        foreach ($classes as $class) {
            ClassModel::updateOrCreate(
                [
                    'course_id' => $class['course_id'],
                    'cohort_id' => $class['cohort_id']
                ],
                $class
            );
        }
    }
}