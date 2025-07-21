<?php
// app/Models/Course.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_code',
        'course_name',
        'credits',
        'faculty',
        'lecturer_id',
        'description',
        'status'
    ];

    // Relationships
    public function lecturer()
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    public function classes()
    {
        return $this->hasMany(ClassModel::class);
    }

    // Accessors
    public function getLecturerNameAttribute()
    {
        return $this->lecturer ? $this->lecturer->name : 'No Lecturer Assigned';
    }
}