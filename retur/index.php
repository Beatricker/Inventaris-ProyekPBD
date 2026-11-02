<?php

/**
 * retur/index.php
 * Riwayat transaksi pengembalian (retur) barang cacat/rusak ke vendor.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
check_role(['Admin', 'Gudang']);

$page_title = 'Retur Barang';
require_once __DIR__ . '/../includes/header.php';

// Ambil riwayat retur barang beserta vendor & no penerimaan asal
$query = "
    SELECT 
        r.idretur,
        r.created_at,
        r.idpenerimaan,
        COALESCE(u.username, 'Sistem') AS nama_petugas,
        COALESCE(v.nama_vendor, 'Vendor Mitra') AS nama_vendor,
        COUNT(dr.iddetail_retur) AS total_item,
        COALESCE(SUM(dr.jumlah), 0) AS total_unit_retur
    FROM retur r
    LEFT JOIN user u ON r.iduser = u.iduser
    LEFT JOIN penerimaan p ON r.idpenerimaan = p.idpenerimaan
    LEFT JOIN pengadaan pg ON p.idpengadaan = pg.idpengadaan
    LEFT JOIN vendor v ON pg.vendor_idvendor = v.idvendor
    LEFT JOIN detail_retur dr ON r.idretur = dr.idretur
    GROUP BY r.idretur, r.created_at, r.idpenerimaan, u.username, v.nama_vendor
    ORDER BY r.idretur DESC
";
$returList = $pdo->query($query)->fetchAll();
?>

<!-- Editorial Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-3">
    <div>
        <h2 class="display-font mb-1" style="font-size: 2.1rem; font-weight: 500;">Retur Barang ke Vendor</h2>
        <p class="text-muted mb-0" style="font-size: 0.98rem;">Catatan pengembalian barang cacat/rusak ke vendor yang otomatis memotong saldo kartu stok fisik.</p>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/retur/tambah.php" class="btn btn-primary">
            <i class="bi bi-arrow-return-left"></i> Catat Retur Barang Baru
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" style="width: 130px;">No. Retur</th>
                        <th>Waktu Retur</th>
                        <th>Dokumen Asal</th>
                        <th>Tujuan Vendor</th>
                        <th>Petugas Gudang</th>
                        <th class="text-center">Total Item</th>
                        <th class="text-center">Jumlah Diretur</th>
                        <th class="pe-4 text-end" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($returList)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-muted opacity-50"></i>
                                Belum ada riwayat pengembalian (retur) barang.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($returList as $r): ?>
                            <tr>
                                <td class="ps-4 tabular text-muted fw-semibold">
                                    #RET-<?= str_pad($r->idretur, 5, '0', STR_PAD_LEFT) ?>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= date('d M Y', strtotime($r->created_at)) ?></div>
                                    <small class="text-muted"><?= date('H:i', strtotime($r->created_at)) ?> WIB</small>
                                </td>
                                <td>
                                    <?php if ($r->idpenerimaan): ?>
                                        <a href="<?= BASE_URL ?>/penerimaan/detail.php?id=<?= $r->idpenerimaan ?>" class="tabular text-decoration-none fw-medium" style="color: var(--color-accent);">
                                            #RCV-<?= str_pad($r->idpenerimaan, 5, '0', STR_PAD_LEFT) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <i class="bi bi-truck text-muted me-1"></i>
                                    <?= htmlspecialchars($r->nama_vendor) ?>
                                </td>
                                <td>
                                    <span class="badge badge-refract-blue">
                                        <i class="bi bi-person me-1"></i><?= htmlspecialchars($r->nama_petugas) ?>
                                    </span>
                                </td>
                                <td class="text-center tabular">
                                    <span class="badge badge-refract-gray"><?= $r->total_item ?> jenis</span>
                                </td>
                                <td class="text-center tabular fw-semibold text-danger">
                                    -<?= number_format($r->total_unit_retur, 0, ',', '.') ?> unit
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="<?= BASE_URL ?>/retur/detail.php?id=<?= $r->idretur ?>" class="btn btn-clear btn-sm">
                                        <i class="bi bi-receipt me-1"></i> Surat Retur &rarr;
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
