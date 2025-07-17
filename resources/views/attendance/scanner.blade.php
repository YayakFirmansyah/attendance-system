@extends('layouts.app')

@section('title', 'Scanner Presensi')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-camera"></i>
                    Scanner Presensi - {{ $class->course->course_name }}
                </h5>
                <div>
                    <span id="api-status" class="badge bg-secondary">Checking API...</span>
                </div>
            </div>
            <div class="card-body">
                <!-- API Status Alert -->
                <div id="api-alert" class="alert alert-info d-none">
                    <i class="fas fa-info-circle"></i>
                    <span id="api-message">Checking Face Recognition API...</span>
                </div>

                <!-- Camera Container -->
                <div class="text-center mb-3">
                    <div class="position-relative d-inline-block">
                        <video id="videoElement" width="640" height="480" autoplay muted class="border rounded bg-dark"></video>
                        <canvas id="canvasElement" width="640" height="480" style="display: none;"></canvas>
                        
                        <!-- Loading Overlay -->
                        <div id="processingOverlay" class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-50 rounded d-none">
                            <div class="text-white text-center">
                                <div class="spinner-border text-light mb-2" role="status"></div>
                                <div>Processing...</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Controls -->
                <div class="text-center mb-3">
                    <button id="startBtn" class="btn btn-success me-2">
                        <i class="fas fa-play"></i> Start Camera
                    </button>
                    <button id="stopBtn" class="btn btn-danger me-2" disabled>
                        <i class="fas fa-stop"></i> Stop Camera
                    </button>
                    <button id="captureBtn" class="btn btn-primary me-2" disabled>
                        <i class="fas fa-camera"></i> Capture Manual
                    </button>
                    <button id="clearLogsBtn" class="btn btn-outline-secondary">
                        <i class="fas fa-trash"></i> Clear Logs
                    </button>
                </div>

                <!-- Settings -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="autoCapture" checked>
                            <label class="form-check-label" for="autoCapture">
                                Auto Capture
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="showLogs" checked>
                            <label class="form-check-label" for="showLogs">
                                Show Logs
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="captureInterval" class="form-label">Interval (seconds):</label>
                        <input type="range" class="form-range" id="captureInterval" min="2" max="10" value="3">
                        <small class="text-muted">Every <span id="intervalValue">3</span> seconds</small>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <small class="text-muted">Min Confidence:</small><br>
                            <span class="badge bg-info">{{ (config('app.face_similarity_threshold', 0.5) * 100) }}%</span>
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title">Captures</h6>
                                <h4 id="totalCaptures" class="text-primary">0</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title">Faces Detected</h6>
                                <h4 id="totalFaces" class="text-info">0</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title">Recognized</h6>
                                <h4 id="totalRecognized" class="text-success">0</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title">Attendance</h6>
                                <h4 id="totalAttendance" class="text-warning">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Attendance List -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list"></i>
                    Attendance Today
                </h5>
                <button class="btn btn-sm btn-outline-primary" onclick="scanner.loadAttendanceData()">
                    <i class="fas fa-sync"></i>
                </button>
            </div>
            <div class="card-body">
                <div id="attendanceList" style="max-height: 400px; overflow-y: auto;">
                    <div class="text-center text-muted">
                        <div class="spinner-border spinner-border-sm" role="status"></div>
                        <p class="mt-2">Loading attendance...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detection Logs -->
        <div class="card mt-3" id="logsCard">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history"></i>
                    Detection Logs
                </h5>
                <small class="text-muted">Live updates</small>
            </div>
            <div class="card-body">
                <div id="detectionLogs" style="max-height: 300px; overflow-y: auto;">
                    <p class="text-muted text-center">No logs yet</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
class FaceRecognitionScanner {
    constructor() {
        this.video = document.getElementById('videoElement');
        this.canvas = document.getElementById('canvasElement');
        this.ctx = this.canvas.getContext('2d');
        this.stream = null;
        this.isProcessing = false;
        this.apiWarmedUp = false;
        this.autoCapture = true;
        this.autoCaptureInterval = null;
        this.captureIntervalSeconds = 3;
        this.classId = {{ $class->id }};
        
        // Statistics
        this.stats = {
            totalCaptures: 0,
            totalFaces: 0,
            totalRecognized: 0,
            totalAttendance: 0
        };
        
        this.initializeEventListeners();
        this.checkApiWarmup();
        this.loadAttendanceData();
    }
    
    initializeEventListeners() {
        // Camera Controls
        document.getElementById('startBtn').addEventListener('click', () => this.startCamera());
        document.getElementById('stopBtn').addEventListener('click', () => this.stopCamera());
        document.getElementById('captureBtn').addEventListener('click', () => this.captureFrame());
        document.getElementById('clearLogsBtn').addEventListener('click', () => this.clearLogs());
        
        // Settings
        document.getElementById('autoCapture').addEventListener('change', (e) => {
            this.autoCapture = e.target.checked;
            if (this.autoCapture && this.stream) {
                this.startAutoCapture();
            } else {
                this.stopAutoCapture();
            }
        });
        
        document.getElementById('showLogs').addEventListener('change', (e) => {
            document.getElementById('logsCard').style.display = e.target.checked ? 'block' : 'none';
        });
        
        document.getElementById('captureInterval').addEventListener('input', (e) => {
            this.captureIntervalSeconds = parseInt(e.target.value);
            document.getElementById('intervalValue').textContent = this.captureIntervalSeconds;
            
            if (this.autoCaptureInterval) {
                this.stopAutoCapture();
                this.startAutoCapture();
            }
        });
    }
    
    async checkApiWarmup() {
        try {
            this.updateApiStatus('Checking API...', 'secondary');
            this.showApiAlert('Checking Face Recognition API status...', 'info');
            
            const response = await fetch('{{ route("api.status") }}');
            const result = await response.json();
            
            if (result.status === 'connected') {
                this.apiWarmedUp = true;
                this.updateApiStatus('API Ready', 'success');
                this.showApiAlert('Face Recognition API is ready!', 'success');
                setTimeout(() => this.hideApiAlert(), 3000);
            } else {
                this.updateApiStatus('API Loading...', 'warning');
                this.showApiAlert('Face Recognition API is loading, please wait...', 'warning');
                setTimeout(() => this.checkApiWarmup(), 5000);
            }
        } catch (error) {
            this.updateApiStatus('API Error', 'danger');
            this.showApiAlert('Face Recognition API connection failed. Please check if Flask API is running.', 'danger');
            setTimeout(() => this.checkApiWarmup(), 10000);
        }
    }
    
    updateApiStatus(message, type) {
        const statusElement = document.getElementById('api-status');
        statusElement.textContent = message;
        statusElement.className = `badge bg-${type}`;
    }
    
    showApiAlert(message, type) {
        const alert = document.getElementById('api-alert');
        const messageElement = document.getElementById('api-message');
        
        alert.className = `alert alert-${type}`;
        messageElement.textContent = message;
        alert.classList.remove('d-none');
    }
    
    hideApiAlert() {
        document.getElementById('api-alert').classList.add('d-none');
    }
    
    async startCamera() {
        try {
            this.stream = await navigator.mediaDevices.getUserMedia({
                video: { 
                    width: { ideal: 640 }, 
                    height: { ideal: 480 },
                    facingMode: 'user'
                }
            });
            
            this.video.srcObject = this.stream;
            
            // Update UI
            document.getElementById('startBtn').disabled = true;
            document.getElementById('stopBtn').disabled = false;
            document.getElementById('captureBtn').disabled = false;
            
            if (this.autoCapture) {
                this.startAutoCapture();
            }
            
            this.addLog('Camera started successfully', 'success');
            
        } catch (error) {
            console.error('Error accessing camera:', error);
            this.addLog('Error accessing camera: ' + error.message, 'error');
            this.showApiAlert('Camera access denied. Please allow camera access and try again.', 'danger');
        }
    }
    
    stopCamera() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }
        
        this.stopAutoCapture();
        
        // Update UI
        document.getElementById('startBtn').disabled = false;
        document.getElementById('stopBtn').disabled = true;
        document.getElementById('captureBtn').disabled = true;
        
        this.addLog('Camera stopped', 'info');
    }
    
    startAutoCapture() {
        if (this.autoCaptureInterval) {
            clearInterval(this.autoCaptureInterval);
        }
        
        this.autoCaptureInterval = setInterval(() => {
            this.captureFrame();
        }, this.captureIntervalSeconds * 1000);
        
        this.addLog(`Auto capture started (every ${this.captureIntervalSeconds}s)`, 'info');
    }
    
    stopAutoCapture() {
        if (this.autoCaptureInterval) {
            clearInterval(this.autoCaptureInterval);
            this.autoCaptureInterval = null;
        }
    }
    
    captureFrame() {
        if (!this.stream || this.isProcessing) return;
        
        if (!this.apiWarmedUp) {
            this.addLog('⏳ Waiting for API to be ready...', 'warning');
            return;
        }
        
        // Draw video frame to canvas
        this.ctx.drawImage(this.video, 0, 0, 640, 480);
        
        // Get base64 image data
        const imageData = this.canvas.toDataURL('image/jpeg', 0.8);
        
        // Update statistics
        this.stats.totalCaptures++;
        this.updateStatistics();
        
        // Process frame
        this.processFrame(imageData);
    }
    
    async processFrame(imageData) {
        if (this.isProcessing) return;
        
        this.isProcessing = true;
        this.showProcessingOverlay();
        this.updateApiStatus('Processing...', 'info');
        
        try {
            const response = await fetch('{{ route("api.attendance.process") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    image: imageData,
                    class_id: this.classId,
                    device_info: navigator.userAgent
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Update statistics
                this.stats.totalFaces += result.total_faces_detected;
                this.stats.totalRecognized += result.total_recognized;
                this.updateStatistics();
                
                // Process recognition results
                if (result.recognized_students.length > 0) {
                    result.recognized_students.forEach(student => {
                        if (student.status === 'new_attendance') {
                            this.addLog(`✅ ${student.student.name} (${student.student.student_id}) - ${(student.confidence * 100).toFixed(1)}%`, 'success');
                            this.stats.totalAttendance++;
                        }
                    });
                    
                    // Refresh attendance list
                    this.loadAttendanceData();
                } else if (result.total_faces_detected > 0) {
                    this.addLog(`👤 ${result.total_faces_detected} face(s) detected but not recognized`, 'warning');
                }
                
                this.updateApiStatus('API Ready', 'success');
                
            } else {
                this.addLog(`❌ Error: ${result.message}`, 'error');
                this.updateApiStatus('API Error', 'danger');
            }
            
        } catch (error) {
            console.error('Processing error:', error);
            this.addLog(`❌ Connection error: ${error.message}`, 'error');
            this.updateApiStatus('API Error', 'danger');
            
        } finally {
            this.isProcessing = false;
            this.hideProcessingOverlay();
        }
    }
    
    showProcessingOverlay() {
        document.getElementById('processingOverlay').classList.remove('d-none');
    }
    
    hideProcessingOverlay() {
        document.getElementById('processingOverlay').classList.add('d-none');
    }
    
    updateStatistics() {
        document.getElementById('totalCaptures').textContent = this.stats.totalCaptures;
        document.getElementById('totalFaces').textContent = this.stats.totalFaces;
        document.getElementById('totalRecognized').textContent = this.stats.totalRecognized;
        document.getElementById('totalAttendance').textContent = this.stats.totalAttendance;
    }
    
    addLog(message, type = 'info') {
        const logsContainer = document.getElementById('detectionLogs');
        const timestamp = new Date().toLocaleTimeString();
        
        const logElement = document.createElement('div');
        const alertClass = type === 'success' ? 'success' : 
                          type === 'warning' ? 'warning' : 
                          type === 'error' ? 'danger' : 'info';
        
        logElement.className = `alert alert-${alertClass} py-2 mb-2`;
        logElement.innerHTML = `
            <div class="d-flex justify-content-between">
                <small><strong>${timestamp}</strong></small>
                <small class="text-muted">#${this.stats.totalCaptures}</small>
            </div>
            <div>${message}</div>
        `;
        
        // Clear placeholder if exists
        if (logsContainer.children.length === 0 || logsContainer.children[0].textContent.includes('No logs yet')) {
            logsContainer.innerHTML = '';
        }
        
        // Add to top of logs
        logsContainer.insertBefore(logElement, logsContainer.firstChild);
        
        // Keep only last 50 logs
        while (logsContainer.children.length > 50) {
            logsContainer.removeChild(logsContainer.lastChild);
        }
    }
    
    clearLogs() {
        document.getElementById('detectionLogs').innerHTML = '<p class="text-muted text-center">No logs yet</p>';
    }
    
    async loadAttendanceData() {
        try {
            const response = await fetch(`{{ route('api.attendance.today', $class) }}`);
            const result = await response.json();
            
            if (result.success) {
                this.displayAttendanceList(result.attendances);
                this.stats.totalAttendance = result.total;
                this.updateStatistics();
            } else {
                console.error('Error loading attendance:', result.message);
            }
            
        } catch (error) {
            console.error('Error loading attendance:', error);
        }
    }
    
    displayAttendanceList(attendances) {
        const container = document.getElementById('attendanceList');
        
        if (attendances.length === 0) {
            container.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="fas fa-clipboard-list fa-3x mb-2"></i>
                    <p>No attendance recorded yet</p>
                </div>
            `;
            return;
        }
        
        let html = '<div class="list-group list-group-flush">';
        attendances.forEach((attendance, index) => {
            const confidence = attendance.similarity_score ? (attendance.similarity_score * 100).toFixed(1) : '0';
            html += `
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold">${attendance.student_name}</div>
                        <small class="text-muted">${attendance.student_id}</small>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">${attendance.check_in}</small>
                        <span class="badge bg-success">${confidence}%</span>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        
        container.innerHTML = html;
    }
}

// Initialize scanner when page loads
document.addEventListener('DOMContentLoaded', function() {
    window.scanner = new FaceRecognitionScanner();
    
    // Auto-refresh attendance every 30 seconds
    setInterval(() => {
        window.scanner.loadAttendanceData();
    }, 30000);
    
    // Handle page unload
    window.addEventListener('beforeunload', function() {
        if (window.scanner) {
            window.scanner.stopCamera();
        }
    });
});
</script>
@endpush