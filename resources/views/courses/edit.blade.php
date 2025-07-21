@extends('layouts.app')

@section('title', 'Edit Course')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Edit Course</h1>
    <a href="{{ route('courses.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to Courses
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Update Course Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('courses.update', $course) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="course_code" class="form-label">Course Code</label>
                                <input type="text" class="form-control @error('course_code') is-invalid @enderror" 
                                       id="course_code" name="course_code" 
                                       value="{{ old('course_code', $course->course_code) }}" required>
                                @error('course_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="credits" class="form-label">Credits (SKS)</label>
                                <select class="form-select @error('credits') is-invalid @enderror" id="credits" name="credits" required>
                                    @for($i = 1; $i <= 6; $i++)
                                        <option value="{{ $i }}" {{ old('credits', $course->credits) == $i ? 'selected' : '' }}>
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
                               id="course_name" name="course_name" 
                               value="{{ old('course_name', $course->course_name) }}" required>
                        @error('course_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="faculty" class="form-label">Faculty</label>
                                <input type="text" class="form-control @error('faculty') is-invalid @enderror" 
                                       id="faculty" name="faculty" 
                                       value="{{ old('faculty', $course->faculty) }}" required>
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
                                        <option value="{{ $lecturer->id }}" {{ old('lecturer_id', $course->lecturer_id) == $lecturer->id ? 'selected' : '' }}>
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
                                  id="description" name="description" rows="3">{{ old('description', $course->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="active" {{ old('status', $course->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $course->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
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
                            <i class="fas fa-save"></i> Update Course
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection