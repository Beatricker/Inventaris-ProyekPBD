<?php

/**
 * penerimaan/tambah.php
 * Form pencatatan penerimaan fisik barang masuk dari vendor.
 * Menambah stok fisik barang secara otomatis ke kartu_stok via Stored Procedure & Trigger.
 * Terintegrasi penuh dengan dokumen Pengadaan (Purchase Order).
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
check_role(['Admin', 'Gudang']);

$error = '';
$success = '';

// Ambil parameter rujukan pengadaan jika ada
$selectedPO = (int)($_GET['idpengadaan'] ?? 0);
$poData = null;
$poItems = [];

if ($selectedPO > 0) {
    $stmtPO = $pdo->prepare("
        SELECT pg.*, v.idvendor, v.nama_vendor, v.badan_hukum 
        FROM pengadaan pg
        LEFT JOIN vendor v ON pg.vendor_idvendor = v.idvendor
        WHERE pg.idpengadaan = ? LIMIT 1
    ");
    $stmtPO->execute([$selectedPO]);
    $poData = $stmtPO->fetch();

    if ($poData) {
        $stmtItems = $pdo->prepare("
            SELECT dp.*, b.nama AS nama_barang, s.nama_satuan, b.stok 
            FROM detail_pengadaan dp
            JOIN barang b ON dp.idbarang = b.idbarang
            JOIN satuan s ON b.idsatuan = s.idsatuan
            WHERE dp.idpengadaan = ?
            ORDER BY dp.iddetail_pengadaan ASC
        ");
        $stmtItems->execute([$selectedPO]);
        $poItems = $stmtItems->fetchAll();
    }
}

// Ambil daftar pengadaan yang belum pernah diterima di gudang untuk pilihan cepat
$pendingPOList = $pdo->query("
    SELECT pg.idpengadaan, pg.timestamp, pg.total_nilai, v.idvendor, v.nama_vendor, v.badan_hukum
    FROM pengadaan pg
    JOIN vendor v ON pg.vendor_idvendor = v.idvendor
    LEFT JOIN penerimaan pn ON pg.idpengadaan = pn.idpengadaan
    WHERE pn.idpenerimaan IS NULL AND pg.total_nilai > 0
    ORDER BY pg.idpengadaan DESC
")->fetchAll();

// Ambil daftar vendor aktif
$vendorList = $pdo->query("SELECT idvendor, nama_vendor, badan_hukum FROM vendor WHERE status = 1 ORDER BY nama_vendor ASC")->fetchAll();

// Ambil daftar barang aktif (beserta harga modal & satuan)
$barangList = $pdo->query("SELECT idbarang, nama_barang, nama_satuan, harga, stok FROM v_barang WHERE status = 1 ORDER BY nama_barang ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idvendor = (int)($_POST['idvendor'] ?? 0);
    $barangIds = $_POST['idbarang'] ?? [];
    $quantities = $_POST['jumlah_terima'] ?? [];
    $prices = $_POST['harga_satuan_terima'] ?? [];
    $inputPengadaan = (int)($_POST['idpengadaan'] ?? 0);

    $iduser = (int)($_SESSION['iduser'] ?? 1);

    if (empty($idvendor)) {
        $error = 'Silakan pilih Mitra Vendor pemasok!';
    } elseif (empty($barangIds) || !is_array($barangIds)) {
        $error = 'Minimal satu barang harus dimasukkan dalam penerimaan!';
    } else {
        $itemsToProcess = [];
        for ($i = 0; $i < count($barangIds); $i++) {
            $idb = (int)($barangIds[$i] ?? 0);
            $qty = (int)($quantities[$i] ?? 0);
            $prc = (int)($prices[$i] ?? 0);

            if ($idb > 0 && $qty > 0) {
                $itemsToProcess[] = [
                    'idbarang'            => $idb,
                    'jumlah_terima'       => $qty,
                    'harga_satuan_terima' => $prc,
                ];
            }
        }

        if (empty($itemsToProcess)) {
            $error = 'Jumlah unit barang yang diterima harus lebih dari 0!';
        } else {
            try {
                // Tentukan idpengadaan yang digunakan:
                // Jika merujuk ke PO yang sudah ada, gunakan ID tersebut.
                // Jika penerimaan langsung tanpa PO sebelumnya, buat header pengadaan otomatis agar integritas FK terpenuhi.
                if ($inputPengadaan > 0) {
                    $idpengadaan = $inputPengadaan;
                } else {
                    $stmtPO = $pdo->prepare("
                        INSERT INTO pengadaan (timestamp, user_iduser, status, vendor_idvendor, subtotal_nilai, ppn, total_nilai)
                        VALUES (NOW(), ?, 'A', ?, 0, 0, 0)
                    ");
                    $stmtPO->execute([$iduser, $idvendor]);
                    $idpengadaan = (int) $pdo->lastInsertId();
                }

                // Eksekusi Stored Procedure sp_transaksi_penerimaan
                // SP ini akan menginsert rincian barang, dan Trigger trg_detail_penerimaan_ai
                // akan otomatis mencatat mutasi stok MASUK ('M') ke tabel kartu_stok
                $result = db_sp_transaksi_penerimaan($pdo, $idpengadaan, $iduser, $itemsToProcess);

                if ($result['status'] === 'SUCCESS') {
                    header('Location: ' . BASE_URL . '/penerimaan/detail.php?id=' . $result['idpenerimaan']);
                    exit;
                } else {
                    $error = 'Gagal menyimpan penerimaan: ' . $result['pesan'];
                }
            } catch (Exception $e) {
                $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
            }
        }
    }
}

$page_title = 'Catat Penerimaan Barang';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-xl-10">
        <!-- Editorial Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-3">
            <div>
                <h2 class="display-font mb-1" style="font-size: 2.1rem; font-weight: 500;">Penerimaan Barang Gudang</h2>
                <p class="text-muted mb-0" style="font-size: 0.98rem;">Mencatat fisik barang masuk dari vendor dan menambah saldo kartu stok otomatis.</p>
            </div>
            <div>
                <a href="<?= BASE_URL ?>/penerimaan/index.php" class="btn btn-clear">
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

        <?php if ($poData): ?>
            <div class="alert alert-info mb-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle-fill fs-5"></i>
                    <div>
                        Penerimaan ini merujuk ke Dokumen Pengadaan <strong>#PO-<?= str_pad($poData->idpengadaan, 5, '0', STR_PAD_LEFT) ?></strong> 
                        (Vendor: <?= htmlspecialchars($poData->nama_vendor) ?>, Total Nilai: Rp <?= number_format($poData->total_nilai, 0, ',', '.') ?>).
                    </div>
                </div>
                <a href="<?= BASE_URL ?>/penerimaan/tambah.php" class="btn btn-sm btn-clear text-danger">
                    <i class="bi bi-x-circle me-1"></i> Batal Rujukan PO
                </a>
            </div>
        <?php endif; ?>

        <form action="" method="POST" id="formPenerimaan">
            <input type="hidden" name="idpengadaan" value="<?= $poData ? $poData->idpengadaan : 0 ?>">

            <!-- Informasi Pengirim / Dokumen -->
            <div class="card mb-4">
                <div class="card-header">
                    <span class="fw-bold"><i class="bi bi-truck me-2" style="color: var(--color-accent);"></i>Informasi Vendor Pemasok</span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <?php if (!$poData && !empty($pendingPOList)): ?>
                            <div class="col-12">
                                <label class="form-label small text-muted">Tarik dari Dokumen Pengadaan (PO) yang Menunggu Pengiriman:</label>
                                <select class="form-select form-select-sm" onchange="if (this.value) window.location.href = '<?= BASE_URL ?>/penerimaan/tambah.php?idpengadaan=' + this.value;">
                                    <option value="">-- Pilih Dokumen PO (Opsional: Barang &amp; Vendor Otomatis Terisi) --</option>
                                    <?php foreach ($pendingPOList as $po): ?>
                                        <option value="<?= $po->idpengadaan ?>">
                                            #PO-<?= str_pad($po->idpengadaan, 5, '0', STR_PAD_LEFT) ?> &bull; <?= htmlspecialchars($po->nama_vendor) ?> &bull; Rp <?= number_format($po->total_nilai, 0, ',', '.') ?> (<?= date('d/m/Y', strtotime($po->timestamp)) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <div class="col-md-6">
                            <label for="idvendor" class="form-label">Mitra Vendor Pengirim <span class="text-danger">*</span></label>
                            <select name="idvendor" id="idvendor" class="form-select" required>
                                <option value="">-- Pilih Mitra Vendor --</option>
                                <?php foreach ($vendorList as $v): ?>
                                    <option value="<?= $v->idvendor ?>" <?= ($poData && $poData->vendor_idvendor == $v->idvendor) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($v->nama_vendor) ?> (<?= htmlspecialchars($v->badan_hukum) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Petugas Penerima</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($username) ?> (<?= ucfirst($userRole) ?>)" readonly style="background-color: #f8fafc;">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Waktu Terima</label>
                            <input type="text" class="form-control" value="<?= date('d M Y - H:i') ?> WIB" readonly style="background-color: #f8fafc;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rincian Barang yang Diterima -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-box-seam me-2" style="color: var(--color-accent);"></i>Rincian Barang yang Diterima</span>
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
                                    <th style="width: 140px;">Satuan</th>
                                    <th style="width: 150px;">Stok Saat Ini</th>
                                    <th style="width: 160px;">Jumlah Masuk <span class="text-danger">*</span></th>
                                    <th style="width: 180px;">Harga Beli Satuan (Rp)</th>
                                    <th style="width: 180px;">Subtotal (Rp)</th>
                                    <th class="pe-4 text-center" style="width: 60px;">Hapus</th>
                                </tr>
                            </thead>
                            <tbody id="containerBaris">
                                <?php if (!empty($poItems)): ?>
                                    <?php foreach ($poItems as $pi): ?>
                                        <tr class="baris-barang">
                                            <td class="ps-4">
                                                <select name="idbarang[]" class="form-select select-barang" required>
                                                    <option value="<?= $pi->idbarang ?>" data-satuan="<?= htmlspecialchars($pi->nama_satuan) ?>" data-harga="<?= $pi->harga_satuan ?>" data-stok="<?= $pi->stok ?>" selected>
                                                        <?= htmlspecialchars($pi->nama_barang) ?>
                                                    </option>
                                                    <?php foreach ($barangList as $b): ?>
                                                        <?php if ($b->idbarang != $pi->idbarang): ?>
                                                            <option value="<?= $b->idbarang ?>" data-satuan="<?= htmlspecialchars($b->nama_satuan) ?>" data-harga="<?= $b->harga ?>" data-stok="<?= $b->stok ?>">
                                                                <?= htmlspecialchars($b->nama_barang) ?>
                                                            </option>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <span class="badge badge-refract-gray span-satuan"><?= htmlspecialchars($pi->nama_satuan) ?></span>
                                            </td>
                                            <td>
                                                <span class="badge badge-refract-blue span-stok"><?= number_format($pi->stok ?? 0, 0, ',', '.') ?></span>
                                            </td>
                                            <td>
                                                <input type="number" name="jumlah_terima[]" class="form-control input-qty text-center" min="1" value="<?= $pi->jumlah ?>" placeholder="Qty" required>
                                            </td>
                                            <td>
                                                <input type="number" name="harga_satuan_terima[]" class="form-control input-harga text-end" min="0" value="<?= $pi->harga_satuan ?>" placeholder="Harga Satuan" required>
                                            </td>
                                            <td class="tabular fw-medium text-end text-dark span-subtotal">
                                                Rp <?= number_format($pi->sub_total, 0, ',', '.') ?>
                                            </td>
                                            <td class="pe-4 text-center">
                                                <button type="button" class="btn btn-clear btn-sm text-danger btn-hapus-baris" title="Hapus Baris">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <!-- Baris Pertama Default -->
                                    <tr class="baris-barang">
                                        <td class="ps-4">
                                            <select name="idbarang[]" class="form-select select-barang" required>
                                                <option value="">-- Pilih Barang --</option>
                                                <?php foreach ($barangList as $b): ?>
                                                    <option value="<?= $b->idbarang ?>" data-satuan="<?= htmlspecialchars($b->nama_satuan) ?>" data-harga="<?= $b->harga ?>" data-stok="<?= $b->stok ?>">
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
                                            <input type="number" name="jumlah_terima[]" class="form-control input-qty text-center" min="1" placeholder="Qty" required>
                                        </td>
                                        <td>
                                            <input type="number" name="harga_satuan_terima[]" class="form-control input-harga text-end" min="0" placeholder="Harga Satuan" required>
                                        </td>
                                        <td class="tabular fw-medium text-end text-dark span-subtotal">
                                            Rp 0
                                        </td>
                                        <td class="pe-4 text-center">
                                            <button type="button" class="btn btn-clear btn-sm text-danger btn-hapus-baris" title="Hapus Baris">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr style="background: #fbfcfe; border-top: 2px solid var(--border-color);">
                                    <td colspan="5" class="ps-4 text-end fw-bold">Total Nilai Penerimaan:</td>
                                    <td class="text-end fw-bold tabular fs-6" style="color: var(--color-accent);" id="labelGrandTotal">
                                        Rp 0
                                    </td>
                                    <td class="pe-4"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi Simpan -->
            <div class="d-flex justify-content-end gap-2 mb-5">
                <a href="<?= BASE_URL ?>/penerimaan/index.php" class="btn btn-clear">Batal</a>
                <button type="submit" class="btn btn-primary px-4 py-2">
                    <i class="bi bi-check-lg me-1"></i> Simpan &amp; Tambah Stok ke Gudang
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Template Script Dynamic Row & Live Calculation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('containerBaris');
    const btnTambah = document.getElementById('btnTambahBaris');
    const grandTotalLabel = document.getElementById('labelGrandTotal');

    function hitungTotal() {
        let grandTotal = 0;
        const barisList = container.querySelectorAll('.baris-barang');

        barisList.forEach(row => {
            const qtyInput = row.querySelector('.input-qty');
            const hargaInput = row.querySelector('.input-harga');
            const subtotalSpan = row.querySelector('.span-subtotal');

            const qty = parseFloat(qtyInput.value) || 0;
            const harga = parseFloat(hargaInput.value) || 0;
            const subtotal = qty * harga;

            subtotalSpan.textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
            grandTotal += subtotal;
        });

        grandTotalLabel.textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');
    }

    function initRowEvents(row) {
        const selectBarang = row.querySelector('.select-barang');
        const satuanSpan = row.querySelector('.span-satuan');
        const stokSpan = row.querySelector('.span-stok');
        const hargaInput = row.querySelector('.input-harga');
        const qtyInput = row.querySelector('.input-qty');
        const btnHapus = row.querySelector('.btn-hapus-baris');

        selectBarang.addEventListener('change', function() {
            const selected = selectBarang.options[selectBarang.selectedIndex];
            if (selected.value) {
                satuanSpan.textContent = selected.getAttribute('data-satuan') || '-';
                stokSpan.textContent = selected.getAttribute('data-stok') || '0';
                if (!hargaInput.value || parseFloat(hargaInput.value) === 0) {
                    hargaInput.value = selected.getAttribute('data-harga') || '0';
                }
            } else {
                satuanSpan.textContent = '-';
                stokSpan.textContent = '0';
            }
            hitungTotal();
        });

        qtyInput.addEventListener('input', hitungTotal);
        hargaInput.addEventListener('input', hitungTotal);

        btnHapus.addEventListener('click', function() {
            if (container.querySelectorAll('.baris-barang').length > 1) {
                row.remove();
                hitungTotal();
            } else {
                alert('Minimal satu baris barang harus tetap ada.');
            }
        });
    }

    // Init semua baris (baik default maupun pre-populated dari PO)
    container.querySelectorAll('.baris-barang').forEach(row => initRowEvents(row));
    hitungTotal();

    // Tambah baris baru
    btnTambah.addEventListener('click', function() {
        const firstRow = container.querySelector('.baris-barang');
        const newRow = firstRow.cloneNode(true);

        // Reset input nilai di baris kloningan
        newRow.querySelector('.select-barang').selectedIndex = 0;
        newRow.querySelector('.span-satuan').textContent = '-';
        newRow.querySelector('.span-stok').textContent = '0';
        newRow.querySelector('.input-qty').value = '';
        newRow.querySelector('.input-harga').value = '';
        newRow.querySelector('.span-subtotal').textContent = 'Rp 0';

        container.appendChild(newRow);
        initRowEvents(newRow);
        hitungTotal();
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
