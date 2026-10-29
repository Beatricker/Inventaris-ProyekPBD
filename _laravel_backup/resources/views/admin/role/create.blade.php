@extends('layouts.admin')

@section('title', 'Tambah Role')
@section('page_title', 'Tambah Role Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-plus-circle me-2"></i>Form Input Master Role</span>
                <a href="{{ route('role.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('role.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="nama_role" class="form-label fw-semibold">Nama Role / Hak Akses <span class="text-danger">*</span></label>
                        <input type="text" name="nama_role" id="nama_role" class="form-control @error('nama_role') is-invalid @enderror" value="{{ old('nama_role') }}" placeholder="Contoh: Manager, Supervisor, Staf" maxlength="100" required autofocus>
                        @error('nama_role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('role.index') }}" class="btn btn-light border px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-save me-1"></i> Simpan Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
