@extends('layouts.admin')

@section('title', 'Riwayat Penjualan')
@section('page_title', 'Transaksi Penjualan (Kasir)')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-0 fw-bold"><i class="bi bi-receipt me-2"></i>Daftar Riwayat Penjualan</h6>
            <small class="text-muted">Data transaksi kasir yang tersimpan di tabel <code>penjualan</code> & <code>detail_penjualan</code></small>
        </div>
        <a href="{{ route('penjualan.create') }}" class="btn btn-success btn-sm d-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-cart-plus"></i> Transaksi Kasir Baru
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3" style="width: 100px;">No. Faktur</th>
                        <th>Waktu Transaksi</th>
                        <th>Kasir / Petugas</th>
                        <th>Margin (%)</th>
                        <th>Subtotal</th>
                        <th>PPN (11%)</th>
                        <th>Total Bayar</th>
                        <th class="text-end pe-3" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($penjualan as $p)
                        <tr>
                            <td class="ps-3 fw-bold text-primary">#TRX-{{ str_pad($p->idpenjualan, 5, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $p->created_at }}</td>
                            <td class="fw-semibold">
                                <i class="bi bi-person text-secondary me-1"></i>{{ $p->username }}
                            </td>
                            <td>
                                @if($p->persen)
                                    <span class="badge bg-info-subtle text-info-emphasis border">{{ $p->persen }}%</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">-</span>
                                @endif
                            </td>
                            <td>Rp {{ number_format($p->subtotal_nilai, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($p->ppn, 0, ',', '.') }}</td>
                            <td class="fw-bold text-success fs-6">Rp {{ number_format($p->total_nilai, 0, ',', '.') }}</td>
                            <td class="text-end pe-3">
                                <a href="{{ route('penjualan.show', $p->idpenjualan) }}" class="btn btn-outline-primary btn-sm" title="Lihat Struk">
                                    <i class="bi bi-file-text"></i> Struk
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-cart-x fs-1 d-block mb-2 text-secondary"></i>
                                Belum ada riwayat transaksi penjualan.
                                <div class="mt-2">
                                    <a href="{{ route('penjualan.create') }}" class="btn btn-sm btn-success">Mulai Transaksi Pertama</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
