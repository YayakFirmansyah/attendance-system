<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Presensi')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            border-radius: 8px;
            margin: 2px 0;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
            transform: translateX(5px);
        }
        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
            margin-left: 250px;
        }
        .user-avatar {
            width: 35px;
            height: 35px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .nav-section {
            margin: 1rem 0;
            padding: 0.5rem 0;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        .nav-section:first-child {
            border-top: none;
        }
        .nav-section-title {
            color: rgba(255,255,255,0.6);
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 0.5rem;
            padding: 0 1rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile toggle button -->
    <button class="btn btn-primary d-md-none position-fixed" 
            style="top: 1rem; left: 1rem; z-index: 1001;" 
            onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <nav class="sidebar p-3" id="sidebar">
        <div class="text-center mb-4">
            <i class="fas fa-user-check fa-3x text-white mb-2"></i>
            <h5 class="text-white">Sistem Presensi</h5>
            <small class="text-white-50">Face Recognition</small>
        </div>
        
        <!-- User Info -->
        <div class="d-flex align-items-center mb-4 p-3 bg-white bg-opacity-10 rounded">
            <div class="user-avatar me-3">
                <i class="fas fa-user text-white"></i>
            </div>
            <div>
                <div class="text-white small fw-bold">{{ auth()->user()->name }}</div>
                <div class="text-white-50 small">{{ ucfirst(auth()->user()->role) }}</div>
            </div>
        </div>

        <!-- Navigation -->
        <ul class="nav flex-column">
            <!-- Main Navigation -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" 
                   href="{{ route('dashboard') }}">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </a>
            </li>
            
            <!-- Admin Only Section -->
            @if(auth()->user()->isAdmin())
                <div class="nav-section">
                    <div class="nav-section-title">Admin</div>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" 
                           href="{{ route('users.index') }}">
                            <i class="fas fa-user-tie me-2"></i>
                            Kelola Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('students.*') ? 'active' : '' }}" 
                           href="{{ route('students.index') }}">
                            <i class="fas fa-users me-2"></i>
                            Kelola Mahasiswa
                        </a>
                    </li>
                </div>
            @endif
            
            <!-- Shared Section (Admin & Dosen) -->
            <div class="nav-section">
                <div class="nav-section-title">Presensi</div>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}" 
                       href="{{ route('attendance.index') }}">
                        <i class="fas fa-calendar-check me-2"></i>
                        Data Presensi
                    </a>
                </li>
            </div>
            
            <!-- Account Section -->
            <div class="nav-section">
                <div class="nav-section-title">Akun</div>
                <li class="nav-item">
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start">
                            <i class="fas fa-sign-out-alt me-2"></i>
                            Logout
                        </button>
                    </form>
                </li>
            </div>
        </ul>
    </nav>

    <!-- Main content -->
    <main class="main-content p-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Ada kesalahan:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // CSRF token setup for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Mobile sidebar toggle
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.querySelector('[onclick="toggleSidebar()"]');
            
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>