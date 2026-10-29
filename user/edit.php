<?php
/**
 * user/edit.php
 * Form edit master user dan proses UPDATE via PDO.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
check_role(['Admin']);

$id = (int)($_GET['id'] ?? 0);

// Cari data user berdasarkan ID
$stmt = $pdo->prepare("SELECT * FROM user WHERE iduser = ? LIMIT 1");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    set_flash('error', 'Data user tidak ditemukan!');
    header('Location: ' . BASE_URL . '/user/index.php');
    exit;
}

// Ambil daftar role dari VIEW v_role
$roles = $pdo->query("SELECT * FROM v_role ORDER BY idrole ASC")->fetchAll();

$error = '';
$usernameInput = $user->username;
$idroleInput = $user->idrole;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameInput = trim($_POST['username'] ?? '');
    $passwordInput = trim($_POST['password'] ?? '');
    $idroleInput = (int)($_POST['idrole'] ?? 0);

    if (empty($usernameInput) || empty($idroleInput)) {
        $error = 'Username dan Role wajib diisi!';
    } else {
        // Cek duplikasi username (selain user saat ini)
        $checkStmt = $pdo->prepare("SELECT iduser FROM user WHERE username = ? AND iduser != ? LIMIT 1");
        $checkStmt->execute([$usernameInput, $id]);
        if ($checkStmt->fetch()) {
            $error = "Username '{$usernameInput}' sudah digunakan oleh akun lain!";
        } else {
            try {
                if (!empty($passwordInput)) {
                    // Update dengan password baru
                    $updateStmt = $pdo->prepare("UPDATE user SET username = ?, password = SHA2(?, 256), idrole = ? WHERE iduser = ?");
                    $updateStmt->execute([$usernameInput, $passwordInput, $idroleInput, $id]);
                } else {
                    // Update tanpa mengubah password
                    $updateStmt = $pdo->prepare("UPDATE user SET username = ?, idrole = ? WHERE iduser = ?");
                    $updateStmt->execute([$usernameInput, $idroleInput, $id]);
                }

                // Jika user mengedit akunnya sendiri, perbarui data session
                if (auth_iduser() == $id) {
                    $_SESSION['username'] = $usernameInput;
                    $roleNameStmt = $pdo->prepare("SELECT nama_role FROM role WHERE idrole = ? LIMIT 1");
                    $roleNameStmt->execute([$idroleInput]);
                    $roleObj = $roleNameStmt->fetch();
                    if ($roleObj) {
                        $_SESSION['role'] = $roleObj->nama_role;
                        $_SESSION['idrole'] = $idroleInput;
                    }
                }

                set_flash('success', 'Data user berhasil diperbarui!');
                header('Location: ' . BASE_URL . '/user/index.php');
                exit;
            } catch (PDOException $e) {
                $error = 'Gagal memperbarui data user: ' . $e->getMessage();
            }
        }
    }
}

$page_title = 'Edit User';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit User: <?= htmlspecialchars($user->username) ?> (ID #<?= $user->iduser ?>)</span>
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
                        <input type="text" name="username" id="username" class="form-control" value="<?= htmlspecialchars($usernameInput) ?>" maxlength="45" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Password Baru (Opsional)</label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Biarkan kosong jika tidak ingin mengubah password" minlength="4">
                        <small class="text-muted">Kosongkan jika password lama tetap ingin dipertahankan.</small>
                    </div>

                    <div class="mb-4">
                        <label for="idrole" class="form-label fw-semibold">Role / Hak Akses <span class="text-danger">*</span></label>
                        <select name="idrole" id="idrole" class="form-select" required>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r->idrole ?>" <?= $idroleInput == $r->idrole ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($r->nama_role) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= BASE_URL ?>/user/index.php" class="btn btn-light border px-4">Batal</a>
                        <button type="submit" class="btn btn-warning px-4 fw-semibold text-dark">
                            <i class="bi bi-check2-circle me-1"></i> Perbarui User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
