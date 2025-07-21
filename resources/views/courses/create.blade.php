@extends('layouts.app')

@section('title', 'Create Course')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Create New Course</h1>
    <a href="{{ route('courses.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to Courses
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Course Information</h5>
            </div>
            <div class="card-body">
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

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('courses.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Course
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection