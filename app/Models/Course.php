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
        'lecturer_name',
        'description',
        'status'
    ];

    protected $casts = [
        'credits' => 'integer',
    ];

    public function classes()
    {
        return $this->hasMany(ClassModel::class, 'course_id');
    }
}