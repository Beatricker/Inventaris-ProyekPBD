<?php
/**
 * config/database.php
 * File konfigurasi koneksi database MySQL menggunakan PDO murni.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

$db_host = '127.0.0.1';
$db_port = '3306';
$db_name = 'proyek_pbd';
$db_user = 'root';
$db_pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset={$charset}";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lempar Exception jika query SQL error
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,       // Format data berupa objek ($row->nama_kolom)
    PDO::ATTR_EMULATE_PREPARES   => false,                 // Menggunakan native prepared statements
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}

// Otomatis tentukan BASE_URL (Bisa diakses dari http://localhost:8000 maupun http://localhost/PutPutMart)
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1:8000';
    
    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])) : '';
    $projRoot = str_replace('\\', '/', realpath(__DIR__ . '/..'));
    
    $subPath = '';
    if (!empty($docRoot) && strpos($projRoot, $docRoot) === 0) {
        $subPath = substr($projRoot, strlen($docRoot));
    }
    
    $subPath = '/' . trim(str_replace('\\', '/', $subPath), '/');
    if ($subPath === '/') {
        $subPath = '';
    }
    
    define('BASE_URL', rtrim($protocol . $host . $subPath, '/'));
}

// -----------------------------------------------------------------------------
// Helper Pemanggil Stored Function MySQL dari Backend PHP
// -----------------------------------------------------------------------------

/**
 * Mengambil sisa stok barang terkini dari tabel kartu_stok via fn_get_sisa_stok()
 */
function db_get_sisa_stok(PDO $pdo, int $idbarang): int {
    $stmt = $pdo->prepare("SELECT fn_get_sisa_stok(?) AS sisa");
    $stmt->execute([$idbarang]);
    return (int) $stmt->fetchColumn();
}

/**
 * Menghitung harga jual barang sesuai margin aktif via fn_hitung_harga_jual()
 */
function db_hitung_harga_jual(PDO $pdo, int $idbarang, ?int $idmargin = null): int {
    $stmt = $pdo->prepare("SELECT fn_hitung_harga_jual(?, ?) AS harga_jual");
    $stmt->execute([$idbarang, $idmargin]);
    return (int) $stmt->fetchColumn();
}

/**
 * Menghitung nilai PPN dari subtotal via fn_hitung_ppn()
 */
function db_hitung_ppn(PDO $pdo, int $subtotal, float $persen = 11.0): int {
    $stmt = $pdo->prepare("SELECT fn_hitung_ppn(?, ?) AS ppn");
    $stmt->execute([$subtotal, $persen]);
    return (int) $stmt->fetchColumn();
}

/**
 * Menghitung total nilai akhir transaksi via fn_hitung_total_transaksi()
 */
function db_hitung_total_transaksi(PDO $pdo, int $subtotal, int $ppn): int {
    $stmt = $pdo->prepare("SELECT fn_hitung_total_transaksi(?, ?) AS total");
    $stmt->execute([$subtotal, $ppn]);
    return (int) $stmt->fetchColumn();
}

// -----------------------------------------------------------------------------
// Helper Pemanggil Stored Procedure MySQL dari Backend PHP
// -----------------------------------------------------------------------------

/**
 * Eksekusi transaksi kasir penjualan via sp_transaksi_penjualan (ACID)
 */
function db_sp_transaksi_penjualan(PDO $pdo, int $iduser, ?int $idmargin, array $items): array {
    $itemsJson = json_encode($items);
    $pdo->query("SET @out_id = 0, @out_status = '', @out_msg = ''");
    $stmt = $pdo->prepare("CALL sp_transaksi_penjualan(?, ?, ?, @out_id, @out_status, @out_msg)");
    $stmt->execute([$iduser, $idmargin, $itemsJson]);
    $stmt->closeCursor();

    $res = $pdo->query("SELECT @out_id AS idpenjualan, @out_status AS status, @out_msg AS pesan")->fetch();
    return [
        'idpenjualan' => (int) $res->idpenjualan,
        'status'      => $res->status,
        'pesan'       => $res->pesan,
    ];
}

/**
 * Eksekusi pencatatan pemesanan pengadaan barang ke vendor via sp_transaksi_pengadaan
 */
function db_sp_transaksi_pengadaan(PDO $pdo, int $iduser, int $idvendor, array $items): array {
    $itemsJson = json_encode($items);
    $pdo->query("SET @out_id = 0, @out_status = '', @out_msg = ''");
    $stmt = $pdo->prepare("CALL sp_transaksi_pengadaan(?, ?, ?, @out_id, @out_status, @out_msg)");
    $stmt->execute([$iduser, $idvendor, $itemsJson]);
    $stmt->closeCursor();

    $res = $pdo->query("SELECT @out_id AS idpengadaan, @out_status AS status, @out_msg AS pesan")->fetch();
    return [
        'idpengadaan' => (int) $res->idpengadaan,
        'status'      => $res->status,
        'pesan'       => $res->pesan,
    ];
}

/**
 * Eksekusi pencatatan penerimaan fisik barang via sp_transaksi_penerimaan
 */
function db_sp_transaksi_penerimaan(PDO $pdo, int $idpengadaan, int $iduser, array $items): array {
    $itemsJson = json_encode($items);
    $pdo->query("SET @out_id = 0, @out_status = '', @out_msg = ''");
    $stmt = $pdo->prepare("CALL sp_transaksi_penerimaan(?, ?, ?, @out_id, @out_status, @out_msg)");
    $stmt->execute([$idpengadaan, $iduser, $itemsJson]);
    $stmt->closeCursor();

    $res = $pdo->query("SELECT @out_id AS idpenerimaan, @out_status AS status, @out_msg AS pesan")->fetch();
    return [
        'idpenerimaan' => (int) $res->idpenerimaan,
        'status'       => $res->status,
        'pesan'        => $res->pesan,
    ];
}

/**
 * Ambil riwayat buku besar mutasi stok via sp_laporan_kartu_stok
 */
function db_sp_laporan_kartu_stok(PDO $pdo, ?int $idbarang = null, ?string $tglMulai = null, ?string $tglSelesai = null): array {
    $stmt = $pdo->prepare("CALL sp_laporan_kartu_stok(?, ?, ?)");
    $stmt->execute([$idbarang, $tglMulai, $tglSelesai]);
    $data = $stmt->fetchAll();
    $stmt->closeCursor();
    return $data;
}


