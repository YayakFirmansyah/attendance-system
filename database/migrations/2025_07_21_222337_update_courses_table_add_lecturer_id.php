<?php
// database/migrations/xxxx_update_courses_table_add_lecturer_id.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            // Add lecturer_id foreign key
            $table->foreignId('lecturer_id')->nullable()->after('faculty')->constrained('users')->onDelete('set null');
            
            // Drop old lecturer_name column if exists
            if (Schema::hasColumn('courses', 'lecturer_name')) {
                $table->dropColumn('lecturer_name');
            }
        });
    }

    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['lecturer_id']);
            $table->dropColumn('lecturer_id');
            $table->string('lecturer_name')->nullable();
        });
    }
};