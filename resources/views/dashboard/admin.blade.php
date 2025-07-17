@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>Dashboard Admin</h1>
        <p class="text-muted mb-0">Selamat datang, {{ auth()->user()->name }}</p>
    </div>
    <div>
        <span id="api-status" class="badge bg-secondary">Checking API...</span>
        <button class="btn btn-outline-primary btn-sm" onclick="checkApiStatus()">
            <i class="fas fa-sync"></i> Refresh
        </button>
    </div>
</div>

<!-- Admin Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title">{{ $totalStudents }}</h4>
                        <p class="card-text">Total Mahasiswa</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('students.index') }}" class="text-white text-decoration-none">
                    <small>Kelola Mahasiswa <i class="fas fa-arrow-right"></i></small>
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title">{{ $totalUsers }}</h4>
                        <p class="card-text">Total Users</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-user-tie fa-2x"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('users.index') }}" class="text-white text-decoration-none">
                    <small>Kelola Users <i class="fas fa-arrow-right"></i></small>
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title">{{ $totalClasses }}</h4>
                        <p class="card-text">Kelas Aktif</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-chalkboard fa-2x"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="#" class="text-white text-decoration-none">
                    <small>Kelola Kelas <i class="fas fa-arrow-right"></i></small>
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title">{{ $todayAttendances }}</h4>
                        <p class="card-text">Presensi Hari Ini</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-calendar-check fa-2x"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('attendance.index') }}" class="text-white text-decoration-none">
                    <small>Lihat Detail <i class="fas fa-arrow-right"></i></small>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Today's Classes & Management -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-calendar-day"></i>
                    Kelas Hari Ini
                </h5>
                <small class="text-muted">{{ $now->format('l, d M Y') }}</small>
            </div>
            <div class="card-body">
                @if($todayClasses->count() > 0)
                    @foreach($todayClasses as $class)
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                            <div>
                                <strong>{{ $class->course->course_name }}</strong><br>
                                <small class="text-muted">
                                    {{ $class->room ?? 'No Room' }} • 
                                    {{ $class->start_time }} - {{ $class->end_time }}
                                </small>
                            </div>
                            <div>
                                <a href="{{ route('attendance.scanner', $class) }}" 
                                   class="btn btn-sm btn-primary me-1">
                                    <i class="fas fa-camera"></i> Scan
                                </a>
                                <a href="{{ route('attendance.class', $class) }}" 
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-calendar-times fa-3x mb-2"></i>
                        <p>Tidak ada kelas hari ini</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Quick Actions for Admin -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 mb-2">
                        <a href="{{ route('students.create') }}" class="btn btn-outline-primary w-100">
                            <i class="fas fa-user-plus"></i><br>
                            <small>Tambah Mahasiswa</small>
                        </a>
                    </div>
                    <div class="col-6 mb-2">
                        <a href="{{ route('users.create') }}" class="btn btn-outline-success w-100">
                            <i class="fas fa-user-tie"></i><br>
                            <small>Tambah User</small>
                        </a>
                    </div>
                    <div class="col-6 mb-2">
                        <a href="#" class="btn btn-outline-info w-100">
                            <i class="fas fa-plus-circle"></i><br>
                            <small>Tambah Kelas</small>
                        </a>
                    </div>
                    <div class="col-6 mb-2">
                        <a href="{{ route('attendance.reports') }}" class="btn btn-outline-warning w-100">
                            <i class="fas fa-chart-bar"></i><br>
                            <small>Laporan</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity & Stats -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-clock"></i>
                    Aktivitas Terbaru
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
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-info-circle"></i>
                        <p class="mb-0">Belum ada aktivitas hari ini</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Attendance Stats per Class -->
        @if(count($classAttendanceStats) > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-bar"></i>
                    Statistik Presensi Hari Ini
                </h5>
            </div>
            <div class="card-body">
                @foreach($classAttendanceStats as $stat)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>{{ $stat['class']->course->course_name }}</span>
                            <span>{{ $stat['attendance_count'] }}/{{ $stat['capacity'] }} ({{ $stat['percentage'] }}%)</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" style="width: {{ $stat['percentage'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function checkApiStatus() {
    $('#api-status').removeClass('bg-success bg-danger').addClass('bg-secondary').text('Checking...');
    
    $.get('{{ route("api.status") }}')
        .done(function(data) {
            if (data.status === 'connected') {
                $('#api-status').removeClass('bg-secondary bg-danger').addClass('bg-success').text('API Connected');
            } else {
                $('#api-status').removeClass('bg-secondary bg-success').addClass('bg-danger').text('API Error');
            }
        })
        .fail(function(xhr, status, error) {
            $('#api-status').removeClass('bg-secondary bg-success').addClass('bg-danger').text('API Disconnected');
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