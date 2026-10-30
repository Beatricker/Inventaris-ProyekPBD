<?php

/**
 * role/index.php
 * Menampilkan daftar master role menggunakan VIEW v_role.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
check_role(['Admin']);

$page_title = 'Data Role';
require_once __DIR__ . '/../includes/header.php';

// Ambil data role dari VIEW v_role
$stmt = $pdo->query("SELECT * FROM v_role ORDER BY idrole ASC");
$roleList = $stmt->fetchAll();
?>

<!-- Editorial Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-3">
    <div>
        <h2 class="display-font mb-1" style="font-size: 2.2rem; font-weight: 500;">Daftar Role</h2>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/role/tambah.php" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Tambah Role Baru
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
                        <th>Nama Role / Hak Akses</th>
                        <th class="text-end pe-4" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($roleList)): ?>
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted">Belum ada data role.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($roleList as $r): ?>
                            <tr>
                                <td class="ps-4 tabular text-muted fw-semibold">#<?= $r->idrole ?></td>
                                <td class="fw-semibold">
                                    <span class="badge badge-refract-blue">
                                        <i class="bi bi-shield me-1"></i> <?= htmlspecialchars($r->nama_role) ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-1">
                                        <a href="<?= BASE_URL ?>/role/edit.php?id=<?= $r->idrole ?>" class="btn-action-icon" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/role/hapus.php?id=<?= $r->idrole ?>" class="btn-action-icon btn-action-danger" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus role ini?')">
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
