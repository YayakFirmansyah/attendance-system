<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cohorts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('angkatan')->nullable();
            $table->string('fakultas')->nullable();
            $table->string('program_studi')->nullable();
            $table->string('kelas')->nullable();
            $table->integer('semester')->nullable();
            $table->timestamps();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('cohort_id')->nullable()->constrained('cohorts')->nullOnDelete();
            $table->dropColumn(['program_study', 'faculty', 'semester']);
        });

        // Keep an index on course_id so FK classes.course_id -> courses.id stays valid
        // while replacing the old composite unique key.
        Schema::table('classes', function (Blueprint $table) {
            $table->index('course_id', 'classes_course_id_temp_index');
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropUnique(['course_id', 'class_code', 'semester']);
            $table->foreignId('cohort_id')->nullable()->constrained('cohorts')->nullOnDelete();
            $table->dropColumn(['semester', 'class_code']);
            $table->unique(['course_id', 'cohort_id']);
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropIndex('classes_course_id_temp_index');
        });
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->index('course_id', 'classes_course_id_temp_index');
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropUnique(['course_id', 'cohort_id']);
            $table->dropForeign(['cohort_id']);
            $table->dropColumn('cohort_id');
            $table->string('class_code');
            $table->integer('semester')->nullable();
            $table->unique(['course_id', 'class_code', 'semester']);
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropIndex('classes_course_id_temp_index');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['cohort_id']);
            $table->dropColumn('cohort_id');
            $table->string('program_study')->nullable();
            $table->string('faculty')->nullable();
            $table->integer('semester')->nullable();
        });

        Schema::dropIfExists('cohorts');
    }
};
