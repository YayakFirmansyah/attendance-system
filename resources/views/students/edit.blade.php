{{-- resources/views/students/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Student')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
    <div>
        <h2 class="h3 mb-1 fw-bold text-primary">Edit Student</h2>
        <p class="text-muted mb-0">Perbarui data mahasiswa dan registrasi wajah</p>
    </div>
    <div class="mt-3 mt-md-0 d-flex gap-2">
        <a href="{{ route('students.show', $student) }}" class="btn btn-outline-info rounded-pill px-3 shadow-sm">
            <i class="fas fa-eye me-1"></i> View Details
        </a>
        <a href="{{ route('students.index') }}" class="btn btn-light border shadow-sm rounded-pill px-3">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 mb-5">
            <div class="card-header bg-transparent border-0 pt-4 pb-2 px-4">
                <h5 class="fw-bold mb-0 text-primary"><i class="fas fa-user-edit me-2"></i> Update Student Information</h5>
            </div>
            <div class="card-body px-4 pb-4">
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
                                <label for="cohort_id" class="form-label">Cohort / Rombel <span class="text-danger">*</span></label>
                                <select class="form-select @error('cohort_id') is-invalid @enderror"
                                    id="cohort_id" name="cohort_id" required>
                                    <option value="">Select Cohort</option>
                                    @foreach($cohorts as $cohort)
                                        <option value="{{ $cohort->id }}" {{ old('cohort_id', $student->cohort_id) == $cohort->id ? 'selected' : '' }}>
                                            {{ $cohort->name }} ({{ $cohort->angkatan }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('cohort_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror"
                                    id="status" name="status" required>
                                    <option value="active" {{ old('status', $student->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $student->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="graduated" {{ old('status', $student->status) == 'graduated' ? 'selected' : '' }}>Graduated</option>
                                </select>
                                @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-5 border-top pt-4">
                        <a href="{{ route('students.show', $student) }}" class="btn btn-light border rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                            <i class="fas fa-save me-1"></i> Update Student
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
            // Reset to original image or default
            @if($student - > profile_photo)
            preview.innerHTML = `
                <img src="{{ $student->profile_photo_url }}" 
                     class="rounded-circle" 
                     style="width: 100%; height: 100%; object-fit: cover;" 
                     alt="{{ $student->name }}">
            `;
            @else
            preview.innerHTML = `
                <div class="bg-light border rounded-circle d-flex align-items-center justify-content-center" 
                     style="width: 100%; height: 100%;">
                    <i class="fas fa-user fa-3x text-muted"></i>
                </div>
            `;
            @endif
        }
    }
</script>
@endpush