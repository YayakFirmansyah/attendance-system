{{-- resources/views/students/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Students Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Students Management</h1>
    <a href="{{ route('students.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Student
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Program Study</th>
                        <th>Semester</th>
                        <th>Face Encodings</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        <tr>
                            <td>
                                @if($student->profile_photo)
                                    <img src="{{ $student->profile_photo_url }}" 
                                         class="rounded-circle" width="40" height="40">
                                @else
                                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                @endif
                            </td>
                            <td><strong>{{ $student->student_id }}</strong></td>
                            <td>{{ $student->name }}</td>
                            <td>{{ $student->program_study }}</td>
                            <td>{{ $student->semester }}</td>
                            <td>
                                <span class="badge {{ $student->faceEncodings->count() > 0 ? 'bg-success' : 'bg-warning' }}">
                                    {{ $student->faceEncodings->count() }} faces
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $student->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($student->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('students.show', $student) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('students.faces', $student) }}" 
                                       class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-camera"></i>
                                    </a>
                                    <a href="{{ route('students.edit', $student) }}" 
                                       class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No students found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{ $students->links() }}
    </div>
</div>
@endsection