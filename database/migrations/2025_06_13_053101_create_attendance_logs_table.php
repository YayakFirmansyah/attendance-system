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
            $table->foreignId('student_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->timestamp('timestamp');
            $table->string('captured_image')->nullable(); // Path to captured image
            $table->decimal('confidence_score', 5, 3)->nullable(); // Face recognition confidence
            $table->json('detected_faces')->nullable(); // Detailed face detection results
            $table->text('device_info')->nullable(); // Browser/device information
            $table->enum('log_type', ['detection', 'attendance', 'error'])->default('detection');
            $table->text('error_message')->nullable(); // Error details if any
            $table->timestamps();
            
            $table->index(['class_id', 'timestamp']);
            $table->index(['student_id', 'timestamp']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_logs');
    }
};