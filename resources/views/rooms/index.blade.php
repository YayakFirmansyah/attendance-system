@extends('layouts.app')

@section('title', 'Manage Rooms')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
    <div>
        <h2 class="h3 mb-1 fw-bold text-primary">Manage Rooms</h2>
        <p class="text-muted mb-0">Kelola master data ruangan untuk jadwal perkuliahan</p>
    </div>
    <div class="mt-3 mt-md-0 d-flex gap-2">
        <a href="{{ route('rooms.create') }}" class="btn btn-primary rounded-pill px-3 shadow-sm">
            <i class="fas fa-plus me-1"></i> Add Room
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-5">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4 py-3 font-weight-medium border-0 rounded-start">Room Code</th>
                        <th class="py-3 font-weight-medium border-0">Room Name</th>
                        <th class="py-3 font-weight-medium border-0">Building</th>
                        <th class="py-3 font-weight-medium border-0">Capacity</th>
                        <th class="py-3 font-weight-medium border-0">Type</th>
                        <th class="py-3 font-weight-medium border-0">Status</th>
                        <th class="pe-4 py-3 font-weight-medium border-0 rounded-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($rooms as $room)
                    <tr>
                        <td class="ps-4"><code>{{ $room->room_code }}</code></td>
                        <td>
                            <strong class="text-dark">{{ $room->room_name }}</strong>
                            @if($room->floor)
                            <br><small class="text-muted">Floor {{ $room->floor }}</small>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $room->building ?? '-' }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $room->capacity }} seats</span></td>
                        <td>
                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">{{ ucfirst(str_replace('_', ' ', $room->type)) }}</span>
                        </td>
                        <td>
                            @php
                            $statusColor = $room->status === 'active' ? 'success' : ($room->status === 'maintenance' ? 'warning' : 'secondary');
                            @endphp
                            <span class="badge bg-{{ $statusColor }} bg-opacity-10 text-{{ $statusColor }} border border-{{ $statusColor }} border-opacity-25 px-2 py-1">
                                {{ ucfirst($room->status) }}
                            </span>
                        </td>
                        <td class="pe-4">
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