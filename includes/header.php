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

    <div class="main-wrapper">
        <!-- Floating Refract Navigation Bar -->
        <header class="refract-nav-wrapper">
            <div class="refract-nav__pill">
                <a class="wordmark" href="<?= BASE_URL ?>/dashboard.php">
                    <span class="wordmark__ring"></span>
                    <span>PutPutMart</span>
                </a>

                <nav class="refract-nav__links d-none d-md-flex">
                    <a href="<?= BASE_URL ?>/dashboard.php" class="refract-nav__link <?= $isDashboard ? 'active' : '' ?>">
                        Dashboard
                    </a>
                    <a href="<?= BASE_URL ?>/barang/index.php" class="refract-nav__link <?= $isBarang ? 'active' : '' ?>">
                        Barang
                    </a>
                    <?php if (in_array($userRole, ['admin', 'gudang'])): ?>
                        <a href="<?= BASE_URL ?>/satuan/index.php" class="refract-nav__link <?= $isSatuan ? 'active' : '' ?>">
                            Satuan
                        </a>
                        <a href="<?= BASE_URL ?>/vendor/index.php" class="refract-nav__link <?= $isVendor ? 'active' : '' ?>">
                            Vendor
                        </a>
                    <?php endif; ?>
                    <?php if ($userRole === 'admin'): ?>
                        <a href="<?= BASE_URL ?>/user/index.php" class="refract-nav__link <?= $isUser ? 'active' : '' ?>">
                            User
                        </a>
                        <a href="<?= BASE_URL ?>/role/index.php" class="refract-nav__link <?= $isRole ? 'active' : '' ?>">
                            Role
                        </a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/margin/index.php" class="refract-nav__link <?= $isMargin ? 'active' : '' ?>">
                        Margin
                    </a>
                </nav>

                <div class="refract-nav__user">
                    <span class="badge badge-refract-blue d-none d-sm-inline-block">
                        <?= htmlspecialchars($username) ?> &bull;
                        <?php if ($userRole === 'admin'): ?>
                            Admin
                        <?php elseif ($userRole === 'kasir'): ?>
                            Kasir
                        <?php elseif ($userRole === 'gudang'): ?>
                            Gudang
                        <?php else: ?>
                            <?= htmlspecialchars(auth_role()) ?>
                        <?php endif; ?>
                    </span>
                    <a href="<?= BASE_URL ?>/auth/logout.php" class="btn btn-clear btn-sm text-danger" title="Keluar dari sistem" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                        <i class="bi bi-box-arrow-right"></i>
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="refract-container">
