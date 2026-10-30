<?php
/**
 * role/tambah.php
 * Form tambah master role baru via PDO INSERT.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
check_role(['Admin']);

$error = '';
$nama_role = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_role = trim($_POST['nama_role'] ?? '');

    if (empty($nama_role)) {
        $error = 'Nama role wajib diisi!';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO role (nama_role) VALUES (?)");
            $stmt->execute([$nama_role]);

            set_flash('success', 'Role baru berhasil ditambahkan!');
            header('Location: ' . BASE_URL . '/role/index.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Gagal menyimpan role: ' . $e->getMessage();
        }
    }
}

$page_title = 'Tambah Role';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-shield-plus me-2"></i>Tambah Role Baru</span>
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
                        <input type="text" name="nama_role" id="nama_role" class="form-control" value="<?= htmlspecialchars($nama_role) ?>" placeholder="Contoh: Manager, Kasir, Gudang" maxlength="100" required autofocus>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= BASE_URL ?>/role/index.php" class="btn btn-light border px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-save me-1"></i> Simpan Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
