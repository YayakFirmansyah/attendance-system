{{-- resources/views/classes/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Class Schedule')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Edit Class Schedule</h1>
    <a href="{{ route('classes.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to Schedules
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Update Schedule Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('classes.update', $class) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="course_id" class="form-label">Course</label>
                                <select class="form-select @error('course_id') is-invalid @enderror" id="course_id" name="course_id" required>
                                    <option value="">Select Course</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ old('course_id', $class->course_id) == $course->id ? 'selected' : '' }}>
                                            {{ $course->course_code }} - {{ $course->course_name }}
                                            @if($course->lecturer)
                                                ({{ $course->lecturer->name }})
                                            @else
                                                (No Lecturer Assigned)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('course_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="class_code" class="form-label">Class Code</label>
                                <input type="text" class="form-control @error('class_code') is-invalid @enderror" 
                                       id="class_code" name="class_code" value="{{ old('class_code', $class->class_code) }}" 
                                       placeholder="e.g., A, B, 01, 02" required>
                                @error('class_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Kelas berapa (A, B, 01, 02, dll)</div>
                            </div>
                        </div>
                    </div>

                    {{-- TAMBAHAN SEMESTER FIELD --}}
                    <div class="mb-3">
                        <label for="semester" class="form-label">Semester</label>
                        <select class="form-select @error('semester') is-invalid @enderror" id="semester" name="semester" required>
                            <option value="">Select Semester</option>
                            <option value="2023/2024 Ganjil" {{ old('semester', $class->semester) == '2023/2024 Ganjil' ? 'selected' : '' }}>2023/2024 Ganjil</option>
                            <option value="2023/2024 Genap" {{ old('semester', $class->semester) == '2023/2024 Genap' ? 'selected' : '' }}>2023/2024 Genap</option>
                            <option value="2024/2025 Ganjil" {{ old('semester', $class->semester) == '2024/2025 Ganjil' ? 'selected' : '' }}>2024/2025 Ganjil</option>
                            <option value="2024/2025 Genap" {{ old('semester', $class->semester) == '2024/2025 Genap' ? 'selected' : '' }}>2024/2025 Genap</option>
                            <option value="2025/2026 Ganjil" {{ old('semester', $class->semester) == '2025/2026 Ganjil' ? 'selected' : '' }}>2025/2026 Ganjil</option>
                            <option value="2025/2026 Genap" {{ old('semester', $class->semester) == '2025/2026 Genap' ? 'selected' : '' }}>2025/2026 Genap</option>
                        </select>
                        @error('semester')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Pilih semester akademik</div>
                    </div>

                    <div class="mb-3">
                        <label for="room_id" class="form-label">Room</label>
                        <select class="form-select @error('room_id') is-invalid @enderror" id="room_id" name="room_id" required>
                            <option value="">Select Room</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}" {{ old('room_id', $class->room_id) == $room->id ? 'selected' : '' }}>
                                    {{ $room->room_code }} - {{ $room->room_name }}
                                    ({{ $room->capacity }} seats, {{ ucfirst($room->type) }})
                                </option>
                            @endforeach
                        </select>
                        @error('room_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="day" class="form-label">Day</label>
                                <select class="form-select @error('day') is-invalid @enderror" id="day" name="day" required>
                                    <option value="">Select Day</option>
                                    <option value="monday" {{ old('day', $class->day) == 'monday' ? 'selected' : '' }}>Monday</option>
                                    <option value="tuesday" {{ old('day', $class->day) == 'tuesday' ? 'selected' : '' }}>Tuesday</option>
                                    <option value="wednesday" {{ old('day', $class->day) == 'wednesday' ? 'selected' : '' }}>Wednesday</option>
                                    <option value="thursday" {{ old('day', $class->day) == 'thursday' ? 'selected' : '' }}>Thursday</option>
                                    <option value="friday" {{ old('day', $class->day) == 'friday' ? 'selected' : '' }}>Friday</option>
                                    <option value="saturday" {{ old('day', $class->day) == 'saturday' ? 'selected' : '' }}>Saturday</option>
                                    <option value="sunday" {{ old('day', $class->day) == 'sunday' ? 'selected' : '' }}>Sunday</option>
                                </select>
                                @error('day')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="start_time" class="form-label">Start Time</label>
                                <input type="time" class="form-control @error('start_time') is-invalid @enderror" 
                                       id="start_time" name="start_time" value="{{ old('start_time', $class->start_time) }}" required>
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="end_time" class="form-label">End Time</label>
                                <input type="time" class="form-control @error('end_time') is-invalid @enderror" 
                                       id="end_time" name="end_time" value="{{ old('end_time', $class->end_time) }}" required>
                                @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="capacity" class="form-label">Capacity</label>
                                <input type="number" class="form-control @error('capacity') is-invalid @enderror" 
                                       id="capacity" name="capacity" value="{{ old('capacity', $class->capacity) }}" 
                                       min="1" max="100" placeholder="e.g., 30" required>
                                @error('capacity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Maximum number of students</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="active" {{ old('status', $class->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $class->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('classes.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Schedule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection