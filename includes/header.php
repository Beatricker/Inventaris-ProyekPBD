<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

check_login();

$userRole = strtolower(trim(auth_role()));
$username = auth_username();

$currentScript = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$isDashboard = (strpos($currentScript, 'dashboard.php') !== false);
$isBarang    = (strpos($currentScript, '/barang/') !== false);
$isSatuan    = (strpos($currentScript, '/satuan/') !== false);
$isVendor    = (strpos($currentScript, '/vendor/') !== false);
$isUser      = (strpos($currentScript, '/user/') !== false);
$isRole      = (strpos($currentScript, '/role/') !== false);
$isMargin    = (strpos($currentScript, '/margin/') !== false);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' - ' : '' ?>PutPutMart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/refract.css">
</head>

<body>

    <div class="app-layout" id="appLayout">
        <!-- Mobile Backdrop -->
        <div class="app-sidebar-backdrop" id="sidebarBackdrop"></div>

        <!-- Fixed Left Sidebar (Annakoot Enterprise Layout) -->
        <aside class="app-sidebar" id="appSidebar">
            <div class="app-sidebar__brand">
                <a class="wordmark" href="<?= BASE_URL ?>/dashboard.php">
                    <span class="wordmark__ring"></span>
                    <span class="app-sidebar__brand-name">PutPutMart</span>
                </a>
            </div>

            <div class="app-sidebar__menu">
                <!-- 1. Menu Utama -->
                <div class="sidebar-group-title">Utama</div>
                <a href="<?= BASE_URL ?>/dashboard.php" class="sidebar-nav-link <?= $isDashboard ? 'active' : '' ?>">
                    <i class="bi bi-grid-1x2"></i>
                    <span class="nav-text">Dashboard</span>
                </a>

                <!-- 2. Katalog & Logistik -->
                <div class="sidebar-group-title">Katalog &amp; Inventaris</div>
                <a href="<?= BASE_URL ?>/barang/index.php" class="sidebar-nav-link <?= $isBarang ? 'active' : '' ?>">
                    <i class="bi bi-box-seam"></i>
                    <span class="nav-text">Data Barang</span>
                </a>
                <?php if (in_array($userRole, ['admin', 'gudang'])): ?>
                    <a href="<?= BASE_URL ?>/satuan/index.php" class="sidebar-nav-link <?= $isSatuan ? 'active' : '' ?>">
                        <i class="bi bi-rulers"></i>
                        <span class="nav-text">Satuan Ukur</span>
                    </a>
                    <a href="<?= BASE_URL ?>/vendor/index.php" class="sidebar-nav-link <?= $isVendor ? 'active' : '' ?>">
                        <i class="bi bi-truck"></i>
                        <span class="nav-text">Mitra Vendor</span>
                    </a>
                <?php endif; ?>
                <?php if (in_array($userRole, ['admin', 'kasir'])): ?>
                    <a href="<?= BASE_URL ?>/margin/index.php" class="sidebar-nav-link <?= $isMargin ? 'active' : '' ?>">
                        <i class="bi bi-graph-up-arrow"></i>
                        <span class="nav-text">Margin Penjualan</span>
                    </a>
                <?php endif; ?>

                <!-- 3. Administrasi (Khusus Admin) -->
                <?php if ($userRole === 'admin'): ?>
                    <div class="sidebar-group-title">Administrasi Sistem</div>
                    <a href="<?= BASE_URL ?>/user/index.php" class="sidebar-nav-link <?= $isUser ? 'active' : '' ?>">
                        <i class="bi bi-people"></i>
                        <span class="nav-text">Akun Pengguna</span>
                    </a>
                    <a href="<?= BASE_URL ?>/role/index.php" class="sidebar-nav-link <?= $isRole ? 'active' : '' ?>">
                        <i class="bi bi-shield-check"></i>
                        <span class="nav-text">Role Akses</span>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Footer Sidebar Mini Profile -->
            <div class="app-sidebar__footer">
                <div class="d-flex align-items-center gap-2">
                    <span class="avatar-circle" style="width: 32px; height: 32px; font-size: 0.8rem;">
                        <?= strtoupper(substr($username, 0, 1)) ?>
                    </span>
                    <div class="app-sidebar__footer-text text-truncate">
                        <div class="fw-semibold text-dark" style="font-size: 0.84rem; line-height: 1.2;"><?= htmlspecialchars($username) ?></div>
                        <div class="text-muted" style="font-size: 0.74rem;"><?= ucfirst($userRole) ?></div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="app-main">
            <!-- Slim Sticky Topbar (Annakoot Layout) -->
            <header class="app-topbar">
                <div class="app-topbar__left">
                    <button type="button" class="btn-sidebar-toggle" id="sidebarToggle" title="Perkecil/Perbesar Menu">
                        <i class="bi bi-text-indent-left"></i>
                    </button>
                    <nav class="app-breadcrumbs d-none d-sm-flex" aria-label="breadcrumb">
                        <a href="<?= BASE_URL ?>/dashboard.php"><i class="bi bi-house"></i></a>
                        <span class="separator">/</span>
                        <span class="current"><?= htmlspecialchars($page_title ?? 'Dashboard') ?></span>
                    </nav>
                </div>

                <div class="app-topbar__right">
                    <div class="user-avatar-pill">
                        <span class="avatar-circle">
                            <?= strtoupper(substr($username, 0, 1)) ?>
                        </span>
                        <div class="user-info-text d-none d-md-block">
                            <div class="user-info-name"><?= htmlspecialchars($username) ?></div>
                        </div>
                        <span class="badge badge-refract-blue ms-1"><?= ucfirst($userRole) ?></span>
                    </div>
                    <a href="<?= BASE_URL ?>/auth/logout.php" class="btn btn-clear btn-sm text-danger p-2" title="Keluar dari sistem" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                        <i class="bi bi-box-arrow-right fs-5"></i>
                    </a>
                </div>
            </header>

            <!-- Main Content Canvas -->
            <main class="app-content">
