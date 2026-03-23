@extends('layouts.app')
@section('title', 'Edit Cohort')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-primary fw-bold">Edit Cohort Data</h2>
        </div>
        <a href="{{ route('cohorts.index') }}" class="btn btn-light border rounded-pill px-4 shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form action="{{ route('cohorts.update', $cohort) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Nama Cohort / Rombel <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $cohort->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Angkatan <span class="text-danger">*</span></label>
                        <input type="number" name="angkatan" class="form-control @error('angkatan') is-invalid @enderror" value="{{ old('angkatan', $cohort->angkatan) }}" required>
                        @error('angkatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kelas <span class="text-danger">*</span></label>
                        <input type="text" name="kelas" class="form-control @error('kelas') is-invalid @enderror" value="{{ old('kelas', $cohort->kelas) }}" required>
                        @error('kelas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Program Studi <span class="text-danger">*</span></label>
                        <input type="text" name="program_studi" class="form-control @error('program_studi') is-invalid @enderror" value="{{ old('program_studi', $cohort->program_studi) }}" required>
                        @error('program_studi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Fakultas <span class="text-danger">*</span></label>
                        <input type="text" name="fakultas" class="form-control @error('fakultas') is-invalid @enderror" value="{{ old('fakultas', $cohort->fakultas) }}" required>
                        @error('fakultas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Semester <span class="text-danger">*</span></label>
                    <input type="number" name="semester" class="form-control @error('semester') is-invalid @enderror" value="{{ old('semester', $cohort->semester) }}" min="1" max="14" required>
                    @error('semester')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="text-end border-top pt-4">
                    <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">Update Cohort</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
