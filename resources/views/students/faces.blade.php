{{-- resources/views/students/faces.blade.php --}}
@extends('layouts.app')

@section('title', 'Face Registration Status')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>Face Registration Status</h1>
        <p class="text-muted mb-0">Check face recognition status for {{ $student->name }}</p>
    </div>
    <a href="{{ route('students.show', $student) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to Student
    </a>
</div>

<div class="row">
    {{-- Student Info --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                @if($student->profile_photo)
                    <img src="{{ $student->profile_photo_url }}" 
                         class="rounded-circle mb-3" 
                         width="120" height="120" 
                         style="object-fit: cover;"
                         alt="{{ $student->name }}">
                @else
                    <div class="bg-light border rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                         style="width: 120px; height: 120px;">
                        <i class="fas fa-user fa-3x text-muted"></i>
                    </div>
                @endif
                
                <h5>{{ $student->name }}</h5>
                <p class="text-muted">{{ $student->student_id }}</p>
                <span class="badge bg-info">{{ $student->program_study }}</span>
            </div>
        </div>
    </div>

    {{-- Face Registration Status --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">Face Recognition Status</h6>
                <button onclick="refreshStatus()" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-sync"></i> Refresh Status
                </button>
            </div>
            <div class="card-body">
                <div id="registrationStatus">
                    @if($student->is_face_registered)
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle fa-2x text-success float-start me-3"></i>
                            <h5 class="alert-heading">Face Registered!</h5>
                            <p class="mb-0">This student's face is registered in the recognition model. They can be automatically recognized during attendance.</p>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle fa-2x text-warning float-start me-3"></i>
                            <h5 class="alert-heading">Face Not Registered</h5>
                            <p class="mb-0">This student's face is not yet registered in the recognition model. Please contact administrator to add this student to the training data.</p>
                        </div>
                    @endif
                </div>

                {{-- Model Information --}}
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Model Information</h6>
                    </div>
                    <div class="card-body">
                        <div id="modelInfo" class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading model information...</p>
                        </div>
                    </div>
                </div>

                {{-- Instructions --}}
                @if(!$student->is_face_registered)
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">How to Register Face</h6>
                        </div>
                        <div class="card-body">
                            <ol>
                                <li><strong>Contact Administrator:</strong> Request to be added to the face recognition training data</li>
                                <li><strong>Provide Photos:</strong> Submit 5-10 clear face photos from different angles</li>
                                <li><strong>Wait for Training:</strong> Administrator will retrain the model with your data</li>
                                <li><strong>Verification:</strong> Your face registration status will be updated automatically</li>
                            </ol>
                            
                            <div class="alert alert-info mt-3">
                                <i class="fas fa-info-circle"></i>
                                <strong>Note:</strong> Face registration is managed through the main training pipeline using main.ipynb. This process requires manual intervention from the system administrator.
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Load model information on page load
document.addEventListener('DOMContentLoaded', function() {
    loadModelInfo();
});

function refreshStatus() {
    // Refresh registration status
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
    btn.disabled = true;
    
    fetch('/api/students/{{ $student->id }}/face-status', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateRegistrationStatus(data.is_registered);
            loadModelInfo(); // Refresh model info too
        } else {
            alert('Failed to refresh status: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error refreshing status: ' + error.message);
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

function updateRegistrationStatus(isRegistered) {
    const container = document.getElementById('registrationStatus');
    
    if (isRegistered) {
        container.innerHTML = `
            <div class="alert alert-success">
                <i class="fas fa-check-circle fa-2x text-success float-start me-3"></i>
                <h5 class="alert-heading">Face Registered!</h5>
                <p class="mb-0">This student's face is registered in the recognition model. They can be automatically recognized during attendance.</p>
            </div>
        `;
    } else {
        container.innerHTML = `
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle fa-2x text-warning float-start me-3"></i>
                <h5 class="alert-heading">Face Not Registered</h5>
                <p class="mb-0">This student's face is not yet registered in the recognition model. Please contact administrator to add this student to the training data.</p>
            </div>
        `;
    }
}

function loadModelInfo() {
    fetch('/api/model-info')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayModelInfo(data.model_info);
        } else {
            displayModelError(data.message);
        }
    })
    .catch(error => {
        displayModelError('Failed to load model information');
    });
}

function displayModelInfo(modelInfo) {
    const container = document.getElementById('modelInfo');
    container.innerHTML = `
        <div class="row text-start">
            <div class="col-md-6">
                <p><strong>Total Registered Students:</strong> ${modelInfo.classes.length}</p>
                <p><strong>Model Status:</strong> <span class="badge bg-success">Active</span></p>
            </div>
            <div class="col-md-6">
                <p><strong>Last Updated:</strong> ${modelInfo.last_updated || 'Unknown'}</p>
                <p><strong>API Status:</strong> <span class="badge bg-success">Connected</span></p>
            </div>
        </div>
        <details class="mt-3">
            <summary class="btn btn-sm btn-outline-info">View All Registered Names</summary>
            <div class="mt-2 p-3 bg-light rounded">
                <div class="row">
                    ${modelInfo.classes.map((name, index) => `
                        <div class="col-md-3 mb-1">
                            <small class="badge bg-secondary">${name}</small>
                        </div>
                    `).join('')}
                </div>
            </div>
        </details>
    `;
}

function displayModelError(message) {
    const container = document.getElementById('modelInfo');
    container.innerHTML = `
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Error:</strong> ${message}
        </div>
    `;
}
</script>
@endpush