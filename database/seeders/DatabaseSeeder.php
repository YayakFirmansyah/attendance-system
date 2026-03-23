<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            UserSeeder::class,
            CohortSeeder::class,
            StudentSeeder::class,
            CourseSeeder::class,
            RoomSeeder::class,
            ClassSeeder::class,
            ClassEnrollmentSeeder::class,
        ]);
    }
}
