<?php
/**
 * barang/hapus.php
 * Proses hapus master barang via PDO DELETE.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
check_role(['Admin']);

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM barang WHERE idbarang = ?");
        $stmt->execute([$id]);

        set_flash('success', 'Data barang berhasil dihapus!');
    } catch (PDOException $e) {
        set_flash('error', 'Gagal menghapus barang! Data barang ini kemungkinan masih terkait dengan transaksi penjualan/pengadaan.');
    }
}

header('Location: ' . BASE_URL . '/barang/index.php');
exit;
