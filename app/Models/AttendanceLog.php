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
        'is_verified',
        'device_info',
        'api_response',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'confidence_score' => 'decimal:3',
        'is_verified' => 'boolean',
        'api_response' => 'array',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function classModel()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    // Accessors
    public function getConfidencePercentageAttribute()
    {
        return round($this->confidence_score * 100, 1);
    }

    public function getCapturedImageUrlAttribute()
    {
        if (!$this->captured_image) {
            return null;
        }
        
        return asset('storage/' . config('app.face_images_path', 'attendance_captures') . '/' . $this->captured_image);
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeUnverified($query)
    {
        return $query->where('is_verified', false);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('timestamp', today());
    }

    public function scopeByClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }
}