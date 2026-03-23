<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClassModel;
use App\Models\Course;
use App\Models\Cohort;
use App\Models\Room;

class ClassSeeder extends Seeder
{
    public function run()
    {
        if (!Course::query()->exists() || !Room::query()->exists() || !Cohort::query()->exists()) {
            $this->command->warn('ClassSeeder skipped: requires courses, rooms, and cohorts.');
            return;
        }

        $rooms = Room::whereIn('room_code', ['LAB-01', 'T-301'])->get()->keyBy('room_code');
        $cohort = Cohort::query()->orderBy('angkatan')->orderBy('kelas')->first();

        if (!$cohort || !$rooms->has('LAB-01') || !$rooms->has('T-301')) {
            $this->command->warn('ClassSeeder skipped: default cohort or required rooms not found.');
            return;
        }

        $courseMap = Course::whereIn('course_code', ['TIF001', 'TIF002', 'TIF003'])
            ->get()
            ->keyBy('course_code');

        $classes = [
            [
                'course_code' => 'TIF001',
                'cohort_id' => $cohort->id,
                'room_id' => $rooms['LAB-01']->id,
                'day' => 'monday',
                'start_time' => '08:00:00',
                'end_time' => '10:30:00',
                'status' => 'active',
            ],
            [
                'course_code' => 'TIF002',
                'cohort_id' => $cohort->id,
                'room_id' => $rooms['T-301']->id,
                'day' => 'tuesday',
                'start_time' => '10:30:00',
                'end_time' => '13:00:00',
                'status' => 'active',
            ],
            [
                'course_code' => 'TIF003',
                'cohort_id' => $cohort->id,
                'room_id' => $rooms['LAB-01']->id,
                'day' => 'wednesday',
                'start_time' => '13:00:00',
                'end_time' => '15:30:00',
                'status' => 'active',
            ],
        ];

        foreach ($classes as $class) {
            $course = $courseMap->get($class['course_code']);

            if (!$course) {
                $this->command->warn("ClassSeeder skipped record: course {$class['course_code']} not found.");
                continue;
            }

            unset($class['course_code']);
            $class['course_id'] = $course->id;

            ClassModel::updateOrCreate(
                [
                    'course_id' => $class['course_id'],
                    'cohort_id' => $class['cohort_id'],
                ],
                $class
            );
        }
    }
}
