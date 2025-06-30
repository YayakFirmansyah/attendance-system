<?php
// database/migrations/xxxx_xx_xx_create_attendances_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->date('date');
            $table->time('check_in');
            $table->time('check_out')->nullable();
            $table->enum('status', ['present', 'late', 'absent'])->default('present');
            $table->decimal('similarity_score', 5, 3)->nullable(); // Face recognition confidence
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Prevent duplicate attendance for same student on same day in same class
            $table->unique(['student_id', 'class_id', 'date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendances');
    }
};