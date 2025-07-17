<?php
// database/migrations/xxxx_create_attendance_logs_table.php - SIMPLIFIED

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
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->timestamp('timestamp');
            $table->decimal('confidence_score', 5, 3)->default(0);
            $table->boolean('is_verified')->default(true); // Hanya log yang berhasil
            $table->string('device_info')->nullable();
            $table->timestamps();
            
            // Prevent duplicate dalam 30 detik
            $table->unique(['student_id', 'class_id', 'timestamp']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_logs');
    }
};