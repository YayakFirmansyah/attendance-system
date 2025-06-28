<?php
// database/migrations/xxxx_xx_xx_create_students_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_id')->unique(); // NIM
            $table->string('name');
            $table->string('email')->unique();
            $table->string('program_study');
            $table->string('faculty');
            $table->integer('semester');
            $table->string('phone')->nullable();
            $table->enum('status', ['active', 'inactive', 'graduated', 'dropped'])->default('active');
            $table->string('profile_photo')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('students');
    }
};