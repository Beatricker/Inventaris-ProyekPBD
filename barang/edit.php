<?php
/**
 * barang/edit.php
 * Form edit master barang dan proses UPDATE via PDO.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
check_role(['Admin', 'Gudang']);

$id = (int)($_GET['id'] ?? 0);

// Cari data barang berdasarkan ID
$stmt = $pdo->prepare("SELECT * FROM barang WHERE idbarang = ? LIMIT 1");
$stmt->execute([$id]);
$barang = $stmt->fetch();

if (!$barang) {
    set_flash('error', 'Data barang tidak ditemukan!');
    header('Location: ' . BASE_URL . '/barang/index.php');
    exit;
}

// Ambil daftar satuan aktif
$satuanList = $pdo->query("SELECT * FROM v_satuan WHERE status = 1 ORDER BY nama_satuan ASC")->fetchAll();

$error = '';
$jenis = $barang->jenis;
$nama = $barang->nama;
$idsatuan = $barang->idsatuan;
$harga = $barang->harga;
$status = $barang->status;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis = trim($_POST['jenis'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $idsatuan = (int)($_POST['idsatuan'] ?? 0);
    $harga = (float)($_POST['harga'] ?? 0);
    $status = trim($_POST['status'] ?? '1');

    if (empty($jenis) || empty($nama) || empty($idsatuan) || $harga < 0) {
        $error = 'Semua field bertanda bintang (*) wajib diisi dengan benar!';
    } else {
        try {
            $updateStmt = $pdo->prepare("UPDATE barang SET jenis = ?, nama = ?, idsatuan = ?, status = ?, harga = ? WHERE idbarang = ?");
            $updateStmt->execute([$jenis, $nama, $idsatuan, $status, $harga, $id]);

            set_flash('success', 'Data barang berhasil diperbarui!');
            header('Location: ' . BASE_URL . '/barang/index.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Gagal memperbarui barang: ' . $e->getMessage();
        }
    }
}

$page_title = 'Edit Barang';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Barang: <?= htmlspecialchars($barang->nama) ?> (ID #<?= $barang->idbarang ?>)</span>
                <a href="<?= BASE_URL ?>/barang/index.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="jenis" class="form-label fw-semibold">Jenis Barang <span class="text-danger">*</span></label>
                            <select name="jenis" id="jenis" class="form-select" required>
                                <option value="B" <?= $jenis === 'B' ? 'selected' : '' ?>>B - Barang Jadi / Dagangan</option>
                                <option value="K" <?= $jenis === 'K' ? 'selected' : '' ?>>K - Komponen / Konsumsi</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="idsatuan" class="form-label fw-semibold">Satuan Barang <span class="text-danger">*</span></label>
                            <select name="idsatuan" id="idsatuan" class="form-select" required>
                                <?php foreach ($satuanList as $s): ?>
                                    <option value="<?= $s->idsatuan ?>" <?= $idsatuan == $s->idsatuan ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($s->nama_satuan) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="nama" class="form-label fw-semibold">Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" name="nama" id="nama" class="form-control" value="<?= htmlspecialchars($nama) ?>" maxlength="45" required>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="harga" class="form-label fw-semibold">Harga Jual (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="harga" id="harga" class="form-control" value="<?= htmlspecialchars($harga) ?>" min="0" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label fw-semibold">Status Ketersediaan <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="1" <?= $status == '1' ? 'selected' : '' ?>>Aktif (Tersedia untuk Dijual)</option>
                                <option value="0" <?= $status == '0' ? 'selected' : '' ?>>Non-Aktif (Disembunyikan)</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= BASE_URL ?>/barang/index.php" class="btn btn-light border px-4">Batal</a>
                        <button type="submit" class="btn btn-warning px-4 fw-semibold text-dark">
                            <i class="bi bi-check2-circle me-1"></i> Perbarui Data Barang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
