{{-- resources/views/students/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Manage Students')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>Manage Students</h1>
        <p class="text-muted mb-0">Kelola data mahasiswa dan pendaftaran wajah</p>
    </div>
    <a href="{{ route('students.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Student
    </a>
</div>

<div class="card">
    <div class="card-body">
        <!-- Filter & Search -->
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search students...">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="semesterFilter">
                    <option value="">All Semesters</option>
                    @for($i = 1; $i <= 8; $i++)
                        <option value="{{ $i }}">Semester {{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="graduated">Graduated</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Photo</th>
                        <th>Student Info</th>
                        <th>Academic</th>
                        <th>Contact</th>
                        <th>Face Status</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($student->profile_photo)
                                    <img src="{{ $student->profile_photo_url }}" 
                                         class="rounded-circle me-2" 
                                         width="40" height="40" 
                                         alt="{{ $student->name }}">
                                @else
                                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-2" 
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div>
                                <strong>{{ $student->name }}</strong><br>
                                <small class="text-muted">{{ $student->student_id }}</small><br>
                                <small class="text-primary">{{ $student->email }}</small>
                            </div>
                        </td>
                        <td>
                            <div>
                                <span class="badge bg-info mb-1">{{ $student->program_study }}</span><br>
                                <small class="text-muted">{{ $student->faculty }}</small><br>
                                <small><strong>Semester:</strong> {{ $student->semester }}</small>
                            </div>
                        </td>
                        <td>
                            @if($student->phone)
                                <small>
                                    <i class="fas fa-phone text-muted"></i> 
                                    {{ $student->phone }}
                                </small>
                            @else
                                <small class="text-muted">No phone</small>
                            @endif
                        </td>
                        <td>
                            @if($student->is_face_registered)
                                <span class="badge bg-success">
                                    <i class="fas fa-check"></i> Registered
                                </span>
                            @else
                                <span class="badge bg-warning">
                                    <i class="fas fa-exclamation"></i> Not Registered
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $student->status === 'active' ? 'success' : 
                                      ($student->status === 'graduated' ? 'info' : 'secondary') }}">
                                {{ ucfirst($student->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('students.show', $student) }}" 
                                   class="btn btn-outline-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('students.faces', $student) }}" 
                                   class="btn btn-outline-primary" title="Manage Face">
                                    <i class="fas fa-camera"></i>
                                </a>
                                <a href="{{ route('students.edit', $student) }}" 
                                   class="btn btn-outline-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="deleteStudent({{ $student->id }}, '{{ $student->name }}')" 
                                        class="btn btn-outline-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="fas fa-users fa-3x mb-3"></i>
                            <h5>No students found</h5>
                            <p>Start by adding your first student.</p>
                            <a href="{{ route('students.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add First Student
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($students->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $students->links() }}
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
                <p>Are you sure you want to delete this student?</p>
                <p class="text-muted" id="deleteStudentName"></p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    This will also delete all attendance records and face encodings for this student.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Student</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function deleteStudent(id, name) {
    document.getElementById('deleteStudentName').textContent = name;
    document.getElementById('deleteForm').action = '/students/' + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Simple search functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const tableRows = document.querySelectorAll('tbody tr');
    
    tableRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Filter functionality
document.getElementById('semesterFilter').addEventListener('change', function() {
    filterTable();
});

document.getElementById('statusFilter').addEventListener('change', function() {
    filterTable();
});

function filterTable() {
    const semesterFilter = document.getElementById('semesterFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    const tableRows = document.querySelectorAll('tbody tr');
    
    tableRows.forEach(row => {
        let showRow = true;
        
        if (semesterFilter) {
            const semesterText = row.cells[2].textContent;
            showRow = showRow && semesterText.includes('Semester: ' + semesterFilter);
        }
        
        if (statusFilter) {
            const statusText = row.cells[5].textContent.toLowerCase();
            showRow = showRow && statusText.includes(statusFilter);
        }
        
        row.style.display = showRow ? '' : 'none';
    });
}
</script>
@endpush