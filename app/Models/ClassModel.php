<?php
// app/Models/ClassModel.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'course_id',
        'class_code',
        'semester',
        'day',
        'start_time',
        'end_time',
        'room',
        'capacity',
        'status'
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'capacity' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'class_id');
    }

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class, 'class_id');
    }

    public function getTodayAttendances()
    {
        return $this->attendances()->whereDate('date', today())->get();
    }

    public function getFullNameAttribute()
    {
        return $this->course->course_name . ' - ' . $this->class_code;
    }
}