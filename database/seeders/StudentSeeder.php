<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;

class StudentSeeder extends Seeder
{
    public function run()
    {
        $data = [
            "214100001_ainung", "214100002_akbar", "214100003_aldi", "214100004_alfian",
            "214100005_amir", "214100006_ardian", "214100007_arif", "214100008_ariq",
            "214100009_aris", "214100010_arya", "214100011_bagas", "214100012_bahtiar",
            "214100013_cinta", "214100014_dafa", "214100015_dania", "214100016_denny",
            "214100017_dimas", "214100018_fahrul", "214100019_fajar", "214100020_farah",
            "214100021_fauzi", "214100022_femas", "214100023_fiki", "214100024_firman",
            "214100025_frendika", "214100026_galih", "214100027_ibnu", "214100028_jovita",
            "214100029_kader", "214100030_maruf", "214100031_mugni", "214100032_nadi",
            "214100033_nasya", "214100034_naufal", "214100035_nesa", "214100036_nugi",
            "214100037_panji", "214100038_praditya", "214100039_rasya", "214100040_rifaldi",
            "214100041_syafa", "214100042_syauqi", "214100043_wira", "214100044_yayak",
            "214100045_yudas", "214100046_zanuar", "214100047_zidan"
        ];

        foreach ($data as $index => $item) {
            [$student_id, $name] = explode('_', $item);
            Student::create([
                'student_id' => $student_id,
                'name' => ucfirst($name),
                'email' => strtolower($name) . '@student.univ.ac.id',
                'program_study' => 'Teknik Informatika',
                'faculty' => 'Fakultas Teknik',
                'semester' => 6,
                'phone' => '08' . str_pad((string)(123450000 + $index + 1), 9, '0', STR_PAD_LEFT)
            ]);
        }
    }
}
