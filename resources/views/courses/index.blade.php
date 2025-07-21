@extends('layouts.app')

@section('title', 'Manage Courses')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>Manage Courses</h1>
        <p class="text-muted mb-0">Kelola mata kuliah dalam sistem</p>
    </div>
    <a href="{{ route('courses.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Course
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Course Name</th>
                        <th>Credits</th>
                        <th>Faculty</th>
                        <th>Lecturer</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($courses as $course)
                    <tr>
                        <td><code>{{ $course->course_code }}</code></td>
                        <td>
                            <strong>{{ $course->course_name }}</strong>
                            @if($course->description)
                                <br><small class="text-muted">{{ Str::limit($course->description, 50) }}</small>
                            @endif
                        </td>
                        <td>{{ $course->credits }} SKS</td>
                        <td>{{ $course->faculty }}</td>
                        <td>{{ $course->lecturer ? $course->lecturer->name : 'No Lecturer' }}</td>
                        <td>
                            <span class="badge bg-{{ $course->status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($course->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('courses.edit', $course) }}" class="btn btn-outline-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="deleteCourse({{ $course->id }}, '{{ $course->course_name }}')" 
                                        class="btn btn-outline-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="fas fa-book fa-3x mb-2"></i>
                            <p>No courses found. <a href="{{ route('courses.create') }}">Create your first course</a></p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($courses->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $courses->links() }}
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
                <p>Are you sure you want to delete course <strong id="courseName"></strong>?</p>
                <p class="text-danger"><small>This action cannot be undone.</small></p>
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
function deleteCourse(id, name) {
    document.getElementById('courseName').textContent = name;
    document.getElementById('deleteForm').action = `/courses/${id}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush