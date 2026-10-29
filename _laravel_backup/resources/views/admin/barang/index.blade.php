@extends('layouts.admin')

@section('title', 'Master Barang')
@section('page_title', 'Master Data Barang')

@section('content')
@php
    $canManage = in_array(strtolower(session('role')), ['admin', 'gudang']);
@endphp

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-0 fw-bold"><i class="bi bi-box-seam me-2"></i>Daftar Master Barang</h6>
            <small class="text-muted">Data diambil dari VIEW <code>v_barang</code></small>
        </div>
        @if($canManage)
            <a href="{{ route('barang.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-plus-lg"></i> Tambah Barang Baru
            </a>
        @else
            <span class="badge bg-secondary-subtle text-secondary border">Mode Lihat (Katalog Kasir)</span>
        @endif
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3" style="width: 70px;">ID</th>
                        <th>Jenis</th>
                        <th>Nama Barang</th>
                        <th>Satuan</th>
                        <th>Harga Jual</th>
                        <th>Status</th>
                        @if($canManage)
                            <th class="text-end pe-3" style="width: 140px;">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($barang as $b)
                        <tr>
                            <td class="ps-3 fw-bold text-muted">#{{ $b->idbarang }}</td>
                            <td>
                                <span class="badge {{ $b->jenis == 'B' ? 'bg-primary' : 'bg-warning text-dark' }}">
                                    {{ $b->jenis == 'B' ? 'B (Barang Jadi)' : 'K (Konsumsi)' }}
                                </span>
                            </td>
                            <td class="fw-semibold">{{ $b->nama_barang }}</td>
                            <td>{{ $b->nama_satuan }}</td>
                            <td class="fw-bold text-primary">Rp {{ number_format($b->harga, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge {{ $b->status == 1 ? 'bg-success' : 'bg-danger' }}">
                                    {{ $b->status == 1 ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </td>
                            @if($canManage)
                                <td class="text-end pe-3">
                                    <a href="{{ route('barang.edit', $b->idbarang) }}" class="btn btn-warning btn-sm" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('barang.destroy', $b->idbarang) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus barang ini?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canManage ? '7' : '6' }}" class="text-center py-4 text-muted">Belum ada data barang.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
