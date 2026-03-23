<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'student_id',
        'enrolled_at',
        'status',
        'notes'
    ];

    protected $casts = [
        'enrolled_at' => 'date',
    ];

    // Relationships
    public function classModel()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    // Methods
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function drop(): bool
    {
        return $this->update(['status' => 'dropped']);
    }

    public function complete(): bool
    {
        return $this->update(['status' => 'completed']);
    }
}
