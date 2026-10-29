@extends('layouts.admin')

@section('title', 'Master Satuan')
@section('page_title', 'Master Data Satuan')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-0 fw-bold"><i class="bi bi-rulers me-2"></i>Daftar Master Satuan</h6>
            <small class="text-muted">Data diambil dari VIEW <code>v_satuan</code></small>
        </div>
        <a href="{{ route('satuan.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
            <i class="bi bi-plus-lg"></i> Tambah Satuan Baru
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3" style="width: 80px;">ID</th>
                        <th>Nama Satuan</th>
                        <th>Status</th>
                        <th class="text-end pe-3" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($satuan as $s)
                        <tr>
                            <td class="ps-3 fw-bold text-muted">#{{ $s->idsatuan }}</td>
                            <td class="fw-semibold">{{ $s->nama_satuan }}</td>
                            <td>
                                <span class="badge {{ $s->status == 1 ? 'bg-success' : 'bg-danger' }}">
                                    {{ $s->status == 1 ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('satuan.edit', $s->idsatuan) }}" class="btn btn-warning btn-sm" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('satuan.destroy', $s->idsatuan) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus satuan ini?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">Belum ada data satuan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
