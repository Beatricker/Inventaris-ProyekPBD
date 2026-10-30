<?php

/**
 * user/index.php
 * Menampilkan daftar master user menggunakan VIEW v_user.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
check_role(['Admin']);

$page_title = 'Data User';
require_once __DIR__ . '/../includes/header.php';

// Ambil data user dari VIEW v_user
$stmt = $pdo->query("SELECT * FROM v_user ORDER BY iduser ASC");
$userList = $stmt->fetchAll();
?>

<!-- Editorial Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-3">
    <div>
        <h2 class="display-font mb-1" style="font-size: 2.2rem; font-weight: 500;">Daftar Pengguna</h2>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/user/tambah.php" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> Tambah User Baru
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" style="width: 80px;">ID</th>
                        <th>Username</th>
                        <th>Role Akses</th>
                        <th class="text-end pe-4" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($userList)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">Belum ada data user.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($userList as $u): ?>
                            <tr>
                                <td class="ps-4 tabular text-muted fw-semibold">#<?= $u->iduser ?></td>
                                <td class="fw-semibold">
                                    <i class="bi bi-person-circle me-2" style="color: var(--color-accent);"></i><?= htmlspecialchars($u->username) ?>
                                    <?php if (auth_iduser() == $u->iduser): ?>
                                        <span class="badge badge-refract-blue ms-1">Anda</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $roleBadgeClass = 'badge-refract-blue';
                                    if (strtolower($u->nama_role) === 'admin') $roleBadgeClass = 'badge-refract-red';
                                    elseif (strtolower($u->nama_role) === 'kasir') $roleBadgeClass = 'badge-refract-green';
                                    elseif (strtolower($u->nama_role) === 'gudang') $roleBadgeClass = 'badge-refract-yellow';
                                    ?>
                                    <span class="badge <?= $roleBadgeClass ?>">
                                        <?= htmlspecialchars($u->nama_role) ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-1">
                                        <a href="<?= BASE_URL ?>/user/edit.php?id=<?= $u->iduser ?>" class="btn-action-icon" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php if (auth_iduser() != $u->iduser): ?>
                                            <a href="<?= BASE_URL ?>/user/hapus.php?id=<?= $u->iduser ?>" class="btn-action-icon btn-action-danger" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        <?php endif; ?>
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
