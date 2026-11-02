<?php

/**
 * retur/detail.php
 * Rincian dokumen resmi bukti pengembalian (retur) barang cacat ke vendor.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
check_role(['Admin', 'Gudang']);

$idretur = (int)($_GET['id'] ?? 0);

if (!$idretur) {
    header('Location: ' . BASE_URL . '/retur/index.php');
    exit;
}

// Ambil data header retur
$stmt = $pdo->prepare("
    SELECT 
        r.idretur,
        r.created_at,
        r.idpenerimaan,
        COALESCE(u.username, 'Petugas Gudang') AS nama_petugas,
        COALESCE(v.nama_vendor, 'Vendor Mitra') AS nama_vendor,
        v.badan_hukum
    FROM retur r
    LEFT JOIN user u ON r.iduser = u.iduser
    LEFT JOIN penerimaan p ON r.idpenerimaan = p.idpenerimaan
    LEFT JOIN pengadaan pg ON p.idpengadaan = pg.idpengadaan
    LEFT JOIN vendor v ON pg.vendor_idvendor = v.idvendor
    WHERE r.idretur = ?
    LIMIT 1
");
$stmt->execute([$idretur]);
$retur = $stmt->fetch();

if (!$retur) {
    header('Location: ' . BASE_URL . '/retur/index.php');
    exit;
}

// Ambil rincian detail barang yang diretur
$stmtItems = $pdo->prepare("
    SELECT 
        dr.iddetail_retur,
        dr.jumlah,
        dr.alasan,
        b.idbarang,
        b.nama AS nama_barang,
        s.nama_satuan
    FROM detail_retur dr
    JOIN detail_penerimaan dp ON dr.iddetail_penerimaan = dp.iddetail_penerimaan
    JOIN barang b ON dp.barang_idbarang = b.idbarang
    JOIN satuan s ON b.idsatuan = s.idsatuan
    WHERE dr.idretur = ?
    ORDER BY dr.iddetail_retur ASC
");
$stmtItems->execute([$idretur]);
$items = $stmtItems->fetchAll();

$totalQtyRetur = 0;
foreach ($items as $it) {
    $totalQtyRetur += (int) $it->jumlah;
}

$page_title = 'Rincian Retur #' . str_pad($idretur, 5, '0', STR_PAD_LEFT);
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-xl-9">
        <!-- Editorial Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-3">
            <div>
                <h2 class="display-font mb-1" style="font-size: 2.1rem; font-weight: 500;">Surat Jalan Retur Barang</h2>
                <p class="text-muted mb-0" style="font-size: 0.98rem;">Dokumen resmi pengembalian barang cacat/rusak ke vendor &bull; Stok otomatis dipotong.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>/retur/index.php" class="btn btn-clear">
                    <i class="bi bi-arrow-left"></i> Riwayat Retur
                </a>
                <a href="<?= BASE_URL ?>/barang/index.php" class="btn btn-outline-primary">
                    <i class="bi bi-box-seam"></i> Cek Stok di Katalog &rarr;
                </a>
            </div>
        </div>

        <!-- Receipt Card Container -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted" style="font-size: 0.85rem;">Nomor Dokumen Retur:</span>
                    <h5 class="mb-0 fw-bold display-font text-danger">
                        #RET-<?= str_pad($retur->idretur, 5, '0', STR_PAD_LEFT) ?>
                    </h5>
                </div>
                <div>
                    <span class="badge badge-refract-red px-3 py-2">
                        <i class="bi bi-arrow-return-left me-1"></i> Stok Fisik Dipotong
                    </span>
                </div>
            </div>
            <div class="card-body p-4">
                <!-- Meta Info Strip -->
                <div class="row g-4 mb-4 pb-3" style="border-bottom: 1px solid var(--color-rule);">
                    <div class="col-sm-6 col-md-3">
                        <div class="text-muted" style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em;">Mitra Pemasok Tujuan</div>
                        <div class="fw-semibold text-dark mt-1">
                            <i class="bi bi-truck text-muted me-1"></i><?= htmlspecialchars($retur->nama_vendor) ?>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="text-muted" style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em;">Waktu Retur</div>
                        <div class="fw-semibold text-dark mt-1">
                            <?= date('d M Y, H:i', strtotime($retur->created_at)) ?> WIB
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="text-muted" style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em;">Dokumen Asal</div>
                        <div class="fw-semibold mt-1">
                            <?php if ($retur->idpenerimaan): ?>
                                <a href="<?= BASE_URL ?>/penerimaan/detail.php?id=<?= $retur->idpenerimaan ?>" class="tabular text-decoration-none fw-semibold" style="color: var(--color-accent);">
                                    #RCV-<?= str_pad($retur->idpenerimaan, 5, '0', STR_PAD_LEFT) ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="text-muted" style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em;">Petugas Penanggung Jawab</div>
                        <div class="fw-semibold text-dark mt-1">
                            <i class="bi bi-person text-muted me-1"></i><?= htmlspecialchars($retur->nama_petugas) ?>
                        </div>
                    </div>
                </div>

                <!-- Tabel Item Barang yang Diretur -->
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-2" style="width: 50px;">No</th>
                                <th>Nama Produk</th>
                                <th>Satuan</th>
                                <th class="text-center">Jumlah Diretur</th>
                                <th class="pe-2">Alasan Pengembalian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($items as $it): ?>
                                <tr>
                                    <td class="ps-2 tabular text-muted"><?= $no++ ?></td>
                                    <td class="fw-semibold text-dark"><?= htmlspecialchars($it->nama_barang) ?></td>
                                    <td><?= htmlspecialchars($it->nama_satuan) ?></td>
                                    <td class="text-center tabular fw-bold text-danger">
                                        -<?= number_format($it->jumlah, 0, ',', '.') ?>
                                    </td>
                                    <td class="pe-2 text-muted">
                                        <i class="bi bi-chat-left-dots text-secondary me-1"></i>
                                        <?= htmlspecialchars($it->alasan) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="border-top: 2px solid var(--color-rule); background: #fbfcfe;">
                                <td colspan="3" class="ps-2 fw-bold">Total Barang Dikembalikan ke Vendor:</td>
                                <td class="text-center fw-bold tabular text-danger">
                                    -<?= number_format($totalQtyRetur, 0, ',', '.') ?> unit
                                </td>
                                <td class="pe-2 text-muted fst-italic">Saldo kartu stok telah dipotong otomatis.</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Action Bottom Bar -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <a href="<?= BASE_URL ?>/retur/index.php" class="btn btn-clear">
                &larr; Kembali ke Daftar Retur
            </a>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-clear" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Cetak Dokumen
                </button>
                <a href="<?= BASE_URL ?>/retur/tambah.php" class="btn btn-danger">
                    <i class="bi bi-plus-lg me-1"></i> Catat Retur Baru
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
