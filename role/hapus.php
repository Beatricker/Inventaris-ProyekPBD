<?php
/**
 * role/hapus.php
 * Proses hapus master role via PDO DELETE.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
check_role(['Admin']);

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM role WHERE idrole = ?");
        $stmt->execute([$id]);

        set_flash('success', 'Data role berhasil dihapus!');
    } catch (PDOException $e) {
        set_flash('error', 'Gagal menghapus role! Role ini masih digunakan oleh data user.');
    }
}

header('Location: ' . BASE_URL . '/role/index.php');
exit;
