<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Presensi')</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-color: #6d28d9;
            --primary-hover: #5b21b6;
            --primary-soft: #ede9fe;
            --primary-border: #c4b5fd;
            --sidebar-bg-light: #ffffff;
            --sidebar-bg-dark: #111827;
            --body-bg-light: #f3f4f6;
            --body-bg-dark: #0f172a;
            --text-light: #1f2937;
            --text-dark: #f8fafc;
            --card-bg-light: #ffffff;
            --card-bg-dark: #1e293b;
            --border-light: #e5e7eb;
            --border-dark: #334155;
            --transition-speed: 0.3s;

            --bs-primary: var(--primary-color);
            --bs-primary-rgb: 109, 40, 217;
            --bs-success: #7c3aed;
            --bs-success-rgb: 124, 58, 237;
            --bs-info: #8b5cf6;
            --bs-info-rgb: 139, 92, 246;
            --bs-warning: #6d28d9;
            --bs-warning-rgb: 109, 40, 217;
            --bs-danger: #5b21b6;
            --bs-danger-rgb: 91, 33, 182;
        }

        [data-bs-theme="dark"] {
            --bs-body-bg: var(--body-bg-dark);
            --bs-body-color: var(--text-dark);
            --bs-border-color: var(--border-dark);
            --bs-card-bg: var(--card-bg-dark);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bs-body-bg);
            color: var(--bs-body-color);
            font-size: 0.92rem;
            line-height: 1.45;
            transition: background-color var(--transition-speed), color var(--transition-speed);
            overflow-x: hidden;
            -webkit-tap-highlight-color: transparent;
        }

        /* Sidebar Glassmorphism & Modernization */
        .sidebar {
            min-height: 100vh;
            background: var(--sidebar-bg-light);
            border-right: 1px solid var(--border-light);
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            z-index: 1040;
            transition: transform var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1), background-color var(--transition-speed);
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.03);
        }

        [data-bs-theme="dark"] .sidebar {
            background: var(--sidebar-bg-dark);
            border-right: 1px solid var(--border-dark);
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.2);
        }

        .sidebar-brand {
            padding: 1.1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            font-size: 1.08rem;
            color: var(--primary-color);
            border-bottom: 1px solid var(--border-light);
        }

        [data-bs-theme="dark"] .sidebar-brand {
            border-bottom-color: var(--border-dark);
            color: #818cf8;
        }

        .sidebar-brand i {
            font-size: 1.25rem;
            background: linear-gradient(135deg, var(--primary-color), #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .user-profile {
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), #8b5cf6);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.95rem;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
        }

        .user-info {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.82rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-role {
            font-size: 0.68rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        [data-bs-theme="dark"] .user-role {
            color: #9ca3af;
        }

        .nav-section {
            padding: 1rem 1rem 0 1rem;
        }

        .nav-section-title {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #9ca3af;
            margin-bottom: 0.5rem;
            padding-left: 0.75rem;
        }

        .sidebar .nav-link {
            color: #4b5563;
            border-radius: 8px;
            margin-bottom: 0.25rem;
            padding: 0.58rem 0.8rem;
            font-weight: 500;
            font-size: 0.84rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        [data-bs-theme="dark"] .sidebar .nav-link {
            color: #e2e8f0;
        }

        .sidebar .nav-link i {
            font-size: 0.92rem;
            width: 20px;
            text-align: center;
            color: #9ca3af;
            transition: color 0.2s ease;
        }

        .sidebar .nav-link:hover {
            background: #f3f4f6;
            color: var(--primary-color);
        }

        [data-bs-theme="dark"] .sidebar .nav-link:hover {
            background: #1e293b;
            color: #818cf8;
        }

        .sidebar .nav-link:hover i {
            color: var(--primary-color);
        }

        [data-bs-theme="dark"] .sidebar .nav-link:hover i {
            color: #818cf8;
        }

        .sidebar .nav-link.active {
            background: rgba(79, 70, 229, 0.1);
            color: var(--primary-color);
            font-weight: 600;
        }

        [data-bs-theme="dark"] .sidebar .nav-link.active {
            background: rgba(129, 140, 248, 0.15);
            color: #818cf8;
        }

        .sidebar .nav-link.active i {
            color: var(--primary-color);
        }

        [data-bs-theme="dark"] .sidebar .nav-link.active i {
            color: #818cf8;
        }

        .sidebar-footer {
            margin-top: auto;
            padding: 1rem;
            border-top: 1px solid var(--border-light);
        }

        [data-bs-theme="dark"] .sidebar-footer {
            border-top-color: var(--border-dark);
        }

        /* Main Content Area */
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
        }

        .top-navbar {
            height: 56px;
            background: var(--sidebar-bg-light);
            border-bottom: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.1rem;
            position: sticky;
            top: 0;
            z-index: 1030;
            transition: background-color var(--transition-speed), border-color var(--transition-speed);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.9);
        }

        [data-bs-theme="dark"] .top-navbar {
            background: rgba(17, 24, 39, 0.9);
            border-bottom-color: var(--border-dark);
        }

        .content-wrapper {
            padding: 1rem;
            flex-grow: 1;
        }

        /* Buttons & Toggles */
        .btn-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            background: transparent;
            color: #6b7280;
            transition: all 0.2s;
        }

        [data-bs-theme="dark"] .btn-icon {
            color: #9ca3af;
        }

        .btn-icon:hover {
            background: #f3f4f6;
            color: var(--primary-color);
        }

        [data-bs-theme="dark"] .btn-icon:hover {
            background: #1e293b;
            color: #818cf8;
        }

        .theme-toggle-wrapper {
            background: #f3f4f6;
            border-radius: 20px;
            padding: 4px;
            display: flex;
            gap: 2px;
        }

        [data-bs-theme="dark"] .theme-toggle-wrapper {
            background: #1e293b;
        }

        .theme-btn {
            border: none;
            background: transparent;
            color: #9ca3af;
            border-radius: 16px;
            padding: 4px 12px;
            font-size: 0.85rem;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }

        .theme-btn.active {
            background: white;
            color: var(--primary-color);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        [data-bs-theme="dark"] .theme-btn.active {
            background: var(--sidebar-bg-dark);
            color: #818cf8;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        [data-bs-theme="dark"] ::-webkit-scrollbar-thumb {
            background: #475569;
        }

        [data-bs-theme="dark"] ::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }

        /* Card Modernization */
        .card {
            border: 1px solid var(--border-light);
            border-radius: 12px;
            box-shadow: 0 2px 5px rgba(15, 23, 42, 0.06);
            background: var(--card-bg-light);
            transition: all var(--transition-speed);
            overflow: hidden;
            margin-bottom: 1rem;
        }

        [data-bs-theme="dark"] .card {
            background: var(--card-bg-dark);
            border-color: var(--border-dark);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--border-light);
            padding: 0.85rem 1rem;
            font-weight: 600;
        }

        [data-bs-theme="dark"] .card-header {
            border-bottom-color: var(--border-dark);
        }

        .card-body {
            padding: 1rem;
        }

        /* Form Controls */
        .form-control,
        .form-select {
            border-radius: 8px;
            border-color: #d1d5db;
            padding: 0.5rem 0.8rem;
            font-size: 0.88rem;
            background-color: var(--sidebar-bg-light);
            color: var(--text-light);
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select {
            background-color: #0f172a;
            border-color: #334155;
            color: var(--text-dark);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.2);
            background-color: var(--sidebar-bg-light);
        }

        [data-bs-theme="dark"] .form-control:focus,
        [data-bs-theme="dark"] .form-select:focus {
            background-color: #0f172a;
            color: var(--text-dark);
            border-color: #818cf8;
            box-shadow: 0 0 0 0.25rem rgba(129, 140, 248, 0.2);
        }

        /* Buttons */
        .btn {
            border-radius: 8px;
            padding: 0.43rem 0.95rem;
            font-weight: 500;
            font-size: 0.86rem;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            transform: translateY(-1px);
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .text-success {
            color: #7c3aed !important;
        }

        .text-info {
            color: #8b5cf6 !important;
        }

        .text-warning {
            color: #6d28d9 !important;
        }

        .text-danger {
            color: #5b21b6 !important;
        }

        .border-success,
        .border-info,
        .border-warning,
        .border-danger {
            border-color: var(--primary-border) !important;
        }

        .bg-primary,
        .bg-success,
        .bg-info,
        .bg-warning,
        .bg-danger {
            color: #fff !important;
        }

        h1,
        .h1 {
            font-size: 1.45rem;
        }

        h2,
        .h2 {
            font-size: 1.2rem;
        }

        h3,
        .h3 {
            font-size: 1.05rem;
        }

        h4,
        .h4 {
            font-size: 0.98rem;
        }

        .table {
            font-size: 0.84rem;
        }

        .badge {
            font-size: 0.72rem;
            font-weight: 600;
        }

        /* Mobile Adjustments */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1035;
            opacity: 0;
            transition: opacity var(--transition-speed);
        }

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .sidebar-overlay.show {
                display: block;
                opacity: 1;
            }

            .content-wrapper {
                padding: 1rem;
            }
        }
    </style>
</head>

<body>

    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-fingerprint"></i>
            <span>FaceAttend</span>
        </div>

        <!-- User Info -->
        <div class="user-profile">
            <div class="user-avatar">
                {{ substr(auth()->user()->name ?? 'User', 0, 1) }}
            </div>
            <div class="user-info">
                <span class="user-name">{{ auth()->user()->name ?? 'User Name' }}</span>
                <span class="user-role">{{ ucfirst(auth()->user()->role ?? 'Admin') }}</span>
            </div>
        </div>

        <!-- Navigation -->
        <div class="grow overflow-y-auto">
            <ul class="nav flex-column mb-3" style="padding: 0 0.75rem;">
                <!-- Main Navigation -->
                <li class="nav-item mt-2">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                        href="{{ route('dashboard') }}">
                        <i class="fas fa-chart-pie"></i>
                        Dashboard
                    </a>
                </li>

                <!-- Admin Only Section -->
                @if(auth()->check() && auth()->user()->isAdmin())
                <div class="nav-section mt-3">
                    <div class="nav-section-title">Administration</div>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                            href="{{ route('users.index') }}">
                            <i class="fas fa-user-shield"></i>
                            Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('courses.*') ? 'active' : '' }}"
                            href="{{ route('courses.index') }}">
                            <i class="fas fa-book-open"></i>
                            Courses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('rooms.*') ? 'active' : '' }}"
                            href="{{ route('rooms.index') }}">
                            <i class="fas fa-door-open"></i>
                            Rooms
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('classes.*') ? 'active' : '' }}"
                            href="{{ route('classes.index') }}">
                            <i class="fas fa-calendar-alt"></i>
                            Schedules
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('cohorts.*') ? 'active' : '' }}"
                            href="{{ route('cohorts.index') }}">
                            <i class="fas fa-layer-group"></i>
                            Cohorts
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('students.*') ? 'active' : '' }}"
                            href="{{ route('students.index') }}">
                            <i class="fas fa-users"></i>
                            Students
                        </a>
                    </li>
                </div>
                @endif
            </ul>
        </div>

        <!-- Account Section -->
        <div class="sidebar-footer">
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start text-danger d-flex align-items-center gap-3" style="padding: 0.75rem 1rem; border-radius: 10px; transition: background 0.2s;">
                    <i class="fas fa-sign-out-alt" style="width: 20px; text-align: center;"></i>
                    <span class="fw-medium">Logout</span>
                </button>
            </form>
        </div>
    </nav>

    <!-- Main content container -->
    <div class="main-content">
        <!-- Top Navbar -->
        <header class="top-navbar">
            <div class="d-flex align-items-center">
                <button class="btn-icon d-lg-none me-3" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="fw-medium d-none d-sm-block text-muted">@yield('title', 'Overview')</div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <!-- Theme Toggle -->
                <div class="theme-toggle-wrapper">
                    <button class="theme-btn active" id="btn-light" onclick="setTheme('light')" title="Light Mode">
                        <i class="fas fa-sun"></i>
                    </button>
                    <button class="theme-btn" id="btn-dark" onclick="setTheme('dark')" title="Dark Mode">
                        <i class="fas fa-moon"></i>
                    </button>
                </div>
            </div>
        </header>

        <!-- Dynamic Content -->
        <main class="content-wrapper">
            <!-- Global Alerts -->
            <div id="alerts-container">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" style="border-radius: 12px;" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" style="border-radius: 12px;" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" style="border-radius: 12px;" role="alert">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Ada kesalahan:</strong>
                    </div>
                    <ul class="mb-0 mt-1 small">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif
            </div>

            <!-- Page Content -->
            @yield('content')

        </main>

        <!-- Footer -->
        <footer class="mt-auto py-3 px-4 text-center text-muted small" style="border-top: 1px solid var(--border-light);">
            FaceAttend System &copy; {{ date('Y') }}. AI Face Recognition.
        </footer>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // CSRF Setup
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Mobile Sidebar Toggle
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('show');
            document.body.style.overflow = document.getElementById('sidebar').classList.contains('show') ? 'hidden' : '';
        }

        // Theme Management
        function initTheme() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            setTheme(savedTheme, false);
        }

        function setTheme(theme, save = true) {
            document.documentElement.setAttribute('data-bs-theme', theme);

            // Update Toggle buttons
            document.getElementById('btn-light').classList.toggle('active', theme === 'light');
            document.getElementById('btn-dark').classList.toggle('active', theme === 'dark');

            // Allow components to adapt if needed
            if (typeof window.onThemeChange === 'function') {
                window.onThemeChange(theme);
            }

            if (save) {
                localStorage.setItem('theme', theme);
            }
        }

        // Auto collapse sidebar on larger screens if screen resizes
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 992) {
                document.getElementById('sidebar').classList.remove('show');
                document.getElementById('sidebarOverlay').classList.remove('show');
                document.body.style.overflow = '';
            }
        });

        // Initialize things
        document.addEventListener('DOMContentLoaded', () => {
            initTheme();
        });
    </script>

    @stack('scripts')
</body>

</html>