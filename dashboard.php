<?php

/**
 * dashboard.php
 * Halaman Dashboard Utama PutPutMart berbasis Role (Refract Hallmark Architecture).
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

$page_title = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

$userRole = strtolower(trim(auth_role()));
$username = auth_username();

// Ambil data statistik tabel master
$totalSatuan = $pdo->query("SELECT COUNT(*) AS total FROM satuan")->fetch()->total ?? 0;
$totalBarang = $pdo->query("SELECT COUNT(*) AS total FROM barang")->fetch()->total ?? 0;
$totalVendor = $pdo->query("SELECT COUNT(*) AS total FROM vendor")->fetch()->total ?? 0;
$totalUser   = $pdo->query("SELECT COUNT(*) AS total FROM user")->fetch()->total ?? 0;
$totalRole   = $pdo->query("SELECT COUNT(*) AS total FROM role")->fetch()->total ?? 0;
$totalMargin = $pdo->query("SELECT COUNT(*) AS total FROM margin_penjualan")->fetch()->total ?? 0;

// Ambil data terbaru melalui Database VIEW
$recentBarang = $pdo->query("SELECT * FROM v_barang ORDER BY idbarang DESC LIMIT 6")->fetchAll();
$recentUser   = $pdo->query("SELECT * FROM v_user ORDER BY iduser DESC LIMIT 5")->fetchAll();
$recentMargin = $pdo->query("SELECT * FROM v_margin ORDER BY idmargin_penjualan DESC LIMIT 5")->fetchAll();
$recentVendor = $pdo->query("SELECT * FROM v_vendor ORDER BY idvendor DESC LIMIT 5")->fetchAll();
?>

<!-- 1. Editorial Header -->
<div class="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-3">
    <div>
        <div class="d-inline-flex align-items-center gap-2 mb-2">
        </div>
        <h1 class="display-font mb-1" style="font-size: 2.35rem; font-weight: 500;">
            <?php if ($userRole === 'admin'): ?>
                Katalog & Manajemen Sistem
            <?php elseif ($userRole === 'gudang'): ?>
                Logistik & Manajemen Inventaris
            <?php else: ?>
                Katalog Produk & Penjualan
            <?php endif; ?>
        </h1>
        <p class="text-muted mb-0" style="font-size: 1.05rem; max-width: 620px;">
            <?php if ($userRole === 'admin'): ?>
            <?php elseif ($userRole === 'gudang'): ?>
            <?php else: ?>
            <?php endif; ?>
        </p>
    </div>

    <div class="d-flex gap-2">
        <?php if (in_array($userRole, ['admin', 'gudang'])): ?>
            <a href="<?= BASE_URL ?>/barang/tambah.php" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Tambah Barang
            </a>
        <?php endif; ?>
        <?php if ($userRole === 'admin'): ?>
            <a href="<?= BASE_URL ?>/user/tambah.php" class="btn btn-clear">
                <i class="bi bi-person-plus"></i> Tambah User
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- 2. Refract Unified Metric Board -->
<div class="refract-strip mb-4">
    <?php if ($userRole === 'admin'): ?>
        <div class="row g-0">
            <div class="col-6 col-md-4 col-xl-2 refract-strip__cell">
                <div class="refract-strip__label">Barang Terdaftar</div>
                <div class="refract-strip__num tabular"><?= $totalBarang ?></div>
                <a href="<?= BASE_URL ?>/barang/index.php" class="refract-strip__sub">Katalog SKU &rarr;</a>
            </div>
            <div class="col-6 col-md-4 col-xl-2 refract-strip__cell">
                <div class="refract-strip__label">Satuan Ukur</div>
                <div class="refract-strip__num tabular"><?= $totalSatuan ?></div>
                <a href="<?= BASE_URL ?>/satuan/index.php" class="refract-strip__sub">Daftar Satuan &rarr;</a>
            </div>
            <div class="col-6 col-md-4 col-xl-2 refract-strip__cell">
                <div class="refract-strip__label">Mitra Vendor</div>
                <div class="refract-strip__num tabular"><?= $totalVendor ?></div>
                <a href="<?= BASE_URL ?>/vendor/index.php" class="refract-strip__sub">Pemasok &rarr;</a>
            </div>
            <div class="col-6 col-md-4 col-xl-2 refract-strip__cell">
                <div class="refract-strip__label">Margin Jual</div>
                <div class="refract-strip__num tabular"><?= $totalMargin ?></div>
                <a href="<?= BASE_URL ?>/margin/index.php" class="refract-strip__sub">Tier Margin &rarr;</a>
            </div>
            <div class="col-6 col-md-4 col-xl-2 refract-strip__cell">
                <div class="refract-strip__label">Pengguna</div>
                <div class="refract-strip__num tabular"><?= $totalUser ?></div>
                <a href="<?= BASE_URL ?>/user/index.php" class="refract-strip__sub">Akun Sistem &rarr;</a>
            </div>
            <div class="col-6 col-md-4 col-xl-2 refract-strip__cell">
                <div class="refract-strip__label">Role Akses</div>
                <div class="refract-strip__num tabular"><?= $totalRole ?></div>
                <a href="<?= BASE_URL ?>/role/index.php" class="refract-strip__sub">Tingkat Hak &rarr;</a>
            </div>
        </div>
    <?php elseif ($userRole === 'gudang'): ?>
        <div class="row g-0">
            <div class="col-md-4 refract-strip__cell">
                <div class="refract-strip__label">Koleksi Barang</div>
                <div class="refract-strip__num tabular"><?= $totalBarang ?></div>
                <a href="<?= BASE_URL ?>/barang/index.php" class="refract-strip__sub">Kelola Inventaris &rarr;</a>
            </div>
            <div class="col-md-4 refract-strip__cell">
                <div class="refract-strip__label">Satuan Tersedia</div>
                <div class="refract-strip__num tabular"><?= $totalSatuan ?></div>
                <a href="<?= BASE_URL ?>/satuan/index.php" class="refract-strip__sub">Kelola Satuan &rarr;</a>
            </div>
            <div class="col-md-4 refract-strip__cell">
                <div class="refract-strip__label">Mitra Pemasok</div>
                <div class="refract-strip__num tabular"><?= $totalVendor ?></div>
                <a href="<?= BASE_URL ?>/vendor/index.php" class="refract-strip__sub">Kelola Vendor &rarr;</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-0">
            <div class="col-md-6 refract-strip__cell">
                <div class="refract-strip__label">Produk Siap Jual</div>
                <div class="refract-strip__num tabular"><?= $totalBarang ?></div>
                <a href="<?= BASE_URL ?>/barang/index.php" class="refract-strip__sub">Buka Katalog &rarr;</a>
            </div>
            <div class="col-md-6 refract-strip__cell">
                <div class="refract-strip__label">Margin Keuntungan Aktif</div>
                <div class="refract-strip__num tabular"><?= $totalMargin ?></div>
                <a href="<?= BASE_URL ?>/margin/index.php" class="refract-strip__sub">Lihat Rincian &rarr;</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- 3. Main Workspace: Katalog Barang & Panel Referensi -->
<div class="row g-4">
    <!-- Kolom Utama: Katalog Barang -->
    <div class="<?= ($userRole === 'kasir') ? 'col-12' : 'col-lg-8' ?>">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-box-seam me-2" style="color: var(--color-accent);"></i>Katalog Barang Terkini
                    </h5>
                </div>
                <a href="<?= BASE_URL ?>/barang/index.php" class="btn btn-clear btn-sm">
                    Lihat Semua (<?= $totalBarang ?>) &rarr;
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Nama Produk</th>
                                <th>Kategori</th>
                                <th>Satuan</th>
                                <th>Harga Pokok</th>
                                <th>Harga Jual</th>
                                <th class="text-center">Sisa Stok</th>
                                <th class="pe-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentBarang)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">Belum ada barang terdaftar.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentBarang as $b): ?>
                                    <tr>
                                        <td class="ps-4 fw-semibold text-dark">
                                            <?= htmlspecialchars($b->nama_barang) ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $b->jenis == 'B' ? 'badge-refract-blue' : 'badge-refract-yellow' ?>">
                                                <?= $b->jenis == 'B' ? 'Barang Jadi' : 'Konsumsi' ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($b->nama_satuan) ?></td>
                                        <td class="tabular text-muted">
                                            Rp <?= number_format($b->harga, 0, ',', '.') ?>
                                        </td>
                                        <td class="tabular fw-medium" style="color: var(--color-accent);">
                                            Rp <?= number_format($b->harga_jual ?? $b->harga, 0, ',', '.') ?>
                                        </td>
                                        <td class="text-center tabular">
                                            <span class="badge <?= ($b->stok ?? 0) > 0 ? 'badge-refract-blue' : 'badge-refract-gray' ?>">
                                                <?= number_format($b->stok ?? 0, 0, ',', '.') ?>
                                            </span>
                                        </td>
                                        <td class="pe-4">
                                            <span class="badge <?= $b->status == 1 ? 'badge-refract-green' : 'badge-refract-gray' ?>">
                                                <?= $b->status == 1 ? 'Aktif' : 'Non-Aktif' ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Samping: Panel Referensi Operasional -->
    <?php if ($userRole === 'admin'): ?>
        <div class="col-lg-4">
            <!-- Panel Pengguna Sistem -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-people me-2" style="color: var(--color-accent);"></i>Pengguna Sistem</span>
                    <a href="<?= BASE_URL ?>/user/index.php" class="btn btn-clear btn-sm">Semua</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">User</th>
                                    <th class="pe-4 text-end">Role</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentUser as $u): ?>
                                    <tr>
                                        <td class="ps-4 fw-semibold">
                                            <i class="bi bi-person-circle text-muted me-1"></i> <?= htmlspecialchars($u->username) ?>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <span class="badge badge-refract-blue">
                                                <?= htmlspecialchars($u->nama_role) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Panel Margin Penjualan -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-graph-up-arrow me-2" style="color: var(--color-accent);"></i>Tier Margin</span>
                    <a href="<?= BASE_URL ?>/margin/index.php" class="btn btn-clear btn-sm">Semua</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Persentase</th>
                                    <th class="pe-4 text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentMargin as $m): ?>
                                    <tr>
                                        <td class="ps-4 tabular fw-semibold" style="color: var(--color-accent);">
                                            <?= htmlspecialchars($m->persen) ?>%
                                        </td>
                                        <td class="pe-4 text-end">
                                            <span class="badge <?= $m->status == 1 ? 'badge-refract-green' : 'badge-refract-red' ?>">
                                                <?= $m->status == 1 ? 'Aktif' : 'Non-Aktif' ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif ($userRole === 'gudang'): ?>
        <div class="col-lg-4">
            <!-- Panel Mitra Vendor untuk Gudang -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-truck me-2" style="color: var(--color-accent);"></i>Mitra Vendor</span>
                    <a href="<?= BASE_URL ?>/vendor/index.php" class="btn btn-clear btn-sm">Semua</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Nama Vendor</th>
                                    <th class="pe-4 text-end">Badan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentVendor as $v): ?>
                                    <tr>
                                        <td class="ps-4 fw-semibold"><?= htmlspecialchars($v->nama_vendor) ?></td>
                                        <td class="pe-4 text-end">
                                            <span class="badge badge-refract-blue"><?= htmlspecialchars($v->badan_hukum) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
