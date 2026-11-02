<?php

/**
 * kartu_stok/index.php
 * Buku Besar Kartu Stok (Stock Ledger) untuk audit mutasi masuk, keluar, dan retur.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
check_role(['Admin', 'Gudang']);

// Ambil parameter filter
$filterBarang     = (int)($_GET['idbarang'] ?? 0);
$filterJenis      = trim($_GET['jenis'] ?? '');
$filterTglMulai   = trim($_GET['tgl_mulai'] ?? '');
$filterTglSelesai = trim($_GET['tgl_selesai'] ?? '');

// Ambil daftar barang untuk dropdown filter
$barangList = $pdo->query("SELECT idbarang, nama_barang, nama_satuan FROM v_barang ORDER BY nama_barang ASC")->fetchAll();

// Bangun query dinamis buku besar kartu stok
$where = [];
$params = [];

if ($filterBarang > 0) {
    $where[] = "k.idbarang = ?";
    $params[] = $filterBarang;
}

if (!empty($filterJenis) && in_array($filterJenis, ['M', 'K', 'R'])) {
    $where[] = "k.jenis_transaksi = ?";
    $params[] = $filterJenis;
}

if (!empty($filterTglMulai)) {
    $where[] = "DATE(k.created_at) >= ?";
    $params[] = $filterTglMulai;
}

if (!empty($filterTglSelesai)) {
    $where[] = "DATE(k.created_at) <= ?";
    $params[] = $filterTglSelesai;
}

$whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$query = "
    SELECT 
        k.idkartu_stok,
        k.created_at,
        k.idbarang,
        b.nama AS nama_barang,
        s.nama_satuan,
        k.jenis_transaksi,
        k.idtransaksi,
        COALESCE(k.masuk, 0) AS masuk,
        COALESCE(k.keluar, 0) AS keluar,
        k.stock AS saldo_stok
    FROM kartu_stok k
    JOIN barang b ON k.idbarang = b.idbarang
    JOIN satuan s ON b.idsatuan = s.idsatuan
    {$whereSql}
    ORDER BY k.idkartu_stok DESC
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$mutasiList = $stmt->fetchAll();

// Hitung ringkasan total mutasi
$sumMasuk = 0;
$sumKeluar = 0;
$sumRetur = 0;

foreach ($mutasiList as $m) {
    if ($m->jenis_transaksi === 'M') {
        $sumMasuk += (int)$m->masuk;
    } elseif ($m->jenis_transaksi === 'K') {
        $sumKeluar += (int)$m->keluar;
    } elseif ($m->jenis_transaksi === 'R') {
        $sumRetur += (int)$m->keluar;
    }
}

$page_title = 'Buku Kartu Stok';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Editorial Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-3">
    <div>
        <h2 class="display-font mb-1" style="font-size: 2.1rem; font-weight: 500;">Buku Besar Kartu Stok</h2>
        <p class="text-muted mb-0" style="font-size: 0.98rem;">Audit riwayat mutasi fisik barang (Penerimaan, Penjualan Kasir, dan Retur) secara kronologis.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-clear" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak Laporan
        </button>
        <a href="<?= BASE_URL ?>/penerimaan/tambah.php" class="btn btn-primary">
            <i class="bi bi-box-arrow-in-down me-1"></i> + Penerimaan Stok
        </a>
    </div>
</div>

<!-- 1. Metrics Strip Ringkasan Mutasi -->
<div class="refract-strip mb-4">
    <div class="row g-0">
        <div class="col-6 col-md-3 refract-strip__cell">
            <div class="refract-strip__label">Total Masuk (M)</div>
            <div class="refract-strip__num tabular text-success">+<?= number_format($sumMasuk, 0, ',', '.') ?></div>
            <span class="text-muted" style="font-size: 0.78rem;">Penerimaan dari vendor</span>
        </div>
        <div class="col-6 col-md-3 refract-strip__cell">
            <div class="refract-strip__label">Total Keluar (K)</div>
            <div class="refract-strip__num tabular text-primary">-<?= number_format($sumKeluar, 0, ',', '.') ?></div>
            <span class="text-muted" style="font-size: 0.78rem;">Penjualan ke kasir</span>
        </div>
        <div class="col-6 col-md-3 refract-strip__cell">
            <div class="refract-strip__label">Total Retur (R)</div>
            <div class="refract-strip__num tabular text-danger">-<?= number_format($sumRetur, 0, ',', '.') ?></div>
            <span class="text-muted" style="font-size: 0.78rem;">Pengembalian barang rusak</span>
        </div>
        <div class="col-6 col-md-3 refract-strip__cell">
            <div class="refract-strip__label">Total Baris Mutasi</div>
            <div class="refract-strip__num tabular"><?= count($mutasiList) ?></div>
            <span class="text-muted" style="font-size: 0.78rem;">Entri riwayat tercatat</span>
        </div>
    </div>
</div>

<!-- 2. Form Filter Data -->
<div class="card mb-4">
    <div class="card-body p-3">
        <form action="" method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label for="idbarang" class="form-label" style="font-size: 0.8rem;">Pilih Produk</label>
                <select name="idbarang" id="idbarang" class="form-select form-select-sm">
                    <option value="">-- Semua Barang --</option>
                    <?php foreach ($barangList as $b): ?>
                        <option value="<?= $b->idbarang ?>" <?= $filterBarang == $b->idbarang ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b->nama_barang) ?> (<?= htmlspecialchars($b->nama_satuan) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="jenis" class="form-label" style="font-size: 0.8rem;">Jenis Mutasi</label>
                <select name="jenis" id="jenis" class="form-select form-select-sm">
                    <option value="">Semua Jenis</option>
                    <option value="M" <?= $filterJenis === 'M' ? 'selected' : '' ?>>Masuk (M)</option>
                    <option value="K" <?= $filterJenis === 'K' ? 'selected' : '' ?>>Keluar (K)</option>
                    <option value="R" <?= $filterJenis === 'R' ? 'selected' : '' ?>>Retur (R)</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="tgl_mulai" class="form-label" style="font-size: 0.8rem;">Dari Tanggal</label>
                <input type="date" name="tgl_mulai" id="tgl_mulai" class="form-control form-control-sm" value="<?= htmlspecialchars($filterTglMulai) ?>">
            </div>
            <div class="col-md-2">
                <label for="tgl_selesai" class="form-label" style="font-size: 0.8rem;">Sampai Tanggal</label>
                <input type="date" name="tgl_selesai" id="tgl_selesai" class="form-control form-control-sm" value="<?= htmlspecialchars($filterTglSelesai) ?>">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="<?= BASE_URL ?>/kartu_stok/index.php" class="btn btn-clear btn-sm" title="Reset Filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- 3. Tabel Buku Besar Mutasi Stok -->
<div class="card mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" style="width: 80px;">No. Log</th>
                        <th>Waktu Mutasi</th>
                        <th>Nama Produk</th>
                        <th class="text-center">Jenis Mutasi</th>
                        <th>Dokumen Terkait</th>
                        <th class="text-center">Masuk</th>
                        <th class="text-center">Keluar</th>
                        <th class="pe-4 text-end">Saldo Stok</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($mutasiList)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-journal-x fs-1 d-block mb-2 opacity-50"></i>
                                Tidak ada catatan mutasi stok yang sesuai kriteria filter.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($mutasiList as $m): ?>
                            <tr>
                                <td class="ps-4 tabular text-muted fw-semibold">
                                    #<?= $m->idkartu_stok ?>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= date('d M Y', strtotime($m->created_at)) ?></div>
                                    <small class="text-muted"><?= date('H:i:s', strtotime($m->created_at)) ?> WIB</small>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($m->nama_barang) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($m->nama_satuan) ?></small>
                                </td>
                                <td class="text-center">
                                    <?php if ($m->jenis_transaksi === 'M'): ?>
                                        <span class="badge badge-refract-green">
                                            <i class="bi bi-arrow-down-left me-1"></i> Masuk (M)
                                        </span>
                                    <?php elseif ($m->jenis_transaksi === 'K'): ?>
                                        <span class="badge badge-refract-blue">
                                            <i class="bi bi-arrow-up-right me-1"></i> Penjualan (K)
                                        </span>
                                    <?php elseif ($m->jenis_transaksi === 'R'): ?>
                                        <span class="badge badge-refract-red">
                                            <i class="bi bi-arrow-return-left me-1"></i> Retur Vendor (R)
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($m->jenis_transaksi === 'M'): ?>
                                        <a href="<?= BASE_URL ?>/penerimaan/detail.php?id=<?= $m->idtransaksi ?>" class="tabular text-decoration-none fw-medium" style="color: var(--color-accent);">
                                            #RCV-<?= str_pad($m->idtransaksi, 5, '0', STR_PAD_LEFT) ?>
                                        </a>
                                    <?php elseif ($m->jenis_transaksi === 'R'): ?>
                                        <a href="<?= BASE_URL ?>/retur/detail.php?id=<?= $m->idtransaksi ?>" class="tabular text-decoration-none fw-medium text-danger">
                                            #RET-<?= str_pad($m->idtransaksi, 5, '0', STR_PAD_LEFT) ?>
                                        </a>
                                    <?php elseif ($m->jenis_transaksi === 'K'): ?>
                                        <span class="tabular text-muted">
                                            #POS-<?= str_pad($m->idtransaksi, 5, '0', STR_PAD_LEFT) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center tabular fw-semibold text-success">
                                    <?= $m->masuk > 0 ? '+' . number_format($m->masuk, 0, ',', '.') : '-' ?>
                                </td>
                                <td class="text-center tabular fw-semibold text-danger">
                                    <?= $m->keluar > 0 ? '-' . number_format($m->keluar, 0, ',', '.') : '-' ?>
                                </td>
                                <td class="pe-4 text-end tabular fw-bold" style="color: var(--color-accent); font-size: 1.05rem;">
                                    <?= number_format($m->saldo_stok, 0, ',', '.') ?>
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
