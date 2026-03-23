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
        // Add indexes to students table
        Schema::table('students', function (Blueprint $table) {
            $table->index('student_id', 'idx_student_id');
            $table->index('status', 'idx_student_status');
            $table->index('email', 'idx_student_email');
        });

        // Add indexes to users table
        Schema::table('users', function (Blueprint $table) {
            $table->index('role', 'idx_role');
            $table->index('status', 'idx_user_status');
            if (Schema::hasColumn('users', 'employee_id')) {
                $table->index('employee_id', 'idx_employee_id');
            }
        });

        // Add indexes to courses table
        Schema::table('courses', function (Blueprint $table) {
            $table->index('status', 'idx_course_status');
            $table->index('faculty', 'idx_faculty');
        });

        // Add indexes to attendance_logs table
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->index('timestamp', 'idx_timestamp');
            $table->index('class_id', 'idx_log_class');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('idx_student_id');
            $table->dropIndex('idx_student_status');
            $table->dropIndex('idx_student_email');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_role');
            $table->dropIndex('idx_user_status');
            if (Schema::hasColumn('users', 'employee_id')) {
                $table->dropIndex('idx_employee_id');
            }
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex('idx_course_status');
            $table->dropIndex('idx_faculty');
        });

        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropIndex('idx_timestamp');
            $table->dropIndex('idx_log_class');
        });
    }
};
