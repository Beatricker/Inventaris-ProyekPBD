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
