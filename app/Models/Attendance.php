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
        'status',
        'notes'
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime:H:i',
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
}