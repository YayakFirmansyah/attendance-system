@extends('layouts.app')

@section('title', 'Cohorts / Rombel Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-primary fw-bold">Cohorts / Rombel</h2>
            <p class="text-muted">Kelola data angkatan dan rombongan belajar</p>
        </div>
        <a href="{{ route('cohorts.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
            <i class="fas fa-plus me-1"></i> Tambah Cohort
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Nama Rombel</th>
                            <th>Program Studi</th>
                            <th>Angkatan / Kelas</th>
                            <th>Semester</th>
                            <th class="pe-4 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cohorts as $cohort)
                            <tr>
                                <td class="ps-4 fw-medium text-dark">{{ $cohort->name }}</td>
                                <td>{{ $cohort->program_studi }}<br><small class="text-muted">{{ $cohort->fakultas }}</small></td>
                                <td>{{ $cohort->angkatan }} / Kelas {{ $cohort->kelas }}</td>
                                <td>Semester {{ $cohort->semester }}</td>
                                <td class="pe-4 text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('cohorts.edit', $cohort) }}" class="btn btn-sm btn-light text-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('cohorts.destroy', $cohort) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus cohort ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-light text-danger" type="submit" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada data cohort/rombel.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Pagination handled by DataTables -->
    </div>
</div>
@endsection
