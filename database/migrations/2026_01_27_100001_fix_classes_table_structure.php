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
        Schema::table('classes', function (Blueprint $table) {
            // Drop old room column if exists
            if (Schema::hasColumn('classes', 'room')) {
                $table->dropColumn('room');
            }
            
            // Add room_id if not exists
            if (!Schema::hasColumn('classes', 'room_id')) {
                $table->foreignId('room_id')->after('course_id')->nullable()->constrained('rooms')->onDelete('set null');
            }
            
            // Add capacity if not exists
            if (!Schema::hasColumn('classes', 'capacity')) {
                $table->integer('capacity')->after('room_id')->default(0);
            }
            
            // Add audit fields if not exists
            if (!Schema::hasColumn('classes', 'created_by')) {
                $table->foreignId('created_by')->after('status')->nullable()->constrained('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('classes', 'updated_by')) {
                $table->foreignId('updated_by')->after('created_by')->nullable()->constrained('users')->onDelete('set null');
            }
            
            // Add indexes for performance
            $table->index(['day', 'start_time'], 'idx_schedule');
            $table->index('semester', 'idx_semester');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex('idx_schedule');
            $table->dropIndex('idx_semester');
            
            // Drop new columns
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['room_id']);
            
            $table->dropColumn(['created_by', 'updated_by', 'room_id', 'capacity']);
            
            // Restore old room column
            $table->string('room')->nullable();
        });
    }
};
