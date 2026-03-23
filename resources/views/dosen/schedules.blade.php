@extends('layouts.app')

@section('title', 'My Schedules')

@section('content')
<div class="d-flex flex-column justify-content-between align-items-start mb-4">
    <div>
        <h2 class="h3 mb-1 fw-bold text-primary">My Schedules</h2>
        <p class="text-muted mb-0">Semua riwayat jadwal mengajar kelas Anda</p>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-5">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4 py-3 font-weight-medium border-0 rounded-start">Course</th>
                        <th class="py-3 font-weight-medium border-0">Cohort / Rombel</th>
                        <th class="py-3 font-weight-medium border-0">Room</th>
                        <th class="py-3 font-weight-medium border-0">Day & Time</th>
                        <th class="py-3 font-weight-medium border-0">Status</th>
                        <th class="pe-4 py-3 font-weight-medium border-0 rounded-end text-end">Action</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($classes as $class)
                    <tr>
                        <td class="ps-4">
                            <strong class="text-dark">{{ $class->course->course_name }}</strong>
                            <br><small class="text-muted">{{ $class->course->course_code }} • {{ $class->course->credits }} SKS</small>
                        </td>
                        <td>
                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">{{ $class->cohort ? $class->cohort->name : 'N/A' }}</span>
                        </td>
                        <td>
                            <strong class="text-dark">{{ $class->room->room_code ?? $class->room->room_id }}</strong><br>
                            <small class="text-muted">{{ $class->room->room_name }}</small>
                        </td>
                        <td>
                            <span class="fw-medium">{{ ucfirst($class->day) }}</span><br>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($class->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($class->end_time)->format('H:i') }}</small>
                        </td>
                        <td>
                            <span class="badge bg-{{ $class->status === 'active' ? 'success' : 'secondary' }} bg-opacity-10 text-{{ $class->status === 'active' ? 'success' : 'secondary' }} border border-{{ $class->status === 'active' ? 'success' : 'secondary' }} border-opacity-25 px-2 py-1">
                                {{ ucfirst($class->status) }}
                            </span>
                        </td>
                        <td class="pe-4 text-end">
                            <a href="{{ route('attendance.scanner', $class) }}" class="btn btn-sm btn-light text-primary" title="Scanner">
                                <i class="fas fa-camera"></i> Scan
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="fas fa-calendar fa-3x mb-2 text-light" style="opacity: 0.2;"></i>
                            <p class="mt-2">Anda belum memiliki jadwal kelas.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
