@extends('layouts.app')

@section('title', 'Tambah User')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>Tambah User Baru</h1>
        <p class="text-muted mb-0">Tambahkan admin atau dosen baru ke sistem</p>
    </div>
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-plus"></i>
                    Form Tambah User
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Lengkap</label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name') }}" 
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="employee_id" class="form-label">Employee ID</label>
                                <input type="text" 
                                       class="form-control @error('employee_id') is-invalid @enderror" 
                                       id="employee_id" 
                                       name="employee_id" 
                                       value="{{ old('employee_id') }}" 
                                       placeholder="Contoh: ADM001 atau DOS001"
                                       required>
                                @error('employee_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">NIP atau kode identitas karyawan</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select @error('role') is-invalid @enderror" 
                                        id="role" 
                                        name="role" 
                                        required>
                                    <option value="">Pilih Role</option>
                                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>
                                        Admin
                                    </option>
                                    <option value="dosen" {{ old('role') === 'dosen' ? 'selected' : '' }}>
                                        Dosen
                                    </option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select @error('status') is-invalid @enderror" 
                                id="status" 
                                name="status" 
                                required>
                            <option value="">Pilih Status</option>
                            <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>
                                Active
                            </option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>
                                Inactive
                            </option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Minimal 6 karakter</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       required>
                                <div class="form-text">Ulangi password yang sama</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto generate employee ID based on role
document.getElementById('role').addEventListener('change', function() {
    const role = this.value;
    const employeeIdField = document.getElementById('employee_id');
    
    if (role === 'admin' && !employeeIdField.value) {
        const timestamp = Date.now().toString().slice(-3);
        employeeIdField.value = 'ADM' + timestamp;
    } else if (role === 'dosen' && !employeeIdField.value) {
        const timestamp = Date.now().toString().slice(-3);
        employeeIdField.value = 'DOS' + timestamp;
    }
});
</script>
@endpush