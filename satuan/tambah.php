<?php
/**
 * satuan/tambah.php
 * Form tambah satuan baru dan proses INSERT via PDO.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
check_role(['Admin']);

$error = '';
$nama_satuan = '';
$status = '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_satuan = trim($_POST['nama_satuan'] ?? '');
    $status = trim($_POST['status'] ?? '1');

    if (empty($nama_satuan)) {
        $error = 'Nama satuan wajib diisi!';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO satuan (nama_satuan, status) VALUES (?, ?)");
            $stmt->execute([$nama_satuan, $status]);

            set_flash('success', 'Satuan baru berhasil ditambahkan!');
            header('Location: ' . BASE_URL . '/satuan/index.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Gagal menyimpan data satuan: ' . $e->getMessage();
        }
    }
}

$page_title = 'Tambah Satuan';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-plus-circle me-2"></i>Tambah Satuan Baru</span>
                <a href="<?= BASE_URL ?>/satuan/index.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="mb-3">
                        <label for="nama_satuan" class="form-label fw-semibold">Nama Satuan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_satuan" id="nama_satuan" class="form-control" value="<?= htmlspecialchars($nama_satuan) ?>" placeholder="Contoh: Pcs, Box, Kg, Liter" maxlength="45" required autofocus>
                    </div>

                    <div class="mb-4">
                        <label for="status" class="form-label fw-semibold">Status Satuan <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="1" <?= $status === '1' ? 'selected' : '' ?>>Aktif (Dapat Digunakan)</option>
                            <option value="0" <?= $status === '0' ? 'selected' : '' ?>>Non-Aktif (Disembunyikan)</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= BASE_URL ?>/satuan/index.php" class="btn btn-light border px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-save me-1"></i> Simpan Satuan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
