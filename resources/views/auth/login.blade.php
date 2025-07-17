<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Presensi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        .logo-section {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 15px 15px 0 0;
            color: white;
        }
        @media (min-width: 768px) {
            .logo-section {
                border-radius: 15px 0 0 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid vh-100 d-flex align-items-center justify-content-center p-3">
        <div class="login-card col-12 col-md-8 col-lg-6 col-xl-5">
            <div class="row g-0 h-100">
                <!-- Logo Section -->
                <div class="col-md-5 logo-section d-flex flex-column justify-content-center align-items-center p-4">
                    <i class="fas fa-user-check fa-4x mb-3"></i>
                    <h4 class="fw-bold text-center">Sistem Presensi</h4>
                    <p class="text-center opacity-75 mb-0">Multi-face Recognition</p>
                </div>
                
                <!-- Login Form -->
                <div class="col-md-7 p-4">
                    <div class="d-flex flex-column h-100 justify-content-center">
                        <div class="mb-4">
                            <h3 class="fw-bold text-dark mb-2">Selamat Datang</h3>
                            <p class="text-muted">Silakan login untuk melanjutkan</p>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                @foreach ($errors->all() as $error)
                                    {{ $error }}
                                @endforeach
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="email" class="form-label text-dark fw-semibold">Email</label>
                                <input type="email" 
                                       class="form-control form-control-lg @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       placeholder="masukkan email"
                                       required 
                                       autofocus>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label text-dark fw-semibold">Password</label>
                                <input type="password" 
                                       class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       placeholder="masukkan password"
                                       required>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label text-muted" for="remember">
                                    Ingat saya
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold">
                                Login
                            </button>
                        </form>

                        <div class="mt-4 text-center">
                            <small class="text-muted">
                                © 2025 Sistem Presensi - Face Recognition
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js"></script>
</body>
</html>