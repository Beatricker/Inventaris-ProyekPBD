<?php
/**
 * user/tambah.php
 * Form tambah master user baru via PDO INSERT dengan hash SHA2(?, 256).
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
check_role(['Admin']);

// Ambil daftar role dari VIEW v_role
$roles = $pdo->query("SELECT * FROM v_role ORDER BY idrole ASC")->fetchAll();

$error = '';
$usernameInput = '';
$idroleInput = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameInput = trim($_POST['username'] ?? '');
    $passwordInput = trim($_POST['password'] ?? '');
    $idroleInput = (int)($_POST['idrole'] ?? 0);

    if (empty($usernameInput) || empty($passwordInput) || empty($idroleInput)) {
        $error = 'Username, Password, dan Role wajib diisi!';
    } else {
        // Cek apakah username sudah digunakan
        $checkStmt = $pdo->prepare("SELECT iduser FROM user WHERE username = ? LIMIT 1");
        $checkStmt->execute([$usernameInput]);
        if ($checkStmt->fetch()) {
            $error = "Username '{$usernameInput}' sudah digunakan, silakan pilih username lain!";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO user (username, password, idrole) VALUES (?, SHA2(?, 256), ?)");
                $stmt->execute([$usernameInput, $passwordInput, $idroleInput]);

                set_flash('success', 'User baru berhasil didaftarkan!');
                header('Location: ' . BASE_URL . '/user/index.php');
                exit;
            } catch (PDOException $e) {
                $error = 'Gagal menyimpan user: ' . $e->getMessage();
            }
        }
    }
}

$page_title = 'Tambah User';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-person-plus me-2"></i>Form Input Master User</span>
                <a href="<?= BASE_URL ?>/user/index.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" id="username" class="form-control" value="<?= htmlspecialchars($usernameInput) ?>" placeholder="Contoh: kasir_dua" maxlength="45" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password (minimal 4 karakter)" minlength="4" required>
                        <small class="text-muted">Password akan di-hash otomatis menggunakan fungsi SQL <code>SHA2(password, 256)</code></small>
                    </div>

                    <div class="mb-4">
                        <label for="idrole" class="form-label fw-semibold">Role / Hak Akses <span class="text-danger">*</span></label>
                        <select name="idrole" id="idrole" class="form-select" required>
                            <option value="" disabled selected>-- Pilih Role --</option>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r->idrole ?>" <?= $idroleInput == $r->idrole ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($r->nama_role) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= BASE_URL ?>/user/index.php" class="btn btn-light border px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-save me-1"></i> Simpan User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
