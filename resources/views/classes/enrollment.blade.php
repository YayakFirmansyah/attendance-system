@extends('layouts.app')

@section('title', 'Mahasiswa Cohort: ' . $class->course->course_name)

@section('content')
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h4 class="mb-1 fw-bold text-primary">{{ $class->course->course_name }}</h4>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar-day me-1"></i> {{ ucfirst($class->day) }},
                {{ \Carbon\Carbon::parse($class->start_time)->format('H:i') }} -
                {{ \Carbon\Carbon::parse($class->end_time)->format('H:i') }}
                <span class="ms-3"><i class="fas fa-door-open me-1"></i> {{ $class->room->name ?? 'TBA' }}</span>
            </p>
            <p class="text-muted mb-0">
                <i class="fas fa-users me-1"></i> Cohort: {{ $class->cohort->name ?? 'N/A' }}
            </p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('classes.index') }}" class="btn btn-light border shadow-sm rounded-pill px-4">
                <i class="fas fa-arrow-left me-2"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Cohort Summary -->
        <div class="col-lg-4">
            <div class="card h-100 border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 pt-4 pb-2 px-4">
                    <h6 class="fw-bold mb-0"><i class="fas fa-info-circle text-primary me-2"></i> Aturan Keanggotaan</h6>
                </div>
                <div class="card-body px-4">
                    <div class="alert alert-info border-0 rounded-4 mb-4">
                        <i class="fas fa-layer-group me-2"></i>
                        Absensi kelas ini mengikuti semua mahasiswa aktif pada cohort yang sama.
                    </div>

                    <div
                        class="d-flex align-items-center justify-content-between p-3 rounded-4 bg-primary bg-opacity-10 border border-primary border-opacity-25">
                        <div>
                            <h3 class="fw-bold text-primary mb-0">{{ $students->count() }}</h3>
                            <span class="small text-muted">Total Mahasiswa Cohort</span>
                        </div>
                        <div class="text-primary opacity-50 fs-1">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cohort Students List -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div
                    class="card-header bg-transparent border-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="fas fa-users text-success me-2"></i> Daftar Mahasiswa Cohort</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th class="ps-4 py-3 font-weight-medium border-0 rounded-start">Mahasiswa</th>
                                    <th class="py-3 font-weight-medium border-0">NIM</th>
                                    <th class="py-3 font-weight-medium border-0">Cohort</th>
                                    <th class="pe-4 py-3 font-weight-medium border-0 text-end rounded-end">Status</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                @forelse($students as $student)
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3 fw-bold"
                                                    style="width: 40px; height: 40px;">
                                                    {{ substr($student->name, 0, 1) }}
                                                </div>
                                                <span class="fw-medium text-dark">{{ $student->name }}</span>
                                            </div>
                                        </td>
                                        <td class="py-3 text-muted">{{ $student->student_id }}</td>
                                        <td class="py-3 text-muted">{{ $class->cohort->name ?? 'N/A' }}</td>
                                        <td class="pe-4 py-3 text-end">
                                            <span class="badge bg-success">Aktif</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="fas fa-folder-open fs-1 mb-3 opacity-50"></i>
                                                <p class="mb-0">Belum ada mahasiswa aktif pada cohort ini.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
