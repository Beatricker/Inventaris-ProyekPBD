<?php
/**
 * satuan/hapus.php
 * Proses hapus data satuan via PDO DELETE.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
check_role(['Admin', 'Gudang']);

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM satuan WHERE idsatuan = ?");
        $stmt->execute([$id]);

        set_flash('success', 'Data satuan berhasil dihapus!');
    } catch (PDOException $e) {
        set_flash('error', 'Gagal menghapus satuan! Data satuan ini masih digunakan oleh data barang.');
    }
}

header('Location: ' . BASE_URL . '/satuan/index.php');
exit;
