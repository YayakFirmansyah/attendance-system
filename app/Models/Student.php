<?php
// app/Models/Student.php - FIXED VERSION

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

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
        'status' => 'active',
    ];

    protected $withCount = ['attendances'];

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

    // Cached attendance statistics
    public function getAttendanceStatsAttribute()
    {
        return Cache::remember("student_attendance_stats_{$this->id}", 300, function () {
            $total = $this->attendances()->count();
            $present = $this->attendances()->whereIn('status', ['present', 'late'])->count();
            $late = $this->attendances()->where('status', 'late')->count();
            
            return [
                'total' => $total,
                'present' => $present,
                'late' => $late,
                'rate' => $total > 0 ? round(($present / $total) * 100, 1) : 0
            ];
        });
    }

    // FIXED: Face registration check
    public function getIsFaceRegisteredAttribute()
    {
        return Cache::remember("student_face_registered_{$this->id}", 300, function () {
            try {
                $apiUrl = config('app.python_api_url', 'http://localhost:5000');
                $response = \Http::timeout(2)->get($apiUrl . '/api/model-info');
                
                if ($response->successful()) {
                    $data = $response->json();
                    $classes = $data['model_info']['classes'] ?? [];
                    
                    // Check dengan student_id (prioritas utama)
                    if (in_array($this->student_id, $classes)) {
                        return true;
                    }
                    
                    // Check dengan nama (fallback)
                    if (in_array($this->name, $classes)) {
                        return true;
                    }
                    
                    // Check case insensitive
                    $studentIdLower = strtolower($this->student_id);
                    $nameLower = strtolower($this->name);
                    
                    foreach ($classes as $class) {
                        $classLower = strtolower($class);
                        if ($classLower === $studentIdLower || $classLower === $nameLower) {
                            return true;
                        }
                    }
                    
                    return false;
                } else {
                    return 'api_error';
                }
                
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                return 'api_offline';
            } catch (\Exception $e) {
                return 'api_error';
            }
        });
    }

    // Face registration status
    public function getFaceRegistrationStatusAttribute()
    {
        $status = $this->is_face_registered;
        
        switch ($status) {
            case true:
                return ['status' => 'registered', 'message' => 'Face registered'];
            case false:
                return ['status' => 'not_registered', 'message' => 'Face not registered'];
            case 'api_offline':
                return ['status' => 'api_offline', 'message' => 'API Flask belum berjalan'];
            case 'api_error':
                return ['status' => 'api_error', 'message' => 'API error - cek koneksi'];
            default:
                return ['status' => 'unknown', 'message' => 'Status tidak diketahui'];
        }
    }

    // Recent attendances
    public function getRecentAttendancesAttribute()
    {
        return Cache::remember("student_recent_attendances_{$this->id}", 300, function () {
            return $this->attendances()
                ->with(['classModel.course'])
                ->orderBy('date', 'desc')
                ->limit(10)
                ->get();
        });
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

    public function getTodayAttendances()
    {
        return $this->attendances()
            ->with('classModel.course')
            ->whereDate('date', today())
            ->get();
    }

    public function getProfilePhotoUrlAttribute()
    {
        return $this->profile_photo ? asset('storage/students/' . $this->profile_photo) : null;
    }
}