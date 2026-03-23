<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Presensi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-wrapper {
            width: 100%;
            max-width: 420px;
            padding: 2rem;
        }

        .login-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            padding: 2.5rem 2.25rem;
            border: 1px solid rgba(229, 231, 235, 0.5);
        }

        .brand-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #6d28d9, #5b21b6);
            color: white;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.55rem;
            margin: 0 auto 1.5rem;
            box-shadow: 0 10px 15px -3px rgba(109, 40, 217, 0.3);
        }

        h4.auth-title {
            font-weight: 700;
            color: #111827;
            text-align: center;
            margin-bottom: 0.5rem;
        }

        p.auth-subtitle {
            color: #6b7280;
            text-align: center;
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        .form-control,
        .form-control:focus {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 0.72rem 0.9rem;
            font-size: 0.9rem;
            color: #111827;
            box-shadow: none;
            transition: all 0.2s;
        }

        .form-control:focus {
            background-color: #ffffff;
            border-color: #6d28d9;
            box-shadow: 0 0 0 4px rgba(109, 40, 217, 0.12);
        }

        .form-label {
            font-weight: 500;
            color: #374151;
            font-size: 0.9rem;
            margin-bottom: 0.4rem;
        }

        .btn-primary {
            background: #6d28d9;
            border: none;
            border-radius: 12px;
            padding: 0.72rem 0.95rem;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s;
            box-shadow: 0 4px 6px -1px rgba(109, 40, 217, 0.25);
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background: #5b21b6;
            transform: translateY(-1px);
            box-shadow: 0 6px 8px -1px rgba(109, 40, 217, 0.35);
        }

        .form-check-input:checked {
            background-color: #6d28d9;
            border-color: #6d28d9;
        }

        .form-check-label {
            font-size: 0.9rem;
            color: #4b5563;
        }

        .alert {
            border-radius: 12px;
            font-size: 0.9rem;
            border: none;
        }

        .alert-danger {
            background-color: #fef2f2;
            color: #991b1b;
        }
    </style>
</head>

<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="brand-icon">
                <i class="fas fa-fingerprint"></i>
            </div>
            <h4 class="auth-title">Sistem Presensi</h4>
            <p class="auth-subtitle">Face Recognition Login</p>

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                        name="email" value="{{ old('email') }}" placeholder="name@example.com" required autofocus>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                        name="password" placeholder="••••••••" required>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">
                            Remember me
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Sign In
                </button>
            </form>

            <div class="mt-4 text-center">
                <small class="text-muted opacity-75">
                    &copy; {{ date('Y') }} Sistem Presensi.<br>Powered by Multi-face Recognition.
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
