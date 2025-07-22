{{-- resources/views/students/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Add New Student')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Add New Student</h1>
    <a href="{{ route('students.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to Students
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Student Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('students.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    {{-- Profile Photo --}}
                    <div class="mb-4 text-center">
                        <div class="mb-3">
                            <div id="imagePreview" class="mx-auto" style="width: 120px; height: 120px;">
                                <div class="bg-light border rounded-circle d-flex align-items-center justify-content-center" 
                                     style="width: 100%; height: 100%;">
                                    <i class="fas fa-user fa-3x text-muted"></i>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="profile_photo" class="form-label">Profile Photo</label>
                            <input type="file" class="form-control @error('profile_photo') is-invalid @enderror" 
                                   id="profile_photo" name="profile_photo" accept="image/*" onchange="previewImage(this)">
                            @error('profile_photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Max 2MB. JPG, PNG, GIF allowed.</div>
                        </div>
                    </div>

                    {{-- Basic Information --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="student_id" class="form-label">Student ID <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('student_id') is-invalid @enderror" 
                                       id="student_id" name="student_id" value="{{ old('student_id') }}" 
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
                                       id="name" name="name" value="{{ old('name') }}" 
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
                               id="email" name="email" value="{{ old('email') }}" 
                               placeholder="e.g., john.doe@student.university.ac.id" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                               id="phone" name="phone" value="{{ old('phone') }}" 
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
                                    <option value="Teknik Informatika" {{ old('program_study') == 'Teknik Informatika' ? 'selected' : '' }}>Teknik Informatika</option>
                                    <option value="Sistem Informasi" {{ old('program_study') == 'Sistem Informasi' ? 'selected' : '' }}>Sistem Informasi</option>
                                    <option value="Teknik Komputer" {{ old('program_study') == 'Teknik Komputer' ? 'selected' : '' }}>Teknik Komputer</option>
                                    <option value="Manajemen Informatika" {{ old('program_study') == 'Manajemen Informatika' ? 'selected' : '' }}>Manajemen Informatika</option>
                                    <option value="Data Science" {{ old('program_study') == 'Data Science' ? 'selected' : '' }}>Data Science</option>
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
                                    <option value="Fakultas Teknik" {{ old('faculty') == 'Fakultas Teknik' ? 'selected' : '' }}>Fakultas Teknik</option>
                                    <option value="Fakultas Ilmu Komputer" {{ old('faculty') == 'Fakultas Ilmu Komputer' ? 'selected' : '' }}>Fakultas Ilmu Komputer</option>
                                    <option value="Fakultas Teknologi Informasi" {{ old('faculty') == 'Fakultas Teknologi Informasi' ? 'selected' : '' }}>Fakultas Teknologi Informasi</option>
                                </select>
                                @error('faculty')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="semester" class="form-label">Current Semester <span class="text-danger">*</span></label>
                                <select class="form-select @error('semester') is-invalid @enderror" 
                                        id="semester" name="semester" required>
                                    <option value="">Select Semester</option>
                                    @for($i = 1; $i <= 8; $i++)
                                        <option value="{{ $i }}" {{ old('semester') == $i ? 'selected' : '' }}>
                                            Semester {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                                @error('semester')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status" required>
                                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="graduated" {{ old('status') == 'graduated' ? 'selected' : '' }}>Graduated</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('students.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Student
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.innerHTML = `
                <img src="${e.target.result}" 
                     class="rounded-circle" 
                     style="width: 100%; height: 100%; object-fit: cover;" 
                     alt="Preview">
            `;
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.innerHTML = `
            <div class="bg-light border rounded-circle d-flex align-items-center justify-content-center" 
                 style="width: 100%; height: 100%;">
                <i class="fas fa-user fa-3x text-muted"></i>
            </div>
        `;
    }
}

// Auto-generate email based on student ID and name
document.getElementById('student_id').addEventListener('blur', generateEmail);
document.getElementById('name').addEventListener('blur', generateEmail);

function generateEmail() {
    const studentId = document.getElementById('student_id').value;
    const name = document.getElementById('name').value;
    const emailField = document.getElementById('email');
    
    if (studentId && name && !emailField.value) {
        const cleanName = name.toLowerCase().replace(/\s+/g, '.');
        emailField.value = `${cleanName}.${studentId}@student.university.ac.id`;
    }
}
</script>
@endpush