<?php

/**
 * pengadaan/detail.php
 * Lembar dokumen resmi Purchase Order (PO) pengadaan barang ke mitra vendor.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
check_role(['Admin']);

$idpengadaan = (int)($_GET['id'] ?? 0);

if (!$idpengadaan) {
    header('Location: ' . BASE_URL . '/pengadaan/index.php');
    exit;
}

// 1. Ambil data header pengadaan
$stmt = $pdo->prepare("
    SELECT 
        pg.idpengadaan,
        pg.timestamp,
        pg.status,
        pg.subtotal_nilai,
        pg.ppn,
        pg.total_nilai,
        COALESCE(u.username, 'Admin') AS nama_petugas,
        COALESCE(r.nama_role, 'Admin') AS role_petugas,
        v.idvendor,
        COALESCE(v.nama_vendor, 'Vendor Mitra') AS nama_vendor,
        v.badan_hukum,
        v.status AS status_vendor
    FROM pengadaan pg
    LEFT JOIN user u ON pg.user_iduser = u.iduser
    LEFT JOIN role r ON u.idrole = r.idrole
    LEFT JOIN vendor v ON pg.vendor_idvendor = v.idvendor
    WHERE pg.idpengadaan = ?
    LIMIT 1
");
$stmt->execute([$idpengadaan]);
$pengadaan = $stmt->fetch();

if (!$pengadaan) {
    set_flash('error', 'Dokumen pengadaan tidak ditemukan!');
    header('Location: ' . BASE_URL . '/pengadaan/index.php');
    exit;
}

// 2. Ambil rincian barang dalam detail_pengadaan
$stmtItems = $pdo->prepare("
    SELECT 
        dp.iddetail_pengadaan,
        dp.idbarang,
        b.nama AS nama_barang,
        s.nama_satuan,
        dp.jumlah,
        dp.harga_satuan,
        dp.sub_total
    FROM detail_pengadaan dp
    JOIN barang b ON dp.idbarang = b.idbarang
    JOIN satuan s ON b.idsatuan = s.idsatuan
    WHERE dp.idpengadaan = ?
    ORDER BY dp.iddetail_pengadaan ASC
");
$stmtItems->execute([$idpengadaan]);
$itemList = $stmtItems->fetchAll();

// 3. Cek apakah pengadaan ini sudah pernah dicatat di penerimaan fisik gudang
$stmtTerima = $pdo->prepare("
    SELECT 
        pn.idpenerimaan,
        pn.created_at,
        COALESCE(u.username, 'Petugas Gudang') AS nama_petugas_gudang
    FROM penerimaan pn
    LEFT JOIN user u ON pn.iduser = u.iduser
    WHERE pn.idpengadaan = ?
    LIMIT 1
");
$stmtTerima->execute([$idpengadaan]);
$penerimaanGudang = $stmtTerima->fetch();

$page_title = 'Purchase Order #PO-' . str_pad($pengadaan->idpengadaan, 5, '0', STR_PAD_LEFT);
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-xl-9">
        <!-- Top Toolbar (Non-printable) -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3 d-print-none">
            <div class="d-flex align-items-center gap-2">
                <a href="<?= BASE_URL ?>/pengadaan/index.php" class="btn btn-clear">
                    <i class="bi bi-arrow-left"></i> Kembali ke Riwayat
                </a>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-clear" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Cetak Dokumen PO
                </button>
                <?php if (!$penerimaanGudang && count($itemList) > 0): ?>
                    <a href="<?= BASE_URL ?>/penerimaan/tambah.php?idpengadaan=<?= $pengadaan->idpengadaan ?>" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-down me-1"></i> Terima Barang di Gudang
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Banner Status Fisik Gudang (Non-printable) -->
        <div class="d-print-none mb-4">
            <?php if ($penerimaanGudang): ?>
                <div class="alert alert-success d-flex align-items-center justify-content-between" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                        <div>
                            <strong>Pengadaan ini telah selesai diterima secara fisik di gudang!</strong><br>
                            <small class="text-muted">
                                Diterima pada <?= date('d M Y - H:i', strtotime($penerimaanGudang->created_at)) ?> WIB oleh <?= htmlspecialchars($penerimaanGudang->nama_petugas_gudang) ?>.
                            </small>
                        </div>
                    </div>
                    <a href="<?= BASE_URL ?>/penerimaan/detail.php?id=<?= $penerimaanGudang->idpenerimaan ?>" class="btn btn-sm btn-outline-success">
                        Buka Bukti Penerimaan (#RCV-<?= str_pad($penerimaanGudang->idpenerimaan, 5, '0', STR_PAD_LEFT) ?>) &rarr;
                    </a>
                </div>
            <?php else: ?>
                <div class="alert alert-warning d-flex align-items-center justify-content-between" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-hourglass-split fs-4 me-3"></i>
                        <div>
                            <strong>Menunggu Pengiriman Fisik dari Vendor.</strong><br>
                            <small class="text-muted">Pesanan belum dicatat sebagai barang masuk di gudang.</small>
                        </div>
                    </div>
                    <?php if (count($itemList) > 0): ?>
                        <a href="<?= BASE_URL ?>/penerimaan/tambah.php?idpengadaan=<?= $pengadaan->idpengadaan ?>" class="btn btn-sm btn-primary">
                            Proses Penerimaan Fisik Sekarang &rarr;
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Printable PO Sheet (Refract Paper Layout) -->
        <div class="card p-4 p-md-5 mb-5" id="printablePO" style="box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
            <!-- Document Header -->
            <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="wordmark__ring" style="width: 20px; height: 20px;"></span>
                        <h4 class="fw-bold mb-0" style="letter-spacing: -0.5px;">PutPutMart</h4>
                    </div>
                    <p class="text-muted mb-0 small">Sistem Manajemen Kasir &amp; Inventaris Terpadu</p>
                    <p class="text-muted mb-0 small">Dokumen Resmi Pemesanan Pengadaan Barang</p>
                </div>
                <div class="text-end">
                    <div class="badge badge-refract-blue mb-2 px-3 py-2" style="font-size: 0.82rem;">
                        SURAT PEMESANAN (PO)
                    </div>
                    <div class="display-font fw-bold fs-4" style="color: var(--color-accent);">
                        #PO-<?= str_pad($pengadaan->idpengadaan, 5, '0', STR_PAD_LEFT) ?>
                    </div>
                    <small class="text-muted tabular">
                        Tanggal: <?= date('d M Y - H:i', strtotime($pengadaan->timestamp)) ?> WIB
                    </small>
                </div>
            </div>

            <!-- Meta Data: Vendor & Pemesan -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="p-3 rounded" style="background: #f8fafc; border: 1px solid var(--border-color);">
                        <div class="text-muted small fw-semibold text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            Ditujukan Kepada (Vendor Pemasok):
                        </div>
                        <h5 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($pengadaan->nama_vendor) ?></h5>
                        <div class="badge badge-refract-gray mb-1">
                            Badan Hukum: <?= htmlspecialchars($pengadaan->badan_hukum ?? 'Perusahaan') ?>
                        </div>
                        <div class="small text-muted">
                            Status Kemitraan: <?= $pengadaan->status_vendor == 1 ? '<span class="text-success fw-medium">Mitra Aktif</span>' : '<span class="text-muted">Non-Aktif</span>' ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 rounded" style="background: #f8fafc; border: 1px solid var(--border-color);">
                        <div class="text-muted small fw-semibold text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            Diterbitkan Oleh (Pemohon):
                        </div>
                        <h5 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($pengadaan->nama_petugas) ?></h5>
                        <div class="badge badge-refract-blue mb-1">
                            Role: <?= htmlspecialchars($pengadaan->role_petugas) ?>
                        </div>
                        <div class="small text-muted">
                            Status Pengadaan: <span class="badge badge-refract-green">Disetujui / Aktif (<?= $pengadaan->status ?>)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Rincian Barang yang Dipesan -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle mb-0" style="border-color: var(--border-color);">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th>Nama Barang / Produk</th>
                            <th class="text-center" style="width: 120px;">Satuan</th>
                            <th class="text-center" style="width: 130px;">Jumlah Pesan</th>
                            <th class="text-end" style="width: 170px;">Harga Satuan</th>
                            <th class="text-end" style="width: 190px;">Subtotal (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($itemList)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    Tidak ada item barang dalam dokumen pengadaan ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($itemList as $item): ?>
                                <tr>
                                    <td class="text-center tabular text-muted"><?= $no++ ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($item->nama_barang) ?></div>
                                        <small class="text-muted">Kode SKU: #BRG-<?= str_pad($item->idbarang, 4, '0', STR_PAD_LEFT) ?></small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-refract-gray"><?= htmlspecialchars($item->nama_satuan) ?></span>
                                    </td>
                                    <td class="text-center tabular fw-bold">
                                        <?= number_format($item->jumlah, 0, ',', '.') ?>
                                    </td>
                                    <td class="text-end tabular text-muted">
                                        Rp <?= number_format($item->harga_satuan, 0, ',', '.') ?>
                                    </td>
                                    <td class="text-end tabular fw-semibold" style="color: var(--color-accent);">
                                        Rp <?= number_format($item->sub_total, 0, ',', '.') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Ringkasan Finansial Tagihan Pengadaan -->
            <div class="row justify-content-end mb-4">
                <div class="col-md-6 col-lg-5">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Subtotal Nilai Barang:</td>
                            <td class="text-end tabular fw-semibold">
                                Rp <?= number_format($pengadaan->subtotal_nilai, 0, ',', '.') ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Pajak Pertambahan Nilai (PPN 11%):</td>
                            <td class="text-end tabular fw-semibold">
                                Rp <?= number_format($pengadaan->ppn, 0, ',', '.') ?>
                            </td>
                        </tr>
                        <tr style="border-top: 2px solid var(--border-color);">
                            <td class="fw-bold fs-6 pt-2">Total Tagihan (Grand Total):</td>
                            <td class="text-end tabular fw-bold fs-5 pt-2" style="color: var(--color-accent);">
                                Rp <?= number_format($pengadaan->total_nilai, 0, ',', '.') ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Tanda Tangan & Konfirmasi (Printable) -->
            <div class="row g-4 mt-4 pt-3 border-top">
                <div class="col-6 text-center">
                    <p class="text-muted small mb-5">Pihak Pemesan (PutPutMart)</p>
                    <div class="fw-bold text-dark border-bottom d-inline-block px-4 pb-1">
                        <?= htmlspecialchars($pengadaan->nama_petugas) ?>
                    </div>
                    <div class="text-muted small mt-1">Petugas Pengadaan / <?= htmlspecialchars($pengadaan->role_petugas) ?></div>
                </div>
                <div class="col-6 text-center">
                    <p class="text-muted small mb-5">Pihak Vendor Mitra Pemasok</p>
                    <div class="fw-bold text-dark border-bottom d-inline-block px-4 pb-1">
                        ( .................................................. )
                    </div>
                    <div class="text-muted small mt-1"><?= htmlspecialchars($pengadaan->nama_vendor) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body {
        background-color: #fff !important;
        color: #000 !important;
    }
    .app-sidebar, .app-topbar, .app-footer, .btn, .alert, .d-print-none {
        display: none !important;
    }
    .app-main {
        margin: 0 !important;
        padding: 0 !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
    }
    #printablePO {
        width: 100% !important;
        margin: 0 !important;
    }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
