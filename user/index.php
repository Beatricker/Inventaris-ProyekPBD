<?php
/**
 * user/index.php
 * Menampilkan daftar master user menggunakan VIEW v_user.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
check_role(['Admin']);

$page_title = 'Master User';
require_once __DIR__ . '/../includes/header.php';

// Ambil data user dari VIEW v_user
$stmt = $pdo->query("SELECT * FROM v_user ORDER BY iduser ASC");
$userList = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-0 fw-bold"><i class="bi bi-people me-2"></i>Daftar Master User</h6>
            <small class="text-muted">Data diambil dari database VIEW <code>v_user</code> (Password tersimpan dalam hash SHA2-256)</small>
        </div>
        <a href="<?= BASE_URL ?>/user/tambah.php" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
            <i class="bi bi-person-plus"></i> Tambah User Baru
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3" style="width: 80px;">ID</th>
                        <th>Username</th>
                        <th>Role Akses</th>
                        <th class="text-end pe-3" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($userList)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">Belum ada data user.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($userList as $u): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-muted">#<?= $u->iduser ?></td>
                                <td class="fw-semibold">
                                    <i class="bi bi-person-circle text-primary me-2"></i><?= htmlspecialchars($u->username) ?>
                                    <?php if (auth_iduser() == $u->iduser): ?>
                                        <span class="badge bg-info text-dark ms-1">Anda</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $u->nama_role == 'Admin' ? 'bg-danger' : ($u->nama_role == 'Kasir' ? 'bg-success' : 'bg-warning text-dark') ?> px-2 py-1">
                                        <?= htmlspecialchars($u->nama_role) ?>
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="<?= BASE_URL ?>/user/edit.php?id=<?= $u->iduser ?>" class="btn btn-warning btn-sm" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <?php if (auth_iduser() != $u->iduser): ?>
                                        <a href="<?= BASE_URL ?>/user/hapus.php?id=<?= $u->iduser ?>" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php endif; ?>
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
