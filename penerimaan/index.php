<?php

/**
 * penerimaan/index.php
 * Riwayat transaksi penerimaan barang masuk ke gudang dari vendor.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
check_role(['Admin', 'Gudang']);

$page_title = 'Penerimaan Barang';
require_once __DIR__ . '/../includes/header.php';

// Ambil riwayat penerimaan barang beserta ringkasan item & vendor
$query = "
    SELECT 
        p.idpenerimaan,
        p.created_at,
        p.status,
        p.idpengadaan,
        COALESCE(u.username, 'Sistem') AS nama_petugas,
        COALESCE(v.nama_vendor, 'Vendor Umum / Langsung') AS nama_vendor,
        COUNT(dp.iddetail_penerimaan) AS total_item,
        COALESCE(SUM(dp.jumlah_terima), 0) AS total_qty,
        COALESCE(SUM(dp.sub_total_terima), 0) AS total_nilai
    FROM penerimaan p
    LEFT JOIN user u ON p.iduser = u.iduser
    LEFT JOIN pengadaan pg ON p.idpengadaan = pg.idpengadaan
    LEFT JOIN vendor v ON pg.vendor_idvendor = v.idvendor
    LEFT JOIN detail_penerimaan dp ON p.idpenerimaan = dp.idpenerimaan
    GROUP BY p.idpenerimaan, p.created_at, p.status, p.idpengadaan, u.username, v.nama_vendor
    ORDER BY p.idpenerimaan DESC
";
$penerimaanList = $pdo->query($query)->fetchAll();
?>

<!-- Editorial Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-3">
    <div>
        <h2 class="display-font mb-1" style="font-size: 2.1rem; font-weight: 500;">Penerimaan Barang Gudang</h2>
        <p class="text-muted mb-0" style="font-size: 0.98rem;">Catatan logistik barang masuk dari vendor yang langsung menambah saldo kartu stok fisik.</p>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/penerimaan/tambah.php" class="btn btn-primary">
            <i class="bi bi-box-arrow-in-down"></i> Catat Penerimaan Barang Baru
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" style="width: 130px;">No. Bukti</th>
                        <th>Waktu Masuk</th>
                        <th>Mitra Vendor</th>
                        <th>Petugas Gudang</th>
                        <th class="text-center">Total Item</th>
                        <th class="text-center">Total Unit</th>
                        <th class="pe-4 text-end" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($penerimaanList)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-muted opacity-50"></i>
                                Belum ada riwayat penerimaan barang masuk.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($penerimaanList as $p): ?>
                            <tr>
                                <td class="ps-4 tabular text-muted fw-semibold">
                                    #RCV-<?= str_pad($p->idpenerimaan, 5, '0', STR_PAD_LEFT) ?>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= date('d M Y', strtotime($p->created_at)) ?></div>
                                    <small class="text-muted"><?= date('H:i', strtotime($p->created_at)) ?> WIB</small>
                                </td>
                                <td>
                                    <i class="bi bi-truck text-muted me-1"></i>
                                    <?= htmlspecialchars($p->nama_vendor) ?>
                                </td>
                                <td>
                                    <span class="badge badge-refract-blue">
                                        <i class="bi bi-person me-1"></i><?= htmlspecialchars($p->nama_petugas) ?>
                                    </span>
                                </td>
                                <td class="text-center tabular">
                                    <span class="badge badge-refract-gray"><?= $p->total_item ?> jenis</span>
                                </td>
                                <td class="text-center tabular fw-semibold" style="color: var(--color-accent);">
                                    +<?= number_format($p->total_qty, 0, ',', '.') ?> unit
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="<?= BASE_URL ?>/penerimaan/detail.php?id=<?= $p->idpenerimaan ?>" class="btn btn-clear btn-sm">
                                        <i class="bi bi-receipt me-1"></i> Rincian &rarr;
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
