<?php
/**
 * user/hapus.php
 * Proses hapus master user via PDO DELETE.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
check_role(['Admin']);

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    // Larang menghapus akun sendiri yang sedang aktif login
    if ($id == auth_iduser()) {
        set_flash('error', 'Tidak dapat menghapus akun Anda sendiri yang sedang aktif login!');
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM user WHERE iduser = ?");
            $stmt->execute([$id]);

            set_flash('success', 'User berhasil dihapus!');
        } catch (PDOException $e) {
            set_flash('error', 'Gagal menghapus user! User ini memiliki riwayat transaksi/aktivitas di database.');
        }
    }
}

header('Location: ' . BASE_URL . '/user/index.php');
exit;
