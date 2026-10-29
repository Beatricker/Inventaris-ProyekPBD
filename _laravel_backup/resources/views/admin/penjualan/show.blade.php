@extends('layouts.admin')

@section('title', 'Struk Transaksi #' . $penjualan->idpenjualan)
@section('page_title', 'Struk Pembayaran')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <div class="card border shadow-sm" id="receiptArea">
            <div class="card-body p-4">
                <!-- Header Struk -->
                <div class="text-center pb-3 border-bottom mb-3">
                    <h4 class="fw-bold mb-1"><i class="bi bi-shop text-primary"></i> PutPutMart</h4>
                    <p class="small text-secondary mb-0">Sistem Inventaris & Kasir Minimarket</p>
                    <p class="small text-muted mb-0">Jl. Kampus Terpadu, Indonesia</p>
                </div>

                <!-- Info Transaksi -->
                <div class="row small mb-3">
                    <div class="col-6">
                        <div><strong>No. Faktur:</strong> #TRX-{{ str_pad($penjualan->idpenjualan, 5, '0', STR_PAD_LEFT) }}</div>
                        <div><strong>Kasir:</strong> {{ $penjualan->username }}</div>
                    </div>
                    <div class="col-6 text-end">
                        <div><strong>Tanggal:</strong> {{ date('d/m/Y', strtotime($penjualan->created_at)) }}</div>
                        <div><strong>Waktu:</strong> {{ date('H:i:s', strtotime($penjualan->created_at)) }} WIB</div>
                    </div>
                </div>

                <!-- Tabel Detail Barang -->
                <table class="table table-sm align-middle mb-3">
                    <thead class="table-light small">
                        <tr>
                            <th>Barang</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Harga</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @foreach($detail as $d)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $d->nama_barang }}</div>
                                    <small class="text-muted">/ {{ $d->nama_satuan }}</small>
                                </td>
                                <td class="text-center">{{ $d->jumlah }}</td>
                                <td class="text-end">Rp {{ number_format($d->harga_satuan, 0, ',', '.') }}</td>
                                <td class="text-end fw-semibold">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Rincian Total Bayar -->
                <div class="border-top pt-2 mb-4">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-secondary">Subtotal Nilai:</span>
                        <span>Rp {{ number_format($penjualan->subtotal_nilai, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-secondary">PPN (11%):</span>
                        <span>Rp {{ number_format($penjualan->ppn, 0, ',', '.') }}</span>
                    </div>
                    @if($penjualan->persen)
                        <div class="d-flex justify-content-between small mb-1 text-muted">
                            <span>Margin Penjualan:</span>
                            <span>{{ $penjualan->persen }}%</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between fw-bold fs-5 mt-2 pt-2 border-top">
                        <span>TOTAL BAYAR:</span>
                        <span class="text-success">Rp {{ number_format($penjualan->total_nilai, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Footer Struk -->
                <div class="text-center small text-muted border-top pt-3">
                    <p class="mb-1">Terima Kasih Telah Berbelanja di <strong>PutPutMart</strong>!</p>
                    <p class="mb-0 text-secondary" style="font-size: 0.75rem;">Barang yang sudah dibeli tidak dapat ditukar/dikembalikan tanpa nota resmi.</p>
                </div>
            </div>

            <!-- Tombol Aksi di Luar Cetak -->
            <div class="card-footer bg-light p-3 d-flex justify-content-between d-print-none">
                <a href="{{ route('penjualan.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Riwayat Penjualan
                </a>
                <div class="d-flex gap-2">
                    <a href="{{ route('penjualan.create') }}" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-cart-plus"></i> Transaksi Baru
                    </a>
                    <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
                        <i class="bi bi-printer"></i> Cetak Struk
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
