{{-- resources/views/attendance/history/reports.blade.php --}}
@extends('layouts.app')

@section('title', 'Attendance Reports')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Attendance Reports</h2>
            <p class="text-muted">Generate comprehensive attendance reports</p>
        </div>
        <a href="{{ route('attendance.history.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to History
        </a>
    </div>

    <div class="row">
        <div class="col-md-4">
            {{-- Report Configuration --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Report Configuration</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('attendance.history.reports') }}">
                        <input type="hidden" name="generate" value="1">
                        
                        <div class="mb-3">
                            <label for="report_type" class="form-label">Report Type</label>
                            <select name="report_type" id="report_type" class="form-select" required>
                                <option value="summary" {{ request('report_type') == 'summary' ? 'selected' : '' }}>
                                    Summary Report
                                </option>
                                <option value="detailed" {{ request('report_type') == 'detailed' ? 'selected' : '' }}>
                                    Detailed Report
                                </option>
                                <option value="by_student" {{ request('report_type') == 'by_student' ? 'selected' : '' }}>
                                    By Student
                                </option>
                                <option value="by_class" {{ request('report_type') == 'by_class' ? 'selected' : '' }}>
                                    By Class
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="class_id" class="form-label">Class (Optional)</label>
                            <select name="class_id" id="class_id" class="form-select">
                                <option value="">All Classes</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->course->course_name }} - {{ $class->course->course_code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="date_from" class="form-label">From Date</label>
                                    <input type="date" name="date_from" id="date_from" class="form-control" 
                                           value="{{ request('date_from', now()->subMonth()->format('Y-m-d')) }}" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="date_to" class="form-label">To Date</label>
                                    <input type="date" name="date_to" id="date_to" class="form-control" 
                                           value="{{ request('date_to', now()->format('Y-m-d')) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="format" class="form-label">Output Format</label>
                            <select name="format" id="format" class="form-select" required>
                                <option value="view" {{ request('format') == 'view' ? 'selected' : '' }}>
                                    View in Browser
                                </option>
                                <option value="excel" {{ request('format') == 'excel' ? 'selected' : '' }}>
                                    Excel (.xlsx)
                                </option>
                                <option value="pdf" {{ request('format') == 'pdf' ? 'selected' : '' }}>
                                    PDF Document
                                </option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-chart-line"></i> Generate Report
                        </button>
                    </form>
                </div>
            </div>

            {{-- Quick Reports --}}
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">Quick Reports</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('attendance.history.reports', [
                            'generate' => 1,
                            'report_type' => 'summary',
                            'date_from' => now()->startOfMonth()->format('Y-m-d'),
                            'date_to' => now()->format('Y-m-d'),
                            'format' => 'view'
                        ]) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-calendar-month"></i> This Month Summary
                        </a>
                        <a href="{{ route('attendance.history.reports', [
                            'generate' => 1,
                            'report_type' => 'summary',
                            'date_from' => now()->startOfWeek()->format('Y-m-d'),
                            'date_to' => now()->format('Y-m-d'),
                            'format' => 'view'
                        ]) }}" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-calendar-week"></i> This Week Summary
                        </a>
                        <a href="{{ route('attendance.history.reports', [
                            'generate' => 1,
                            'report_type' => 'detailed',
                            'date_from' => now()->format('Y-m-d'),
                            'date_to' => now()->format('Y-m-d'),
                            'format' => 'view'
                        ]) }}" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-calendar-day"></i> Today Detailed
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            {{-- Report Types Info --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Report Types</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <h6 class="text-primary">
                                    <i class="fas fa-chart-pie"></i> Summary Report
                                </h6>
                                <p class="mb-2">Overview statistik kehadiran dengan total present, late, dan absent.</p>
                                <small class="text-muted">Best for: Quick overview dan dashboard</small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <h6 class="text-success">
                                    <i class="fas fa-list"></i> Detailed Report
                                </h6>
                                <p class="mb-2">Detail lengkap setiap record presensi dengan informasi mahasiswa dan kelas.</p>
                                <small class="text-muted">Best for: Audit trail dan verifikasi detail</small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <h6 class="text-info">
                                    <i class="fas fa-user-graduate"></i> By Student
                                </h6>
                                <p class="mb-2">Rangkuman kehadiran per mahasiswa dengan tingkat kehadiran.</p>
                                <small class="text-muted">Best for: Evaluasi performa individual</small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <h6 class="text-warning">
                                    <i class="fas fa-chalkboard-teacher"></i> By Class
                                </h6>
                                <p class="mb-2">Analisis kehadiran per kelas dan mata kuliah.</p>
                                <small class="text-muted">Best for: Monitoring kelas dan scheduling</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Reports History --}}
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">Recent Reports</h6>
                </div>
                <div class="card-body">
                    <div class="text-muted text-center py-3">
                        <i class="fas fa-clock fa-2x mb-2"></i>
                        <p class="mb-0">Recent report history will appear here</p>
                        <small>Generate reports to see history</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-adjust date range based on report type
    const reportType = document.getElementById('report_type');
    const dateFrom = document.getElementById('date_from');
    const dateTo = document.getElementById('date_to');
    
    reportType.addEventListener('change', function() {
        const today = new Date();
        const oneWeekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
        const oneMonthAgo = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);
        
        switch(this.value) {
            case 'summary':
                dateFrom.value = oneMonthAgo.toISOString().split('T')[0];
                break;
            case 'detailed':
                dateFrom.value = oneWeekAgo.toISOString().split('T')[0];
                break;
            case 'by_student':
            case 'by_class':
                dateFrom.value = oneMonthAgo.toISOString().split('T')[0];
                break;
        }
        dateTo.value = today.toISOString().split('T')[0];
    });
    
    // Validate date range
    dateFrom.addEventListener('change', validateDates);
    dateTo.addEventListener('change', validateDates);
    
    function validateDates() {
        const from = new Date(dateFrom.value);
        const to = new Date(dateTo.value);
        
        if (from > to) {
            dateTo.value = dateFrom.value;
        }
        
        // Max 1 year range
        const oneYearAfter = new Date(from.getTime() + 365 * 24 * 60 * 60 * 1000);
        if (to > oneYearAfter) {
            dateTo.value = oneYearAfter.toISOString().split('T')[0];
            alert('Maximum date range is 1 year');
        }
    }
});
</script>
@endsection