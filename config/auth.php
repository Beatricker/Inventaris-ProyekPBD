<?php
/**
 * config/auth.php
 * Pengelolaan sesi login dan Role-Based Access Control (RBAC).
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Pastikan user telah login sebelum mengakses halaman.
 */
function check_login() {
    if (!isset($_SESSION['user'])) {
        set_flash('error', 'Silakan login terlebih dahulu untuk mengakses sistem.');
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

/**
 * Validasi hak akses berdasarkan role pengguna.
 * Super Admin selalu diizinkan mengakses semua halaman.
 * 
 * @param array $allowedRoles Daftar role yang diizinkan (contoh: ['Admin', 'Gudang'])
 */
function check_role(array $allowedRoles = []) {
    check_login();

    $userRole = strtolower(trim($_SESSION['role'] ?? ''));
    $normalizedAllowed = array_map(function($r) {
        return strtolower(trim($r));
    }, $allowedRoles);

    // Super Admin memiliki akses penuh ke seluruh modul
    if ($userRole === 'admin' || in_array($userRole, $normalizedAllowed)) {
        return true;
    }

    set_flash('error', "Akses Ditolak! Role '{$_SESSION['role']}' tidak memiliki izin mengakses halaman tersebut.");
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

/**
 * Set pesan notifikasi flash (success, error, warning).
 */
function set_flash($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

/**
 * Ambil dan hapus pesan flash agar hanya tampil satu kali.
 */
function get_flash($type) {
    if (isset($_SESSION['flash'][$type])) {
        $msg = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $msg;
    }
    return null;
}

/**
 * Mengambil username yang sedang login.
 */
function auth_username() {
    return $_SESSION['username'] ?? 'User';
}

/**
 * Mengambil nama role pengguna yang sedang login.
 */
function auth_role() {
    return $_SESSION['role'] ?? '';
}

/**
 * Mengambil ID user yang sedang login.
 */
function auth_iduser() {
    return $_SESSION['iduser'] ?? 0;
}
