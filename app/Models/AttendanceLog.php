<?php
// app/Models/AttendanceLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'class_id',
        'timestamp',
        'captured_image',
        'confidence_score',
        'detected_faces',
        'device_info'
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'detected_faces' => 'array',
        'confidence_score' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function classModel()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function getCapturedImageUrlAttribute()
    {
        return $this->captured_image ? asset('storage/attendance_logs/' . $this->captured_image) : null;
    }
}