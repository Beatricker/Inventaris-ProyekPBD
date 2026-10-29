<?php
/**
 * role/edit.php
 * Form edit master role dan proses UPDATE via PDO.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
check_role(['Admin']);

$id = (int)($_GET['id'] ?? 0);

// Cari data role berdasarkan ID
$stmt = $pdo->prepare("SELECT * FROM role WHERE idrole = ? LIMIT 1");
$stmt->execute([$id]);
$role = $stmt->fetch();

if (!$role) {
    set_flash('error', 'Data role tidak ditemukan!');
    header('Location: ' . BASE_URL . '/role/index.php');
    exit;
}

$error = '';
$nama_role = $role->nama_role;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_role = trim($_POST['nama_role'] ?? '');

    if (empty($nama_role)) {
        $error = 'Nama role wajib diisi!';
    } else {
        try {
            $updateStmt = $pdo->prepare("UPDATE role SET nama_role = ? WHERE idrole = ?");
            $updateStmt->execute([$nama_role, $id]);

            set_flash('success', 'Data role berhasil diperbarui!');
            header('Location: ' . BASE_URL . '/role/index.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Gagal memperbarui data role: ' . $e->getMessage();
        }
    }
}

$page_title = 'Edit Role';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Role: <?= htmlspecialchars($role->nama_role) ?> (ID #<?= $role->idrole ?>)</span>
                <a href="<?= BASE_URL ?>/role/index.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="mb-4">
                        <label for="nama_role" class="form-label fw-semibold">Nama Role / Hak Akses <span class="text-danger">*</span></label>
                        <input type="text" name="nama_role" id="nama_role" class="form-control" value="<?= htmlspecialchars($nama_role) ?>" maxlength="100" required autofocus>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= BASE_URL ?>/role/index.php" class="btn btn-light border px-4">Batal</a>
                        <button type="submit" class="btn btn-warning px-4 fw-semibold text-dark">
                            <i class="bi bi-check2-circle me-1"></i> Perbarui Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
