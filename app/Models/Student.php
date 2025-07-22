<?php
// app/Models/Student.php - FIXED

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'name',
        'email',
        'program_study',
        'faculty',
        'semester',
        'phone',
        'status',
        'profile_photo'
    ];

    protected $casts = [
        'status' => 'string',
        'semester' => 'integer',
    ];

    protected $attributes = [
        'status' => 'active', // Default status
    ];

    // Relationships
    public function faceEncodings()
    {
        return $this->hasMany(FaceEncoding::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeBySemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }

    public function scopeByProgram($query, $program)
    {
        return $query->where('program_study', $program);
    }

    // Accessors
    public function getTodayAttendances()
    {
        return $this->attendances()->whereDate('date', today())->get();
    }

    public function getProfilePhotoUrlAttribute()
    {
        return $this->profile_photo ? asset('storage/students/' . $this->profile_photo) : null;
    }

    public function getAttendanceRateAttribute()
    {
        $total = $this->attendances->count();
        if ($total === 0) return 0;
        
        $present = $this->attendances->whereIn('status', ['present', 'late'])->count();
        return round(($present / $total) * 100, 1);
    }

    public function getIsFaceRegisteredAttribute()
    {
        try {
            // Gunakan static method untuk avoid dependency injection issues
            return $this->checkFaceRegistrationFromApi();
        } catch (\Exception $e) {
            // Jika API error, return false dan log error
            \Log::warning('Face registration check failed', [
                'student_id' => $this->id,
                'student_name' => $this->name,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check face registration from API
     */
    private function checkFaceRegistrationFromApi()
    {
        $apiUrl = config('app.python_api_url', 'http://localhost:5000');
        
        try {
            $response = \Http::timeout(5)->get($apiUrl . '/api/model-info');
            
            if ($response->successful()) {
                $data = $response->json();
                $classes = $data['model_info']['classes'] ?? [];
                
                $normalizedStudentName = strtolower(trim($this->name));
                
                foreach ($classes as $className) {
                    if (strtolower(trim($className)) === $normalizedStudentName) {
                        return true;
                    }
                }
            }
            
            return false;
            
        } catch (\Exception $e) {
            \Log::error('API face check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getFaceRegistrationStatusAttribute()
    {
        return $this->is_face_registered ? 'registered' : 'not_registered';
    }

    // Mutators
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucwords(strtolower($value));
    }
}