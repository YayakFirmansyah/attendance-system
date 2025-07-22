{{-- resources/views/students/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Student')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Edit Student</h1>
    <div>
        <a href="{{ route('students.show', $student) }}" class="btn btn-outline-info me-2">
            <i class="fas fa-eye"></i> View Details
        </a>
        <a href="{{ route('students.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Students
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Update Student Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('students.update', $student) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    {{-- Profile Photo --}}
                    <div class="mb-4 text-center">
                        <div class="mb-3">
                            <div id="imagePreview" class="mx-auto" style="width: 120px; height: 120px;">
                                @if($student->profile_photo)
                                    <img src="{{ $student->profile_photo_url }}" 
                                         class="rounded-circle" 
                                         style="width: 100%; height: 100%; object-fit: cover;" 
                                         alt="{{ $student->name }}">
                                @else
                                    <div class="bg-light border rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 100%; height: 100%;">
                                        <i class="fas fa-user fa-3x text-muted"></i>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="profile_photo" class="form-label">Profile Photo</label>
                            <input type="file" class="form-control @error('profile_photo') is-invalid @enderror" 
                                   id="profile_photo" name="profile_photo" accept="image/*" onchange="previewImage(this)">
                            @error('profile_photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Max 2MB. JPG, PNG, GIF allowed. Leave empty to keep current photo.</div>
                        </div>
                    </div>

                    {{-- Basic Information --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="student_id" class="form-label">Student ID <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('student_id') is-invalid @enderror" 
                                       id="student_id" name="student_id" value="{{ old('student_id', $student->student_id) }}" 
                                       placeholder="e.g., 2023010001" required>
                                @error('student_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $student->name) }}" 
                                       placeholder="e.g., John Doe" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email', $student->email) }}" 
                               placeholder="e.g., john.doe@student.university.ac.id" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                               id="phone" name="phone" value="{{ old('phone', $student->phone) }}" 
                               placeholder="e.g., +62812345678">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Academic Information --}}
                    <h6 class="border-bottom pb-2 mb-3">Academic Information</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="program_study" class="form-label">Program Study <span class="text-danger">*</span></label>
                                <select class="form-select @error('program_study') is-invalid @enderror" 
                                        id="program_study" name="program_study" required>
                                    <option value="">Select Program Study</option>
                                    <option value="Teknik Informatika" {{ old('program_study', $student->program_study) == 'Teknik Informatika' ? 'selected' : '' }}>Teknik Informatika</option>
                                    <option value="Sistem Informasi" {{ old('program_study', $student->program_study) == 'Sistem Informasi' ? 'selected' : '' }}>Sistem Informasi</option>
                                    <option value="Teknik Komputer" {{ old('program_study', $student->program_study) == 'Teknik Komputer' ? 'selected' : '' }}>Teknik Komputer</option>
                                    <option value="Manajemen Informatika" {{ old('program_study', $student->program_study) == 'Manajemen Informatika' ? 'selected' : '' }}>Manajemen Informatika</option>
                                    <option value="Data Science" {{ old('program_study', $student->program_study) == 'Data Science' ? 'selected' : '' }}>Data Science</option>
                                </select>
                                @error('program_study')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="faculty" class="form-label">Faculty <span class="text-danger">*</span></label>
                                <select class="form-select @error('faculty') is-invalid @enderror" 
                                        id="faculty" name="faculty" required>
                                    <option value="">Select Faculty</option>
                                    <option value="Fakultas Teknik" {{ old('faculty', $student->faculty) == 'Fakultas Teknik' ? 'selected' : '' }}>Fakultas Teknik</option>
                                    <option value="Fakultas Ilmu Komputer" {{ old('faculty', $student->faculty) == 'Fakultas Ilmu Komputer' ? 'selected' : '' }}>Fakultas Ilmu Komputer</option>
                                    <option value="Fakultas Teknologi Informasi" {{ old('faculty', $student->faculty) == 'Fakultas Teknologi Informasi' ? 'selected' : '' }}>Fakultas Teknologi Informasi</option>
                                </select>
                                @error('faculty')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    {{-- Add more fields as needed --}}

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Student
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection