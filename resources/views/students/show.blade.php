{{-- resources/views/students/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Student Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Student Details</h1>
    <div>
        <a href="{{ route('students.faces', $student) }}" class="btn btn-primary me-2">
            <i class="fas fa-camera"></i> Manage Face
        </a>
        <a href="{{ route('students.edit', $student) }}" class="btn btn-warning me-2">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="{{ route('students.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Students
        </a>
    </div>
</div>

<div class="row">
    {{-- Student Profile --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="mb-3">
                    @if($student->profile_photo)
                        <img src="{{ $student->profile_photo_url }}" 
                             class="rounded-circle mb-3" 
                             width="150" height="150" 
                             style="object-fit: cover;"
                             alt="{{ $student->name }}">
                    @else
                        <div class="bg-light border rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                             style="width: 150px; height: 150px;">
                            <i class="fas fa-user fa-4x text-muted"></i>
                        </div>
                    @endif
                </div>
                
                <h4>{{ $student->name }}</h4>
                <p class="text-muted mb-1">{{ $student->student_id }}</p>
                <span class="badge bg-{{ $student->status === 'active' ? 'success' : 
                      ($student->status === 'graduated' ? 'info' : 'secondary') }} fs-6">
                    {{ ucfirst($student->status) }}
                </span>
                
                <hr>
                
                <div class="text-start">
                    <p class="mb-2">
                        <i class="fas fa-envelope text-muted me-2"></i>
                        <small>{{ $student->email }}</small>
                    </p>
                    @if($student->phone)
                        <p class="mb-2">
                            <i class="fas fa-phone text-muted me-2"></i>
                            <small>{{ $student->phone }}</small>
                        </p>
                    @endif
                    <p class="mb-2">
                        <i class="fas fa-graduation-cap text-muted me-2"></i>
                        <small>{{ $student->program_study }}</small>
                    </p>
                    <p class="mb-2">
                        <i class="fas fa-university text-muted me-2"></i>
                        <small>{{ $student->faculty }}</small>
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-calendar text-muted me-2"></i>
                        <small>Semester {{ $student->semester }}</small>
                    </p>
                </div>
            </div>
        </div>

        {{-- Face Registration Status --}}
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">Face Registration</h6>
            </div>
            <div class="card-body">
                @php
                    $faceCount = $student->faceEncodings->count();
                @endphp
                
                @if($faceCount > 0)
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <strong>Registered</strong><br>
                        {{ $faceCount }} face encoding(s) available
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Not Registered</strong><br>
                        No face encodings found
                    </div>
                @endif
                
                <a href="{{ route('students.faces', $student) }}" class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-camera"></i> Manage Face Registration
                </a>
            </div>
        </div>
    </div>

    {{-- Student Details & Attendance --}}
    <div class="col-md-8">
        {{-- Quick Stats --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $student->attendances->count() }}</h4>
                                <small>Total Classes</small>
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
                                <h4 class="mb-0">{{ $student->attendances->where('status', 'present')->count() }}</h4>
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
                                <h4 class="mb-0">{{ $student->attendances->where('status', 'late')->count() }}</h4>
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
                @if($student->attendances->count() > 0)
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
                                @foreach($student->attendances->take(10) as $attendance)
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
                                    <td>
                                        @if($attendance->similarity_score)
                                            <small class="text-muted">{{ $attendance->similarity_percentage }}</small>
                                        @else
                                            <small class="text-muted">-</small>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if($student->attendances->count() > 10)
                        <div class="text-center mt-3">
                            <small class="text-muted">Showing 10 of {{ $student->attendances->count() }} records</small>
                        </div>
                    @endif
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-calendar-times fa-3x mb-2"></i>
                        <p>No attendance records found</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Face Encodings Info (if any) --}}
        @if($student->faceEncodings->count() > 0)
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">Face Encodings</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($student->faceEncodings as $encoding)
                            <div class="col-md-6 mb-2">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-fingerprint text-primary me-2"></i>
                                    <div>
                                        <small>
                                            <strong>Encoding #{{ $loop->iteration }}</strong><br>
                                            <span class="text-muted">{{ $encoding->created_at->format('d M Y H:i') }}</span>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@endsection