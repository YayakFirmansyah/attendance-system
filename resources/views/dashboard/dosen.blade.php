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
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">{{ $todayClasses->count() }}</h4>
                                <p class="card-text">Total Kelas Hari Ini</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-chalkboard-teacher fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">{{ $todayAttendances }}</h4>
                                <p class="card-text">Mahasiswa Hadir Hari Ini</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-user-check fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Today's Classes -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-day"></i>
                            Jadwal Kelas Hari Ini
                        </h5>
                        <small class="text-muted">{{ $now->format('l, d M Y') }}</small>
                    </div>
                    <div class="card-body">
                        @if ($todayClasses->count() > 0)
                            <div class="row">
                                @foreach ($todayClasses as $class)
                                    <div class="col-md-6 mb-3">
                                        <div class="card border-left-primary h-100">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="card-title mb-1">{{ $class->course->course_name }}</h6>
                                                        <span class="badge bg-secondary mb-2">{{ $class->cohort->name ?? 'Unknown Class' }}</span>
                                                        <p class="card-text text-muted mb-2">
                                                            <i class="fas fa-door-open"></i>
                                                            {{ $class->room ? $class->room->room_code . ' - ' . $class->room->room_name : 'No Room' }}<br>
                                                            <i class="fas fa-clock"></i>
                                                            {{ $class->start_time->format('H:i') }} -
                                                            {{ $class->end_time->format('H:i') }}
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
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
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

            <!-- Presensi Hari Ini -->
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-clipboard-check"></i>
                            Riwayat Presensi Mahasiswa Hari Ini
                        </h5>
                        <span class="badge bg-info">{{ $todayAttendances }} data</span>
                    </div>
                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                        @if ($todayAttendanceList->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Mahasiswa</th>
                                            <th>Kelas</th>
                                            <th>Waktu</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($todayAttendanceList as $attendance)
                                            <tr>
                                                <td>
                                                    <strong>{{ $attendance->student->name ?? 'Unknown Student' }}</strong>
                                                </td>
                                                <td>
                                                    {{ $attendance->classModel->course->course_name ?? 'Unknown Class' }}
                                                </td>
                                                <td>
                                                    {{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '--:--' }}
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge bg-{{ $attendance->status === 'present' ? 'success' : ($attendance->status === 'late' ? 'warning' : 'secondary') }}">
                                                        {{ ucfirst($attendance->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p class="mb-0">Belum ada presensi hari ini.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
