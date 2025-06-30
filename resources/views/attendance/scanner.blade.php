{{-- resources/views/attendance/scanner.blade.php - FIXED VERSION --}}
@extends('layouts.app')

@section('title', 'Attendance Scanner - ' . $class->course->course_name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>Attendance Scanner</h1>
        <p class="text-muted">
            {{ $class->course->course_name }} - {{ $class->class_code }} 
            ({{ $class->room }})
        </p>
    </div>
    <div>
        <a href="{{ route('attendance.class', $class) }}" class="btn btn-secondary me-2">
            <i class="fas fa-list"></i> View Attendance
        </a>
        <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<!-- Notification Toast Container -->
<div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>

<div class="row">
    <!-- Camera Section -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-camera"></i>
                    Live Face Recognition
                    <span id="recognition-status" class="badge bg-secondary ms-2">Ready</span>
                </h5>
            </div>
            <div class="card-body text-center">
                <div class="webcam-container position-relative d-inline-block">
                    <video id="webcam" width="640" height="480" autoplay 
                           style="border: 3px solid #dee2e6; border-radius: 8px;"></video>
                    <canvas id="canvas" width="640" height="480" style="display: none;"></canvas>
                    <div id="detection-overlay" class="detection-overlay"></div>
                </div>
                
                <div class="mt-3">
                    <button id="start-scanner" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-video"></i> Start Scanner
                    </button>
                    <button id="stop-scanner" class="btn btn-danger btn-lg" disabled>
                        <i class="fas fa-stop"></i> Stop Scanner
                    </button>
                </div>
                
                <div class="mt-3">
                    <div class="form-check form-switch d-inline-block">
                        <input class="form-check-input" type="checkbox" id="auto-capture" checked>
                        <label class="form-check-label" for="auto-capture">
                            Auto-capture every 3 seconds
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Results Section -->
    <div class="col-md-4">
        <!-- Today's Attendance -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users"></i>
                    Today's Attendance
                </h5>
                <button class="btn btn-sm btn-outline-primary" onclick="loadTodayAttendance()">
                    <i class="fas fa-sync"></i>
                </button>
            </div>
            <div class="card-body">
                <div id="attendance-list">
                    <div class="text-center">
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-2">Loading attendance...</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recognition Results -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-eye"></i>
                    Recognition Results
                </h5>
            </div>
            <div class="card-body">
                <div id="recognition-results">
                    <div class="text-center text-muted">
                        <i class="fas fa-camera fa-2x mb-2 opacity-50"></i>
                        <p>Start scanner to see recognition results...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let webcam, canvas, ctx;
let isScanning = false;
let scanInterval;
let lastCaptureTime = 0;

$(document).ready(function() {
    webcam = document.getElementById('webcam');
    canvas = document.getElementById('canvas');
    ctx = canvas.getContext('2d');
    
    // Load today's attendance
    loadTodayAttendance();
    
    // Event listeners
    $('#start-scanner').click(startScanner);
    $('#stop-scanner').click(stopScanner);
    
    // Auto-refresh attendance every 30 seconds
    setInterval(loadTodayAttendance, 30000);
});

function startScanner() {
    navigator.mediaDevices.getUserMedia({ 
        video: { 
            width: 640, 
            height: 480,
            facingMode: 'user'
        } 
    })
    .then(function(stream) {
        webcam.srcObject = stream;
        isScanning = true;
        
        $('#start-scanner').prop('disabled', true);
        $('#stop-scanner').prop('disabled', false);
        $('#recognition-status').removeClass('bg-secondary bg-danger').addClass('bg-success').text('Scanning...');
        
        showNotification('Scanner started successfully', 'success');
        
        // Start auto-capture if enabled
        if ($('#auto-capture').is(':checked')) {
            scanInterval = setInterval(captureAndProcess, 3000);
        }
        
        // Manual capture on spacebar
        $(document).on('keydown', function(e) {
            if (e.code === 'Space' && isScanning) {
                e.preventDefault();
                captureAndProcess();
            }
        });
    })
    .catch(function(err) {
        console.error('Camera error:', err);
        showNotification('Error accessing camera: ' + err.message, 'error');
        $('#recognition-status').removeClass('bg-success bg-warning').addClass('bg-danger').text('Camera Error');
    });
}

function stopScanner() {
    if (webcam.srcObject) {
        webcam.srcObject.getTracks().forEach(track => track.stop());
    }
    
    isScanning = false;
    
    if (scanInterval) {
        clearInterval(scanInterval);
    }
    
    $('#start-scanner').prop('disabled', false);
    $('#stop-scanner').prop('disabled', true);
    $('#recognition-status').removeClass('bg-success bg-warning bg-danger').addClass('bg-secondary').text('Stopped');
    
    $(document).off('keydown');
    showNotification('Scanner stopped', 'info');
}

function captureAndProcess() {
    if (!isScanning) return;
    
    // Throttle captures
    const now = Date.now();
    if (now - lastCaptureTime < 2000) return;
    lastCaptureTime = now;
    
    // Capture frame
    ctx.drawImage(webcam, 0, 0, 640, 480);
    const imageData = canvas.toDataURL('image/jpeg', 0.8);
    
    // Process attendance
    processAttendance(imageData);
}

function processAttendance(imageData) {
    $('#recognition-status').removeClass('bg-success bg-danger').addClass('bg-warning').text('Processing...');
    
    $.ajax({
        url: '{{ route("api.attendance.process") }}',
        method: 'POST',
        data: {
            image: imageData,
            class_id: {{ $class->id }},
            device_info: navigator.userAgent,
            _token: '{{ csrf_token() }}'
        },
        timeout: 30000,
        success: function(response) {
            console.log('API Response:', response);
            
            if (response.success) {
                $('#recognition-status').removeClass('bg-warning bg-danger').addClass('bg-success').text('Scanning...');
                
                displayRecognitionResults(response);
                
                // Check for verified faces and attendance records
                if (response.results && response.results.length > 0) {
                    let verifiedCount = 0;
                    response.results.forEach(result => {
                        if (result.verified) {
                            verifiedCount++;
                            showAttendanceNotification(result);
                        }
                    });
                    
                    if (verifiedCount > 0) {
                        // Refresh attendance list after a short delay
                        setTimeout(loadTodayAttendance, 1000);
                    }
                    
                    if (verifiedCount === 0) {
                        showNotification('Faces detected but not recognized', 'warning');
                    }
                } else {
                    showNotification('No faces detected', 'info');
                    displayNoFacesDetected();
                }
            } else {
                $('#recognition-status').removeClass('bg-warning').addClass('bg-danger').text('Error');
                showNotification('Recognition failed: ' + (response.message || 'Unknown error'), 'error');
                displayRecognitionError(response.message);
            }
        },
        error: function(xhr, status, error) {
            $('#recognition-status').removeClass('bg-warning').addClass('bg-danger').text('Error');
            console.error('Processing error:', xhr.responseText);
            
            let errorMessage = 'Processing failed';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (status === 'timeout') {
                errorMessage = 'Request timeout - Python API too slow';
            } else if (xhr.status === 0) {
                errorMessage = 'Connection failed - Check if Python API is running';
            }
            
            showNotification(errorMessage, 'error');
            displayRecognitionError(errorMessage);
            
            // Resume scanning after error
            setTimeout(() => {
                if (isScanning) {
                    $('#recognition-status').removeClass('bg-danger').addClass('bg-success').text('Scanning...');
                }
            }, 3000);
        }
    });
}

function displayRecognitionResults(response) {
    const container = $('#recognition-results');
    
    if (response.results && response.results.length > 0) {
        let html = '<div class="mb-2"><small class="text-muted">' + new Date().toLocaleTimeString() + '</small></div>';
        
        response.results.forEach((result, index) => {
            const similarity = (result.similarity * 100).toFixed(1);
            const isVerified = result.verified;
            
            html += `
                <div class="border rounded p-2 mb-2 ${isVerified ? 'border-success bg-light' : 'border-warning'}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${isVerified ? result.student_name : 'Unknown Face'}</strong>
                            ${isVerified ? '<br><small class="text-muted">ID: ' + result.student_id + '</small>' : ''}
                        </div>
                        <div class="text-end">
                            <span class="badge ${isVerified ? 'bg-success' : 'bg-warning'}">${similarity}%</span>
                            <br><small class="text-muted">MTCNN: ${(result.mtcnn_confidence * 100).toFixed(1)}%</small>
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.html(html);
    } else {
        displayNoFacesDetected();
    }
}

function displayNoFacesDetected() {
    $('#recognition-results').html(`
        <div class="text-center text-muted">
            <i class="fas fa-user-slash fa-2x mb-2 opacity-50"></i>
            <p>No faces detected in last scan.</p>
            <small>Make sure your face is clearly visible and well-lit.</small>
        </div>
    `);
}

function displayRecognitionError(errorMessage) {
    $('#recognition-results').html(`
        <div class="text-center text-danger">
            <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
            <p><strong>Recognition Error</strong></p>
            <small>${errorMessage}</small>
        </div>
    `);
}

function loadTodayAttendance() {
    // Show loading state
    $('#attendance-list').html(`
        <div class="text-center">
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-2">Loading attendance...</p>
        </div>
    `);
    
    // Use dedicated API endpoint for attendance
    $.ajax({
        url: '{{ route("api.attendance.today", $class) }}',
        method: 'GET',
        timeout: 10000,
        success: function(response) {
            if (response.success && response.attendances) {
                displayTodayAttendance(response.attendances);
            } else {
                $('#attendance-list').html('<p class="text-muted">No attendance recorded yet.</p>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading attendance:', error);
            $('#attendance-list').html(`
                <div class="text-center text-danger">
                    <i class="fas fa-exclamation-triangle mb-2"></i>
                    <p>Error loading attendance</p>
                    <button class="btn btn-sm btn-outline-primary" onclick="loadTodayAttendance()">
                        <i class="fas fa-retry"></i> Retry
                    </button>
                </div>
            `);
        }
    });
}

function displayTodayAttendance(attendances) {
    let html = `<div class="mb-2"><strong>Present Today: ${attendances.length}</strong></div>`;
    
    if (attendances.length > 0) {
        attendances.forEach(attendance => {
            const time = new Date(attendance.check_in).toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
            
            html += `
                <div class="d-flex justify-content-between align-items-center border-bottom pb-1 mb-1">
                    <div>
                        <span>${attendance.student.name}</span>
                        <br><small class="text-muted">${attendance.student.nim}</small>
                    </div>
                    <div class="text-end">
                        <small class="text-success">${time}</small>
                        ${attendance.similarity_score ? '<br><small class="text-muted">' + (attendance.similarity_score * 100).toFixed(1) + '%</small>' : ''}
                    </div>
                </div>
            `;
        });
    } else {
        html += '<p class="text-muted">No attendance recorded yet.</p>';
    }
    
    $('#attendance-list').html(html);
}

function showAttendanceNotification(result) {
    const similarity = (result.similarity * 100).toFixed(1);
    showNotification(
        `✅ <strong>${result.student_name}</strong> marked present<br><small>Confidence: ${similarity}%</small>`,
        'success',
        5000
    );
}

function showNotification(message, type = 'info', duration = 4000) {
    const types = {
        'success': { bg: 'bg-success', icon: 'fas fa-check-circle' },
        'error': { bg: 'bg-danger', icon: 'fas fa-exclamation-circle' },
        'warning': { bg: 'bg-warning text-dark', icon: 'fas fa-exclamation-triangle' },
        'info': { bg: 'bg-info', icon: 'fas fa-info-circle' }
    };
    
    const config = types[type] || types['info'];
    const toastId = 'toast-' + Date.now();
    
    const toast = `
        <div id="${toastId}" class="toast align-items-center text-white ${config.bg} border-0 mb-2" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="${config.icon} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    $('#toast-container').append(toast);
    
    const $toast = $('#' + toastId);
    $toast.toast({ delay: duration }).toast('show').on('hidden.bs.toast', function() {
        $(this).remove();
    });
}
</script>
@endpush