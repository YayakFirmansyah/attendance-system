@extends('layouts.app')

@section('title', 'Dashboard Dosen')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>Dashboard Dosen</h1>
        <p class="text-muted mb-0">Selamat datang, {{ auth()->user()->name }}</p>
    </div>
    <div>
        <span id="api-status" class="badge bg-secondary">Checking API...</span>
        <button class="btn btn-outline-primary btn-sm" onclick="checkApiStatus()">
            <i class="fas fa-sync"></i> Refresh
        </button>
    </div>
</div>

<!-- Dosen Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title">{{ $todayClasses->count() }}</h4>
                        <p class="card-text">Kelas Hari Ini</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-chalkboard fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card text-white bg-success">
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
    
    <div class="col-md-4">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title">{{ $todayLogs }}</h4>
                        <p class="card-text">Deteksi Wajah</p>
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
                                            <i class="fas fa-door-open"></i> {{ $class->room ?? 'No Room' }}<br>
                                            <i class="fas fa-clock"></i> {{ $class->start_time }} - {{ $class->end_time }}
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
                    <i class="fas fa-clock"></i>
                    Aktivitas Terbaru
                </h5>
            </div>
            <div class="card-body">
                @if($recentLogs->count() > 0)
                    @foreach($recentLogs as $log)
                        <div class="d-flex align-items-center border-bottom pb-2 mb-2">
                            <div class="me-3">
                                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" 
                                     style="width: 35px; height: 35px;">
                                    <i class="fas fa-user-check"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold">{{ $log->student->name }}</div>
                                <small class="text-muted">
                                    {{ $log->classModel->course->course_name }}<br>
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
                        <i class="fas fa-info-circle fa-2x mb-2"></i>
                        <p class="mb-0">Belum ada aktivitas presensi hari ini</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Quick Actions for Dosen -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt"></i>
                    Menu Cepat
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('attendance.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-list"></i> Semua Presensi
                    </a>
                    <a href="{{ route('attendance.reports') }}" class="btn btn-outline-success">
                        <i class="fas fa-chart-line"></i> Laporan Presensi
                    </a>
                    <a href="#" class="btn btn-outline-info">
                        <i class="fas fa-calendar"></i> Jadwal Mengajar
                    </a>
                </div>
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