<?php

/**
 * includes/sidebar.php
 * Sidebar navigasi sistem PutPutMart berbasis Role.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/auth.php';

$userRole = strtolower(trim(auth_role()));
$currentScript = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$isDashboard = (strpos($currentScript, 'dashboard.php') !== false);
$isSatuan    = (strpos($currentScript, '/satuan/') !== false);
$isBarang    = (strpos($currentScript, '/barang/') !== false);
$isVendor    = (strpos($currentScript, '/vendor/') !== false);
$isUser      = (strpos($currentScript, '/user/') !== false);
$isRole      = (strpos($currentScript, '/role/') !== false);
$isMargin    = (strpos($currentScript, '/margin/') !== false);
?>
<aside class="sidebar">
    <div class="brand">
        <div class="brand-icon">
            <i class="bi bi-shop"></i>
        </div>
        <span>PutPutMart</span>
    </div>

    <nav class="nav flex-column py-2">
        <a class="nav-link <?= $isDashboard ? 'active' : '' ?>" href="<?= BASE_URL ?>/dashboard.php">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <!-- Menu Logistik & Barang -->
        <?php if ($userRole === 'admin'): ?>
            <div class="nav-header">Master Data</div>
            <a class="nav-link <?= $isSatuan ? 'active' : '' ?>" href="<?= BASE_URL ?>/satuan/index.php">
                <i class="bi bi-rulers"></i> Satuan
            </a>
            <a class="nav-link <?= $isBarang ? 'active' : '' ?>" href="<?= BASE_URL ?>/barang/index.php">
                <i class="bi bi-box-seam"></i> Barang
            </a>
            <a class="nav-link <?= $isVendor ? 'active' : '' ?>" href="<?= BASE_URL ?>/vendor/index.php">
                <i class="bi bi-truck"></i> Vendor
            </a>
            <a class="nav-link <?= $isPengadaan ? 'active' : '' ?>" href="<?= BASE_URL ?>/pengadaan/index.php">
                <i class="bi bi-cart-plus"></i> Pengadaan Barang
            </a>
        <?php elseif ($userRole === 'gudang'): ?>
            <div class="nav-header">Logistik &amp; Gudang</div>
            <a class="nav-link <?= $isBarang ? 'active' : '' ?>" href="<?= BASE_URL ?>/barang/index.php">
                <i class="bi bi-box-seam"></i> Data Barang (Lihat)
            </a>
        <?php elseif ($userRole === 'kasir'): ?>
            <!-- Kasir hanya diizinkan melihat katalog barang -->
            <div class="nav-header">Katalog Produk</div>
            <a class="nav-link <?= $isBarang ? 'active' : '' ?>" href="<?= BASE_URL ?>/barang/index.php">
                <i class="bi bi-box-seam"></i> Katalog Harga Barang
            </a>
        <?php endif; ?>

        <!-- Menu Manajemen Pengguna & Finansial: Super Admin -->
        <?php if ($userRole === 'admin'): ?>
            <div class="nav-header">Pengaturan Super Admin</div>
            <a class="nav-link <?= $isUser ? 'active' : '' ?>" href="<?= BASE_URL ?>/user/index.php">
                <i class="bi bi-people"></i> User
            </a>
            <a class="nav-link <?= $isRole ? 'active' : '' ?>" href="<?= BASE_URL ?>/role/index.php">
                <i class="bi bi-shield-lock"></i> Role
            </a>
            <a class="nav-link <?= $isMargin ? 'active' : '' ?>" href="<?= BASE_URL ?>/margin/index.php">
                <i class="bi bi-graph-up-arrow"></i> Margin Penjualan
            </a>
        <?php elseif ($userRole === 'kasir'): ?>
            <!-- Kasir bisa melihat info margin aktif -->
            <a class="nav-link <?= $isMargin ? 'active' : '' ?>" href="<?= BASE_URL ?>/margin/index.php">
                <i class="bi bi-graph-up-arrow"></i> Info Margin Penjualan
            </a>
        <?php endif; ?>
    </nav>
</aside>
