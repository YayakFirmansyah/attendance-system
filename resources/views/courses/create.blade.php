@extends('layouts.app')

@section('title', 'Create Course')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
    <div>
        <h2 class="h3 mb-1 fw-bold text-primary">Create New Course</h2>
        <p class="text-muted mb-0">Tambahkan mata kuliah baru ke dalam sistem</p>
    </div>
    <div class="mt-3 mt-md-0 d-flex gap-2">
        <a href="{{ route('courses.index') }}" class="btn btn-light border shadow-sm rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i> Kembali
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 mb-5">
            <div class="card-header bg-transparent border-0 pt-4 pb-2 px-4">
                <h5 class="fw-bold mb-0 text-primary"><i class="fas fa-book me-2"></i> Course Information</h5>
            </div>
            <div class="card-body px-4 pb-4">
                <form action="{{ route('courses.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="course_code" class="form-label">Course Code</label>
                                <input type="text" class="form-control @error('course_code') is-invalid @enderror"
                                    id="course_code" name="course_code" value="{{ old('course_code') }}"
                                    placeholder="e.g., TI101" required>
                                @error('course_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="credits" class="form-label">Credits (SKS)</label>
                                <select class="form-select @error('credits') is-invalid @enderror" id="credits" name="credits" required>
                                    <option value="">Select Credits</option>
                                    @for($i = 1; $i <= 6; $i++)
                                        <option value="{{ $i }}" {{ old('credits') == $i ? 'selected' : '' }}>
                                        {{ $i }} SKS
                                        </option>
                                        @endfor
                                </select>
                                @error('credits')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="course_name" class="form-label">Course Name</label>
                        <input type="text" class="form-control @error('course_name') is-invalid @enderror"
                            id="course_name" name="course_name" value="{{ old('course_name') }}"
                            placeholder="e.g., Introduction to Programming" required>
                        @error('course_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="faculty" class="form-label">Faculty</label>
                                <input type="text" class="form-control @error('faculty') is-invalid @enderror"
                                    id="faculty" name="faculty" value="{{ old('faculty') }}"
                                    placeholder="e.g., Computer Science" required>
                                @error('faculty')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="lecturer_id" class="form-label">Lecturer</label>
                                <select class="form-select @error('lecturer_id') is-invalid @enderror" id="lecturer_id" name="lecturer_id" required>
                                    <option value="">Select Lecturer</option>
                                    @foreach($lecturers as $lecturer)
                                    <option value="{{ $lecturer->id }}" {{ old('lecturer_id') == $lecturer->id ? 'selected' : '' }}>
                                        {{ $lecturer->name }} ({{ $lecturer->employee_id }})
                                    </option>
                                    @endforeach
                                </select>
                                @error('lecturer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                            id="description" name="description" rows="3"
                            placeholder="Course description (optional)">{{ old('description') }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-5 border-top pt-4">
                        <a href="{{ route('courses.index') }}" class="btn btn-light border rounded-pill px-4">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                            <i class="fas fa-save me-1"></i> Create Course
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection