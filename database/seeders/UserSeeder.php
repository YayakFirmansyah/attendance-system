<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin user
        User::updateOrCreate(
            ['email' => 'admin@presensi.com'],
            [
                'name' => 'Administrator',
                'email' => 'admin@presensi.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'employee_id' => 'ADM001',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Create sample Dosen user
        User::updateOrCreate(
            ['email' => 'dosen@presensi.com'],
            [
                'name' => 'Dr. Dosen Sample',
                'email' => 'dosen@presensi.com',
                'password' => Hash::make('dosen123'),
                'role' => 'dosen',
                'employee_id' => 'DOS001',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Default users created successfully!');
        $this->command->info('Admin: admin@presensi.com / admin123');
        $this->command->info('Dosen: dosen@presensi.com / dosen123');
    }
}