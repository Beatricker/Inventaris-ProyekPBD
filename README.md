# PutPutMart - Aplikasi Penjual (Native PHP)

Sistem Monolitik Manajemen Master Data & Inventaris Toko berbasis **Native PHP + MySQL PDO + Bootstrap 5**.
Dibuat untuk memenuhi proyek tugas praktikum **Pemrograman Basis Data (Semester 3)**.

---

### Cara Menjalankan Aplikasi

#### Cara 1: Menggunakan PHP Built-in Server (Sangat Mudah)
Buka terminal di folder proyek ini (`PutPutMart`), lalu ketik:
```bash
php -S 127.0.0.1:8000
```
Buka browser dan akses: **`http://127.0.0.1:8000`**

#### Cara 2: Menggunakan Laragon / XAMPP Apache
1. Pastikan folder `PutPutMart` diletakkan di dalam folder `www` (Laragon) atau `htdocs` (XAMPP).
2. Nyalakan service Apache dan MySQL.
3. Buka browser dan akses: **`http://localhost/PutPutMart`**

---

### Akun Pengujian (Klik Cepat di Halaman Login)

1. **Super Admin**: `admin_pusat` / `admin123` (Hak akses penuh ke 6 tabel master)
2. **Gudang**: `gudang_utama` / `gudang123` (Hak akses logistik: Satuan, Barang, Vendor)
3. **Kasir**: `kasir_satu` / `kasir123` (Hak akses read-only: Katalog Barang, Info Margin)

---
