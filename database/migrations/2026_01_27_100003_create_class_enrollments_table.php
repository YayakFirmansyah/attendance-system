<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('class_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->date('enrolled_at')->default(now());
            $table->enum('status', ['active', 'dropped', 'completed'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Unique constraint: student can only enroll once per class
            $table->unique(['class_id', 'student_id'], 'unique_enrollment');
            
            // Indexes for performance
            $table->index('class_id', 'idx_class');
            $table->index('student_id', 'idx_student');
            $table->index('status', 'idx_enrollment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_enrollments');
    }
};
