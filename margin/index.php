<?php

/**
 * margin/index.php
 * Menampilkan daftar master margin penjualan menggunakan VIEW v_margin.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
check_role(['Admin', 'Kasir']);

$page_title = 'Margin Penjualan';
require_once __DIR__ . '/../includes/header.php';

$userRole = strtolower(trim(auth_role()));
$isAdmin = ($userRole === 'admin');

// Ambil data margin penjualan dari database VIEW v_margin
$stmt = $pdo->query("SELECT * FROM v_margin ORDER BY idmargin_penjualan ASC");
$marginList = $stmt->fetchAll();
?>

<!-- Editorial Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-3">
    <div>
        <h2 class="display-font mb-1" style="font-size: 2.2rem; font-weight: 500;">Margin Penjualan</h2>
    </div>
    <div>
        <?php if ($isAdmin): ?>
            <a href="<?= BASE_URL ?>/margin/tambah.php" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Tambah Margin Baru
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
                        <th class="ps-4" style="width: 80px;">ID</th>
                        <th>Persentase Margin (%)</th>
                        <th>User Pembuat</th>
                        <th>Status</th>
                        <th>Waktu Dibuat</th>
                        <?php if ($isAdmin): ?>
                            <th class="text-end pe-4" style="width: 140px;">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($marginList)): ?>
                        <tr>
                            <td colspan="<?= $isAdmin ? '6' : '5' ?>" class="text-center py-5 text-muted">Belum ada data margin penjualan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($marginList as $m): ?>
                            <tr>
                                <td class="ps-4 tabular text-muted fw-semibold">#<?= $m->idmargin_penjualan ?></td>
                                <td>
                                    <span class="badge badge-refract-blue px-3 py-1 fs-6 tabular">
                                        <?= htmlspecialchars($m->persen) ?>%
                                    </span>
                                </td>
                                <td class="fw-semibold">
                                    <i class="bi bi-person text-muted me-1"></i><?= htmlspecialchars($m->username) ?>
                                </td>
                                <td>
                                    <span class="badge <?= $m->status == 1 ? 'badge-refract-green' : 'badge-refract-red' ?>">
                                        <?= $m->status == 1 ? 'Aktif' : 'Non-Aktif' ?>
                                    </span>
                                </td>
                                <td class="small tabular text-muted"><?= htmlspecialchars($m->created_at) ?></td>
                                <?php if ($isAdmin): ?>
                                    <td class="text-end pe-4">
                                        <div class="d-inline-flex gap-1">
                                            <a href="<?= BASE_URL ?>/margin/edit.php?id=<?= $m->idmargin_penjualan ?>" class="btn-action-icon" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/margin/hapus.php?id=<?= $m->idmargin_penjualan ?>" class="btn-action-icon btn-action-danger" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus margin penjualan ini?')">
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
