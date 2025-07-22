{{-- resources/views/attendance/history/index.blade.php - LAYOUT FIXED --}}
@extends('layouts.app')

@section('title', 'Attendance History')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div class="mb-3 mb-md-0">
            <h2 class="h3 mb-1">Attendance History</h2>
            <p class="text-muted mb-0">Kelola dan monitor data presensi mahasiswa</p>
        </div>
        <div class="d-flex flex-column flex-md-row gap-2">
            <a href="{{ route('attendance.history.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> <span class="d-none d-sm-inline">Manual Entry</span>
            </a>
            <a href="{{ route('attendance.history.reports') }}" class="btn btn-info">
                <i class="fas fa-chart-bar"></i> <span class="d-none d-sm-inline">Reports</span>
            </a>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-6 col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ number_format($stats['total']) }}</h4>
                            <small>Total Records</small>
                        </div>
                        <i class="fas fa-list fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ number_format($stats['present']) }}</h4>
                            <small>Present</small>
                        </div>
                        <i class="fas fa-check fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ number_format($stats['late']) }}</h4>
                            <small>Late</small>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ number_format($stats['absent']) }}</h4>
                            <small>Absent</small>
                        </div>
                        <i class="fas fa-times fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-filter"></i> Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small">Class</label>
                    <select name="class_id" class="form-select form-select-sm">
                        <option value="">All Classes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->course->course_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-sm-6">
                    <label class="form-label small">Date</label>
                    <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}">
                </div>
                <div class="col-md-2 col-sm-6">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                        <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                        <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                    </select>
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small">Student</label>
                    <input type="text" name="student_search" class="form-control form-control-sm" 
                           placeholder="Name or Student ID" value="{{ request('student_search') }}">
                </div>
                <div class="col-md-2 col-sm-12 d-flex align-items-end">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="{{ route('attendance.history.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </div>
            </form>
            
            {{-- Results Info --}}
            <div class="mt-3">
                <small class="text-muted">
                    Showing {{ $attendances->firstItem() ?? 0 }} to {{ $attendances->lastItem() ?? 0 }} 
                    of {{ $attendances->total() }} results
                </small>
            </div>
        </div>
    </div>

    {{-- Attendance Records --}}
    <form id="bulkForm" method="POST" action="{{ route('attendance.history.bulk-edit') }}">
        @csrf
        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                <h5 class="mb-2 mb-md-0">Attendance Records</h5>
                <div class="d-flex gap-2 w-100 w-md-auto">
                    <select name="bulk_action" class="form-select form-select-sm" style="min-width: 140px;" disabled>
                        <option value="">Bulk Action</option>
                        <option value="present">Mark Present</option>
                        <option value="late">Mark Late</option>
                        <option value="absent">Mark Absent</option>
                        <option value="delete">Delete</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-warning" disabled id="bulkSubmit">
                        Apply
                    </button>
                </div>
            </div>
            
            <div class="card-body p-0">
                @if($attendances->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="30" class="ps-3">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Student</th>
                                <th class="d-none d-md-table-cell">Class</th>
                                <th class="d-none d-lg-table-cell">Date</th>
                                <th>Status</th>
                                <th class="d-none d-md-table-cell">Time</th>
                                <th class="d-none d-lg-table-cell">Confidence</th>
                                <th width="100">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attendances as $attendance)
                            <tr>
                                <td class="ps-3">
                                    <input type="checkbox" name="attendance_ids[]" 
                                           value="{{ $attendance->id }}" class="form-check-input record-checkbox">
                                </td>
                                <td>
                                    <div>
                                        <div class="fw-semibold">{{ $attendance->student->name }}</div>
                                        <small class="text-muted">{{ $attendance->student->student_id }}</small>
                                        <div class="d-md-none">
                                            <small class="text-muted">
                                                {{ $attendance->classModel->course->course_code }} • 
                                                {{ $attendance->date->format('d/m') }}
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <div>
                                        <div class="fw-medium">{{ $attendance->classModel->course->course_name }}</div>
                                        <small class="text-muted">{{ $attendance->classModel->course->course_code }}</small>
                                    </div>
                                </td>
                                <td class="d-none d-lg-table-cell">{{ $attendance->date->format('d M Y') }}</td>
                                <td>
                                    <span class="badge bg-{{ $attendance->status === 'present' ? 'success' : 
                                          ($attendance->status === 'late' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($attendance->status) }}
                                    </span>
                                    <div class="d-md-none">
                                        <small class="text-muted">
                                            {{ $attendance->check_in ? $attendance->check_in->format('H:i') : '-' }}
                                        </small>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    {{ $attendance->check_in ? $attendance->check_in->format('H:i') : '-' }}
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    @if($attendance->similarity_score)
                                        <span class="badge bg-info">
                                            {{ round($attendance->similarity_score * 100, 1) }}%
                                        </span>
                                    @else
                                        <span class="text-muted">Manual</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('attendance.history.show', $attendance) }}" 
                                           class="btn btn-sm btn-outline-primary"
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('attendance.history.edit', $attendance) }}" 
                                           class="btn btn-sm btn-outline-warning"
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No attendance records found</h5>
                    <p class="text-muted">Try adjusting your filters or create a manual entry</p>
                    <a href="{{ route('attendance.history.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Manual Entry
                    </a>
                </div>
                @endif
            </div>
            
            @if($attendances->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-center">
                    {{ $attendances->links() }}
                </div>
            </div>
            @endif
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.record-checkbox');
    const bulkAction = document.querySelector('select[name="bulk_action"]');
    const bulkSubmit = document.getElementById('bulkSubmit');
    
    // Select all functionality
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            toggleBulkActions();
        });
    }
    
    // Individual checkbox change
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', toggleBulkActions);
    });
    
    function toggleBulkActions() {
        const selectedCount = document.querySelectorAll('.record-checkbox:checked').length;
        const hasSelection = selectedCount > 0;
        
        if (bulkAction) bulkAction.disabled = !hasSelection;
        if (bulkSubmit) bulkSubmit.disabled = !hasSelection || !bulkAction?.value;
        
        if (selectAll) {
            selectAll.indeterminate = selectedCount > 0 && selectedCount < checkboxes.length;
            selectAll.checked = selectedCount === checkboxes.length;
        }
    }
    
    if (bulkAction) {
        bulkAction.addEventListener('change', function() {
            if (bulkSubmit) {
                bulkSubmit.disabled = !this.value || document.querySelectorAll('.record-checkbox:checked').length === 0;
            }
        });
    }
    
    // Bulk form submission
    const bulkForm = document.getElementById('bulkForm');
    if (bulkForm) {
        bulkForm.addEventListener('submit', function(e) {
            const selectedCount = document.querySelectorAll('.record-checkbox:checked').length;
            const action = bulkAction?.value;
            
            if (selectedCount === 0) {
                e.preventDefault();
                alert('Please select at least one record');
                return;
            }
            
            if (action === 'delete') {
                if (!confirm(`Are you sure you want to delete ${selectedCount} attendance record(s)? This action cannot be undone.`)) {
                    e.preventDefault();
                }
            } else if (action) {
                if (!confirm(`Are you sure you want to mark ${selectedCount} record(s) as ${action}?`)) {
                    e.preventDefault();
                }
            }
        });
    }
});
</script>
@endpush