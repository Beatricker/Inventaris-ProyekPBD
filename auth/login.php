<?php

/**
 * auth/login.php
 * Halaman login dan proses autentikasi menggunakan PDO & SHA2(password, 256).
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Jika sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$error = get_flash('error');
$oldUsername = '';

// Proses form login saat tombol submit ditekan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $oldUsername = $username;

    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi!';
    } else {
        // Query autentikasi murni dengan Prepared Statement & SHA2(?, 256)
        $stmt = $pdo->prepare(
            "SELECT u.iduser, u.username, u.idrole, r.nama_role
             FROM user u
             JOIN role r ON u.idrole = r.idrole
             WHERE u.username = ? AND u.password = SHA2(?, 256)
             LIMIT 1"
        );
        $stmt->execute([$username, $password]);
        $user = $stmt->fetch();

        if ($user) {
            // Simpan informasi login ke SESSION
            $_SESSION['user'] = $user;
            $_SESSION['iduser'] = $user->iduser;
            $_SESSION['username'] = $user->username;
            $_SESSION['role'] = $user->nama_role;
            $_SESSION['idrole'] = $user->idrole;

            header('Location: ' . BASE_URL . '/dashboard.php');
            exit;
        } else {
            $error = 'Username atau password salah! Silakan coba lagi.';
        }
    }
}
?>
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
    <!-- Hallmark Refract Theme -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/refract.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background-color: var(--color-paper);
            background-image: 
                radial-gradient(circle at 85% 15%, rgba(186, 230, 253, 0.6) 0%, transparent 45%),
                radial-gradient(circle at 15% 85%, rgba(186, 230, 253, 0.45) 0%, transparent 40%);
        }

        .login-card {
            max-width: 440px;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--color-rule);
            border-radius: 24px;
            box-shadow: 0 24px 50px -20px rgba(0, 109, 168, 0.18), 0 4px 12px -2px rgba(12, 26, 44, 0.05);
            overflow: hidden;
        }

        .login-header {
            padding: 36px 32px 24px;
            text-align: center;
            background: transparent;
        }

        .login-body {
            padding: 0 32px 36px;
        }

        .demo-chip {
            padding: 6px 14px;
            border-radius: var(--radius-pill);
            background: #f8fafc;
            border: 1px solid var(--color-rule);
            font-size: 0.775rem;
            color: var(--color-muted);
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .demo-chip:hover {
            background: var(--color-accent-soft);
            color: var(--color-accent);
            border-color: rgba(0, 109, 168, 0.3);
            transform: translateY(-1px);
        }
    </style>
</head>

<body>

    <div class="login-card">
        <div class="login-header">
            <div class="d-inline-flex align-items-center justify-content-center mb-3">
                <span class="wordmark__ring" style="width: 28px; height: 28px; border-width: 5px;"></span>
            </div>
            <h3 class="fw-bold mb-1" style="font-size: 1.75rem;">PutPutMart</h3>
            <p class="mb-0 text-muted" style="font-size: 0.92rem;">Sistem Informasi Kasir & Inventaris</p>
        </div>

        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div><?= htmlspecialchars($error) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" name="username" id="username" class="form-control" value="<?= htmlspecialchars($oldUsername) ?>" placeholder="Masukkan username" required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-key"></i></span>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 mb-3 fw-semibold">
                    <i class="bi bi-box-arrow-in-right"></i> Masuk ke Sistem
                </button>
            </form>

            <div class="pt-3 border-top text-center" style="border-color: var(--color-rule) !important;">
                <div class="text-muted small mb-2 fw-semibold">Pilih Akun Cepat (Demo):</div>
                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <button type="button" class="demo-chip" onclick="setLogin('admin_pusat', 'admin123')">
                        <i class="bi bi-shield-check text-danger"></i> Admin
                    </button>
                    <button type="button" class="demo-chip" onclick="setLogin('gudang_utama', 'gudang123')">
                        <i class="bi bi-box-seam text-warning"></i> Gudang
                    </button>
                    <button type="button" class="demo-chip" onclick="setLogin('kasir_satu', 'kasir123')">
                        <i class="bi bi-cart-check text-success"></i> Kasir
                    </button>
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
