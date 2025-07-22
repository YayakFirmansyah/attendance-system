{{-- resources/views/attendance/history/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Manual Attendance Entry')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Manual Attendance Entry</h5>
                    <a href="{{ route('attendance.history.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Manual Entry:</strong> Gunakan untuk menambah data presensi secara manual, 
                        misalnya untuk koreksi atau entry data yang tidak terdeteksi sistem.
                    </div>

                    <form method="POST" action="{{ route('attendance.history.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="student_id" class="form-label">Student <span class="text-danger">*</span></label>
                                    <select name="student_id" id="student_id" class="form-select @error('student_id') is-invalid @enderror" required>
                                        <option value="">Select Student</option>
                                        @foreach($students as $student)
                                            <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}
                                                    data-program="{{ $student->program_study }}">
                                                {{ $student->name }} - {{ $student->student_id }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('student_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div id="studentInfo" class="form-text"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="class_id" class="form-label">Class <span class="text-danger">*</span></label>
                                    <select name="class_id" id="class_id" class="form-select @error('class_id') is-invalid @enderror" required>
                                        <option value="">Select Class</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}
                                                    data-course="{{ $class->course->course_name }}"
                                                    data-code="{{ $class->course->course_code }}"
                                                    data-time="{{ $class->start_time->format('H:i') }} - {{ $class->end_time->format('H:i') }}">
                                                {{ $class->course->course_name }} - {{ $class->course->course_code }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('class_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div id="classInfo" class="form-text"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="date" name="date" id="date" 
                                           class="form-control @error('date') is-invalid @enderror"
                                           value="{{ old('date', now()->format('Y-m-d')) }}" required>
                                    @error('date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="">Select Status</option>
                                        <option value="present" {{ old('status') == 'present' ? 'selected' : '' }}>
                                            Present
                                        </option>
                                        <option value="late" {{ old('status') == 'late' ? 'selected' : '' }}>
                                            Late
                                        </option>
                                        <option value="absent" {{ old('status') == 'absent' ? 'selected' : '' }}>
                                            Absent
                                        </option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row" id="timeInputs">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="check_in" class="form-label">Check In Time</label>
                                    <input type="time" name="check_in" id="check_in" 
                                           class="form-control @error('check_in') is-invalid @enderror"
                                           value="{{ old('check_in') }}">
                                    @error('check_in')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Required for Present/Late status</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="check_out" class="form-label">Check Out Time</label>
                                    <input type="time" name="check_out" id="check_out" 
                                           class="form-control @error('check_out') is-invalid @enderror"
                                           value="{{ old('check_out') }}">
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
                                      placeholder="Add any notes for this manual entry...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Duplicate Check Warning --}}
                        <div id="duplicateWarning" class="alert alert-warning d-none">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Warning:</strong> An attendance record might already exist for this combination.
                            Please check the attendance history before proceeding.
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('attendance.history.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Create Attendance
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
    const studentSelect = document.getElementById('student_id');
    const classSelect = document.getElementById('class_id');
    const statusSelect = document.getElementById('status');
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');
    const timeInputs = document.getElementById('timeInputs');
    const studentInfo = document.getElementById('studentInfo');
    const classInfo = document.getElementById('classInfo');
    
    // Show student info
    studentSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option.value) {
            const program = option.dataset.program;
            studentInfo.innerHTML = `<i class="fas fa-graduation-cap"></i> ${program}`;
        } else {
            studentInfo.innerHTML = '';
        }
    });
    
    // Show class info
    classSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option.value) {
            const course = option.dataset.course;
            const code = option.dataset.code;
            const time = option.dataset.time;
            classInfo.innerHTML = `<i class="fas fa-clock"></i> ${time} • <i class="fas fa-book"></i> ${code}`;
        } else {
            classInfo.innerHTML = '';
        }
    });
    
    // Handle status change
    statusSelect.addEventListener('change', function() {
        const isAbsent = this.value === 'absent';
        
        checkInInput.disabled = isAbsent;
        checkOutInput.disabled = isAbsent;
        
        if (isAbsent) {
            checkInInput.value = '';
            checkOutInput.value = '';
            timeInputs.style.opacity = '0.5';
        } else {
            timeInputs.style.opacity = '1';
            if (this.value === 'late') {
                // Auto-suggest late time based on class schedule
                const classOption = classSelect.options[classSelect.selectedIndex];
                if (classOption.value) {
                    const startTime = classOption.dataset.time.split(' - ')[0];
                    checkInInput.value = startTime;
                }
            }
        }
    });
    
    // Simple duplicate check (client-side warning)
    function checkDuplicate() {
        const studentId = studentSelect.value;
        const classId = classSelect.value;
        const date = document.getElementById('date').value;
        const warning = document.getElementById('duplicateWarning');
        
        if (studentId && classId && date) {
            // In real implementation, you'd make an AJAX call to check
            // For now, just show warning if all fields are filled
            warning.classList.remove('d-none');
        } else {
            warning.classList.add('d-none');
        }
    }
    
    studentSelect.addEventListener('change', checkDuplicate);
    classSelect.addEventListener('change', checkDuplicate);
    document.getElementById('date').addEventListener('change', checkDuplicate);
});
</script>
@endsection