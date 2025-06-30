<?php
// app/Models/Attendance.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'class_id',
        'date',
        'check_in',
        'check_out',
        'status',
        'similarity_score',
        'notes'
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime:H:i:s',
        'check_out' => 'datetime:H:i:s',
        'similarity_score' => 'decimal:3'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function classModel()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function logs()
    {
        return $this->hasMany(AttendanceLog::class, 'student_id', 'student_id')
                    ->where('class_id', $this->class_id)
                    ->whereDate('timestamp', $this->date);
    }

    public function isLate()
    {
        if (!$this->check_in || !$this->classModel) {
            return false;
        }
        
        return $this->check_in > $this->classModel->start_time;
    }

    public function getFormattedCheckInAttribute()
    {
        return $this->check_in ? $this->check_in->format('H:i') : '-';
    }

    public function getSimilarityPercentageAttribute()
    {
        return $this->similarity_score ? round($this->similarity_score * 100, 1) . '%' : '-';
    }
}