<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomSeeder extends Seeder
{
    public function run()
    {
        $rooms = [
            [
                'room_code' => 'LAB-01',
                'room_name' => 'Laboratorium Komputer Dasar',
                'capacity' => 40,
                'building' => 'Gedung A',
                'type' => 'lab',
                'status' => 'active'
            ],
            [
                'room_code' => 'T-301',
                'room_name' => 'Ruang Teori 301',
                'capacity' => 50,
                'building' => 'Gedung Teknik',
                'type' => 'classroom',
                'status' => 'active'
            ]
        ];

        foreach ($rooms as $room) {
            Room::updateOrCreate(
                ['room_code' => $room['room_code']],
                $room
            );
        }
    }
}
