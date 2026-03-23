@extends('layouts.app')

@section('title', 'Manage Courses')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
    <div>
        <h2 class="h3 mb-1 fw-bold text-primary">Manage Courses</h2>
        <p class="text-muted mb-0">Kelola mata kuliah dalam sistem</p>
    </div>
    <div class="mt-3 mt-md-0 d-flex gap-2">
        <a href="{{ route('courses.create') }}" class="btn btn-primary rounded-pill px-3 shadow-sm">
            <i class="fas fa-plus me-1"></i> Add Course
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-5">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4 py-3 font-weight-medium border-0 rounded-start">Code</th>
                        <th class="py-3 font-weight-medium border-0">Course Name</th>
                        <th class="py-3 font-weight-medium border-0">Credits</th>
                        <th class="py-3 font-weight-medium border-0">Faculty</th>
                        <th class="py-3 font-weight-medium border-0">Lecturer</th>
                        <th class="py-3 font-weight-medium border-0">Status</th>
                        <th class="pe-4 py-3 font-weight-medium border-0 rounded-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($courses as $course)
                    <tr>
                        <td class="ps-4"><code>{{ $course->course_code }}</code></td>
                        <td>
                            <strong class="text-dark">{{ $course->course_name }}</strong>
                            @if($course->description)
                            <br><small class="text-muted">{{ Str::limit($course->description, 50) }}</small>
                            @endif
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $course->credits }} SKS</span></td>
                        <td class="text-muted small">{{ $course->faculty }}</td>
                        <td class="text-muted small">{{ $course->lecturer ? $course->lecturer->name : 'No Lecturer' }}</td>
                        <td>
                            <span class="badge bg-{{ $course->status === 'active' ? 'success' : 'secondary' }} bg-opacity-10 text-{{ $course->status === 'active' ? 'success' : 'secondary' }} border border-{{ $course->status === 'active' ? 'success' : 'secondary' }} border-opacity-25 px-2 py-1">
                                {{ ucfirst($course->status) }}
                            </span>
                        </td>
                        <td class="pe-4">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('courses.edit', $course) }}" class="btn btn-sm btn-light text-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="deleteCourse({{ $course->id }}, '{{ $course->course_name }}')"
                                    class="btn btn-sm btn-light text-danger" title="Delete">
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

        <!-- Pagination handled by DataTables -->
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