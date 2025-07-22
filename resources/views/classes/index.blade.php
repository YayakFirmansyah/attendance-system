{{-- resources/views/classes/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Class Schedules')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>Class Schedules</h1>
        <p class="text-muted mb-0">Kelola jadwal kelas perkuliahan</p>
    </div>
    <a href="{{ route('classes.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Schedule
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Course</th>
                        <th>Semester</th>
                        <th>Room</th>
                        <th>Day</th>
                        <th>Time</th>
                        <th>Capacity</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classes as $class)
                    <tr>
                        <td>
                            <div>
                                <strong>{{ $class->course->course_name }}</strong>
                                @if($class->class_code)
                                    <span class="badge bg-success ms-1">{{ $class->class_code }}</span>
                                @endif
                                <br>
                                <small class="text-muted">
                                    {{ $class->course->course_code }} • {{ $class->course->credits }} SKS<br>
                                    Dosen: {{ $class->course->lecturer ? $class->course->lecturer->name : 'No Lecturer' }}
                                </small>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $class->semester }}</span>
                        </td>
                        <td>
                            <div>
                                <strong>{{ $class->room->room_code }}</strong><br>
                                <small class="text-muted">{{ $class->room->room_name }}</small>
                                @if($class->room->building)
                                    <br><small class="text-muted">{{ $class->room->building }}</small>
                                @endif
                            </div>
                        </td>
                        <td>{{ ucfirst($class->day) }}</td>
                        <td>
                            <small>{{ $class->start_time }} - {{ $class->end_time }}</small>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $class->room->capacity }} seats</span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $class->status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($class->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('attendance.scanner', $class) }}" 
                                   class="btn btn-outline-primary" title="Scanner">
                                    <i class="fas fa-camera"></i>
                                </a>
                                <a href="{{ route('classes.edit', $class) }}" 
                                   class="btn btn-outline-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="deleteClass({{ $class->id }}, '{{ $class->course->course_name }} - {{ $class->class_code }}')" 
                                        class="btn btn-outline-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fas fa-calendar fa-3x mb-2"></i>
                            <p>No class schedules found.</p>
                            <a href="{{ route('classes.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create First Schedule
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($classes->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $classes->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this class schedule?</p>
                <p class="text-muted" id="deleteClassName"></p>
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
function deleteClass(id, className) {
    document.getElementById('deleteClassName').textContent = className;
    document.getElementById('deleteForm').action = '/classes/' + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush