{{-- resources/views/students/index.blade.php - IMPROVED VERSION --}}
@extends('layouts.app')

@section('title', 'Students Management')

@section('content')
<div class="container-fluid">
    {{-- Header Section --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div class="mb-3 mb-md-0">
            <h2 class="h3 mb-1">Students Management</h2>
            <p class="text-muted mb-0">Kelola data mahasiswa dan registrasi wajah</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary" onclick="refreshApiStatus()">
                <i class="fas fa-sync-alt"></i> 
                <span class="d-none d-sm-inline">Refresh Status</span>
            </button>
            <button class="btn btn-outline-info" onclick="refreshFaceStatus()">
                <i class="fas fa-user-check"></i> 
                <span class="d-none d-sm-inline">Refresh Faces</span>
            </button>
            <a href="{{ route('students.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> 
                <span class="d-none d-sm-inline">Add Student</span>
            </a>
        </div>
    </div>

    {{-- API Status Alert --}}
    <div id="apiStatusAlert" class="alert alert-info d-none">
        <i class="fas fa-info-circle"></i>
        <span id="apiStatusMessage">Checking API status...</span>
    </div>

    {{-- Filters & Search Card --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('students.index') }}" id="filterForm">
                <div class="row g-3">
                    {{-- Search Input --}}
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Search Students</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" 
                                   class="form-control" 
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Name, Student ID, Email...">
                            <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Semester Filter --}}
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Semester</label>
                        <select class="form-select" name="semester">
                            <option value="">All Semesters</option>
                            @for($i = 1; $i <= 8; $i++)
                                <option value="{{ $i }}" {{ request('semester') == $i ? 'selected' : '' }}>
                                    Semester {{ $i }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    {{-- Program Study Filter --}}
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Program Study</label>
                        <select class="form-select" name="program">
                            <option value="">All Programs</option>
                            @php
                                $programs = \App\Models\Student::distinct()->pluck('program_study')->sort();
                            @endphp
                            @foreach($programs as $program)
                                <option value="{{ $program }}" {{ request('program') == $program ? 'selected' : '' }}>
                                    {{ $program }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status Filter --}}
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Status</label>
                        <select class="form-select" name="status">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="graduated" {{ request('status') == 'graduated' ? 'selected' : '' }}>Graduated</option>
                        </select>
                    </div>

                    {{-- Reset Filters --}}
                    <div class="col-md-1 d-flex align-items-end">
                        <a href="{{ route('students.index') }}" class="btn btn-outline-warning w-100">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </div>

                {{-- Results Counter --}}
                <div class="row mt-3">
                    <div class="col">
                        <small class="text-muted">
                            Showing {{ $students->count() }} of {{ $students->total() }} students
                            @if(request()->hasAny(['search', 'semester', 'program', 'status']))
                                <span class="badge bg-info ms-2">Filtered</span>
                            @endif
                        </small>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Students Table Card --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Student</th>
                            <th class="d-none d-md-table-cell">Academic Info</th>
                            <th class="d-none d-lg-table-cell">Contact</th>
                            <th>Face Status</th>
                            <th>Status</th>
                            <th class="pe-3" style="min-width: 180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                        <tr>
                            {{-- Student Info Column --}}
                            <td class="ps-3">
                                <div class="d-flex align-items-center">
                                    {{-- Profile Photo --}}
                                    <div class="flex-shrink-0 me-3">
                                        @if($student->profile_photo)
                                            <img src="{{ $student->profile_photo_url }}" 
                                                 class="rounded-circle" 
                                                 width="45" height="45" 
                                                 style="object-fit: cover;"
                                                 alt="{{ $student->name }}">
                                        @else
                                            <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 45px; height: 45px;">
                                                <i class="fas fa-user text-white"></i>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    {{-- Student Details --}}
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-dark">{{ $student->name }}</div>
                                        <div class="small text-muted">{{ $student->student_id }}</div>
                                        <div class="small text-muted d-md-none">
                                            {{ $student->program_study }} - Sem {{ $student->semester }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Academic Info (Hidden on mobile) --}}
                            <td class="d-none d-md-table-cell">
                                <div class="small">
                                    <div class="fw-medium text-dark">{{ $student->program_study }}</div>
                                    <div class="text-muted">{{ $student->faculty }}</div>
                                    <div class="text-muted">Semester {{ $student->semester }}</div>
                                </div>
                            </td>

                            {{-- Contact Info (Hidden on smaller screens) --}}
                            <td class="d-none d-lg-table-cell">
                                <div class="small">
                                    <div class="text-dark">{{ $student->email }}</div>
                                    <div class="text-muted">{{ $student->phone ?: '-' }}</div>
                                </div>
                            </td>

                            {{-- Face Registration Status --}}
                            <td>
                                <div class="face-status-{{ $student->id }}">
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-spinner fa-spin"></i> Loading...
                                    </span>
                                </div>
                            </td>

                            {{-- Status --}}
                            <td>
                                <span class="badge bg-{{ $student->status === 'active' ? 'success' : 
                                      ($student->status === 'graduated' ? 'info' : 'secondary') }}">
                                    {{ ucfirst($student->status) }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="pe-3">
                                <div class="d-flex gap-1 flex-wrap">
                                    {{-- View Button --}}
                                    <a href="{{ route('students.show', $student) }}" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                        <span class="d-none d-lg-inline ms-1">View</span>
                                    </a>
                                    
                                    {{-- Edit Button --}}
                                    <a href="{{ route('students.edit', $student) }}" 
                                       class="btn btn-sm btn-outline-secondary" 
                                       title="Edit Student">
                                        <i class="fas fa-edit"></i>
                                        <span class="d-none d-lg-inline ms-1">Edit</span>
                                    </a>
                                    
                                    {{-- Manage Faces Button --}}
                                    <a href="{{ route('students.faces', $student) }}" 
                                       class="btn btn-sm btn-outline-info" 
                                       title="Manage Faces">
                                        <i class="fas fa-camera"></i>
                                        <span class="d-none d-xl-inline ms-1">Faces</span>
                                    </a>
                                    
                                    {{-- Delete Button --}}
                                    <button class="btn btn-sm btn-outline-danger" 
                                            onclick="deleteStudent({{ $student->id }}, '{{ $student->name }}')"
                                            title="Delete Student">
                                        <i class="fas fa-trash"></i>
                                        <span class="d-none d-xl-inline ms-1">Delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-users fa-3x mb-3"></i>
                                    <p class="mb-0">No students found</p>
                                    @if(request()->hasAny(['search', 'semester', 'program', 'status']))
                                        <a href="{{ route('students.index') }}" class="btn btn-secondary mt-2">
                                            Clear Filters
                                        </a>
                                    @else
                                        <a href="{{ route('students.create') }}" class="btn btn-primary mt-2">
                                            Add First Student
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($students->hasPages())
        <div class="card-footer bg-light">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center">
                {{-- Pagination Info --}}
                <div class="mb-2 mb-sm-0">
                    <small class="text-muted">
                        Showing {{ $students->firstItem() }} to {{ $students->lastItem() }} 
                        of {{ $students->total() }} results
                    </small>
                </div>
                
                {{-- Pagination Links --}}
                <div class="d-flex justify-content-center">
                    <nav aria-label="Students pagination">
                        <ul class="pagination pagination-sm mb-0">
                            {{-- Previous Page Link --}}
                            @if ($students->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link">
                                        <i class="fas fa-chevron-left"></i>
                                        <span class="d-none d-sm-inline ms-1">Previous</span>
                                    </span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $students->previousPageUrl() }}" rel="prev">
                                        <i class="fas fa-chevron-left"></i>
                                        <span class="d-none d-sm-inline ms-1">Previous</span>
                                    </a>
                                </li>
                            @endif

                            {{-- Pagination Elements --}}
                            @foreach ($students->getUrlRange(1, $students->lastPage()) as $page => $url)
                                @if ($page == $students->currentPage())
                                    <li class="page-item active">
                                        <span class="page-link">{{ $page }}</span>
                                    </li>
                                @else
                                    {{-- Show first page, last page, current page and 2 pages around current --}}
                                    @if ($page == 1 || $page == $students->lastPage() || 
                                         ($page >= $students->currentPage() - 1 && $page <= $students->currentPage() + 1))
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                        </li>
                                    @elseif ($page == 2 && $students->currentPage() > 4)
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    @elseif ($page == $students->lastPage() - 1 && $students->currentPage() < $students->lastPage() - 3)
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    @endif
                                @endif
                            @endforeach

                            {{-- Next Page Link --}}
                            @if ($students->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $students->nextPageUrl() }}" rel="next">
                                        <span class="d-none d-sm-inline me-1">Next</span>
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            @else
                                <li class="page-item disabled">
                                    <span class="page-link">
                                        <span class="d-none d-sm-inline me-1">Next</span>
                                        <i class="fas fa-chevron-right"></i>
                                    </span>
                                </li>
                            @endif
                        </ul>
                    </nav>
                </div>
            </div>
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
                <p>Are you sure you want to delete student:</p>
                <p class="fw-bold text-danger" id="deleteStudentName"></p>
                <p class="small text-muted">This action cannot be undone and will also delete all related attendance records.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete Student
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Load face registration status
    loadBulkFaceStatus();
    
    // Setup form submission with debounce for search
    setupSearchDebounce();
    
    // Check API status
    checkApiStatus();
});

// Setup search with debounce
function setupSearchDebounce() {
    const searchInput = document.querySelector('input[name="search"]');
    const form = document.getElementById('filterForm');
    let searchTimeout;

    // Auto-submit form on search input with debounce
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            form.submit();
        }, 500);
    });

    // Auto-submit on filter changes
    form.querySelectorAll('select').forEach(select => {
        select.addEventListener('change', () => form.submit());
    });
}

// Load face registration status for all students
async function loadBulkFaceStatus() {
    try {
        const response = await fetch('/api/students/face-status/bulk');
        const data = await response.json();
        
        if (data.success) {
            data.students.forEach(student => {
                updateFaceStatusDisplay(student.id, student.status, student.message);
            });
        } else {
            // Show error for all students
            document.querySelectorAll('[class*="face-status-"]').forEach(container => {
                container.innerHTML = '<span class="badge bg-danger"><i class="fas fa-times"></i> API Error</span>';
            });
        }
    } catch (error) {
        // Show offline status for all students
        document.querySelectorAll('[class*="face-status-"]').forEach(container => {
            container.innerHTML = '<span class="badge bg-secondary"><i class="fas fa-cloud-slash"></i> Offline</span>';
        });
    }
}

// Update face status display
function updateFaceStatusDisplay(studentId, status, message) {
    const container = document.querySelector(`.face-status-${studentId}`);
    if (!container) return;
    
    const badges = {
        'registered': '<span class="badge bg-success" title="' + message + '"><i class="fas fa-check-circle"></i> Registered</span>',
        'not_registered': '<span class="badge bg-warning" title="' + message + '"><i class="fas fa-exclamation-triangle"></i> Not Registered</span>',
        'api_offline': '<span class="badge bg-secondary" title="' + message + '"><i class="fas fa-cloud-slash"></i> API Offline</span>',
        'api_error': '<span class="badge bg-danger" title="' + message + '"><i class="fas fa-times-circle"></i> API Error</span>'
    };
    
    container.innerHTML = badges[status] || '<span class="badge bg-secondary">Unknown</span>';
}

// Refresh functions
async function refreshFaceStatus() {
    await loadBulkFaceStatus();
    showMessage('Face status refreshed successfully', 'success');
}

async function checkApiStatus() {
    try {
        const response = await fetch('/api/flask-status');
        const data = await response.json();
        const alert = document.getElementById('apiStatusAlert');
        const message = document.getElementById('apiStatusMessage');
        
        alert.className = `alert d-block alert-${data.status === 'connected' ? 'success' : data.status === 'offline' ? 'warning' : 'danger'}`;
        message.innerHTML = `<i class="fas fa-${data.status === 'connected' ? 'check' : data.status === 'offline' ? 'exclamation-triangle' : 'times'}"></i> ${data.message || 'Flask API status: ' + data.status}`;
        
        if (data.status === 'connected') {
            setTimeout(() => alert.classList.add('d-none'), 3000);
        }
    } catch (error) {
        const alert = document.getElementById('apiStatusAlert');
        alert.className = 'alert alert-danger d-block';
        document.getElementById('apiStatusMessage').innerHTML = '<i class="fas fa-times"></i> Failed to check API status';
    }
}

function refreshApiStatus() {
    showMessage('Refreshing API status and face data...', 'info');
    Promise.all([checkApiStatus(), refreshFaceStatus()]);
}

function showMessage(message, type = 'info') {
    const alert = document.getElementById('apiStatusAlert');
    const messageEl = document.getElementById('apiStatusMessage');
    
    alert.className = `alert alert-${type} d-block`;
    messageEl.innerHTML = `<i class="fas fa-${type === 'success' ? 'check' : 'info-circle'}"></i> ${message}`;
    
    setTimeout(() => alert.classList.add('d-none'), 3000);
}

// Utility functions
function clearSearch() {
    document.querySelector('input[name="search"]').value = '';
    document.getElementById('filterForm').submit();
}

function deleteStudent(id, name) {
    document.getElementById('deleteStudentName').textContent = name;
    document.getElementById('deleteForm').action = `/students/${id}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush

@push('styles')
<style>
.table th {
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.025em;
    border-bottom: 2px solid #dee2e6;
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.5rem;
}

/* Pagination Improvements */
.pagination-sm .page-link {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 0.375rem;
}

.pagination-sm .page-item:not(:first-child) .page-link {
    margin-left: 2px;
}

.pagination-sm .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
    font-weight: 600;
}

.pagination-sm .page-item.disabled .page-link {
    background-color: #f8f9fa;
    border-color: #dee2e6;
    color: #6c757d;
}

.pagination-sm .page-link:hover:not(.disabled) {
    background-color: #e9ecef;
    border-color: #dee2e6;
    color: #0d6efd;
}

.pagination-sm .page-link i {
    font-size: 0.75rem;
}

/* Card footer styling */
.card-footer {
    padding: 1rem 1.25rem;
    border-top: 1px solid rgba(0,0,0,.125);
}

/* Action buttons styling */
.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    border-radius: 0.375rem;
}

.btn-sm i {
    font-size: 0.75rem;
}

/* Action buttons responsive behavior */
@media (max-width: 1200px) {
    .btn-sm .d-xl-inline {
        display: none !important;
    }
}

@media (max-width: 992px) {
    .btn-sm .d-lg-inline {
        display: none !important;
    }
    
    .btn-sm {
        padding: 0.2rem 0.4rem;
        font-size: 0.8rem;
    }
    
    .btn-sm i {
        font-size: 0.7rem;
    }
}

@media (max-width: 768px) {
    /* Stack buttons vertically on mobile */
    .d-flex.gap-1.flex-wrap {
        flex-direction: column;
        gap: 0.25rem !important;
    }
    
    .btn-sm {
        width: 100%;
        justify-content: flex-start;
    }
}
</style>
@endpush