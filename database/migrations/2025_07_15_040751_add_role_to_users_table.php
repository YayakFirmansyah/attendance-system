<?php
// database/migrations/xxxx_xx_xx_add_role_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'dosen'])->default('dosen')->after('email');
            $table->string('employee_id')->unique()->nullable()->after('role'); // NIP untuk dosen
            $table->enum('status', ['active', 'inactive'])->default('active')->after('employee_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'employee_id', 'status']);
        });
    }
};