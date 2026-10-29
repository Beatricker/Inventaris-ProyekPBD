<?php
/**
 * includes/sidebar.php
 * Sidebar navigasi sistem PutPutMart berbasis Role.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

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
        <i class="bi bi-shop text-primary fs-4"></i>
        <span>PutPutMart</span>
    </div>

    <nav class="nav flex-column py-2">
        <a class="nav-link <?= $isDashboard ? 'active' : '' ?>" href="<?= BASE_URL ?>/dashboard.php">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <!-- Menu Master Logistik / Inventaris: Gudang & Admin -->
        <?php if (in_array($userRole, ['admin', 'gudang'])): ?>
            <div class="nav-header">Logistik & Barang</div>
            <a class="nav-link <?= $isSatuan ? 'active' : '' ?>" href="<?= BASE_URL ?>/satuan/index.php">
                <i class="bi bi-rulers"></i> 1. Master Satuan
            </a>
            <a class="nav-link <?= $isBarang ? 'active' : '' ?>" href="<?= BASE_URL ?>/barang/index.php">
                <i class="bi bi-box-seam"></i> 2. Master Barang
            </a>
            <a class="nav-link <?= $isVendor ? 'active' : '' ?>" href="<?= BASE_URL ?>/vendor/index.php">
                <i class="bi bi-truck"></i> 3. Master Vendor
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
                <i class="bi bi-people"></i> 4. Master User
            </a>
            <a class="nav-link <?= $isRole ? 'active' : '' ?>" href="<?= BASE_URL ?>/role/index.php">
                <i class="bi bi-shield-lock"></i> 5. Master Role
            </a>
            <a class="nav-link <?= $isMargin ? 'active' : '' ?>" href="<?= BASE_URL ?>/margin/index.php">
                <i class="bi bi-graph-up-arrow"></i> 6. Margin Penjualan
            </a>
        <?php elseif ($userRole === 'kasir'): ?>
            <!-- Kasir bisa melihat info margin aktif -->
            <a class="nav-link <?= $isMargin ? 'active' : '' ?>" href="<?= BASE_URL ?>/margin/index.php">
                <i class="bi bi-graph-up-arrow"></i> Info Margin Penjualan
            </a>
        <?php endif; ?>
    </nav>
</aside>
