{{-- resources/views/attendance/scanner.blade.php --}}
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
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users"></i>
                    Today's Attendance
                </h5>
            </div>
            <div class="card-body">
                <div id="attendance-list">
                    <!-- Attendance will be loaded here -->
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
                    <p class="text-muted">Start scanner to see recognition results...</p>
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
        $('#recognition-status').removeClass('bg-secondary').addClass('bg-success').text('Scanning...');
        
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
        alert('Error accessing camera: ' + err.message);
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
    $('#recognition-status').removeClass('bg-success').addClass('bg-secondary').text('Stopped');
    
    $(document).off('keydown');
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
    $('#recognition-status').removeClass('bg-success').addClass('bg-warning').text('Processing...');
    
    $.ajax({
        url: '{{ route("api.attendance.process") }}',
        method: 'POST',
        data: {
            image: imageData,
            class_id: {{ $class->id }},
            device_info: navigator.userAgent
        },
        success: function(response) {
            $('#recognition-status').removeClass('bg-warning').addClass('bg-success').text('Scanning...');
            
            if (response.success) {
                displayRecognitionResults(response);
                
                if (response.results && response.results.length > 0) {
                    // Refresh attendance list
                    loadTodayAttendance();
                    
                    // Show success notification
                    response.results.forEach(result => {
                        showAttendanceNotification(result);
                    });
                }
            } else {
                console.log('Recognition failed:', response.message);
            }
        },
        error: function(xhr) {
            $('#recognition-status').removeClass('bg-warning').addClass('bg-danger').text('Error');
            console.error('Processing error:', xhr.responseText);
            
            setTimeout(() => {
                if (isScanning) {
                    $('#recognition-status').removeClass('bg-danger').addClass('bg-success').text('Scanning...');
                }
            }, 2000);
        }
    });
}

function displayRecognitionResults(response) {
    const container = $('#recognition-results');
    
    if (response.detected_faces && response.detected_faces.length > 0) {
        let html = '<div class="mb-2"><small class="text-muted">' + new Date().toLocaleTimeString() + '</small></div>';
        
        response.detected_faces.forEach(face => {
            const confidence = (face.confidence_score * 100).toFixed(1);
            const isRecognized = face.student_id !== null;
            
            html += `
                <div class="border rounded p-2 mb-2 ${isRecognized ? 'border-success bg-light' : 'border-warning'}">
                    <div class="d-flex justify-content-between">
                        <span>${isRecognized ? 'Recognized' : 'Unknown'}</span>
                        <span class="badge ${isRecognized ? 'bg-success' : 'bg-warning'}">${confidence}%</span>
                    </div>
                    ${isRecognized ? `<small class="text-muted">Student ID: ${face.student_id}</small>` : ''}
                </div>
            `;
        });
        
        container.html(html);
    } else {
        container.html('<p class="text-muted">No faces detected in last scan.</p>');
    }
}

function loadTodayAttendance() {
    $.get('{{ route("attendance.class", $class) }}')
        .done(function(data) {
            // Extract attendance data from response
            // This is a simplified version - you might need to adjust based on your actual response
            const parser = new DOMParser();
            const doc = parser.parseFromString(data, 'text/html');
            const attendanceRows = doc.querySelectorAll('tbody tr');
            
            let html = '';
            let count = 0;
            
            attendanceRows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length >= 3) {
                    const name = cells[1].textContent.trim();
                    const time = cells[2].textContent.trim();
                    
                    if (name && time && time !== '-') {
                        html += `
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-1 mb-1">
                                <span>${name}</span>
                                <small class="text-muted">${time}</small>
                            </div>
                        `;
                        count++;
                    }
                }
            });
            
            if (html) {
                $('#attendance-list').html(`
                    <div class="mb-2">
                        <strong>Present Today: ${count}</strong>
                    </div>
                    ${html}
                `);
            } else {
                $('#attendance-list').html('<p class="text-muted">No attendance recorded yet.</p>');
            }
        })
        .fail(function() {
            $('#attendance-list').html('<p class="text-danger">Error loading attendance.</p>');
        });
}

function showAttendanceNotification(result) {
    // Create toast notification
    const toast = `
        <div class="toast align-items-center text-white bg-success border-0" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
            <div class="d-flex">
                <div class="toast-body">
                    <strong>${result.student_name}</strong> marked present<br>
                    <small>Confidence: ${(result.confidence * 100).toFixed(1)}%</small>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    $('body').append(toast);
    $('.toast').last().toast({ delay: 4000 }).toast('show').on('hidden.bs.toast', function() {
        $(this).remove();
    });
}
</script>
@endpush