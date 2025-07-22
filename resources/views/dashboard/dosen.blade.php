{{-- resources/views/dashboard/dosen.blade.php - FIXED --}}
@extends('layouts.app')

@section('title', 'Dosen Dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Dashboard Dosen</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <small class="text-muted">{{ $now->format('l, d F Y • H:i') }} WIB</small>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-6">
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
            </div>
        </div>
        
        <div class="col-md-6">
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
        <div class="col-md-8">
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
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="card-title">{{ $class->course->course_name }}</h6>
                                            <p class="card-text text-muted mb-2">
                                                <i class="fas fa-door-open"></i> 
                                                {{ $class->room ? $class->room->room_code . ' - ' . $class->room->room_name : 'No Room' }}<br>
                                                <i class="fas fa-clock"></i> 
                                                {{ $class->start_time->format('H:i') }} - {{ $class->end_time->format('H:i') }}
                                            </p>
                                            <small class="text-muted">
                                                Kode: {{ $class->course->course_code }} • 
                                                SKS: {{ $class->course->credits }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <a href="{{ route('attendance.scanner', $class) }}" 
                                               class="btn btn-primary mb-1">
                                                <i class="fas fa-camera"></i> Mulai Presensi
                                            </a><br>
                                            <a href="{{ route('attendance.class', $class) }}" 
                                               class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-list"></i> Lihat Data
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-calendar-times fa-4x mb-3"></i>
                            <h5>Tidak Ada Kelas Hari Ini</h5>
                            <p>Anda tidak memiliki jadwal kelas pada hari ini.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="col-md-4">
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
                                         style="width: 35px; height: 35px;">
                                        <i class="fas fa-{{ $log->is_verified ? 'check' : 'exclamation' }} text-white small"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <h6 class="mb-1 small">{{ $log->student->name ?? 'Unknown' }}</h6>
                                    <p class="mb-1 text-muted small">
                                        {{ $log->classModel->course->course_name ?? 'Unknown Course' }}
                                    </p>
                                    <small class="text-muted">
                                        {{ $log->timestamp->diffForHumans() }}<br>
                                        Confidence: {{ round($log->confidence_score * 100, 1) }}%
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-3"></i>
                            <p class="small">Belum ada aktivitas hari ini</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection