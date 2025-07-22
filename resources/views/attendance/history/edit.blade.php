{{-- resources/views/attendance/history/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Attendance')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Edit Attendance Record</h5>
                    <a href="{{ route('attendance.history.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
                <div class="card-body">
                    {{-- Student & Class Info --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Student Information</h6>
                                    <p class="mb-1"><strong>{{ $attendance->student->name }}</strong></p>
                                    <p class="mb-1 text-muted">{{ $attendance->student->student_id }}</p>
                                    <p class="mb-0 text-muted">{{ $attendance->student->program_study }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Class Information</h6>
                                    <p class="mb-1"><strong>{{ $attendance->classModel->course->course_name }}</strong></p>
                                    <p class="mb-1 text-muted">{{ $attendance->classModel->course->course_code }}</p>
                                    <p class="mb-0 text-muted">{{ $attendance->date->format('l, d F Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Edit Form --}}
                    <form method="POST" action="{{ route('attendance.history.update', $attendance) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="present" {{ old('status', $attendance->status) == 'present' ? 'selected' : '' }}>
                                            Present
                                        </option>
                                        <option value="late" {{ old('status', $attendance->status) == 'late' ? 'selected' : '' }}>
                                            Late
                                        </option>
                                        <option value="absent" {{ old('status', $attendance->status) == 'absent' ? 'selected' : '' }}>
                                            Absent
                                        </option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Original Confidence</label>
                                    <div class="form-control-plaintext">
                                        @if($attendance->similarity_score)
                                            <span class="badge bg-info">
                                                {{ round($attendance->similarity_score * 100, 1) }}%
                                            </span>
                                        @else
                                            <span class="text-muted">Manual Entry</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="check_in" class="form-label">Check In Time</label>
                                    <input type="time" name="check_in" id="check_in" 
                                           class="form-control @error('check_in') is-invalid @enderror"
                                           value="{{ old('check_in', $attendance->check_in ? $attendance->check_in->format('H:i') : '') }}">
                                    @error('check_in')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Leave empty if student was absent</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="check_out" class="form-label">Check Out Time</label>
                                    <input type="time" name="check_out" id="check_out" 
                                           class="form-control @error('check_out') is-invalid @enderror"
                                           value="{{ old('check_out', $attendance->check_out ? $attendance->check_out->format('H:i') : '') }}">
                                    @error('check_out')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Optional</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea name="notes" id="notes" rows="3" 
                                      class="form-control @error('notes') is-invalid @enderror"
                                      placeholder="Add any notes about this attendance record...">{{ old('notes', $attendance->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Original Data Info --}}
                        @if($attendance->similarity_score || $attendance->created_at != $attendance->updated_at)
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h6 class="card-title">Record Information</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <strong>Created:</strong> {{ $attendance->created_at->format('d M Y H:i') }}
                                        </small>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <strong>Last Modified:</strong> {{ $attendance->updated_at->format('d M Y H:i') }}
                                        </small>
                                    </div>
                                </div>
                                @if($attendance->similarity_score)
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <strong>Detection Method:</strong> Face Recognition (Auto)
                                    </small>
                                </div>
                                @else
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <strong>Detection Method:</strong> Manual Entry
                                    </small>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('attendance.history.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Attendance
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status');
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');
    
    function toggleTimeInputs() {
        const isAbsent = statusSelect.value === 'absent';
        checkInInput.disabled = isAbsent;
        checkOutInput.disabled = isAbsent;
        
        if (isAbsent) {
            checkInInput.value = '';
            checkOutInput.value = '';
        }
    }
    
    statusSelect.addEventListener('change', toggleTimeInputs);
    toggleTimeInputs(); // Initial call
});
</script>
@endsection