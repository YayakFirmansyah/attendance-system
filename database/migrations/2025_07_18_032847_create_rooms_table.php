<?php
// database/migrations/xxxx_create_rooms_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_code')->unique();
            $table->string('room_name');
            $table->string('building')->nullable();
            $table->string('floor')->nullable();
            $table->integer('capacity');
            $table->enum('type', ['classroom', 'lab', 'auditorium', 'meeting_room'])->default('classroom');
            $table->text('facilities')->nullable(); // JSON or text
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rooms');
    }
};