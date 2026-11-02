<?php

/**
 * pengadaan/tambah.php
 * Form pembuatan pemesanan pengadaan barang (Purchase Order / PO) ke vendor.
 * Menggunakan Stored Procedure sp_transaksi_pengadaan dan Trigger perhitungan PPN/Total.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
check_role(['Admin']);

$error = '';
$success = '';

// Ambil daftar vendor aktif
$vendorList = $pdo->query("SELECT idvendor, nama_vendor, badan_hukum FROM vendor WHERE status = 1 ORDER BY nama_vendor ASC")->fetchAll();

// Ambil daftar barang aktif (beserta harga modal, satuan, dan sisa stok terkini)
$barangList = $pdo->query("SELECT idbarang, nama_barang, nama_satuan, harga, stok FROM v_barang WHERE status = 1 ORDER BY nama_barang ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idvendor = (int)($_POST['idvendor'] ?? 0);
    $barangIds = $_POST['idbarang'] ?? [];
    $quantities = $_POST['jumlah'] ?? [];
    $prices = $_POST['harga_satuan'] ?? [];

    $iduser = (int)($_SESSION['iduser'] ?? 1);

    if (empty($idvendor)) {
        $error = 'Silakan pilih Mitra Vendor pemasok!';
    } elseif (empty($barangIds) || !is_array($barangIds)) {
        $error = 'Minimal satu barang harus dimasukkan dalam dokumen pengadaan!';
    } else {
        $itemsToProcess = [];
        for ($i = 0; $i < count($barangIds); $i++) {
            $idb = (int)($barangIds[$i] ?? 0);
            $qty = (int)($quantities[$i] ?? 0);
            $prc = (int)($prices[$i] ?? 0);

            if ($idb > 0 && $qty > 0) {
                $itemsToProcess[] = [
                    'idbarang'     => $idb,
                    'jumlah'       => $qty,
                    'harga_satuan' => $prc,
                ];
            }
        }

        if (empty($itemsToProcess)) {
            $error = 'Jumlah unit barang yang dipesan harus lebih dari 0!';
        } else {
            try {
                // Eksekusi Stored Procedure sp_transaksi_pengadaan
                // SP ini secara ACID memasukkan data ke tabel pengadaan & detail_pengadaan.
                // Database Trigger trg_detail_pengadaan_ai akan otomatis menghitung subtotal,
                // PPN 11% via fn_hitung_ppn(), dan total_nilai via fn_hitung_total_transaksi().
                $result = db_sp_transaksi_pengadaan($pdo, $iduser, $idvendor, $itemsToProcess);

                if ($result['status'] === 'SUCCESS') {
                    set_flash('success', 'Dokumen Pengadaan (#PO-' . str_pad($result['idpengadaan'], 5, '0', STR_PAD_LEFT) . ') berhasil diterbitkan.');
                    header('Location: ' . BASE_URL . '/pengadaan/detail.php?id=' . $result['idpengadaan']);
                    exit;
                } else {
                    $error = 'Gagal menyimpan pengadaan: ' . $result['pesan'];
                }
            } catch (Exception $e) {
                $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
            }
        }
    }
}

$page_title = 'Buat Pengadaan Barang Baru';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-xl-10">
        <!-- Editorial Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-3">
            <div>
                <h2 class="display-font mb-1" style="font-size: 2.1rem; font-weight: 500;">Pemesanan Pengadaan Barang</h2>
                <p class="text-muted mb-0" style="font-size: 0.98rem;">Menerbitkan pesanan pembelian (Purchase Order) baru kepada vendor mitra resmi.</p>
            </div>
            <div>
                <a href="<?= BASE_URL ?>/pengadaan/index.php" class="btn btn-clear">
                    <i class="bi bi-arrow-left"></i> Kembali ke Riwayat
                </a>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                <div><?= htmlspecialchars($error) ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="formPengadaan">
            <!-- 1. Informasi Vendor & Pemohon -->
            <div class="card mb-4">
                <div class="card-header">
                    <span class="fw-bold"><i class="bi bi-truck me-2" style="color: var(--color-accent);"></i>Informasi Vendor Pemasok</span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="idvendor" class="form-label">Mitra Vendor Dituju <span class="text-danger">*</span></label>
                            <select name="idvendor" id="idvendor" class="form-select" required>
                                <option value="">-- Pilih Mitra Vendor Pemasok --</option>
                                <?php foreach ($vendorList as $v): ?>
                                    <option value="<?= $v->idvendor ?>" <?= (isset($_POST['idvendor']) && $_POST['idvendor'] == $v->idvendor) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($v->nama_vendor) ?> (<?= htmlspecialchars($v->badan_hukum) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Pilih vendor pemasok resmi yang akan menerima Purchase Order ini.</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Petugas Pengada</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($username) ?> (<?= ucfirst($userRole) ?>)" readonly style="background-color: #f8fafc;">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Waktu Pengajuan</label>
                            <input type="text" class="form-control" value="<?= date('d M Y - H:i') ?> WIB" readonly style="background-color: #f8fafc;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Rincian Barang yang Dipesan -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-box-seam me-2" style="color: var(--color-accent);"></i>Rincian Barang yang Dipesan</span>
                    <button type="button" class="btn btn-clear btn-sm text-primary" id="btnTambahBaris">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Baris Barang
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" id="tabelBarang">
                            <thead>
                                <tr>
                                    <th class="ps-4" style="min-width: 280px;">Nama Produk</th>
                                    <th style="width: 130px;">Satuan</th>
                                    <th style="width: 140px;">Sisa Stok Gudang</th>
                                    <th style="width: 150px;">Jumlah Pesan <span class="text-danger">*</span></th>
                                    <th style="width: 180px;">Harga Satuan (Rp) <span class="text-danger">*</span></th>
                                    <th style="width: 180px;">Subtotal (Rp)</th>
                                    <th class="pe-4 text-center" style="width: 60px;">Hapus</th>
                                </tr>
                            </thead>
                            <tbody id="containerBaris">
                                <!-- Baris Pertama Default -->
                                <tr class="baris-barang">
                                    <td class="ps-4">
                                        <select name="idbarang[]" class="form-select select-barang" required>
                                            <option value="">-- Pilih Barang --</option>
                                            <?php foreach ($barangList as $b): ?>
                                                <option value="<?= $b->idbarang ?>" 
                                                        data-satuan="<?= htmlspecialchars($b->nama_satuan) ?>" 
                                                        data-harga="<?= $b->harga ?>" 
                                                        data-stok="<?= $b->stok ?>">
                                                    <?= htmlspecialchars($b->nama_barang) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <span class="badge badge-refract-gray span-satuan">-</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-refract-blue span-stok">0</span>
                                    </td>
                                    <td>
                                        <input type="number" name="jumlah[]" class="form-control input-qty text-center" min="1" placeholder="Qty" required>
                                    </td>
                                    <td>
                                        <input type="number" name="harga_satuan[]" class="form-control input-harga text-end" min="0" placeholder="0" required>
                                    </td>
                                    <td class="tabular fw-semibold text-end span-subtotal" style="color: var(--color-accent);">
                                        Rp 0
                                    </td>
                                    <td class="pe-4 text-center">
                                        <button type="button" class="btn btn-action-icon btn-action-danger btn-hapus-baris" title="Hapus Baris">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 3. Ringkasan Finansial Real-time (Subtotal, PPN 11%, Grand Total) -->
            <div class="row justify-content-end mb-4">
                <div class="col-md-5 col-lg-4">
                    <div class="card p-3" style="background: #f8fafc; border: 1px solid var(--border-color);">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal Barang:</span>
                            <span class="fw-semibold tabular" id="displaySubtotal">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Pajak PPN (11%):</span>
                            <span class="fw-semibold tabular" id="displayPPN">Rp 0</span>
                        </div>
                        <hr class="my-2" style="border-color: var(--border-color);">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold fs-6">Grand Total:</span>
                            <span class="fw-bold fs-5 tabular" style="color: var(--color-accent);" id="displayTotal">Rp 0</span>
                        </div>
                        <small class="text-muted mt-2 d-block" style="font-size: 0.76rem;">
                            * Subtotal, PPN 11%, dan Grand Total dikunci secara konsisten di database via stored function &amp; trigger.
                        </small>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi Simpan -->
            <div class="d-flex justify-content-end gap-2 mb-5">
                <a href="<?= BASE_URL ?>/pengadaan/index.php" class="btn btn-clear">Batal</a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check2-circle me-1"></i> Terbitkan Dokumen Pengadaan (PO)
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('containerBaris');
    const btnTambah = document.getElementById('btnTambahBaris');
    const displaySubtotal = document.getElementById('displaySubtotal');
    const displayPPN = document.getElementById('displayPPN');
    const displayTotal = document.getElementById('displayTotal');

    function formatRupiah(num) {
        return 'Rp ' + Number(num).toLocaleString('id-ID');
    }

    function hitungRingkasanTotal() {
        let subtotalTotal = 0;
        const rows = container.querySelectorAll('.baris-barang');

        rows.forEach(row => {
            const qtyInput = row.querySelector('.input-qty');
            const hargaInput = row.querySelector('.input-harga');
            const subtotalSpan = row.querySelector('.span-subtotal');

            const qty = parseInt(qtyInput.value) || 0;
            const harga = parseInt(hargaInput.value) || 0;
            const rowSubtotal = qty * harga;

            subtotalSpan.textContent = formatRupiah(rowSubtotal);
            subtotalTotal += rowSubtotal;
        });

        const ppnTotal = Math.round(subtotalTotal * 0.11);
        const grandTotal = subtotalTotal + ppnTotal;

        displaySubtotal.textContent = formatRupiah(subtotalTotal);
        displayPPN.textContent = formatRupiah(ppnTotal);
        displayTotal.textContent = formatRupiah(grandTotal);
    }

    function initRowEvents(row) {
        const selectBarang = row.querySelector('.select-barang');
        const spanSatuan = row.querySelector('.span-satuan');
        const spanStok = row.querySelector('.span-stok');
        const inputHarga = row.querySelector('.input-harga');
        const inputQty = row.querySelector('.input-qty');
        const btnHapus = row.querySelector('.btn-hapus-baris');

        selectBarang.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            if (this.value) {
                spanSatuan.textContent = selected.dataset.satuan || '-';
                spanStok.textContent = Number(selected.dataset.stok || 0).toLocaleString('id-ID');
                if (!inputHarga.value || inputHarga.value === '0') {
                    inputHarga.value = selected.dataset.harga || '0';
                }
            } else {
                spanSatuan.textContent = '-';
                spanStok.textContent = '0';
                inputHarga.value = '';
            }
            hitungRingkasanTotal();
        });

        inputQty.addEventListener('input', hitungRingkasanTotal);
        inputHarga.addEventListener('input', hitungRingkasanTotal);

        btnHapus.addEventListener('click', function() {
            const totalRows = container.querySelectorAll('.baris-barang').length;
            if (totalRows > 1) {
                row.remove();
                hitungRingkasanTotal();
            } else {
                alert('Minimal harus ada 1 baris barang dalam pengadaan!');
            }
        });
    }

    // Inisialisasi baris pertama
    initRowEvents(container.querySelector('.baris-barang'));

    // Tambah baris baru
    btnTambah.addEventListener('click', function() {
        const firstRow = container.querySelector('.baris-barang');
        const newRow = firstRow.cloneNode(true);

        // Reset input nilai di baris baru
        newRow.querySelector('.select-barang').selectedIndex = 0;
        newRow.querySelector('.span-satuan').textContent = '-';
        newRow.querySelector('.span-stok').textContent = '0';
        newRow.querySelector('.input-qty').value = '';
        newRow.querySelector('.input-harga').value = '';
        newRow.querySelector('.span-subtotal').textContent = 'Rp 0';

        initRowEvents(newRow);
        container.appendChild(newRow);
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
