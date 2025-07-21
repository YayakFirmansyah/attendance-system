<?php
// database/migrations/xxxx_update_classes_table_add_room_id.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('classes', function (Blueprint $table) {
            // Add room_id foreign key
            $table->foreignId('room_id')->nullable()->after('course_id')->constrained()->onDelete('cascade');
            
            // Drop old room column if exists
            if (Schema::hasColumn('classes', 'room')) {
                $table->dropColumn('room');
            }
            
            // Drop capacity column since it comes from room
            if (Schema::hasColumn('classes', 'capacity')) {
                $table->dropColumn('capacity');
            }
        });
    }

    public function down()
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign(['room_id']);
            $table->dropColumn('room_id');
            $table->string('room')->nullable();
            $table->integer('capacity')->nullable();
        });
    }
};