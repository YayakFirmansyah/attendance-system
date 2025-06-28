<?php
// app/Models/Student.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'name',
        'email',
        'program_study',
        'faculty',
        'semester',
        'phone',
        'status',
        'profile_photo'
    ];

    protected $casts = [
        'status' => 'string',
        'semester' => 'integer',
    ];

    public function faceEncodings()
    {
        return $this->hasMany(FaceEncoding::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function getTodayAttendances()
    {
        return $this->attendances()->whereDate('date', today())->get();
    }

    public function getProfilePhotoUrlAttribute()
    {
        return $this->profile_photo ? asset('storage/students/' . $this->profile_photo) : null;
    }
}