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

### Struktur Folder Proyek

```text
PutPutMart/
├── config/
│   ├── database.php       # Koneksi PDO ke MySQL 'proyek_pbd' & auto BASE_URL
│   └── auth.php           # Helper cek login, cek role, dan flash message
├── includes/
│   ├── header.php         # Head, Bootstrap 5 CSS, topbar profil
│   ├── sidebar.php        # Menu samping dinamis per Role
│   └── footer.php         # Footer & Bootstrap 5 JS
├── auth/
│   ├── login.php          # Form login dengan tombol autofill akun uji coba
│   └── logout.php         # Logout & hapus session
├── index.php              # Gerbang utama (redirect otomatis)
├── dashboard.php          # Dashboard ringkasan master data
├── satuan/                # CRUD Master Satuan (v_satuan)
├── barang/                # CRUD Master Barang (v_barang)
├── vendor/                # CRUD Master Vendor (v_vendor)
├── user/                  # CRUD Master User (v_user, SHA2-256)
├── role/                  # CRUD Master Role (v_role)
├── margin/                # CRUD Master Margin Penjualan (v_margin)
└── _laravel_backup/       # Arsip proyek Laravel sebelumnya
```
