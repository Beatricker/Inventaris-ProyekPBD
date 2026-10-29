@extends('layouts.admin')

@section('title', 'Master User')
@section('page_title', 'Master Data Pengguna (User)')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-0 fw-bold"><i class="bi bi-people me-2"></i>Daftar Master User</h6>
            <small class="text-muted">Data diambil dari VIEW <code>v_user</code> (password tersimpan dalam hash SHA2-256)</small>
        </div>
        <a href="{{ route('user.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
            <i class="bi bi-person-plus"></i> Tambah User Baru
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3" style="width: 80px;">ID</th>
                        <th>Username</th>
                        <th>Role Akses</th>
                        <th class="text-end pe-3" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($user as $u)
                        <tr>
                            <td class="ps-3 fw-bold text-muted">#{{ $u->iduser }}</td>
                            <td class="fw-semibold">
                                <i class="bi bi-person-circle text-primary me-2"></i>{{ $u->username }}
                                @if(session('iduser') == $u->iduser)
                                    <span class="badge bg-info text-dark ms-1">Anda</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $u->nama_role == 'Admin' ? 'bg-danger' : ($u->nama_role == 'Kasir' ? 'bg-success' : 'bg-warning text-dark') }} px-2 py-1">
                                    {{ $u->nama_role }}
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('user.edit', $u->iduser) }}" class="btn btn-warning btn-sm" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                @if(session('iduser') != $u->iduser)
                                    <form action="{{ route('user.destroy', $u->iduser) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">Belum ada data user.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
