<?php

namespace Tests\Feature;

use Tests\TestCase;

class PutPutMartTest extends TestCase
{
    /**
     * Test root / otomatis mengarahkan ke dashboard.
     */
    public function test_halaman_utama_redirect_ke_dashboard(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('dashboard'));
    }

    /**
     * Test halaman login dapat diakses dengan sukses.
     */
    public function test_halaman_login_dapat_diakses(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('PutPutMart');
    }

    /**
     * Test proses login berhasil dengan akun Admin via Raw SQL.
     */
    public function test_login_berhasil_dengan_akun_admin(): void
    {
        $response = $this->post('/login', [
            'username' => 'admin_pusat',
            'password' => 'admin123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('user');
        $this->assertEquals('Admin', session('role'));
    }

    /**
     * Test proses login berhasil dengan akun Kasir via Raw SQL.
     */
    public function test_login_berhasil_dengan_akun_kasir(): void
    {
        $response = $this->post('/login', [
            'username' => 'kasir_satu',
            'password' => 'kasir123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('user');
        $this->assertEquals('Kasir', session('role'));
    }

    /**
     * Test proses login berhasil dengan akun Gudang via Raw SQL.
     */
    public function test_login_berhasil_dengan_akun_gudang(): void
    {
        $response = $this->post('/login', [
            'username' => 'gudang_utama',
            'password' => 'gudang123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('user');
        $this->assertEquals('Gudang', session('role'));
    }

    /**
     * Test hak akses Super Admin: Memiliki akses ke seluruh modul.
     */
    public function test_role_admin_memiliki_akses_penuh(): void
    {
        $adminSession = [
            'user' => (object)['iduser' => 1, 'username' => 'admin_pusat', 'idrole' => 1, 'nama_role' => 'Admin'],
            'iduser' => 1,
            'username' => 'admin_pusat',
            'role' => 'Admin',
            'idrole' => 1,
        ];

        // Master Data
        $this->withSession($adminSession)->get('/satuan')->assertStatus(200);
        $this->withSession($adminSession)->get('/satuan/create')->assertStatus(200);
        $this->withSession($adminSession)->get('/satuan/1/edit')->assertStatus(200);

        $this->withSession($adminSession)->get('/barang')->assertStatus(200);
        $this->withSession($adminSession)->get('/barang/create')->assertStatus(200);
        $this->withSession($adminSession)->get('/barang/1/edit')->assertStatus(200);

        $this->withSession($adminSession)->get('/vendor')->assertStatus(200);
        $this->withSession($adminSession)->get('/vendor/create')->assertStatus(200);
        $this->withSession($adminSession)->get('/vendor/1/edit')->assertStatus(200);

        $this->withSession($adminSession)->get('/user')->assertStatus(200);
        $this->withSession($adminSession)->get('/user/create')->assertStatus(200);
        $this->withSession($adminSession)->get('/user/1/edit')->assertStatus(200);

        $this->withSession($adminSession)->get('/role')->assertStatus(200);
        $this->withSession($adminSession)->get('/role/create')->assertStatus(200);
        $this->withSession($adminSession)->get('/role/1/edit')->assertStatus(200);

        $this->withSession($adminSession)->get('/margin')->assertStatus(200);
        $this->withSession($adminSession)->get('/margin/create')->assertStatus(200);
        $this->withSession($adminSession)->get('/margin/1/edit')->assertStatus(200);

        // Transaksi Penjualan
        $this->withSession($adminSession)->get('/penjualan')->assertStatus(200);
        $this->withSession($adminSession)->get('/penjualan/create')->assertStatus(200);
    }

    /**
     * Test hak akses Petugas Gudang: Hanya mengelola Satuan, Barang, dan Vendor.
     * Tidak boleh mengakses User, Role, Margin, atau Penjualan.
     */
    public function test_role_gudang_terbatas_pada_logistik(): void
    {
        $gudangSession = [
            'user' => (object)['iduser' => 4, 'username' => 'gudang_utama', 'idrole' => 3, 'nama_role' => 'Gudang'],
            'iduser' => 4,
            'username' => 'gudang_utama',
            'role' => 'Gudang',
            'idrole' => 3,
        ];

        // Yang diizinkan
        $this->withSession($gudangSession)->get('/satuan')->assertStatus(200);
        $this->withSession($gudangSession)->get('/barang')->assertStatus(200);
        $this->withSession($gudangSession)->get('/vendor')->assertStatus(200);

        // Yang DITOLAK
        $this->withSession($gudangSession)->get('/user')->assertRedirect(route('dashboard'));
        $this->withSession($gudangSession)->get('/role')->assertRedirect(route('dashboard'));
        $this->withSession($gudangSession)->get('/penjualan')->assertRedirect(route('dashboard'));
    }

    /**
     * Test hak akses Kasir: Mengakses Penjualan, katalog Barang (lihat harga), dan Margin (info).
     * Tidak boleh mengakses User, Role, Vendor, Satuan, atau Tambah Barang.
     */
    public function test_role_kasir_terbatas_pada_penjualan(): void
    {
        $kasirSession = [
            'user' => (object)['iduser' => 2, 'username' => 'kasir_satu', 'idrole' => 2, 'nama_role' => 'Kasir'],
            'iduser' => 2,
            'username' => 'kasir_satu',
            'role' => 'Kasir',
            'idrole' => 2,
        ];

        // Yang diizinkan
        $this->withSession($kasirSession)->get('/penjualan')->assertStatus(200);
        $this->withSession($kasirSession)->get('/penjualan/create')->assertStatus(200);
        $this->withSession($kasirSession)->get('/barang')->assertStatus(200); // Lihat daftar harga
        $this->withSession($kasirSession)->get('/margin')->assertStatus(200); // Info margin

        // Yang DITOLAK
        $this->withSession($kasirSession)->get('/user')->assertRedirect(route('dashboard'));
        $this->withSession($kasirSession)->get('/role')->assertRedirect(route('dashboard'));
        $this->withSession($kasirSession)->get('/satuan')->assertRedirect(route('dashboard'));
        $this->withSession($kasirSession)->get('/vendor')->assertRedirect(route('dashboard'));
        $this->withSession($kasirSession)->get('/barang/create')->assertRedirect(route('dashboard'));
    }

    /**
     * Test transaksi penjualan kasir berhasil disimpan dengan Raw SQL.
     */
    public function test_transaksi_penjualan_berhasil_disimpan(): void
    {
        $kasirSession = [
            'user' => (object)['iduser' => 2, 'username' => 'kasir_satu', 'idrole' => 2, 'nama_role' => 'Kasir'],
            'iduser' => 2,
            'username' => 'kasir_satu',
            'role' => 'Kasir',
            'idrole' => 2,
        ];

        $response = $this->withSession($kasirSession)->post('/penjualan', [
            'idbarang' => [1, 2],
            'jumlah' => [2, 1],
            'idmargin_penjualan' => 1,
        ]);

        $response->assertSessionHas('success');
    }

    /**
     * Test memastikan modul dan link transaksi kasir tidak ditampilkan di UI/sidebar/dashboard saat ini.
     */
    public function test_transaksi_kasir_tidak_ditampilkan_di_tampilan(): void
    {
        $adminSession = [
            'user' => (object)['iduser' => 1, 'username' => 'admin_pusat', 'idrole' => 1, 'nama_role' => 'Admin'],
            'iduser' => 1,
            'username' => 'admin_pusat',
            'role' => 'Admin',
            'idrole' => 1,
        ];

        $response = $this->withSession($adminSession)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertDontSee('Transaksi Kasir');
        $response->assertDontSee('Transaksi Kasir Baru');
        $response->assertDontSee('Total Transaksi Kasir');

        // Pastikan ke-6 Master Tabel tetap tampil di dashboard Super Admin
        $response->assertSee('1. Master Satuan');
        $response->assertSee('2. Master Barang');
        $response->assertSee('3. Master Vendor');
        $response->assertSee('4. Master User');
        $response->assertSee('5. Master Role');
        $response->assertSee('6. Margin Penjualan');

        $kasirSession = [
            'user' => (object)['iduser' => 2, 'username' => 'kasir_satu', 'idrole' => 2, 'nama_role' => 'Kasir'],
            'iduser' => 2,
            'username' => 'kasir_satu',
            'role' => 'Kasir',
            'idrole' => 2,
        ];
        $responseKasir = $this->withSession($kasirSession)->get('/dashboard');
        $responseKasir->assertStatus(200);
        $responseKasir->assertDontSee('Transaksi Kasir');
        $responseKasir->assertDontSee('Transaksi Penjualan Terakhir');
        $responseKasir->assertSee('Katalog Barang Aktif');
        $responseKasir->assertSee('Margin Penjualan');
    }
}
