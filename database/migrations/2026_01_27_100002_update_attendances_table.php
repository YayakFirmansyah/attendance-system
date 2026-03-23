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
        Schema::table('attendances', function (Blueprint $table) {
            // Add recorded_by field to track who recorded attendance
            if (!Schema::hasColumn('attendances', 'recorded_by')) {
                $table->foreignId('recorded_by')->after('notes')->nullable()->constrained('users')->onDelete('set null');
            }
            
            // Add is_manual field to distinguish manual vs auto attendance
            if (!Schema::hasColumn('attendances', 'is_manual')) {
                $table->boolean('is_manual')->after('recorded_by')->default(false);
            }
            
            // Add excused_reason for excused absences
            if (!Schema::hasColumn('attendances', 'excused_reason')) {
                $table->text('excused_reason')->after('is_manual')->nullable();
            }
            
            // Add attachment path for proof documents
            if (!Schema::hasColumn('attendances', 'attachment_path')) {
                $table->string('attachment_path')->after('excused_reason')->nullable();
            }
            
            // Add indexes for better query performance
            $table->index(['class_id', 'date'], 'idx_class_date');
            $table->index(['student_id', 'date'], 'idx_student_date');
            $table->index('status', 'idx_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex('idx_class_date');
            $table->dropIndex('idx_student_date');
            $table->dropIndex('idx_status');
            
            // Drop foreign key
            $table->dropForeign(['recorded_by']);
            
            // Drop columns
            $table->dropColumn(['recorded_by', 'is_manual', 'excused_reason', 'attachment_path']);
        });
    }
};
