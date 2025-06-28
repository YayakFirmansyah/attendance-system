{{-- resources/views/students/faces.blade.php --}}
@extends('layouts.app')

@section('title', 'Manage Face Recognition - ' . $student->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>Face Recognition Setup</h1>
        <p class="text-muted">{{ $student->name }} ({{ $student->student_id }})</p>
    </div>
    <a href="{{ route('students.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Students
    </a>
</div>

<div class="row">
    <!-- Face Capture -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-camera"></i>
                    Capture Student Faces
                </h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <video id="webcam" width="640" height="480" autoplay style="border: 2px solid #dee2e6; border-radius: 8px;"></video>
                    <canvas id="canvas" width="640" height="480" style="display: none;"></canvas>
                </div>
                
                <div class="text-center mb-3">
                    <button id="start-camera" class="btn btn-primary me-2">
                        <i class="fas fa-video"></i> Start Camera
                    </button>
                    <button id="capture-face" class="btn btn-success me-2" disabled>
                        <i class="fas fa-camera"></i> Capture Face
                    </button>
                    <button id="upload-faces" class="btn btn-info" disabled>
                        <i class="fas fa-upload"></i> Upload Faces (<span id="face-count">0</span>)
                    </button>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Instructions:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Look directly at the camera</li>
                        <li>Ensure good lighting</li>
                        <li>Capture 3-5 different angles</li>
                        <li>Keep your face clearly visible</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Captured Faces Preview -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-images"></i>
                    Captured Faces
                </h5>
            </div>
            <div class="card-body">
                <div id="captured-faces" class="row g-2">
                    <!-- Captured faces will appear here -->
                </div>
                
                <div class="mt-3 text-center" style="display: none;" id="upload-progress">
                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                    <span>Uploading faces...</span>
                </div>
            </div>
        </div>
        
        <!-- Existing Face Encodings -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-database"></i>
                    Existing Face Data
                </h5>
            </div>
            <div class="card-body">
                @if($student->faceEncodings->count() > 0)
                    @foreach($student->faceEncodings as $encoding)
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                            <div>
                                <img src="{{ $encoding->image_url }}" width="50" height="50" class="rounded">
                                <span class="ms-2">
                                    {{ $encoding->is_primary ? 'Primary' : 'Secondary' }}
                                </span>
                            </div>
                            <small class="text-muted">
                                {{ $encoding->created_at->format('M d, Y') }}
                            </small>
                        </div>
                    @endforeach
                    
                    <div class="mt-3">
                        <button id="retrain-model" class="btn btn-warning btn-sm w-100">
                            <i class="fas fa-sync"></i> Retrain Recognition Model
                        </button>
                    </div>
                @else
                    <p class="text-muted">No face encodings yet. Capture some faces above.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let webcam, canvas, ctx;
let capturedFaces = [];
let isStreaming = false;

$(document).ready(function() {
    webcam = document.getElementById('webcam');
    canvas = document.getElementById('canvas');
    ctx = canvas.getContext('2d');
    
    // Start camera button
    $('#start-camera').click(function() {
        startCamera();
    });
    
    // Capture face button
    $('#capture-face').click(function() {
        captureFace();
    });
    
    // Upload faces button
    $('#upload-faces').click(function() {
        uploadFaces();
    });
    
    // Retrain model button
    $('#retrain-model').click(function() {
        retrainModel();
    });
});

function startCamera() {
    navigator.mediaDevices.getUserMedia({ 
        video: { 
            width: 640, 
            height: 480,
            facingMode: 'user'
        } 
    })
    .then(function(stream) {
        webcam.srcObject = stream;
        isStreaming = true;
        
        $('#start-camera').prop('disabled', true).text('Camera Active');
        $('#capture-face').prop('disabled', false);
    })
    .catch(function(err) {
        alert('Error accessing camera: ' + err.message);
    });
}

function captureFace() {
    if (!isStreaming) return;
    
    // Draw current frame to canvas
    ctx.drawImage(webcam, 0, 0, 640, 480);
    
    // Get image data as base64
    const imageData = canvas.toDataURL('image/jpeg', 0.8);
    
    // Add to captured faces
    capturedFaces.push(imageData);
    
    // Update UI
    updateCapturedFacesPreview();
    updateFaceCount();
    
    // Enable upload button if we have faces
    if (capturedFaces.length > 0) {
        $('#upload-faces').prop('disabled', false);
    }
}

function updateCapturedFacesPreview() {
    const container = $('#captured-faces');
    container.empty();
    
    capturedFaces.forEach((face, index) => {
        const preview = `
            <div class="col-6">
                <div class="position-relative">
                    <img src="${face}" class="img-fluid rounded" style="max-height: 80px;">
                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0" 
                            onclick="removeFace(${index})" style="padding: 2px 6px;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
        container.append(preview);
    });
}

function removeFace(index) {
    capturedFaces.splice(index, 1);
    updateCapturedFacesPreview();
    updateFaceCount();
    
    if (capturedFaces.length === 0) {
        $('#upload-faces').prop('disabled', true);
    }
}

function updateFaceCount() {
    $('#face-count').text(capturedFaces.length);
}

function uploadFaces() {
    if (capturedFaces.length === 0) {
        alert('Please capture some faces first.');
        return;
    }
    
    $('#upload-progress').show();
    $('#upload-faces').prop('disabled', true);
    
    $.ajax({
        url: '{{ route("students.upload-faces", $student) }}',
        method: 'POST',
        data: {
            faces: capturedFaces
        },
        success: function(response) {
            $('#upload-progress').hide();
            
            if (response.success) {
                alert('Faces uploaded successfully!\n' + response.message);
                
                // Clear captured faces
                capturedFaces = [];
                updateCapturedFacesPreview();
                updateFaceCount();
                $('#upload-faces').prop('disabled', true);
                
                // Reload page to show updated face encodings
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                alert('Error uploading faces: ' + response.message);
                $('#upload-faces').prop('disabled', false);
            }
        },
        error: function(xhr) {
            $('#upload-progress').hide();
            $('#upload-faces').prop('disabled', false);
            
            let message = 'Error uploading faces.';
            try {
                const response = JSON.parse(xhr.responseText);
                message = response.message || message;
            } catch (e) {}
            
            alert(message);
        }
    });
}

function retrainModel() {
    if (!confirm('Retrain the face recognition model? This may take a few minutes.')) {
        return;
    }
    
    const button = $('#retrain-model');
    const originalText = button.html();
    
    button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Retraining...');
    
    $.ajax({
        url: '{{ route("api.retrain") }}',
        method: 'POST',
        success: function(response) {
            button.prop('disabled', false).html(originalText);
            
            if (response.success) {
                alert('Model retrained successfully!\n' + response.message);
            } else {
                alert('Error retraining model: ' + response.message);
            }
        },
        error: function(xhr) {
            button.prop('disabled', false).html(originalText);
            alert('Error retraining model. Please try again.');
        }
    });
}
</script>
@endpush