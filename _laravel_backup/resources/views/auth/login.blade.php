<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PutPutMart</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            padding: 20px;
        }
        .login-card {
            max-width: 420px;
            width: 100%;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        .login-header {
            background: #1e293b;
            color: #ffffff;
            padding: 28px 24px;
            text-align: center;
            border-bottom: 3px solid #3b82f6;
        }
        .login-body {
            padding: 28px 24px;
        }
        .form-control {
            border-radius: 8px;
            padding: 10px 14px;
        }
        .demo-account-badge {
            cursor: pointer;
            transition: all 0.2s;
        }
        .demo-account-badge:hover {
            opacity: 0.85;
            transform: scale(0.98);
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <div class="d-inline-flex p-3 bg-primary bg-opacity-25 text-primary rounded-circle mb-2">
                <i class="bi bi-shop fs-2"></i>
            </div>
            <h4 class="fw-bold mb-1">PutPutMart</h4>
            <p class="mb-0 text-white-50 small">Login Sistem Inventaris & Master Data</p>
        </div>

        <div class="login-body">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 small" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 small" role="alert">
                    <i class="bi bi-check-circle-fill"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('login.process') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="username" class="form-label fw-semibold small text-secondary">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-person"></i></span>
                        <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" placeholder="Masukkan username" required autofocus>
                    </div>
                    @error('username')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold small text-secondary">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-key"></i></span>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Masukkan password" required>
                    </div>
                    @error('password')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm mb-3">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                </button>
            </form>

            <!-- Akun Pengujian Cepat -->
            <div class="p-3 bg-light rounded-3 border small">
                <div class="fw-bold text-secondary mb-2"><i class="bi bi-info-circle"></i> Akun Pengujian (Klik untuk isi cepat):</div>
                <div class="d-flex flex-column gap-1">
                    <span class="badge bg-primary text-start demo-account-badge py-2 px-3" onclick="setLogin('admin_pusat', 'admin123')">
                        <i class="bi bi-shield-lock me-1"></i> Admin: admin_pusat / admin123
                    </span>
                    <span class="badge bg-success text-start demo-account-badge py-2 px-3" onclick="setLogin('kasir_satu', 'kasir123')">
                        <i class="bi bi-cart me-1"></i> Kasir: kasir_satu / kasir123
                    </span>
                    <span class="badge bg-secondary text-start demo-account-badge py-2 px-3" onclick="setLogin('gudang_utama', 'gudang123')">
                        <i class="bi bi-box me-1"></i> Gudang: gudang_utama / gudang123
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function setLogin(u, p) {
            document.getElementById('username').value = u;
            document.getElementById('password').value = p;
        }
    </script>
</body>
</html>
