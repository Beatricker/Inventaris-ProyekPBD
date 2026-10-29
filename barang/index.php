<?php
/**
 * barang/index.php
 * Menampilkan daftar master barang menggunakan VIEW v_barang.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
check_role(['Admin', 'Gudang', 'Kasir']);

$page_title = 'Master Barang';
require_once __DIR__ . '/../includes/header.php';

$canManage = in_array($userRole, ['admin', 'gudang']);

// Ambil data barang dari database VIEW v_barang
$stmt = $pdo->query("SELECT * FROM v_barang ORDER BY idbarang DESC");
$barangList = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-0 fw-bold"><i class="bi bi-box-seam me-2"></i>Daftar Master Barang</h6>
            <small class="text-muted">Data diambil dari database VIEW <code>v_barang</code></small>
        </div>
        <?php if ($canManage): ?>
            <a href="<?= BASE_URL ?>/barang/tambah.php" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-plus-lg"></i> Tambah Barang Baru
            </a>
        <?php else: ?>
            <span class="badge bg-secondary-subtle text-secondary border">Mode Lihat (Katalog Kasir)</span>
        <?php endif; ?>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3" style="width: 70px;">ID</th>
                        <th>Jenis</th>
                        <th>Nama Barang</th>
                        <th>Satuan</th>
                        <th>Harga Jual</th>
                        <th>Status</th>
                        <?php if ($canManage): ?>
                            <th class="text-end pe-3" style="width: 140px;">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($barangList)): ?>
                        <tr>
                            <td colspan="<?= $canManage ? '7' : '6' ?>" class="text-center py-4 text-muted">Belum ada data barang.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($barangList as $b): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-muted">#<?= $b->idbarang ?></td>
                                <td>
                                    <span class="badge <?= $b->jenis == 'B' ? 'bg-primary' : 'bg-warning text-dark' ?>">
                                        <?= $b->jenis == 'B' ? 'B (Barang Jadi)' : 'K (Konsumsi)' ?>
                                    </span>
                                </td>
                                <td class="fw-semibold"><?= htmlspecialchars($b->nama_barang) ?></td>
                                <td><?= htmlspecialchars($b->nama_satuan) ?></td>
                                <td class="fw-bold text-primary">Rp <?= number_format($b->harga, 0, ',', '.') ?></td>
                                <td>
                                    <span class="badge <?= $b->status == 1 ? 'bg-success' : 'bg-danger' ?>">
                                        <?= $b->status == 1 ? 'Aktif' : 'Non-Aktif' ?>
                                    </span>
                                </td>
                                <?php if ($canManage): ?>
                                    <td class="text-end pe-3">
                                        <a href="<?= BASE_URL ?>/barang/edit.php?id=<?= $b->idbarang ?>" class="btn btn-warning btn-sm" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/barang/hapus.php?id=<?= $b->idbarang ?>" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus barang ini?')">
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
