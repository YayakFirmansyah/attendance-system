{{-- resources/views/dashboard/admin.blade.php - FIXED --}}
@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Dashboard Admin</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <small class="text-muted">{{ $now->format('l, d F Y • H:i') }} WIB</small>
            </div>
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
                            <h4 class="card-title">{{ $totalClasses }}</h4>
                            <p class="card-text">Total Kelas</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chalkboard-teacher fa-2x"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('classes.index') }}" class="text-white text-decoration-none">
                        <small>Kelola Kelas <i class="fas fa-arrow-right"></i></small>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-info">
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

        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">{{ $todayLogs }}</h4>
                            <p class="card-text">Log Pengenalan</p>
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
                                        <i class="fas fa-door-open"></i> 
                                        {{ $class->room ? $class->room->room_code . ' - ' . $class->room->room_name : 'No Room' }}<br>
                                        <i class="fas fa-clock"></i> 
                                        {{ $class->start_time->format('H:i') }} - {{ $class->end_time->format('H:i') }}
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
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-calendar-times fa-3x mb-3"></i>
                            <p>Tidak ada kelas hari ini</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history"></i>
                        Aktivitas Terkini
                    </h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @if($recentLogs->count() > 0)
                        @foreach($recentLogs as $log)
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <div class="bg-{{ $log->is_verified ? 'success' : 'warning' }} rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-{{ $log->is_verified ? 'check' : 'exclamation' }} text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">{{ $log->student->name ?? 'Unknown' }}</h6>
                                    <p class="mb-1 text-muted">
                                        {{ $log->classModel->course->course_name ?? 'Unknown Course' }}
                                    </p>
                                    <small class="text-muted">
                                        {{ $log->timestamp->diffForHumans() }} • 
                                        Confidence: {{ round($log->confidence_score * 100, 1) }}%
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>Belum ada aktivitas hari ini</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Class Attendance Statistics -->
    @if($classAttendanceStats && count($classAttendanceStats) > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar"></i>
                        Statistik Presensi Kelas Hari Ini
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($classAttendanceStats as $stat)
                            <div class="col-md-4 mb-3">
                                <div class="card border-left-primary">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ $stat['class']->course->course_name }}</h6>
                                        <p class="card-text">
                                            <strong>{{ $stat['attendance_count'] }}</strong> / {{ $stat['capacity'] }} mahasiswa
                                        </p>
                                        <div class="progress mb-2">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: {{ $stat['percentage'] }}%" 
                                                 aria-valuenow="{{ $stat['percentage'] }}" 
                                                 aria-valuemin="0" aria-valuemax="100">
                                                {{ $stat['percentage'] }}%
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            {{ $stat['class']->room ? $stat['class']->room->room_code : 'No Room' }} • 
                                            {{ $stat['class']->start_time->format('H:i') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection