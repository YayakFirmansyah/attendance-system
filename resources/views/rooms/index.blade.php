@extends('layouts.app')

@section('title', 'Manage Rooms')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>Manage Rooms</h1>
        <p class="text-muted mb-0">Kelola ruangan untuk jadwal kelas</p>
    </div>
    <a href="{{ route('rooms.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Room
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Room Code</th>
                        <th>Room Name</th>
                        <th>Building</th>
                        <th>Capacity</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rooms as $room)
                    <tr>
                        <td><code>{{ $room->room_code }}</code></td>
                        <td>
                            <strong>{{ $room->room_name }}</strong>
                            @if($room->floor)
                                <br><small class="text-muted">Floor {{ $room->floor }}</small>
                            @endif
                        </td>
                        <td>{{ $room->building ?? '-' }}</td>
                        <td>{{ $room->capacity }} seats</td>
                        <td>
                            <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $room->type)) }}</span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $room->status === 'active' ? 'success' : ($room->status === 'maintenance' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($room->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('rooms.edit', $room) }}" class="btn btn-outline-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="deleteRoom({{ $room->id }}, '{{ $room->room_name }}')" 
                                        class="btn btn-outline-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="fas fa-door-open fa-3x mb-2"></i>
                            <p>No rooms found. <a href="{{ route('rooms.create') }}">Create your first room</a></p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($rooms->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $rooms->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete room <strong id="roomName"></strong>?</p>
                <p class="text-danger"><small>This will affect all related class schedules.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteRoom(id, name) {
    document.getElementById('roomName').textContent = name;
    document.getElementById('deleteForm').action = `/rooms/${id}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush