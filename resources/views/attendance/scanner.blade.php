@extends('layouts.app')

@section('title', 'Scanner Presensi')

@push('styles')
    <style>
        /* Scanner Specific Styles */
        .scanner-container {
            position: relative;
            width: 100%;
            max-width: 640px;
            margin: 0 auto;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            background: #000;
            aspect-ratio: 4/3;
        }

        @media (max-width: 768px) {
            .scanner-container {
                aspect-ratio: 3/4;
                border-radius: 20px;
            }
        }

        #videoElement {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1);
        }

        /* Scanner Overlay & Animation */
        .scanner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            box-shadow: inset 0 0 0 40px rgba(0, 0, 0, 0.3);
            z-index: 10;
            transition: box-shadow 0.3s;
        }

        .scan-line {
            position: absolute;
            width: 100%;
            height: 4px;
            background: var(--primary-color, #10b981);
            box-shadow: 0 0 15px var(--primary-color, #10b981), 0 0 30px var(--primary-color, #10b981);
            top: 0;
            left: 0;
            z-index: 11;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .is-scanning .scan-line {
            opacity: 0.8;
            animation: scan 2.5s infinite linear;
        }

        @keyframes scan {

            0%,
            100% {
                top: 10%;
            }

            50% {
                top: 90%;
            }
        }

        /* Floating Camera Controls */
        .camera-controls {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 20;
            display: flex;
            gap: 15px;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(10px);
            padding: 10px 20px;
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-camera-action {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
            transition: all 0.2s;
        }

        .btn-camera-action:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-play {
            background: #10b981;
        }

        .btn-play:hover:not(:disabled) {
            background: #059669;
            transform: scale(1.05);
        }

        .btn-stop {
            background: #ef4444;
        }

        .btn-stop:hover:not(:disabled) {
            background: #dc2626;
            transform: scale(1.05);
        }

        .btn-snap {
            background: transparent;
            border: 2px solid white;
            border-radius: 50%;
            padding: 3px;
            height: 54px;
            width: 54px;
        }

        .btn-snap .inner {
            width: 100%;
            height: 100%;
            background: white;
            border-radius: 50%;
            transition: all 0.2s;
        }

        .btn-snap:hover:not(:disabled) .inner {
            background: #e5e7eb;
            transform: scale(0.9);
        }

        /* Processing Overlay - DIPERBAIKI (Hapus emoticon, ganti spinner) */
        .processing-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(4px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 30;
            color: white;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
        }

        .processing-overlay.active {
            opacity: 1;
            pointer-events: all;
        }

        /* Stats & Settings */
        .stat-card {
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 16px;
            padding: 1rem;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            margin-top: 0.25rem;
        }

        .stat-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
        }

        .settings-pill {
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 20px;
            padding: 1rem 1.5rem;
            margin-top: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        /* SCROLLABLE LIST CONTAINER - FIX UNTUK 5 BARIS */
        .list-container {
            height: 330px;
            /* Pas untuk ~5 item */
            overflow-y: auto;
            padding-right: 10px;
        }

        /* Kustomisasi Scrollbar agar rapi */
        .list-container::-webkit-scrollbar {
            width: 6px;
        }

        .list-container::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.02);
            border-radius: 4px;
        }

        .list-container::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.15);
            border-radius: 4px;
        }

        .list-container::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.25);
        }

        [data-bs-theme="dark"] .list-container::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.02);
        }

        [data-bs-theme="dark"] .list-container::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
        }

        .log-item {
            padding: 0.6rem 0.8rem;
            border-radius: 10px;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .log-success {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .log-warning {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .log-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .log-info {
            background: rgba(59, 130, 246, 0.1);
            color: #2563eb;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        /* Attendance List Styles */
        .attendance-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .attendance-item {
            display: flex;
            align-items: center;
            padding: 0.6rem 0;
            border-bottom: 1px solid var(--bs-border-color);
        }

        .attendance-item:last-child {
            border-bottom: none;
        }

        /* Memastikan sticky header berfungsi dengan baik */
        .sticky-top-header {
            position: sticky;
            top: 0;
            z-index: 5;
            background: var(--bs-body-bg);
        }
    </style>
@endpush

@section('content')
    <div class="row g-4">
        <div class="col-lg-7 col-xl-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="mb-1 fw-bold">{{ $class->course->course_name }}</h4>
                    <div class="d-flex align-items-center gap-2">
                        <span id="api-status" class="badge rounded-pill bg-secondary px-3 py-2">
                            <i class="fas fa-circle-notch fa-spin me-1"></i> Checking API...
                        </span>
                        <span
                            class="badge rounded-pill bg-primary px-3 py-2 bg-opacity-10 text-primary border border-primary">
                            Conf: {{ round($confidenceThreshold * 100) }}%
                        </span>
                    </div>
                </div>
                <div id="sessionControlContainer" class="text-end">
                    @if ($activeSession)
                        <button class="btn btn-danger shadow-sm" onclick="closeSession()">
                            <i class="fas fa-door-closed me-1"></i> Tutup Sesi
                        </button>
                        <span class="d-block text-muted small mt-1">Sesi Aktif</span>
                    @else
                        <button class="btn btn-success shadow-sm" onclick="openSession()">
                            <i class="fas fa-door-open me-1"></i> Buka Sesi
                        </button>
                        <span class="d-block text-muted small mt-1">Belum Dibuka</span>
                    @endif
                </div>
            </div>

            <div class="scanner-container mb-4" id="scannerWrapper">
                @if (!$activeSession)
                    <div class="position-absolute w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-dark bg-opacity-75"
                        style="z-index:40;">
                        <h4 class="text-white mb-3"><i class="fas fa-lock me-2"></i>Sesi Belum Dibuka</h4>
                        <button class="btn btn-success btn-lg" onclick="openSession()">Buka Sesi Sekarang</button>
                    </div>
                @endif
                <video id="videoElement" autoplay playsinline muted></video>
                <canvas id="canvasElement" style="display: none;"></canvas>

                <div class="scanner-overlay"></div>
                <div class="scan-line"></div>

                <div class="processing-overlay" id="processingOverlay">
                    <div class="spinner-border text-light mb-3" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5 class="fw-medium m-0">Recognizing face...</h5>
                </div>

                <div class="camera-controls">
                    <button id="startBtn" class="btn-camera-action btn-play" title="Start Camera">
                        <i class="fas fa-play"></i>
                    </button>
                    <button id="captureBtn" class="btn-camera-action btn-snap" title="Manual Capture" disabled>
                        <div class="inner"></div>
                    </button>
                    <button id="stopBtn" class="btn-camera-action btn-stop" title="Stop Camera" disabled>
                        <i class="fas fa-stop"></i>
                    </button>
                </div>
            </div>

            <div class="settings-pill mb-4">
                <div class="row align-items-center">
                    <div class="col-sm-4 mb-3 mb-sm-0">
                        <div class="form-check form-switch fs-5">
                            <input class="form-check-input" type="checkbox" role="switch" id="autoCapture" checked>
                            <label class="form-check-label fs-6 ms-2 mt-1" for="autoCapture">Auto Scan</label>
                        </div>
                    </div>
                    <div class="col-sm-5 mb-3 mb-sm-0">
                        <div class="d-flex align-items-center gap-3">
                            <label for="captureInterval" class="form-label mb-0 text-nowrap"><i
                                    class="fas fa-stopwatch text-muted"></i> Timer:</label>
                            <input type="range" class="form-range" id="captureInterval" min="2" max="10"
                                value="3">
                            <span class="badge bg-secondary rounded-pill" style="min-width: 40px;"
                                id="intervalValue">3s</span>
                        </div>
                    </div>
                    <div class="col-sm-3 text-sm-end">
                        <div class="form-check form-switch d-inline-block fs-5">
                            <input class="form-check-input" type="checkbox" role="switch" id="showLogs" checked>
                            <label class="form-check-label fs-6 ms-2 mt-1" for="showLogs">Logs</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-label">Captures</div>
                        <div class="stat-value text-primary" id="totalCaptures">0</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-label">Detected</div>
                        <div class="stat-value text-info" id="totalFaces">0</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-label">Recognized</div>
                        <div class="stat-value text-success" id="totalRecognized">0</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-label">Attendance</div>
                        <div class="stat-value text-warning" id="totalAttendance">0</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5 col-xl-4 d-flex flex-column gap-3">

            <div class="card shadow-sm border-0 flex-grow-1" id="logsCard">
                <div
                    class="card-header bg-transparent border-bottom-0 d-flex justify-content-between align-items-center pt-3 pb-2">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-bolt text-warning me-2"></i>Live Activity</h6>
                    <button id="clearLogsBtn"
                        class="btn btn-sm btn-light rounded-pill px-3 py-1 text-muted text-decoration-none"
                        style="font-size: 0.75rem;">Clear</button>
                </div>
                <div class="card-body pt-0 px-2 pb-2">
                    <div class="list-container px-2" id="detectionLogs">
                        <div class="text-center text-muted py-5" id="emptyLogs">
                            <i class="fas fa-history py-2 fs-3 opacity-50"></i>
                            <p class="mb-0 small mt-2">Waiting for camera activity...</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 flex-grow-1 mb-4">
                <div
                    class="card-header bg-transparent border-bottom-0 d-flex justify-content-between align-items-center pt-3 pb-2">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-clipboard-check text-success me-2"></i>Present Today</h6>
                    <button class="btn btn-sm btn-light rounded-circle" onclick="scanner.loadAttendanceData()"
                        title="Refresh">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                <div class="card-body pt-0 px-2 pb-2">
                    <div class="list-container px-3" id="attendanceList">
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-list-check py-2 fs-3 opacity-50"></i>
                            <p class="mt-2 small">Menunggu data presensi...</p>
                        </div>
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
                this.confidenceThreshold = {{ $confidenceThreshold }};

                this.outputWidth = 640;
                this.outputHeight = 480;

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
                document.getElementById('startBtn').addEventListener('click', () => this.startCamera());
                document.getElementById('stopBtn').addEventListener('click', () => this.stopCamera());
                document.getElementById('captureBtn').addEventListener('click', () => this.captureFrame());
                document.getElementById('clearLogsBtn').addEventListener('click', () => this.clearLogs());

                document.getElementById('autoCapture').addEventListener('change', (e) => {
                    this.autoCapture = e.target.checked;
                    document.getElementById('scannerWrapper').classList.toggle('is-scanning', this
                        .autoCapture && this.stream);

                    if (this.autoCapture && this.stream) {
                        this.startAutoCapture();
                    } else {
                        this.stopAutoCapture();
                    }
                });

                document.getElementById('showLogs').addEventListener('change', (e) => {
                    document.getElementById('logsCard').style.display = e.target.checked ? 'flex' : 'none';
                });

                document.getElementById('captureInterval').addEventListener('input', (e) => {
                    this.captureIntervalSeconds = parseInt(e.target.value);
                    document.getElementById('intervalValue').textContent = this.captureIntervalSeconds + 's';

                    if (this.autoCaptureInterval) {
                        this.stopAutoCapture();
                        this.startAutoCapture();
                    }
                });
            }

            async checkApiWarmup() {
                try {
                    this.updateApiStatus('Checking API...', 'secondary', 'fa-circle-notch fa-spin');
                    const response = await fetch('{{ route('api.status') }}');
                    const result = await response.json();

                    if (result.status === 'connected') {
                        this.apiWarmedUp = true;
                        this.updateApiStatus('API Ready', 'success', 'fa-check-circle');
                    } else {
                        this.updateApiStatus('API Loading...', 'warning', 'fa-hourglass-half');
                        setTimeout(() => this.checkApiWarmup(), 5000);
                    }
                } catch (error) {
                    this.updateApiStatus('API Offline', 'danger', 'fa-exclamation-circle');
                    setTimeout(() => this.checkApiWarmup(), 10000);
                }
            }

            updateApiStatus(message, type, icon) {
                const statusElement = document.getElementById('api-status');
                statusElement.innerHTML = `<i class="fas ${icon} me-1"></i> ${message}`;
                statusElement.className =
                    `badge rounded-pill bg-${type} px-3 py-2 text-${type} bg-opacity-10 border border-${type}`;
            }

            async startCamera() {
                try {
                    const isMobile = window.innerWidth <= 768;
                    const constraints = {
                        video: {
                            facingMode: 'user',
                            width: isMobile ? {
                                ideal: 480
                            } : {
                                ideal: 640
                            },
                            height: isMobile ? {
                                ideal: 640
                            } : {
                                ideal: 480
                            }
                        }
                    };

                    this.stream = await navigator.mediaDevices.getUserMedia(constraints);
                    this.video.srcObject = this.stream;

                    this.video.onloadedmetadata = () => {
                        this.outputWidth = this.video.videoWidth;
                        this.outputHeight = this.video.videoHeight;
                        this.canvas.width = this.outputWidth;
                        this.canvas.height = this.outputHeight;
                    };

                    document.getElementById('startBtn').disabled = true;
                    document.getElementById('stopBtn').disabled = false;
                    document.getElementById('captureBtn').disabled = false;

                    if (this.autoCapture) {
                        document.getElementById('scannerWrapper').classList.add('is-scanning');
                        this.startAutoCapture();
                    }

                    this.addLog('Camera connected successfully', 'success');

                } catch (error) {
                    this.addLog('Camera blocked atau unavailable', 'danger');
                }
            }

            stopCamera() {
                if (this.stream) {
                    this.stream.getTracks().forEach(track => track.stop());
                    this.stream = null;
                }

                this.stopAutoCapture();
                document.getElementById('scannerWrapper').classList.remove('is-scanning');

                document.getElementById('startBtn').disabled = false;
                document.getElementById('stopBtn').disabled = true;
                document.getElementById('captureBtn').disabled = true;

                this.addLog('Camera stopped', 'info');
            }

            startAutoCapture() {
                if (this.autoCaptureInterval) clearInterval(this.autoCaptureInterval);
                this.autoCaptureInterval = setInterval(() => {
                    this.captureFrame();
                }, this.captureIntervalSeconds * 1000);
            }

            stopAutoCapture() {
                if (this.autoCaptureInterval) {
                    clearInterval(this.autoCaptureInterval);
                    this.autoCaptureInterval = null;
                }
            }

            captureFrame() {
                if (!this.stream || this.isProcessing) return;
                if (!this.apiWarmedUp) return;

                this.ctx.drawImage(this.video, 0, 0, this.outputWidth, this.outputHeight);
                const imageData = this.canvas.toDataURL('image/jpeg', 0.7);

                this.stats.totalCaptures++;
                this.updateStatistics();
                this.processFrame(imageData);
            }

            async processFrame(imageData) {
                if (this.isProcessing) return;
                this.isProcessing = true;

                document.getElementById('scannerWrapper').classList.remove('is-scanning');
                document.getElementById('processingOverlay').classList.add('active');

                try {
                    const flaskApiUrl =
                    '{{ env('FACE_RECOGNITION_API_URL', 'http://localhost:5000') }}/api/verify-face';

                    const flaskResponse = await fetch(flaskApiUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            image: imageData,
                            predict_threshold: this.confidenceThreshold,
                            min_confidence: this.confidenceThreshold,
                            min_gap: 0.03,
                            single_class_confidence: this.confidenceThreshold,
                            class_id: this.classId
                        })
                    });

                    const result = await flaskResponse.json();

                    if (result.success) {
                        this.stats.totalFaces += result.total_faces_detected || 0;
                        let verifiedStudents = [];

                        if (result.results && result.results.length > 0) {
                            verifiedStudents = result.results.filter(s => s.verified === true && (s.similarity ?? 0) >=
                                this.confidenceThreshold);
                        }

                        if (verifiedStudents.length > 0) {
                            const laravelPayload = verifiedStudents.map(s => ({
                                student_name: s.student_name,
                                confidence: s.similarity
                            }));

                            const activeSessionId = {{ $activeSession->id ?? 'null' }};

                            if (!activeSessionId) {
                                this.addLog('⚠️ Sesi absensi belum dibuka.', 'warning');
                            } else {
                                const laravelResponse = await fetch('{{ route('api.attendance.mark') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                            .getAttribute('content'),
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        session_id: activeSessionId,
                                        students: laravelPayload
                                    })
                                });

                                const laravelResult = await laravelResponse.json();

                                if (laravelResult.success) {
                                    this.stats.totalRecognized += verifiedStudents.length;
                                    this.updateStatistics();

                                    let newAttendanceCount = 0;

                                    laravelResult.results.forEach(studentRecord => {
                                        if (studentRecord.status === 'new_attendance') {
                                            this.addLog(
                                                `✅ Masuk: ${studentRecord.student_name} - ${(studentRecord.confidence * 100).toFixed(0)}%`,
                                                'success');
                                            this.stats.totalAttendance++;
                                            newAttendanceCount++;
                                        } else if (studentRecord.status === 'already_attended') {
                                            this.addLog(
                                                `⚠️ ${studentRecord.student_name} sudah absen di sesi ini`,
                                                'warning');
                                        } else if (studentRecord.status === 'not_enrolled') {
                                            this.addLog(
                                                `🚫 ${studentRecord.student_name} tidak terdaftar di kelas`,
                                                'danger');
                                        } else if (studentRecord.status === 'not_found') {
                                            this.addLog(`❓ ${studentRecord.student_name} tidak ditemukan`,
                                                'warning');
                                        }
                                    });

                                    if (newAttendanceCount > 0) {
                                        this.loadAttendanceData();
                                    }
                                }
                            }
                        } else if (result.total_faces_detected > 0) {
                            this.addLog(`👤 Wajah tidak memenuhi threshold (${result.total_faces_detected})`,
                            'warning');
                        }
                        this.updateApiStatus('API Ready', 'success', 'fa-check-circle');
                    }
                } catch (error) {
                    console.error('Processing error:', error);
                } finally {
                    this.isProcessing = false;
                    document.getElementById('processingOverlay').classList.remove('active');
                    if (this.autoCapture && this.stream) {
                        document.getElementById('scannerWrapper').classList.add('is-scanning');
                    }
                }
            }

            updateStatistics() {
                document.getElementById('totalCaptures').textContent = this.stats.totalCaptures;
                document.getElementById('totalFaces').textContent = this.stats.totalFaces;
                document.getElementById('totalRecognized').textContent = this.stats.totalRecognized;
                document.getElementById('totalAttendance').textContent = this.stats.totalAttendance;
            }

            addLog(message, type = 'info') {
                const logsContainer = document.getElementById('detectionLogs');
                const emptyState = document.getElementById('emptyLogs');
                const timestamp = new Date().toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });

                if (emptyState) emptyState.remove();

                const logElement = document.createElement('div');
                logElement.className = `log-item log-${type}`;
                logElement.innerHTML =
                    `<span class="fw-medium">${message}</span><small class="opacity-75 ms-2 text-nowrap">${timestamp}</small>`;

                logsContainer.insertBefore(logElement, logsContainer.firstChild);

                while (logsContainer.children.length > 30) {
                    logsContainer.removeChild(logsContainer.lastChild);
                }
            }

            clearLogs() {
                document.getElementById('detectionLogs').innerHTML = `
                    <div class="text-center text-muted py-5" id="emptyLogs">
                        <i class="fas fa-history py-2 fs-3 opacity-50"></i>
                        <p class="mb-0 small mt-2">Logs cleared. Waiting for activity...</p>
                    </div>`;
            }

            async loadAttendanceData() {
                try {
                    const response = await fetch(`{{ route('api.attendance.today', $class) }}`);
                    const result = await response.json();

                    if (result.success) {
                        this.displayAttendanceList(result.attendances);
                        this.stats.totalAttendance = result.total_present;
                        this.updateStatistics();
                    }
                } catch (error) {
                    console.error('Error loading attendance list:', error);
                }
            }

            displayAttendanceList(attendances) {
                const container = document.getElementById('attendanceList');

                if (!attendances || attendances.length === 0) {
                    container.innerHTML = `
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-users-slash py-2 fs-3 opacity-50"></i>
                            <p class="mb-0 small mt-2">Belum ada mahasiswa yang masuk KRS kelas ini.</p>
                        </div>`;
                    return;
                }

                // Bagian HTML Diperbarui agar menggunakan Sticky Header & Layout 1 Baris Rapih
                let presentHtml =
                    '<div class="sticky-top-header pt-2 pb-2 mb-2 border-bottom"><h6 class="text-success fw-bold m-0"><i class="fas fa-check-circle me-1"></i> Sudah Hadir</h6></div>';
                let absentHtml =
                    '<div class="sticky-top-header pt-3 pb-2 mb-2 border-bottom"><h6 class="text-danger fw-bold m-0"><i class="fas fa-times-circle me-1"></i> Belum Hadir</h6></div>';

                let presentCount = 0;
                let absentCount = 0;

                attendances.forEach((attendance) => {
                    const initial = attendance.student_name.substring(0, 1).toUpperCase();

                    if (attendance.status === 'present') {
                        presentCount++;
                        const confidence = attendance.similarity_score ? (attendance.similarity_score * 100)
                            .toFixed(0) : '0';
                        const timeStr = attendance.check_in ? attendance.check_in.substring(0, 5) : '--:--';

                        presentHtml +=
                            `<div class="attendance-item"><div class="attendance-avatar me-3 shadow-sm">${initial}</div><div class="flex-grow-1 overflow-hidden"><div class="fw-semibold text-truncate" style="font-size: 0.9rem;">${attendance.student_name}</div><div class="d-flex align-items-center gap-2 mt-1"><span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25" style="font-size: 0.7rem;">${confidence}% Match</span><small class="text-muted"><i class="far fa-clock me-1"></i>${timeStr}</small></div></div></div>`;
                    } else {
                        absentCount++;
                        absentHtml +=
                            `<div class="attendance-item opacity-75"><div class="attendance-avatar me-3 shadow-sm" style="background: #f3f4f6 !important; color: #6b7280; border: 1px solid #d1d5db;">${initial}</div><div class="flex-grow-1 overflow-hidden"><div class="fw-medium text-truncate text-muted" style="font-size: 0.9rem;">${attendance.student_name}</div><div class="d-flex align-items-center gap-2 mt-1"><span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25" style="font-size: 0.7rem;">Alpha</span></div></div></div>`;
                    }
                });

                if (presentCount === 0) presentHtml += '<p class="text-muted small py-2">Belum ada yang hadir.</p>';
                if (absentCount === 0) absentHtml +=
                    '<p class="text-muted small py-2 mt-1">Semua mahasiswa sudah hadir.</p>';

                container.innerHTML = presentHtml + absentHtml;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            window.scanner = new FaceRecognitionScanner();
            setInterval(() => {
                window.scanner.loadAttendanceData();
            }, 30000);

            window.addEventListener('beforeunload', function() {
                if (window.scanner) window.scanner.stopCamera();
            });
        });

        async function openSession() {
            if (!confirm('Buka sesi absensi untuk kelas ini?')) return;
            try {
                const response = await fetch('{{ route('api.attendance.session.open') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    },
                    body: JSON.stringify({
                        class_id: {{ $class->id }}
                    })
                });
                const result = await response.json();
                if (result.success) location.reload();
                else alert(result.message);
            } catch (error) {
                alert('Error opening session');
            }
        }

        async function closeSession() {
            if (!confirm('Tutup sesi ini? Mahasiswa yang belum absen otomatis menjadi Alpha.')) return;
            try {
                const response = await fetch('{{ route('api.attendance.session.close') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    },
                    body: JSON.stringify({
                        session_id: {{ $activeSession->id ?? 'null' }}
                    })
                });
                const result = await response.json();
                if (result.success) {
                    alert(result.message);
                    location.reload();
                } else alert(result.message);
            } catch (error) {
                alert('Error closing session');
            }
        }
    </script>
@endpush
