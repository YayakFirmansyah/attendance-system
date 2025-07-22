{{-- resources/views/students/show.blade.php - OPTIMIZED VERSION --}}
@extends('layouts.app')

@section('title', 'Student Details')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Student Details</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Students</a></li>
                    <li class="breadcrumb-item active">{{ $student->name }}</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('students.edit', $student) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Student
            </a>
            <a href="{{ route('students.faces', $student) }}" class="btn btn-info">
                <i class="fas fa-user-circle"></i> Manage Faces
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            {{-- Student Info Card --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Student Information</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        @if($student->profile_photo_url)
                            <img src="{{ $student->profile_photo_url }}" alt="Profile" 
                                 class="rounded-circle" width="120" height="120">
                        @else
                            <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center" 
                                 style="width: 120px; height: 120px;">
                                <i class="fas fa-user fa-3x text-white"></i>
                            </div>
                        @endif
                    </div>
                    
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Name:</strong></td>
                            <td>{{ $student->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Student ID:</strong></td>
                            <td>{{ $student->student_id }}</td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td>{{ $student->email }}</td>
                        </tr>
                        <tr>
                            <td><strong>Program:</strong></td>
                            <td>{{ $student->program_study }}</td>
                        </tr>
                        <tr>
                            <td><strong>Faculty:</strong></td>
                            <td>{{ $student->faculty }}</td>
                        </tr>
                        <tr>
                            <td><strong>Semester:</strong></td>
                            <td>{{ $student->semester }}</td>
                        </tr>
                        <tr>
                            <td><strong>Phone:</strong></td>
                            <td>{{ $student->phone ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="badge bg-{{ $student->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($student->status) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Face Registration Status Card --}}
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">Face Registration Status</h6>
                </div>
                <div class="card-body">
                    @php $faceStatus = $student->face_registration_status @endphp
                    
                    @if($faceStatus['status'] === 'registered')
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> {{ $faceStatus['message'] }}
                        </div>
                    @elseif($faceStatus['status'] === 'not_registered')
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> {{ $faceStatus['message'] }}
                            <br><small>Upload face photos to enable recognition</small>
                        </div>
                    @elseif($faceStatus['status'] === 'api_offline')
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> {{ $faceStatus['message'] }}
                            <br><small>Start Flask API service to check face registration</small>
                        </div>
                    @elseif($faceStatus['status'] === 'api_error')
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle"></i> {{ $faceStatus['message'] }}
                            <br><small>Check API connection and try again</small>
                        </div>
                    @else
                        <div class="alert alert-secondary">
                            <i class="fas fa-question-circle"></i> {{ $faceStatus['message'] }}
                        </div>
                    @endif
                    
                    <div class="text-center mt-2">
                        <button class="btn btn-sm btn-outline-primary" onclick="refreshFaceStatus()">
                            <i class="fas fa-sync-alt"></i> Refresh Status
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            {{-- Attendance Statistics --}}
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $student->attendance_stats['total'] }}</h4>
                                    <small>Total Attendance</small>
                                </div>
                                <i class="fas fa-calendar-check fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $student->attendance_stats['present'] }}</h4>
                                    <small>Present</small>
                                </div>
                                <i class="fas fa-check fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $student->attendance_stats['late'] }}</h4>
                                    <small>Late</small>
                                </div>
                                <i class="fas fa-clock fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Attendance --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Recent Attendance</h6>
                </div>
                <div class="card-body">
                    @if($student->recent_attendances->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Course</th>
                                        <th>Check In</th>
                                        <th>Status</th>
                                        <th>Confidence</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($student->recent_attendances as $attendance)
                                    <tr>
                                        <td>{{ $attendance->date->format('d M Y') }}</td>
                                        <td>
                                            <small>
                                                <strong>{{ $attendance->classModel->course->course_name }}</strong><br>
                                                {{ $attendance->classModel->course->course_code }}
                                            </small>
                                        </td>
                                        <td>{{ $attendance->formatted_check_in }}</td>
                                        <td>
                                            <span class="badge bg-{{ $attendance->status === 'present' ? 'success' : 
                                                  ($attendance->status === 'late' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($attendance->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $attendance->similarity_percentage }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="text-center mt-3">
                            <p class="text-muted">
                                Attendance Rate: <strong>{{ $student->attendance_stats['rate'] }}%</strong>
                            </p>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No attendance records found</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function refreshFaceStatus() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    
    // Show loading
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking...';
    btn.disabled = true;
    
    // Clear cache and reload
    fetch(`{{ route('api.students.refresh-face-status', $student) }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to refresh status: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error refreshing status');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}
</script>
@endsection