<?php
/**
 * margin/hapus.php
 * Proses hapus master margin penjualan via PDO DELETE.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
check_role(['Admin']);

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM margin_penjualan WHERE idmargin_penjualan = ?");
        $stmt->execute([$id]);

        set_flash('success', 'Margin penjualan berhasil dihapus!');
    } catch (PDOException $e) {
        set_flash('error', 'Gagal menghapus margin penjualan! Data ini mungkin masih digunakan.');
    }
}

header('Location: ' . BASE_URL . '/margin/index.php');
exit;
