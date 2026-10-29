<?php
/**
 * includes/header.php
 * Bagian atas halaman admin/penjual (Head, Topbar, dan Flash Messages).
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Pastikan user sudah login
check_login();

$userRole = strtolower(trim(auth_role()));
$username = auth_username();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' - ' : '' ?>PutPutMart</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: #f8fafc;
        }
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: #1e293b;
            color: #e2e8f0;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }
        .sidebar .brand {
            padding: 20px;
            font-size: 1.25rem;
            font-weight: 700;
            color: #fff;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar .nav-link {
            color: #94a3b8;
            padding: 12px 20px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 3px solid transparent;
            transition: all 0.2s;
            text-decoration: none;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.08);
            border-left-color: #3b82f6;
        }
        .sidebar .nav-header {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            padding: 16px 20px 8px;
            font-weight: 700;
        }
        .main-wrapper {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .topbar {
            height: 64px;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .content-area {
            padding: 28px;
            flex: 1;
        }
        .card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600;
            padding: 14px 20px;
        }
        .footer {
            background: #fff;
            border-top: 1px solid #e2e8f0;
            padding: 16px 28px;
            color: #64748b;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>

    <!-- Sidebar Navigasi -->
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <!-- Main Content Wrapper -->
    <div class="main-wrapper">
        <!-- Topbar -->
        <header class="topbar">
            <div>
                <h5 class="mb-0 fw-bold"><?= isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard' ?></h5>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-weight: 600;">
                        <?= strtoupper(substr($username, 0, 1)) ?>
                    </div>
                    <div>
                        <div class="fw-semibold lh-1"><?= htmlspecialchars($username) ?></div>
                        <small>
                            <?php if ($userRole === 'admin'): ?>
                                <span class="badge bg-danger">Super Admin</span>
                            <?php elseif ($userRole === 'kasir'): ?>
                                <span class="badge bg-success">Petugas Kasir</span>
                            <?php elseif ($userRole === 'gudang'): ?>
                                <span class="badge bg-warning text-dark">Petugas Gudang</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?= htmlspecialchars(auth_role()) ?></span>
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
                <div class="vr"></div>
                <a href="<?= BASE_URL ?>/auth/logout.php" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="content-area">
            <!-- Flash Message Alert -->
            <?php if ($msg = get_flash('success')): ?>
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-check-circle-fill"></i>
                    <div><?= htmlspecialchars($msg) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($msg = get_flash('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div><?= htmlspecialchars($msg) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
