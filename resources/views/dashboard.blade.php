{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Dashboard</h1>
    <div>
        <span id="api-status" class="badge bg-secondary">Checking API...</span>
        <button class="btn btn-outline-primary btn-sm" onclick="checkApiStatus()">
            <i class="fas fa-sync"></i> Refresh
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title">{{ $totalStudents }}</h4>
                        <p class="card-text">Total Students</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title">{{ $totalClasses }}</h4>
                        <p class="card-text">Active Classes</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-chalkboard fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title">{{ $todayAttendances }}</h4>
                        <p class="card-text">Today's Attendance</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-calendar-check fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title">{{ $todayLogs }}</h4>
                        <p class="card-text">Recognition Logs</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-eye fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Today's Classes -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-calendar-day"></i>
                    Today's Classes
                </h5>
            </div>
            <div class="card-body">
                @if($todayClasses->count() > 0)
                    @foreach($todayClasses as $class)
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                            <div>
                                <strong>{{ $class->course->course_name }}</strong><br>
                                <small class="text-muted">
                                    {{ $class->room }} • 
                                    {{ $class->start_time }} - {{ $class->end_time }}
                                </small>
                            </div>
                            <div>
                                <a href="{{ route('attendance.scanner', $class) }}" 
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-camera"></i> Scan
                                </a>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted">No classes scheduled for today.</p>
                @endif
            </div>
        </div>
        
        <!-- Class Attendance Stats -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-bar"></i>
                    Today's Attendance Stats
                </h5>
            </div>
            <div class="card-body">
                @foreach($classAttendanceStats as $stat)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>{{ $stat['class']->course->course_name }}</span>
                            <span>{{ $stat['attendance_count'] }}/{{ $stat['class']->capacity }} ({{ $stat['percentage'] }}%)</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" style="width: {{ $stat['percentage'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-clock"></i>
                    Recent Activity
                </h5>
            </div>
            <div class="card-body">
                @if($recentLogs->count() > 0)
                    @foreach($recentLogs as $log)
                        <div class="d-flex align-items-center border-bottom pb-2 mb-2">
                            <div class="me-3">
                                <i class="fas fa-user-check text-success"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong>{{ $log->student->name }}</strong><br>
                                <small class="text-muted">
                                    {{ $log->classModel->course->course_name }} • 
                                    {{ $log->timestamp->format('H:i') }}
                                </small>
                            </div>
                            <div>
                                <span class="badge bg-success">
                                    {{ number_format($log->confidence_score * 100, 1) }}%
                                </span>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted">No recent activity.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function checkApiStatus() {
    $('#api-status').removeClass('bg-success bg-danger').addClass('bg-secondary').text('Checking...');
    
    $.get('{{ route("api.status") }}')
        .done(function(data) {
            console.log('API Status Response:', data);
            if (data.status === 'connected') {
                $('#api-status').removeClass('bg-secondary bg-danger').addClass('bg-success').text('API Connected');
            } else {
                $('#api-status').removeClass('bg-secondary bg-success').addClass('bg-danger').text('API Error');
            }
        })
        .fail(function(xhr, status, error) {
            console.error('API Status Error:', xhr.responseText);
            let errorMsg = 'API Disconnected';
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.message) {
                    errorMsg = response.message;
                }
            } catch (e) {
                // Use default error message
            }
            $('#api-status').removeClass('bg-secondary bg-success').addClass('bg-danger').text(errorMsg);
        });
}

// Check API status on page load
$(document).ready(function() {
    checkApiStatus();
    
    // Auto-refresh every 30 seconds
    setInterval(checkApiStatus, 30000);
});
</script>
@endpush