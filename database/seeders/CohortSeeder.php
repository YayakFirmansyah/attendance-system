<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cohort;

class CohortSeeder extends Seeder
{
    public function run()
    {
        $cohorts = [
            [
                'name' => 'TI - Kelas A - 2024',
                'angkatan' => 2024,
                'fakultas' => 'Fakultas Teknik',
                'program_studi' => 'Teknik Informatika',
                'kelas' => 'A',
                'semester' => 6
            ],
            [
                'name' => 'TI - Kelas B - 2024',
                'angkatan' => 2024,
                'fakultas' => 'Fakultas Teknik',
                'program_studi' => 'Teknik Informatika',
                'kelas' => 'B',
                'semester' => 6
            ]
        ];

        foreach ($cohorts as $cohort) {
            Cohort::updateOrCreate(
                ['name' => $cohort['name']],
                $cohort
            );
        }
    }
}
