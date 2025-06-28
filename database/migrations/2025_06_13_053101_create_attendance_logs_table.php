<?php
// database/migrations/xxxx_xx_xx_create_attendance_logs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->timestamp('timestamp');
            $table->string('captured_image')->nullable();
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->json('detected_faces')->nullable(); // Multiple faces info
            $table->string('device_info')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_logs');
    }
};