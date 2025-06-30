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
        'device_info',
        'log_type',
        'error_message'
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'detected_faces' => 'array',
        'confidence_score' => 'decimal:3'
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

    public function getDeviceNameAttribute()
    {
        if (!$this->device_info) return 'Unknown';
        
        if (strpos($this->device_info, 'Chrome') !== false) return 'Chrome Browser';
        if (strpos($this->device_info, 'Firefox') !== false) return 'Firefox Browser';
        if (strpos($this->device_info, 'Safari') !== false) return 'Safari Browser';
        if (strpos($this->device_info, 'Edge') !== false) return 'Edge Browser';
        
        return 'Unknown Browser';
    }

    public function getFaceCountAttribute()
    {
        if (!$this->detected_faces || !is_array($this->detected_faces)) return 0;
        
        if (isset($this->detected_faces['faces_count'])) {
            return $this->detected_faces['faces_count'];
        }
        
        return count($this->detected_faces);
    }
}