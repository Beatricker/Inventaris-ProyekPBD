<?php
/**
 * index.php
 * Halaman gerbang utama (Root Redirect).
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

if (isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/dashboard.php');
} else {
    header('Location: ' . BASE_URL . '/auth/login.php');
}
exit;
