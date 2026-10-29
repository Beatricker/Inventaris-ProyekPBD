<?php
/**
 * role/index.php
 * Menampilkan daftar master role menggunakan VIEW v_role.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
check_role(['Admin']);

$page_title = 'Master Role';
require_once __DIR__ . '/../includes/header.php';

// Ambil data role dari VIEW v_role
$stmt = $pdo->query("SELECT * FROM v_role ORDER BY idrole ASC");
$roleList = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-0 fw-bold"><i class="bi bi-shield-lock me-2"></i>Daftar Master Role</h6>
            <small class="text-muted">Data diambil dari database VIEW <code>v_role</code></small>
        </div>
        <a href="<?= BASE_URL ?>/role/tambah.php" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
            <i class="bi bi-plus-lg"></i> Tambah Role Baru
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3" style="width: 80px;">ID</th>
                        <th>Nama Role / Hak Akses</th>
                        <th class="text-end pe-3" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($roleList)): ?>
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted">Belum ada data role.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($roleList as $r): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-muted">#<?= $r->idrole ?></td>
                                <td class="fw-semibold">
                                    <span class="badge bg-secondary-subtle text-secondary border px-2 py-1 fs-6">
                                        <i class="bi bi-person-badge me-1"></i> <?= htmlspecialchars($r->nama_role) ?>
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="<?= BASE_URL ?>/role/edit.php?id=<?= $r->idrole ?>" class="btn btn-warning btn-sm" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/role/hapus.php?id=<?= $r->idrole ?>" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus role ini?')">
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
