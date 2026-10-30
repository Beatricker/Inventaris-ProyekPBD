<?php

/**
 * barang/index.php
 * Menampilkan daftar master barang menggunakan VIEW v_barang.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
check_role(['Admin', 'Gudang', 'Kasir']);

$page_title = 'Data Barang';
require_once __DIR__ . '/../includes/header.php';

$userRole = strtolower(trim(auth_role()));
$canManage = in_array($userRole, ['admin', 'gudang']);

// Ambil data barang dari database VIEW v_barang
$stmt = $pdo->query("SELECT * FROM v_barang ORDER BY idbarang ASC");
$barangList = $stmt->fetchAll();
?>

<!-- Editorial Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-3">
    <div>
        <h2 class="display-font mb-1" style="font-size: 2.2rem; font-weight: 500;">Daftar Barang</h2>
    </div>
    <div>
        <?php if ($canManage): ?>
            <a href="<?= BASE_URL ?>/barang/tambah.php" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Tambah Barang Baru
            </a>
        <?php else: ?>
            <span class="badge badge-refract-blue py-2 px-3">Mode Katalog Kasir</span>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" style="width: 70px;">ID</th>
                        <th>Jenis</th>
                        <th>Nama Barang</th>
                        <th>Satuan</th>
                        <th>Harga Pokok</th>
                        <th>Harga Jual</th>
                        <th class="text-center">Sisa Stok</th>
                        <th>Status</th>
                        <?php if ($canManage): ?>
                            <th class="text-end pe-4" style="width: 120px;">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($barangList)): ?>
                        <tr>
                            <td colspan="<?= $canManage ? '9' : '8' ?>" class="text-center py-5 text-muted">Belum ada data barang.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($barangList as $b): ?>
                            <tr>
                                <td class="ps-4 tabular text-muted fw-semibold">#<?= $b->idbarang ?></td>
                                <td>
                                    <span class="badge <?= $b->jenis == 'B' ? 'badge-refract-blue' : 'badge-refract-yellow' ?>">
                                        <?= $b->jenis == 'B' ? 'Barang Jadi' : 'Konsumsi' ?>
                                    </span>
                                </td>
                                <td class="fw-semibold"><?= htmlspecialchars($b->nama_barang) ?></td>
                                <td><?= htmlspecialchars($b->nama_satuan) ?></td>
                                <td class="tabular text-muted">Rp <?= number_format($b->harga, 0, ',', '.') ?></td>
                                <td class="tabular fw-medium" style="color: var(--color-accent);">Rp <?= number_format($b->harga_jual ?? $b->harga, 0, ',', '.') ?></td>
                                <td class="text-center tabular">
                                    <span class="badge <?= ($b->stok ?? 0) > 0 ? 'badge-refract-blue' : 'badge-refract-gray' ?>">
                                        <?= number_format($b->stok ?? 0, 0, ',', '.') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= $b->status == 1 ? 'badge-refract-green' : 'badge-refract-red' ?>">
                                        <?= $b->status == 1 ? 'Aktif' : 'Non-Aktif' ?>
                                    </span>
                                </td>
                                <?php if ($canManage): ?>
                                    <td class="text-end pe-4">
                                        <div class="d-inline-flex gap-1">
                                            <a href="<?= BASE_URL ?>/barang/edit.php?id=<?= $b->idbarang ?>" class="btn-action-icon" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/barang/hapus.php?id=<?= $b->idbarang ?>" class="btn-action-icon btn-action-danger" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus barang ini?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
