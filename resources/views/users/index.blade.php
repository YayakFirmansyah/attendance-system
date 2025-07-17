@extends('layouts.app')

@section('title', 'Kelola Users')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>Kelola Users</h1>
        <p class="text-muted mb-0">Manajemen admin dan dosen sistem</p>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah User
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Employee ID</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-{{ $user->role === 'admin' ? 'danger' : 'primary' }} text-white d-flex align-items-center justify-content-center me-3" 
                                         style="width: 35px; height: 35px;">
                                        <i class="fas fa-{{ $user->role === 'admin' ? 'user-shield' : 'chalkboard-teacher' }}"></i>
                                    </div>
                                    <div>
                                        <strong>{{ $user->name }}</strong>
                                        @if($user->id === auth()->id())
                                            <span class="badge bg-info ms-1">You</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : 'primary' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td><code>{{ $user->employee_id }}</code></td>
                            <td>
                                <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $user->created_at->format('d M Y') }}<br>
                                    {{ $user->created_at->format('H:i') }}
                                </small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('users.show', $user) }}" 
                                       class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('users.edit', $user) }}" 
                                       class="btn btn-outline-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <button type="button" 
                                                class="btn btn-outline-danger btn-sm" 
                                                onclick="confirmDelete({{ $user->id }}, '{{ $user->name }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-users fa-3x mb-3"></i>
                                <p>Belum ada data user</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus user <strong id="userName"></strong>?</p>
                <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(userId, userName) {
    document.getElementById('userName').textContent = userName;
    document.getElementById('deleteForm').action = '/users/' + userId;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush