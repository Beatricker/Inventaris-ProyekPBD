<?php
/**
 * satuan/index.php
 * Menampilkan daftar master satuan menggunakan VIEW v_satuan.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
check_role(['Admin', 'Gudang']);

$page_title = 'Master Satuan';
require_once __DIR__ . '/../includes/header.php';

// Ambil data satuan dari database VIEW v_satuan
$stmt = $pdo->query("SELECT * FROM v_satuan ORDER BY idsatuan DESC");
$satuanList = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-0 fw-bold"><i class="bi bi-rulers me-2"></i>Daftar Master Satuan</h6>
            <small class="text-muted">Data diambil dari database VIEW <code>v_satuan</code></small>
        </div>
        <a href="<?= BASE_URL ?>/satuan/tambah.php" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
            <i class="bi bi-plus-lg"></i> Tambah Satuan Baru
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3" style="width: 80px;">ID</th>
                        <th>Nama Satuan</th>
                        <th>Status</th>
                        <th class="text-end pe-3" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($satuanList)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">Belum ada data satuan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($satuanList as $s): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-muted">#<?= $s->idsatuan ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($s->nama_satuan) ?></td>
                                <td>
                                    <span class="badge <?= $s->status == 1 ? 'bg-success' : 'bg-danger' ?>">
                                        <?= $s->status == 1 ? 'Aktif' : 'Non-Aktif' ?>
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="<?= BASE_URL ?>/satuan/edit.php?id=<?= $s->idsatuan ?>" class="btn btn-warning btn-sm" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/satuan/hapus.php?id=<?= $s->idsatuan ?>" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus satuan ini?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
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
