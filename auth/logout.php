<?php
/**
 * auth/logout.php
 * Hapus seluruh session dan arahkan kembali ke login.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

session_start();
set_flash('success', 'Anda telah berhasil keluar dari sistem.');
header('Location: ' . BASE_URL . '/auth/login.php');
exit;
