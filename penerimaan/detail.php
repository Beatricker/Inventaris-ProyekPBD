<?php

/**
 * penerimaan/detail.php
 * Rincian dokumen bukti tanda terima barang fisik di gudang.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
check_role(['Admin', 'Gudang']);

$idpenerimaan = (int)($_GET['id'] ?? 0);

if (!$idpenerimaan) {
    header('Location: ' . BASE_URL . '/penerimaan/index.php');
    exit;
}

// Ambil data header penerimaan
$stmt = $pdo->prepare("
    SELECT 
        p.idpenerimaan,
        p.created_at,
        p.status,
        p.idpengadaan,
        COALESCE(u.username, 'Petugas Gudang') AS nama_petugas,
        r.nama_role,
        COALESCE(v.nama_vendor, 'Vendor Mitra') AS nama_vendor,
        v.badan_hukum
    FROM penerimaan p
    LEFT JOIN user u ON p.iduser = u.iduser
    LEFT JOIN role r ON u.idrole = r.idrole
    LEFT JOIN pengadaan pg ON p.idpengadaan = pg.idpengadaan
    LEFT JOIN vendor v ON pg.vendor_idvendor = v.idvendor
    WHERE p.idpenerimaan = ?
    LIMIT 1
");
$stmt->execute([$idpenerimaan]);
$penerimaan = $stmt->fetch();

if (!$penerimaan) {
    header('Location: ' . BASE_URL . '/penerimaan/index.php');
    exit;
}

// Ambil rincian detail barang yang diterima
$stmtItems = $pdo->prepare("
    SELECT 
        dp.iddetail_penerimaan,
        dp.barang_idbarang,
        b.nama AS nama_barang,
        s.nama_satuan,
        dp.jumlah_terima,
        dp.harga_satuan_terima,
        dp.sub_total_terima
    FROM detail_penerimaan dp
    JOIN barang b ON dp.barang_idbarang = b.idbarang
    JOIN satuan s ON b.idsatuan = s.idsatuan
    WHERE dp.idpenerimaan = ?
    ORDER BY dp.iddetail_penerimaan ASC
");
$stmtItems->execute([$idpenerimaan]);
$items = $stmtItems->fetchAll();

$totalQty = 0;
$totalNilai = 0;
foreach ($items as $it) {
    $totalQty += (int) $it->jumlah_terima;
    $totalNilai += (int) $it->sub_total_terima;
}

$page_title = 'Rincian Penerimaan #' . str_pad($idpenerimaan, 5, '0', STR_PAD_LEFT);
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-xl-9">
        <!-- Editorial Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-3">
            <div>
                <h2 class="display-font mb-1" style="font-size: 2.1rem; font-weight: 500;">Bukti Penerimaan Barang</h2>
                <p class="text-muted mb-0" style="font-size: 0.98rem;">Dokumen resmi barang masuk gudang &bull; Saldo kartu stok otomatis bertambah.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>/penerimaan/index.php" class="btn btn-clear">
                    <i class="bi bi-arrow-left"></i> Riwayat
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
                    <span class="text-muted" style="font-size: 0.85rem;">Nomor Dokumen Penerimaan:</span>
                    <h5 class="mb-0 fw-bold display-font" style="color: var(--color-accent);">
                        #RCV-<?= str_pad($penerimaan->idpenerimaan, 5, '0', STR_PAD_LEFT) ?>
                    </h5>
                </div>
                <div>
                    <span class="badge badge-refract-green px-3 py-2">
                        <i class="bi bi-check-circle me-1"></i> Stok Berhasil Masuk
                    </span>
                </div>
            </div>
            <div class="card-body p-4">
                <!-- Meta Info Strip -->
                <div class="row g-4 mb-4 pb-3" style="border-bottom: 1px solid var(--color-rule);">
                    <div class="col-sm-6 col-md-3">
                        <div class="text-muted" style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em;">Mitra Pemasok</div>
                        <div class="fw-semibold text-dark mt-1">
                            <i class="bi bi-truck text-muted me-1"></i><?= htmlspecialchars($penerimaan->nama_vendor) ?>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="text-muted" style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em;">Waktu Penerimaan</div>
                        <div class="fw-semibold text-dark mt-1">
                            <?= date('d M Y, H:i', strtotime($penerimaan->created_at)) ?> WIB
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="text-muted" style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em;">Petugas Penerima</div>
                        <div class="fw-semibold text-dark mt-1">
                            <i class="bi bi-person text-muted me-1"></i><?= htmlspecialchars($penerimaan->nama_petugas) ?>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="text-muted" style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em;">Status Mutasi</div>
                        <div class="fw-semibold mt-1" style="color: var(--color-accent);">
                            <i class="bi bi-journal-check me-1"></i> Tercatat di Kartu Stok
                        </div>
                    </div>
                </div>

                <!-- Tabel Item Barang -->
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-2" style="width: 50px;">No</th>
                                <th>Nama Produk</th>
                                <th>Satuan</th>
                                <th class="text-center">Jumlah Masuk</th>
                                <th class="text-end">Harga Beli Satuan</th>
                                <th class="pe-2 text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($items as $it): ?>
                                <tr>
                                    <td class="ps-2 tabular text-muted"><?= $no++ ?></td>
                                    <td class="fw-semibold text-dark"><?= htmlspecialchars($it->nama_barang) ?></td>
                                    <td><?= htmlspecialchars($it->nama_satuan) ?></td>
                                    <td class="text-center tabular fw-bold" style="color: var(--color-accent);">
                                        +<?= number_format($it->jumlah_terima, 0, ',', '.') ?>
                                    </td>
                                    <td class="text-end tabular text-muted">
                                        Rp <?= number_format($it->harga_satuan_terima, 0, ',', '.') ?>
                                    </td>
                                    <td class="pe-2 text-end tabular fw-medium text-dark">
                                        Rp <?= number_format($it->sub_total_terima, 0, ',', '.') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="border-top: 2px solid var(--color-rule); background: #fbfcfe;">
                                <td colspan="3" class="ps-2 fw-bold">Ringkasan Total:</td>
                                <td class="text-center fw-bold tabular" style="color: var(--color-accent);">
                                    +<?= number_format($totalQty, 0, ',', '.') ?> unit
                                </td>
                                <td></td>
                                <td class="pe-2 text-end fw-bold tabular fs-6" style="color: var(--color-accent);">
                                    Rp <?= number_format($totalNilai, 0, ',', '.') ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Action Bottom Bar -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <a href="<?= BASE_URL ?>/penerimaan/index.php" class="btn btn-clear">
                &larr; Kembali ke Daftar Penerimaan
            </a>
            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>/retur/tambah.php?idpenerimaan=<?= $penerimaan->idpenerimaan ?>" class="btn btn-outline-danger">
                    <i class="bi bi-arrow-return-left me-1"></i> Ajukan Retur Barang
                </a>
                <button type="button" class="btn btn-clear" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Cetak Dokumen
                </button>
                <a href="<?= BASE_URL ?>/penerimaan/tambah.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Catat Penerimaan Baru
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
