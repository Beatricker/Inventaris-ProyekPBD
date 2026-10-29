@extends('layouts.admin')

@section('title', 'Master Vendor')
@section('page_title', 'Master Data Vendor / Supplier')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-0 fw-bold"><i class="bi bi-truck me-2"></i>Daftar Master Vendor</h6>
            <small class="text-muted">Data diambil dari VIEW <code>v_vendor</code></small>
        </div>
        <a href="{{ route('vendor.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
            <i class="bi bi-plus-lg"></i> Tambah Vendor Baru
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3" style="width: 80px;">ID</th>
                        <th>Nama Vendor</th>
                        <th>Badan Hukum</th>
                        <th>Status</th>
                        <th class="text-end pe-3" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vendor as $v)
                        <tr>
                            <td class="ps-3 fw-bold text-muted">#{{ $v->idvendor }}</td>
                            <td class="fw-semibold">{{ $v->nama_vendor }}</td>
                            <td>
                                @if($v->badan_hukum == 'P')
                                    <span class="badge bg-primary">PT (Perseroan Terbatas)</span>
                                @elseif($v->badan_hukum == 'C')
                                    <span class="badge bg-info text-dark">CV (Persekutuan Komanditer)</span>
                                @elseif($v->badan_hukum == 'U')
                                    <span class="badge bg-secondary">UD (Usaha Dagang)</span>
                                @else
                                    <span class="badge bg-light text-dark border">{{ $v->badan_hukum }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $v->status == '1' || $v->status == 'A' ? 'bg-success' : 'bg-danger' }}">
                                    {{ $v->status == '1' || $v->status == 'A' ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('vendor.edit', $v->idvendor) }}" class="btn btn-warning btn-sm" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('vendor.destroy', $v->idvendor) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus vendor ini?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Belum ada data vendor.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
