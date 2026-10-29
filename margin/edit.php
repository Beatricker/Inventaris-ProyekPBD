<?php
/**
 * margin/edit.php
 * Form edit master margin penjualan dan proses UPDATE via PDO.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
check_role(['Admin']);

$id = (int)($_GET['id'] ?? 0);

// Cari data margin penjualan berdasarkan ID
$stmt = $pdo->prepare("SELECT * FROM margin_penjualan WHERE idmargin_penjualan = ? LIMIT 1");
$stmt->execute([$id]);
$margin = $stmt->fetch();

if (!$margin) {
    set_flash('error', 'Data margin penjualan tidak ditemukan!');
    header('Location: ' . BASE_URL . '/margin/index.php');
    exit;
}

// Ambil daftar user dari VIEW v_user
$users = $pdo->query("SELECT iduser, username FROM v_user ORDER BY username ASC")->fetchAll();

$error = '';
$persen = $margin->persen;
$status = $margin->status;
$iduser = $margin->iduser;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $persen = (float)($_POST['persen'] ?? 0);
    $status = trim($_POST['status'] ?? '1');
    $iduser = (int)($_POST['iduser'] ?? 0);

    if ($persen < 0 || $persen > 100 || empty($iduser)) {
        $error = 'Persentase margin (0-100%) dan User Penanggung Jawab wajib diisi dengan benar!';
    } else {
        try {
            $updateStmt = $pdo->prepare("UPDATE margin_penjualan SET persen = ?, status = ?, iduser = ? WHERE idmargin_penjualan = ?");
            $updateStmt->execute([$persen, $status, $iduser, $id]);

            set_flash('success', 'Data margin penjualan berhasil diperbarui!');
            header('Location: ' . BASE_URL . '/margin/index.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Gagal memperbarui margin penjualan: ' . $e->getMessage();
        }
    }
}

$page_title = 'Edit Margin Penjualan';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Margin Penjualan (ID #<?= $margin->idmargin_penjualan ?>)</span>
                <a href="<?= BASE_URL ?>/margin/index.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="mb-3">
                        <label for="persen" class="form-label fw-semibold">Persentase Margin Keuntungan (%) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="persen" id="persen" class="form-control" value="<?= htmlspecialchars($persen) ?>" min="0" max="100" required autofocus>
                            <span class="input-group-text">%</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="iduser" class="form-label fw-semibold">User Penanggung Jawab <span class="text-danger">*</span></label>
                        <select name="iduser" id="iduser" class="form-select" required>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u->iduser ?>" <?= $iduser == $u->iduser ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($u->username) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="status" class="form-label fw-semibold">Status Margin <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="1" <?= $status == '1' ? 'selected' : '' ?>>Aktif</option>
                            <option value="0" <?= $status == '0' ? 'selected' : '' ?>>Non-Aktif</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= BASE_URL ?>/margin/index.php" class="btn btn-light border px-4">Batal</a>
                        <button type="submit" class="btn btn-warning px-4 fw-semibold text-dark">
                            <i class="bi bi-check2-circle me-1"></i> Perbarui Margin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
