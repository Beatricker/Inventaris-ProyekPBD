<?php

/**
 * retur/tambah.php
 * Form pengajuan retur pengembalian barang cacat/rusak ke vendor.
 * Memotong stok fisik barang secara otomatis di kartu_stok via Stored Procedure & Trigger.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
check_role(['Admin', 'Gudang']);

$error = '';
$selectedPenerimaan = (int)($_POST['idpenerimaan'] ?? $_GET['idpenerimaan'] ?? 0);

// Ambil daftar dokumen penerimaan barang untuk dipilih
$penerimaanList = $pdo->query("
    SELECT 
        p.idpenerimaan,
        p.created_at,
        COALESCE(v.nama_vendor, 'Vendor Mitra') AS nama_vendor
    FROM penerimaan p
    LEFT JOIN pengadaan pg ON p.idpengadaan = pg.idpengadaan
    LEFT JOIN vendor v ON pg.vendor_idvendor = v.idvendor
    ORDER BY p.idpenerimaan DESC
")->fetchAll();

$itemsPenerimaan = [];
if ($selectedPenerimaan > 0) {
    $stmt = $pdo->prepare("
        SELECT 
            dp.iddetail_penerimaan,
            dp.barang_idbarang,
            b.nama AS nama_barang,
            s.nama_satuan,
            dp.jumlah_terima,
            fn_get_sisa_stok(dp.barang_idbarang) AS sisa_stok_saat_ini
        FROM detail_penerimaan dp
        JOIN barang b ON dp.barang_idbarang = b.idbarang
        JOIN satuan s ON b.idsatuan = s.idsatuan
        WHERE dp.idpenerimaan = ?
        ORDER BY dp.iddetail_penerimaan ASC
    ");
    $stmt->execute([$selectedPenerimaan]);
    $itemsPenerimaan = $stmt->fetchAll();
}

// Proses submit form retur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proses_retur'])) {
    $iduser = (int)($_SESSION['iduser'] ?? 1);
    $detailIds = $_POST['iddetail_penerimaan'] ?? [];
    $quantities = $_POST['jumlah_retur'] ?? [];
    $reasons = $_POST['alasan_retur'] ?? [];

    if (empty($selectedPenerimaan)) {
        $error = 'Silakan pilih dokumen penerimaan barang asal!';
    } else {
        $itemsToReturn = [];
        for ($i = 0; $i < count($detailIds); $i++) {
            $iddetail = (int)($detailIds[$i] ?? 0);
            $qty = (int)($quantities[$i] ?? 0);
            $alasan = trim($reasons[$i] ?? '');

            if ($iddetail > 0 && $qty > 0) {
                $itemsToReturn[] = [
                    'iddetail_penerimaan' => $iddetail,
                    'jumlah'              => $qty,
                    'alasan'              => !empty($alasan) ? $alasan : 'Barang Rusak / Cacat Produksi',
                ];
            }
        }

        if (empty($itemsToReturn)) {
            $error = 'Minimal satu barang harus memiliki jumlah retur lebih dari 0!';
        } else {
            try {
                // Eksekusi Stored Procedure sp_transaksi_retur
                // Trigger trg_detail_retur_ai akan otomatis mencatat mutasi 'R' (Retur)
                // ke tabel kartu_stok dan memotong saldo stok fisik
                $result = db_sp_transaksi_retur($pdo, $selectedPenerimaan, $iduser, $itemsToReturn);

                if ($result['status'] === 'SUCCESS') {
                    header('Location: ' . BASE_URL . '/retur/detail.php?id=' . $result['idretur']);
                    exit;
                } else {
                    $error = 'Gagal memproses retur: ' . $result['pesan'];
                }
            } catch (Exception $e) {
                $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
            }
        }
    }
}

$page_title = 'Catat Retur Barang';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-xl-10">
        <!-- Editorial Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-3">
            <div>
                <h2 class="display-font mb-1" style="font-size: 2.1rem; font-weight: 500;">Pengajuan Retur ke Vendor</h2>
                <p class="text-muted mb-0" style="font-size: 0.98rem;">Mencatat pengembalian fisik barang cacat/rusak dan otomatis mengurangi saldo kartu stok.</p>
            </div>
            <div>
                <a href="<?= BASE_URL ?>/retur/index.php" class="btn btn-clear">
                    <i class="bi bi-arrow-left"></i> Kembali ke Riwayat
                </a>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger mb-4 d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div><?= htmlspecialchars($error) ?></div>
            </div>
        <?php endif; ?>

        <!-- 1. Pilih Dokumen Penerimaan Asal -->
        <div class="card mb-4">
            <div class="card-header">
                <span class="fw-bold"><i class="bi bi-receipt me-2" style="color: var(--color-accent);"></i>Pilih Dokumen Penerimaan Asal</span>
            </div>
            <div class="card-body p-4">
                <form action="" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-9">
                        <label for="idpenerimaan" class="form-label">Nomor Dokumen Penerimaan (Surat Jalan Gudang)</label>
                        <select name="idpenerimaan" id="idpenerimaan" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Pilih Dokumen Penerimaan --</option>
                            <?php foreach ($penerimaanList as $p): ?>
                                <option value="<?= $p->idpenerimaan ?>" <?= $selectedPenerimaan == $p->idpenerimaan ? 'selected' : '' ?>>
                                    #RCV-<?= str_pad($p->idpenerimaan, 5, '0', STR_PAD_LEFT) ?> &bull; <?= htmlspecialchars($p->nama_vendor) ?> (<?= date('d M Y, H:i', strtotime($p->created_at)) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-clear w-100">
                            <i class="bi bi-arrow-repeat me-1"></i> Muat Barang
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 2. Rincian Barang yang Ingin Diretur -->
        <?php if ($selectedPenerimaan > 0): ?>
            <?php if (empty($itemsPenerimaan)): ?>
                <div class="card p-5 text-center text-muted">
                    <i class="bi bi-exclamation-circle fs-1 mb-2 opacity-50"></i>
                    Dokumen penerimaan ini tidak memiliki item rincian barang.
                </div>
            <?php else: ?>
                <form action="" method="POST">
                    <input type="hidden" name="idpenerimaan" value="<?= $selectedPenerimaan ?>">
                    <input type="hidden" name="proses_retur" value="1">

                    <div class="card mb-4">
                        <div class="card-header">
                            <span class="fw-bold"><i class="bi bi-box-seam me-2" style="color: var(--color-accent);"></i>Pilih Barang &amp; Jumlah yang Diretur</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-4" style="min-width: 240px;">Nama Produk</th>
                                            <th style="width: 120px;">Satuan</th>
                                            <th class="text-center" style="width: 140px;">Diterima Awal</th>
                                            <th class="text-center" style="width: 140px;">Stok Saat Ini</th>
                                            <th class="text-center" style="width: 150px;">Jumlah Retur</th>
                                            <th class="pe-4" style="min-width: 240px;">Alasan Pengembalian</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($itemsPenerimaan as $item): ?>
                                            <tr>
                                                <td class="ps-4">
                                                    <input type="hidden" name="iddetail_penerimaan[]" value="<?= $item->iddetail_penerimaan ?>">
                                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($item->nama_barang) ?></div>
                                                    <small class="text-muted">Item ID #<?= $item->barang_idbarang ?></small>
                                                </td>
                                                <td><?= htmlspecialchars($item->nama_satuan) ?></td>
                                                <td class="text-center tabular text-muted">
                                                    <?= number_format($item->jumlah_terima, 0, ',', '.') ?> unit
                                                </td>
                                                <td class="text-center tabular">
                                                    <span class="badge badge-refract-blue">
                                                        <?= number_format($item->sisa_stok_saat_ini, 0, ',', '.') ?> unit
                                                    </span>
                                                </td>
                                                <td>
                                                    <input type="number" 
                                                           name="jumlah_retur[]" 
                                                           class="form-control text-center" 
                                                           min="0" 
                                                           max="<?= min($item->jumlah_terima, $item->sisa_stok_saat_ini) ?>" 
                                                           value="0" 
                                                           placeholder="0">
                                                </td>
                                                <td class="pe-4">
                                                    <input type="text" 
                                                           name="alasan_retur[]" 
                                                           class="form-control" 
                                                           placeholder="Contoh: Rusak / Cacat / Bocor / Kadaluarsa">
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi Simpan Retur -->
                    <div class="d-flex justify-content-end gap-2 mb-5">
                        <a href="<?= BASE_URL ?>/retur/index.php" class="btn btn-clear">Batal</a>
                        <button type="submit" class="btn btn-danger px-4 py-2" onclick="return confirm('Apakah Anda yakin ingin memproses retur barang ini? Stok fisik barang akan otomatis dipotong.')">
                            <i class="bi bi-arrow-return-left me-1"></i> Simpan Dokumen Retur &amp; Potong Stok
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        <?php else: ?>
            <div class="card p-5 text-center text-muted">
                <i class="bi bi-file-earmark-arrow-down fs-1 mb-2 opacity-50" style="color: var(--color-accent);"></i>
                Silakan pilih salah satu dokumen penerimaan barang di atas untuk memuat daftar produk yang bisa diretur.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
