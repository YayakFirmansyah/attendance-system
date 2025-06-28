<?php
// app/Models/FaceEncoding.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaceEncoding extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'encoding',
        'image_path',
        'is_primary'
    ];

    protected $casts = [
        'encoding' => 'array',
        'is_primary' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function getImageUrlAttribute()
    {
        return asset('storage/face_encodings/' . $this->image_path);
    }
}