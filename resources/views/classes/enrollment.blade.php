@extends('layouts.app')

@section('title', 'Kelola Peserta Kelas: ' . $class->course->course_name)

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h4 class="mb-1 fw-bold text-primary">{{ $class->course->course_name }}</h4>
        <p class="text-muted mb-0">
            <i class="fas fa-calendar-day me-1"></i> {{ ucfirst($class->day) }}, {{ \Carbon\Carbon::parse($class->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($class->end_time)->format('H:i') }}
            <span class="ms-3"><i class="fas fa-door-open me-1"></i> {{ $class->room->name ?? 'TBA' }}</span>
        </p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a href="{{ route('classes.index') }}" class="btn btn-light border shadow-sm rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i> Kembali
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Add New Student Form -->
    <div class="col-lg-4">
        <div class="card h-100 border-0 shadow-sm rounded-4">
            <div class="card-header bg-transparent border-0 pt-4 pb-2 px-4">
                <h6 class="fw-bold mb-0"><i class="fas fa-user-plus text-primary me-2"></i> Tambah Peserta</h6>
            </div>
            <div class="card-body px-4">
                <form action="{{ route('classes.enrollments.store', $class) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-medium">Pilih Mahasiswa</label>
                        <select name="student_id" class="form-select border-0 bg-light" required>
                            <option value="">-- Pilih Mahasiswa --</option>
                            @foreach($availableStudents as $student)
                            <option value="{{ $student->id }}">{{ $student->student_id }} - {{ $student->name }}</option>
                            @endforeach
                        </select>
                        @if($availableStudents->isEmpty())
                        <div class="form-text text-warning mt-2">
                            <i class="fas fa-info-circle me-1"></i> Semua mahasiswa aktif sudah terdaftar di kelas ini.
                        </div>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill" {{ $availableStudents->isEmpty() ? 'disabled' : '' }}>
                        <i class="fas fa-plus-circle me-1"></i> Daftarkan ke Kelas
                    </button>
                </form>

                <hr class="my-4 text-muted opacity-25">

                <div class="d-flex align-items-center justify-content-between p-3 rounded-4 bg-primary bg-opacity-10 border border-primary border-opacity-25">
                    <div>
                        <h3 class="fw-bold text-primary mb-0">{{ $enrollments->count() }}</h3>
                        <span class="small text-muted">Total Peserta</span>
                    </div>
                    <div class="text-primary opacity-50 fs-1">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enrolled Students List -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-transparent border-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="fas fa-users text-success me-2"></i> Daftar Peserta Terdaftar</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4 py-3 font-weight-medium border-0 rounded-start">Mahasiswa</th>
                                <th class="py-3 font-weight-medium border-0">NIM</th>
                                <th class="py-3 font-weight-medium border-0">Tgl Daftar</th>
                                <th class="pe-4 py-3 font-weight-medium border-0 text-end rounded-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @forelse($enrollments as $enrollment)
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3 fw-bold" style="width: 40px; height: 40px;">
                                            {{ substr($enrollment->student->name, 0, 1) }}
                                        </div>
                                        <span class="fw-medium text-dark">{{ $enrollment->student->name }}</span>
                                    </div>
                                </td>
                                <td class="py-3 text-muted">{{ $enrollment->student->student_id }}</td>
                                <td class="py-3 text-muted small">
                                    {{ \Carbon\Carbon::parse($enrollment->enrolled_at)->format('d M Y') }}
                                </td>
                                <td class="pe-4 py-3 text-end">
                                    <form action="{{ route('classes.enrollments.drop', [$class, $enrollment->student_id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin mengeluarkan {{ $enrollment->student->name }} dari kelas ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                            <i class="fas fa-user-minus"></i> Keluarkan
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-folder-open fs-1 mb-3 opacity-50"></i>
                                        <p class="mb-0">Belum ada peserta yang didaftarkan ke kelas ini.</p>
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