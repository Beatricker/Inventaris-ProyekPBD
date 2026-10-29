<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MarginPenjualanController;
use App\Http\Controllers\PenjualanController;

/*
|--------------------------------------------------------------------------
| Web Routes - PutPutMart (Pemrograman Basis Data)
| Role-Based Access Control (RBAC): Admin, Gudang, Kasir
|--------------------------------------------------------------------------
*/

// Redirect halaman utama ke dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// ==========================================
// 1. AUTENTIKASI LOGIN & LOGOUT
// ==========================================
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ==========================================
// 2. PANEL & MASTER DATA DENGAN PEMBATASAN ROLE
// ==========================================
Route::middleware('auth.custom')->group(function () {
    // Dashboard utama (Dapat diakses seluruh role yang login)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ------------------------------------------
    // A. MODUL KASIR: TRANSAKSI PENJUALAN
    // Role: Admin, Kasir
    // ------------------------------------------
    Route::middleware('role:Admin,Kasir')->group(function () {
        Route::get('/penjualan', [PenjualanController::class, 'index'])->name('penjualan.index');
        Route::get('/penjualan/create', [PenjualanController::class, 'create'])->name('penjualan.create');
        Route::post('/penjualan', [PenjualanController::class, 'store'])->name('penjualan.store');
        Route::get('/penjualan/{id}', [PenjualanController::class, 'show'])->name('penjualan.show');
    });

    // ------------------------------------------
    // B. MODUL GUDANG & LOGISTIK: BARANG, SATUAN, VENDOR
    // Role: Admin, Gudang
    // Catatan: Kasir diizinkan melihat katalog barang (barang.index) untuk pengecekan harga
    // ------------------------------------------
    Route::get('/barang', [BarangController::class, 'index'])->name('barang.index')->middleware('role:Admin,Gudang,Kasir');

    Route::middleware('role:Admin,Gudang')->group(function () {
        // Kelola Master Barang (Create, Edit, Delete)
        Route::get('/barang/create', [BarangController::class, 'create'])->name('barang.create');
        Route::post('/barang', [BarangController::class, 'store'])->name('barang.store');
        Route::get('/barang/{id}/edit', [BarangController::class, 'edit'])->name('barang.edit');
        Route::put('/barang/{id}', [BarangController::class, 'update'])->name('barang.update');
        Route::delete('/barang/{id}', [BarangController::class, 'destroy'])->name('barang.destroy');

        // Master Satuan (Full CRUD)
        Route::get('/satuan', [SatuanController::class, 'index'])->name('satuan.index');
        Route::get('/satuan/create', [SatuanController::class, 'create'])->name('satuan.create');
        Route::post('/satuan', [SatuanController::class, 'store'])->name('satuan.store');
        Route::get('/satuan/{id}/edit', [SatuanController::class, 'edit'])->name('satuan.edit');
        Route::put('/satuan/{id}', [SatuanController::class, 'update'])->name('satuan.update');
        Route::delete('/satuan/{id}', [SatuanController::class, 'destroy'])->name('satuan.destroy');

        // Master Vendor (Full CRUD)
        Route::get('/vendor', [VendorController::class, 'index'])->name('vendor.index');
        Route::get('/vendor/create', [VendorController::class, 'create'])->name('vendor.create');
        Route::post('/vendor', [VendorController::class, 'store'])->name('vendor.store');
        Route::get('/vendor/{id}/edit', [VendorController::class, 'edit'])->name('vendor.edit');
        Route::put('/vendor/{id}', [VendorController::class, 'update'])->name('vendor.update');
        Route::delete('/vendor/{id}', [VendorController::class, 'destroy'])->name('vendor.destroy');
    });

    // ------------------------------------------
    // C. MODUL FINANSIAL: MARGIN PENJUALAN
    // Role: Admin (Full CRUD), Kasir (Lihat saja)
    // ------------------------------------------
    Route::get('/margin', [MarginPenjualanController::class, 'index'])->name('margin.index')->middleware('role:Admin,Kasir');
    
    // ------------------------------------------
    // D. MODUL SUPER ADMIN: USER, ROLE, & KELOLA MARGIN
    // Khusus Role: Admin / Super Admin
    // ------------------------------------------
    Route::middleware('role:Admin')->group(function () {
        // Kelola Margin Penjualan
        Route::get('/margin/create', [MarginPenjualanController::class, 'create'])->name('margin.create');
        Route::post('/margin', [MarginPenjualanController::class, 'store'])->name('margin.store');
        Route::get('/margin/{id}/edit', [MarginPenjualanController::class, 'edit'])->name('margin.edit');
        Route::put('/margin/{id}', [MarginPenjualanController::class, 'update'])->name('margin.update');
        Route::delete('/margin/{id}', [MarginPenjualanController::class, 'destroy'])->name('margin.destroy');

        // Master User (Full CRUD)
        Route::get('/user', [UserController::class, 'index'])->name('user.index');
        Route::get('/user/create', [UserController::class, 'create'])->name('user.create');
        Route::post('/user', [UserController::class, 'store'])->name('user.store');
        Route::get('/user/{id}/edit', [UserController::class, 'edit'])->name('user.edit');
        Route::put('/user/{id}', [UserController::class, 'update'])->name('user.update');
        Route::delete('/user/{id}', [UserController::class, 'destroy'])->name('user.destroy');

        // Master Role (Full CRUD)
        Route::get('/role', [RoleController::class, 'index'])->name('role.index');
        Route::get('/role/create', [RoleController::class, 'create'])->name('role.create');
        Route::post('/role', [RoleController::class, 'store'])->name('role.store');
        Route::get('/role/{id}/edit', [RoleController::class, 'edit'])->name('role.edit');
        Route::put('/role/{id}', [RoleController::class, 'update'])->name('role.update');
        Route::delete('/role/{id}', [RoleController::class, 'destroy'])->name('role.destroy');
    });
});
