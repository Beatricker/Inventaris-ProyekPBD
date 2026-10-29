@extends('layouts.admin')

@section('title', 'Transaksi Kasir Baru')
@section('page_title', 'Kasir / POS Penjualan Baru')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <span class="fw-bold fs-6"><i class="bi bi-cart-check-fill text-success me-2"></i>Form Kasir Penjualan Barang</span>
                <a href="{{ route('penjualan.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Riwayat Penjualan
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('penjualan.store') }}" method="POST" id="formPenjualan">
                    @csrf

                    <!-- Info Kasir & Margin Penjualan -->
                    <div class="row g-3 mb-4 p-3 bg-light rounded-3 border">
                        <div class="col-md-4">
                            <label class="form-label small text-secondary fw-semibold">Kasir Bertugas</label>
                            <input type="text" class="form-control form-control-sm bg-white" value="{{ session('username') }} ({{ session('role') }})" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-secondary fw-semibold">Waktu Transaksi</label>
                            <input type="text" class="form-control form-control-sm bg-white" value="{{ date('d F Y, H:i') }} WIB" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-secondary fw-semibold">Margin Penjualan Aktif</label>
                            @if($activeMargin)
                                <input type="hidden" name="idmargin_penjualan" value="{{ $activeMargin->idmargin_penjualan }}">
                                <input type="text" class="form-control form-control-sm bg-white" value="{{ $activeMargin->persen }}% (ID #{{ $activeMargin->idmargin_penjualan }})" readonly>
                            @else
                                <input type="hidden" name="idmargin_penjualan" value="">
                                <input type="text" class="form-control form-control-sm bg-white" value="Standar (Tidak Ada Margin Khusus)" readonly>
                            @endif
                        </div>
                    </div>

                    <!-- Tabel Item Belanja -->
                    <h6 class="fw-bold mb-3"><i class="bi bi-list-check me-2"></i>Daftar Barang yang Dibeli</h6>
                    
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered align-middle" id="tableBarang">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 300px;">Pilih Barang</th>
                                    <th style="width: 150px;">Harga Satuan</th>
                                    <th style="width: 120px;">Jumlah (Qty)</th>
                                    <th style="width: 180px;">Subtotal</th>
                                    <th class="text-center" style="width: 60px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="itemRows">
                                <tr>
                                    <td>
                                        <select name="idbarang[]" class="form-select barang-select" required onchange="updateRow(this)">
                                            <option value="" disabled selected>-- Pilih Barang --</option>
                                            @foreach($barang as $b)
                                                <option value="{{ $b->idbarang }}" data-harga="{{ $b->harga }}" data-satuan="{{ $b->nama_satuan }}">
                                                    {{ $b->nama_barang }} (Rp {{ number_format($b->harga, 0, ',', '.') }} / {{ $b->nama_satuan }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control bg-light harga-display" value="Rp 0" readonly>
                                        <input type="hidden" class="harga-input" value="0">
                                    </td>
                                    <td>
                                        <input type="number" name="jumlah[]" class="form-control qty-input" value="1" min="1" max="999" required onchange="calculateTotal()" onkeyup="calculateTotal()">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control bg-light fw-bold subtotal-display" value="Rp 0" readonly>
                                        <input type="hidden" class="subtotal-input" value="0">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <button type="button" class="btn btn-outline-primary btn-sm mb-4" onclick="addRow()">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Baris Barang
                    </button>

                    <!-- Ringkasan Perhitungan Pembayaran -->
                    <div class="row justify-content-end">
                        <div class="col-md-5">
                            <div class="card bg-light border p-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-secondary">Subtotal Barang:</span>
                                    <span class="fw-semibold" id="labelSubtotal">Rp 0</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-secondary">PPN (11%):</span>
                                    <span class="fw-semibold text-muted" id="labelPpn">Rp 0</span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fw-bold fs-5">Total Pembayaran:</span>
                                    <span class="fw-bold fs-4 text-success" id="labelTotal">Rp 0</span>
                                </div>

                                <button type="submit" class="btn btn-success btn-lg w-100 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-printer-fill"></i> Simpan Transaksi & Cetak Struk
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function formatRupiah(num) {
        return 'Rp ' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function updateRow(selectEl) {
        const row = selectEl.closest('tr');
        const selectedOption = selectEl.options[selectEl.selectedIndex];
        const harga = parseInt(selectedOption.getAttribute('data-harga')) || 0;
        
        row.querySelector('.harga-input').value = harga;
        row.querySelector('.harga-display').value = formatRupiah(harga);

        calculateTotal();
    }

    function calculateTotal() {
        let subtotal = 0;
        const rows = document.querySelectorAll('#itemRows tr');

        rows.forEach(row => {
            const harga = parseInt(row.querySelector('.harga-input').value) || 0;
            const qty = parseInt(row.querySelector('.qty-input').value) || 0;
            const itemSubtotal = harga * qty;

            row.querySelector('.subtotal-input').value = itemSubtotal;
            row.querySelector('.subtotal-display').value = formatRupiah(itemSubtotal);

            subtotal += itemSubtotal;
        });

        const ppn = Math.round(subtotal * 0.11);
        const total = subtotal + ppn;

        document.getElementById('labelSubtotal').innerText = formatRupiah(subtotal);
        document.getElementById('labelPpn').innerText = formatRupiah(ppn);
        document.getElementById('labelTotal').innerText = formatRupiah(total);
    }

    function addRow() {
        const tableBody = document.getElementById('itemRows');
        const firstRow = tableBody.querySelector('tr');
        const newRow = firstRow.cloneNode(true);

        // Reset values
        newRow.querySelector('.barang-select').selectedIndex = 0;
        newRow.querySelector('.harga-input').value = 0;
        newRow.querySelector('.harga-display').value = 'Rp 0';
        newRow.querySelector('.qty-input').value = 1;
        newRow.querySelector('.subtotal-input').value = 0;
        newRow.querySelector('.subtotal-display').value = 'Rp 0';

        tableBody.appendChild(newRow);
    }

    function removeRow(btn) {
        const rows = document.querySelectorAll('#itemRows tr');
        if (rows.length > 1) {
            btn.closest('tr').remove();
            calculateTotal();
        } else {
            alert('Minimal harus ada 1 baris barang dalam transaksi!');
        }
    }
</script>
@endpush
