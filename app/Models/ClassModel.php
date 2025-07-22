<?php
// app/Models/ClassModel.php - FIXED

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'course_id',
        'room_id',      // PERBAIKAN: gunakan room_id
        'class_code',
        'semester',     // TAMBAHAN: semester field
        'day',
        'start_time',
        'end_time',
        'status'
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    // Relationships
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function room()
    {
        return $this->belongsTo(\App\Models\Room::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'class_id');
    }

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class, 'class_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeToday($query)
    {
        $today = strtolower(now()->format('l'));
        return $query->where('day', $today);
    }

    public function scopeBySemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }

    // Accessors
    public function getCapacityAttribute()
    {
        return $this->room ? $this->room->capacity : 0;
    }

    public function getFullClassNameAttribute()
    {
        $courseName = $this->course->course_name ?? 'Unknown Course';
        $classCode = $this->class_code ? ' - ' . $this->class_code : '';
        return $courseName . $classCode;
    }

    public function getRoomNameAttribute()
    {
        return $this->room ? $this->room->room_code . ' - ' . $this->room->room_name : 'No Room';
    }
}