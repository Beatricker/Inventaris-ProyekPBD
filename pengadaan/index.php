<?php

/**
 * pengadaan/index.php
 * Riwayat pemesanan pengadaan barang (Purchase Order) ke mitra vendor.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
check_role(['Admin']);

$page_title = 'Pengadaan Barang';
require_once __DIR__ . '/../includes/header.php';

// Ambil parameter filter
$filterVendor = (int)($_GET['vendor'] ?? 0);
$filterStatusGudang = trim($_GET['status_gudang'] ?? '');
$filterTglMulai = trim($_GET['tgl_mulai'] ?? '');
$filterTglSelesai = trim($_GET['tgl_selesai'] ?? '');

// Susun klausa WHERE dinamis
$where = ["1=1"];
$params = [];

if ($filterVendor > 0) {
    $where[] = "pg.vendor_idvendor = ?";
    $params[] = $filterVendor;
}

if ($filterTglMulai !== '') {
    $where[] = "DATE(pg.timestamp) >= ?";
    $params[] = $filterTglMulai;
}

if ($filterTglSelesai !== '') {
    $where[] = "DATE(pg.timestamp) <= ?";
    $params[] = $filterTglSelesai;
}

if ($filterStatusGudang === 'diterima') {
    $where[] = "pn.idpenerimaan IS NOT NULL";
} elseif ($filterStatusGudang === 'belum') {
    $where[] = "pn.idpenerimaan IS NULL";
}

$whereClause = implode(" AND ", $where);

// Query utama pengadaan beserta info vendor, user, agregasi item, dan status penerimaan fisik di gudang
$query = "
    SELECT 
        pg.idpengadaan,
        pg.timestamp,
        pg.status,
        pg.subtotal_nilai,
        pg.ppn,
        pg.total_nilai,
        COALESCE(u.username, 'Sistem') AS nama_petugas,
        COALESCE(r.nama_role, 'Admin') AS role_petugas,
        v.idvendor,
        COALESCE(v.nama_vendor, 'Vendor Tidak Dikenal') AS nama_vendor,
        v.badan_hukum,
        COUNT(dp.iddetail_pengadaan) AS total_item,
        COALESCE(SUM(dp.jumlah), 0) AS total_qty,
        pn.idpenerimaan AS id_penerimaan_gudang,
        pn.created_at AS tgl_penerimaan_gudang
    FROM pengadaan pg
    LEFT JOIN user u ON pg.user_iduser = u.iduser
    LEFT JOIN role r ON u.idrole = r.idrole
    LEFT JOIN vendor v ON pg.vendor_idvendor = v.idvendor
    LEFT JOIN detail_pengadaan dp ON pg.idpengadaan = dp.idpengadaan
    LEFT JOIN penerimaan pn ON pg.idpengadaan = pn.idpengadaan
    WHERE $whereClause
    GROUP BY pg.idpengadaan, pg.timestamp, pg.status, pg.subtotal_nilai, pg.ppn, pg.total_nilai, 
             u.username, r.nama_role, v.idvendor, v.nama_vendor, v.badan_hukum, pn.idpenerimaan, pn.created_at
    ORDER BY pg.idpengadaan DESC
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$pengadaanList = $stmt->fetchAll();

// Hitung statistik ringkasan kartu KPI
$kpiTotalPO = count($pengadaanList);
$kpiTotalNilai = 0;
$kpiTotalPPN = 0;
$vendorIds = [];

foreach ($pengadaanList as $item) {
    $kpiTotalNilai += (int)$item->total_nilai;
    $kpiTotalPPN += (int)$item->ppn;
    if (!empty($item->idvendor)) {
        $vendorIds[$item->idvendor] = true;
    }
}
$kpiTotalVendor = count($vendorIds);

// Ambil daftar vendor aktif untuk dropdown filter
$daftarVendor = $pdo->query("SELECT idvendor, nama_vendor, badan_hukum FROM vendor WHERE status = 1 ORDER BY nama_vendor ASC")->fetchAll();
?>

<!-- 1. Editorial Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-3">
    <div>
        <h2 class="display-font mb-1" style="font-size: 2.1rem; font-weight: 500;">Pengadaan Barang (Purchase Order)</h2>
        <p class="text-muted mb-0" style="font-size: 0.98rem;">Pemesanan dan rencana pengadaan stok barang resmi ke mitra vendor pemasok.</p>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/pengadaan/tambah.php" class="btn btn-primary">
            <i class="bi bi-cart-plus me-1"></i> Buat Pengadaan Baru
        </a>
    </div>
</div>

<!-- 2. Refract Unified Metric Board -->
<div class="refract-strip mb-4">
    <div class="row g-0">
        <div class="col-6 col-md-3 refract-strip__cell">
            <div class="refract-strip__label">Total Pengadaan (PO)</div>
            <div class="refract-strip__num tabular"><?= number_format($kpiTotalPO, 0, ',', '.') ?></div>
            <span class="refract-strip__sub">Dokumen Terbit</span>
        </div>
        <div class="col-6 col-md-3 refract-strip__cell">
            <div class="refract-strip__label">Total Nilai Pengadaan</div>
            <div class="refract-strip__num tabular" style="color: var(--color-accent); font-size: 1.65rem;">
                Rp <?= number_format($kpiTotalNilai, 0, ',', '.') ?>
            </div>
            <span class="refract-strip__sub">Nilai Komitmen Bruto</span>
        </div>
        <div class="col-6 col-md-3 refract-strip__cell">
            <div class="refract-strip__label">Total PPN Terhitung (11%)</div>
            <div class="refract-strip__num tabular">
                Rp <?= number_format($kpiTotalPPN, 0, ',', '.') ?>
            </div>
            <span class="refract-strip__sub">Pajak Pertambahan Nilai</span>
        </div>
        <div class="col-6 col-md-3 refract-strip__cell">
            <div class="refract-strip__label">Mitra Vendor Terlibat</div>
            <div class="refract-strip__num tabular"><?= $kpiTotalVendor ?></div>
            <span class="refract-strip__sub">Pemasok Aktif</span>
        </div>
    </div>
</div>

<!-- 3. Filter Bar Multi-Kriteria -->
<div class="card mb-4">
    <div class="card-body p-3">
        <form method="GET" action="<?= BASE_URL ?>/pengadaan/index.php" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="vendor" class="form-label small text-muted mb-1">Mitra Vendor</label>
                <select name="vendor" id="vendor" class="form-select form-select-sm">
                    <option value="">-- Semua Mitra Vendor --</option>
                    <?php foreach ($daftarVendor as $v): ?>
                        <option value="<?= $v->idvendor ?>" <?= $filterVendor === (int)$v->idvendor ? 'selected' : '' ?>>
                            <?= htmlspecialchars($v->nama_vendor) ?> (<?= htmlspecialchars($v->badan_hukum) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="status_gudang" class="form-label small text-muted mb-1">Status Penerimaan Gudang</label>
                <select name="status_gudang" id="status_gudang" class="form-select form-select-sm">
                    <option value="">-- Semua Status Gudang --</option>
                    <option value="belum" <?= $filterStatusGudang === 'belum' ? 'selected' : '' ?>>Menunggu Gudang (Belum Diterima)</option>
                    <option value="diterima" <?= $filterStatusGudang === 'diterima' ? 'selected' : '' ?>>Selesai Diterima di Gudang</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="tgl_mulai" class="form-label small text-muted mb-1">Dari Tanggal</label>
                <input type="date" name="tgl_mulai" id="tgl_mulai" class="form-control form-select-sm" value="<?= htmlspecialchars($filterTglMulai) ?>">
            </div>
            <div class="col-md-2">
                <label for="tgl_selesai" class="form-label small text-muted mb-1">Sampai Tanggal</label>
                <input type="date" name="tgl_selesai" id="tgl_selesai" class="form-control form-select-sm" value="<?= htmlspecialchars($filterTglSelesai) ?>">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                    <i class="bi bi-filter"></i> Saring
                </button>
                <a href="<?= BASE_URL ?>/pengadaan/index.php" class="btn btn-clear btn-sm" title="Reset Filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- 4. Tabel Riwayat Pengadaan -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" style="width: 130px;">No. PO</th>
                        <th>Waktu Pengajuan</th>
                        <th>Mitra Vendor</th>
                        <th>Petugas Pengada</th>
                        <th class="text-center">Item</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Subtotal</th>
                        <th class="text-end">Total Nilai</th>
                        <th class="text-center">Status Fisik</th>
                        <th class="pe-4 text-end" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pengadaanList)): ?>
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-muted opacity-50"></i>
                                Belum ada riwayat dokumen pengadaan barang yang sesuai filter.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pengadaanList as $p): ?>
                            <tr>
                                <td class="ps-4 tabular text-muted fw-semibold">
                                    <a href="<?= BASE_URL ?>/pengadaan/detail.php?id=<?= $p->idpengadaan ?>" class="text-decoration-none fw-bold" style="color: var(--color-accent);">
                                        #PO-<?= str_pad($p->idpengadaan, 5, '0', STR_PAD_LEFT) ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= date('d M Y', strtotime($p->timestamp)) ?></div>
                                    <small class="text-muted tabular"><?= date('H:i', strtotime($p->timestamp)) ?> WIB</small>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($p->nama_vendor) ?></div>
                                    <span class="badge badge-refract-gray" style="font-size: 0.72rem;">
                                        <?= htmlspecialchars($p->badan_hukum ?? 'Mitra') ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark"><?= htmlspecialchars($p->nama_petugas) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($p->role_petugas) ?></small>
                                </td>
                                <td class="text-center tabular">
                                    <span class="badge badge-refract-blue">
                                        <?= number_format($p->total_item, 0, ',', '.') ?> jenis
                                    </span>
                                </td>
                                <td class="text-center tabular fw-semibold">
                                    <?= number_format($p->total_qty, 0, ',', '.') ?>
                                </td>
                                <td class="text-end tabular text-muted">
                                    Rp <?= number_format($p->subtotal_nilai, 0, ',', '.') ?>
                                </td>
                                <td class="text-end tabular fw-bold" style="color: var(--color-accent);">
                                    Rp <?= number_format($p->total_nilai, 0, ',', '.') ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($p->id_penerimaan_gudang)): ?>
                                        <a href="<?= BASE_URL ?>/penerimaan/detail.php?id=<?= $p->id_penerimaan_gudang ?>" class="badge badge-refract-green text-decoration-none" title="Diterima pada <?= date('d/m/Y H:i', strtotime($p->tgl_penerimaan_gudang)) ?>">
                                            <i class="bi bi-check2-circle me-1"></i>Diterima (#RCV-<?= str_pad($p->id_penerimaan_gudang, 5, '0', STR_PAD_LEFT) ?>)
                                        </a>
                                    <?php else: ?>
                                        <span class="badge badge-refract-yellow">
                                            <i class="bi bi-hourglass-split me-1"></i>Menunggu Gudang
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="<?= BASE_URL ?>/pengadaan/detail.php?id=<?= $p->idpengadaan ?>" class="btn-action-icon" title="Lihat Rincian Purchase Order">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if (empty($p->id_penerimaan_gudang) && $p->total_item > 0): ?>
                                            <a href="<?= BASE_URL ?>/penerimaan/tambah.php?idpengadaan=<?= $p->idpengadaan ?>" class="btn-action-icon text-primary" title="Proses Penerimaan Fisik di Gudang">
                                                <i class="bi bi-box-arrow-in-down"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
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
