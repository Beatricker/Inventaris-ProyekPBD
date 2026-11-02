<?php

/**
 * satuan/index.php
 * Menampilkan daftar master satuan menggunakan VIEW v_satuan.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
check_role(['Admin']);

$page_title = 'Data Satuan';
require_once __DIR__ . '/../includes/header.php';

// Ambil data satuan dari database VIEW v_satuan
$stmt = $pdo->query("SELECT * FROM v_satuan ORDER BY idsatuan ASC");
$satuanList = $stmt->fetchAll();
?>

<!-- Editorial Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-3">
    <div>
        <h2 class="display-font mb-1" style="font-size: 2.2rem; font-weight: 500;">Daftar Satuan</h2>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/satuan/tambah.php" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Tambah Satuan Baru
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" style="width: 90px;">ID</th>
                        <th>Nama Satuan</th>
                        <th>Status</th>
                        <th class="text-end pe-4" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($satuanList)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">Belum ada data satuan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($satuanList as $s): ?>
                            <tr>
                                <td class="ps-4 tabular text-muted fw-semibold">#<?= $s->idsatuan ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($s->nama_satuan) ?></td>
                                <td>
                                    <span class="badge <?= $s->status == 1 ? 'badge-refract-green' : 'badge-refract-red' ?>">
                                        <?= $s->status == 1 ? 'Aktif' : 'Non-Aktif' ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-1">
                                        <a href="<?= BASE_URL ?>/satuan/edit.php?id=<?= $s->idsatuan ?>" class="btn-action-icon" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/satuan/hapus.php?id=<?= $s->idsatuan ?>" class="btn-action-icon btn-action-danger" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus satuan ini?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
