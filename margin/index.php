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

$isAdmin = (strtolower($userRole) === 'admin');

// Ambil data margin penjualan dari database VIEW v_margin
$stmt = $pdo->query("SELECT * FROM v_margin ORDER BY idmargin_penjualan DESC");
$marginList = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-0 fw-bold"><i class="bi bi-graph-up-arrow me-2"></i>Daftar Master Margin Penjualan</h6>
            <small class="text-muted">Data diambil dari database VIEW <code>v_margin</code></small>
        </div>
        <?php if ($isAdmin): ?>
            <a href="<?= BASE_URL ?>/margin/tambah.php" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-plus-lg"></i> Tambah Margin Baru
            </a>
        <?php else: ?>
            <span class="badge bg-secondary-subtle text-secondary border">Mode Lihat (Hak Akses Kasir)</span>
        <?php endif; ?>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3" style="width: 80px;">ID</th>
                        <th>Persentase Margin (%)</th>
                        <th>User Pembuat</th>
                        <th>Status</th>
                        <th>Waktu Dibuat</th>
                        <?php if ($isAdmin): ?>
                            <th class="text-end pe-3" style="width: 140px;">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($marginList)): ?>
                        <tr>
                            <td colspan="<?= $isAdmin ? '6' : '5' ?>" class="text-center py-4 text-muted">Belum ada data margin penjualan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($marginList as $m): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-muted">#<?= $m->idmargin_penjualan ?></td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6 px-3 py-1">
                                        <?= htmlspecialchars($m->persen) ?>%
                                    </span>
                                </td>
                                <td class="fw-semibold">
                                    <i class="bi bi-person text-muted me-1"></i><?= htmlspecialchars($m->username) ?>
                                </td>
                                <td>
                                    <span class="badge <?= $m->status == 1 ? 'bg-success' : 'bg-danger' ?>">
                                        <?= $m->status == 1 ? 'Aktif' : 'Non-Aktif' ?>
                                    </span>
                                </td>
                                <td class="small text-muted"><?= htmlspecialchars($m->created_at) ?></td>
                                <?php if ($isAdmin): ?>
                                    <td class="text-end pe-3">
                                        <a href="<?= BASE_URL ?>/margin/edit.php?id=<?= $m->idmargin_penjualan ?>" class="btn btn-warning btn-sm" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/margin/hapus.php?id=<?= $m->idmargin_penjualan ?>" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus margin penjualan ini?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
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
