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
 * Mengambil sisa stok barang terkini dari tabel kartu_stok via 1-Function resmi: fn_get_sisa_stok()
 */
function db_get_sisa_stok(PDO $pdo, int $idbarang): int {
    $stmt = $pdo->prepare("SELECT fn_get_sisa_stok(?) AS sisa");
    $stmt->execute([$idbarang]);
    return (int) $stmt->fetchColumn();
}

/**
 * Menghitung harga jual barang sesuai margin aktif
 */
function db_hitung_harga_jual(PDO $pdo, int $idbarang, ?int $idmargin = null): int {
    $stmt = $pdo->prepare("SELECT harga FROM barang WHERE idbarang = ?");
    $stmt->execute([$idbarang]);
    $hargaDasar = (int) $stmt->fetchColumn();

    $persen = 10.0;
    if ($idmargin) {
        $stmtM = $pdo->prepare("SELECT persen FROM margin_penjualan WHERE idmargin_penjualan = ?");
        $stmtM->execute([$idmargin]);
        $persen = (float) ($stmtM->fetchColumn() ?: 10.0);
    } else {
        $stmtM = $pdo->query("SELECT persen FROM margin_penjualan WHERE status = 1 LIMIT 1");
        $persen = (float) ($stmtM->fetchColumn() ?: 10.0);
    }
    return (int) round($hargaDasar * (1 + $persen / 100));
}

/**
 * Menghitung nilai PPN dari subtotal (11%)
 */
function db_hitung_ppn(int $subtotal, float $persen = 11.0): int {
    return (int) round($subtotal * ($persen / 100));
}

/**
 * Menghitung total nilai akhir transaksi
 */
function db_hitung_total_transaksi(int $subtotal, int $ppn): int {
    return $subtotal + $ppn;
}

// -----------------------------------------------------------------------------
// Helper Pemanggil Stored Procedure & Transaksi ACID
// -----------------------------------------------------------------------------

/**
 * Eksekusi pencatatan penerimaan fisik barang via Stored Procedure sp_transaksi_penerimaan (1-SP resmi).
 * Setiap baris detail_penerimaan yang dimasukkan otomatis memicu Trigger trg_update_stok_penerimaan
 * yang memanggil Function fn_get_sisa_stok untuk memperbarui saldo kartu_stok.
 */
function db_sp_transaksi_penerimaan(PDO $pdo, int $idpengadaan, int $iduser, array $items): array {
    if (empty($items)) {
        return ['idpenerimaan' => 0, 'status' => 'INVALID', 'pesan' => 'Daftar rincian barang yang diterima tidak boleh kosong.'];
    }

    try {
        $firstItem = $items[0];
        $pdo->query("SET @out_id = 0");
        $stmt = $pdo->prepare("CALL sp_transaksi_penerimaan(?, ?, ?, ?, ?, @out_id)");
        $stmt->execute([
            $idpengadaan,
            $iduser,
            $firstItem['idbarang'],
            $firstItem['jumlah_terima'],
            $firstItem['harga_satuan_terima'],
        ]);
        $stmt->closeCursor();

        $idpenerimaan = (int) $pdo->query("SELECT @out_id")->fetchColumn();

        if (count($items) > 1 && $idpenerimaan > 0) {
            $stmtDetail = $pdo->prepare("
                INSERT INTO detail_penerimaan (idpenerimaan, barang_idbarang, jumlah_terima, harga_satuan_terima, sub_total_terima)
                VALUES (?, ?, ?, ?, ?)
            ");
            for ($i = 1; $i < count($items); $i++) {
                $it = $items[$i];
                $stmtDetail->execute([
                    $idpenerimaan,
                    $it['idbarang'],
                    $it['jumlah_terima'],
                    $it['harga_satuan_terima'],
                    ($it['jumlah_terima'] * $it['harga_satuan_terima']),
                ]);
            }
        }

        return [
            'idpenerimaan' => $idpenerimaan,
            'status'       => 'SUCCESS',
            'pesan'        => 'Penerimaan barang berhasil dicatat dan saldo stok bertambah otomatis.',
        ];
    } catch (Exception $e) {
        return [
            'idpenerimaan' => 0,
            'status'       => 'ERROR',
            'pesan'        => $e->getMessage(),
        ];
    }
}

/**
 * Eksekusi transaksi pemesanan pengadaan barang (PO)
 */
function db_sp_transaksi_pengadaan(PDO $pdo, int $iduser, int $idvendor, array $items): array {
    if (empty($items)) {
        return ['idpengadaan' => 0, 'status' => 'INVALID', 'pesan' => 'Daftar barang tidak boleh kosong.'];
    }

    $pdo->beginTransaction();
    try {
        $subtotal = 0;
        foreach ($items as $it) {
            $subtotal += ((int)$it['jumlah'] * (int)$it['harga_satuan']);
        }
        $ppn = (int) round($subtotal * 0.11);
        $total = $subtotal + $ppn;

        $stmtPO = $pdo->prepare("
            INSERT INTO pengadaan (timestamp, user_iduser, status, vendor_idvendor, subtotal_nilai, ppn, total_nilai)
            VALUES (NOW(), ?, 'A', ?, ?, ?, ?)
        ");
        $stmtPO->execute([$iduser, $idvendor, $subtotal, $ppn, $total]);
        $idpengadaan = (int) $pdo->lastInsertId();

        $stmtDP = $pdo->prepare("
            INSERT INTO detail_pengadaan (harga_satuan, jumlah, sub_total, idbarang, idpengadaan)
            VALUES (?, ?, ?, ?, ?)
        ");
        foreach ($items as $it) {
            $sub = (int)$it['jumlah'] * (int)$it['harga_satuan'];
            $stmtDP->execute([(int)$it['harga_satuan'], (int)$it['jumlah'], $sub, (int)$it['idbarang'], $idpengadaan]);
        }

        $pdo->commit();
        return [
            'idpengadaan' => $idpengadaan,
            'status'      => 'SUCCESS',
            'pesan'       => 'Pemesanan pengadaan barang berhasil dicatat.',
        ];
    } catch (Exception $e) {
        $pdo->rollBack();
        return [
            'idpengadaan' => 0,
            'status'      => 'ERROR',
            'pesan'       => $e->getMessage(),
        ];
    }
}

/**
 * Eksekusi transaksi retur pengembalian barang cacat ke vendor
 */
function db_sp_transaksi_retur(PDO $pdo, int $idpenerimaan, int $iduser, array $items): array {
    if (empty($items)) {
        return ['idretur' => 0, 'status' => 'INVALID', 'pesan' => 'Daftar retur tidak boleh kosong.'];
    }

    $pdo->beginTransaction();
    try {
        $stmtRetur = $pdo->prepare("
            INSERT INTO retur (created_at, status, idpenerimaan, iduser)
            VALUES (NOW(), 'A', ?, ?)
        ");
        $stmtRetur->execute([$idpenerimaan, $iduser]);
        $idretur = (int) $pdo->lastInsertId();

        $stmtDR = $pdo->prepare("
            INSERT INTO detail_retur (jumlah, alasan, iddetail_penerimaan, idretur)
            VALUES (?, ?, ?, ?)
        ");
        $stmtBarang = $pdo->prepare("SELECT barang_idbarang FROM detail_penerimaan WHERE iddetail_penerimaan = ? LIMIT 1");
        $stmtKS = $pdo->prepare("
            INSERT INTO kartu_stok (jenis_transaksi, masuk, keluar, stock, created_at, idtransaksi, idbarang)
            VALUES ('R', 0, ?, ?, NOW(), ?, ?)
        ");

        foreach ($items as $it) {
            $stmtDR->execute([(int)$it['jumlah'], $it['alasan'], (int)$it['iddetail_penerimaan'], $idretur]);

            $stmtBarang->execute([(int)$it['iddetail_penerimaan']]);
            $idbarang = (int) $stmtBarang->fetchColumn();

            $stokLama = db_get_sisa_stok($pdo, $idbarang);
            $stokBaru = $stokLama - (int)$it['jumlah'];
            $stmtKS->execute([(int)$it['jumlah'], $stokBaru, $idretur, $idbarang]);
        }

        $pdo->commit();
        return [
            'idretur' => $idretur,
            'status'  => 'SUCCESS',
            'pesan'   => 'Retur barang berhasil dicatat.',
        ];
    } catch (Exception $e) {
        $pdo->rollBack();
        return [
            'idretur' => 0,
            'status'  => 'ERROR',
            'pesan'   => $e->getMessage(),
        ];
    }
}

/**
 * Eksekusi transaksi kasir penjualan
 */
function db_sp_transaksi_penjualan(PDO $pdo, int $iduser, ?int $idmargin, array $items): array {
    if (empty($items)) {
        return ['idpenjualan' => 0, 'status' => 'INVALID', 'pesan' => 'Keranjang belanja tidak boleh kosong.'];
    }

    $pdo->beginTransaction();
    try {
        $subtotal = 0;
        foreach ($items as $it) {
            $subtotal += ((int)$it['jumlah'] * (int)$it['harga_satuan']);
        }
        $ppn = (int) round($subtotal * 0.11);
        $total = $subtotal + $ppn;

        $stmtPJ = $pdo->prepare("
            INSERT INTO penjualan (created_at, subtotal_nilai, ppn, total_nilai, iduser, idmargin_penjualan)
            VALUES (NOW(), ?, ?, ?, ?, ?)
        ");
        $stmtPJ->execute([$subtotal, $ppn, $total, $iduser, $idmargin]);
        $idpenjualan = (int) $pdo->lastInsertId();

        $stmtDP = $pdo->prepare("
            INSERT INTO detail_penjualan (penjualan_idpenjualan, idbarang, jumlah, harga_satuan, subtotal)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmtKS = $pdo->prepare("
            INSERT INTO kartu_stok (jenis_transaksi, masuk, keluar, stock, created_at, idtransaksi, idbarang)
            VALUES ('K', 0, ?, ?, NOW(), ?, ?)
        ");

        foreach ($items as $it) {
            $sub = (int)$it['jumlah'] * (int)$it['harga_satuan'];
            $stmtDP->execute([$idpenjualan, (int)$it['idbarang'], (int)$it['jumlah'], (int)$it['harga_satuan'], $sub]);

            $stokLama = db_get_sisa_stok($pdo, (int)$it['idbarang']);
            $stokBaru = $stokLama - (int)$it['jumlah'];
            $stmtKS->execute([(int)$it['jumlah'], $stokBaru, $idpenjualan, (int)$it['idbarang']]);
        }

        $pdo->commit();
        return [
            'idpenjualan' => $idpenjualan,
            'status'      => 'SUCCESS',
            'pesan'       => 'Penjualan berhasil dicatat.',
        ];
    } catch (Exception $e) {
        $pdo->rollBack();
        return [
            'idpenjualan' => 0,
            'status'      => 'ERROR',
            'pesan'       => $e->getMessage(),
        ];
    }
}


