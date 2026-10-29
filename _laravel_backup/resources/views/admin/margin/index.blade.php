@extends('layouts.admin')

@section('title', 'Margin Penjualan')
@section('page_title', 'Master Margin Penjualan')

@section('content')
@php
    $isAdmin = strtolower(session('role')) === 'admin';
@endphp

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-0 fw-bold"><i class="bi bi-graph-up-arrow me-2"></i>Daftar Master Margin Penjualan</h6>
            <small class="text-muted">Data diambil dari VIEW <code>v_margin</code></small>
        </div>
        @if($isAdmin)
            <a href="{{ route('margin.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-plus-lg"></i> Tambah Margin Baru
            </a>
        @else
            <span class="badge bg-secondary-subtle text-secondary border">Mode Lihat (Hak Akses Kasir)</span>
        @endif
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3" style="width: 80px;">ID</th>
                        <th>Persentase Margin (%)</th>
                        <th>User Pembuat</th>
                        <th>Status</th>
                        <th>Waktu Dibuat</th>
                        @if($isAdmin)
                            <th class="text-end pe-3" style="width: 140px;">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($margin as $m)
                        <tr>
                            <td class="ps-3 fw-bold text-muted">#{{ $m->idmargin_penjualan }}</td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6 px-3 py-1">
                                    {{ $m->persen }}%
                                </span>
                            </td>
                            <td class="fw-semibold">
                                <i class="bi bi-person text-muted me-1"></i>{{ $m->username }}
                            </td>
                            <td>
                                <span class="badge {{ $m->status == 1 ? 'bg-success' : 'bg-danger' }}">
                                    {{ $m->status == 1 ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </td>
                            <td class="small text-muted">{{ $m->created_at }}</td>
                            @if($isAdmin)
                                <td class="text-end pe-3">
                                    <a href="{{ route('margin.edit', $m->idmargin_penjualan) }}" class="btn btn-warning btn-sm" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('margin.destroy', $m->idmargin_penjualan) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus margin penjualan ini?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isAdmin ? '6' : '5' }}" class="text-center py-4 text-muted">Belum ada data margin penjualan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
