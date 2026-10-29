@extends('layouts.admin')

@section('title', 'Edit Role')
@section('page_title', 'Edit Data Role')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Role: {{ $role->nama_role }} (ID #{{ $role->idrole }})</span>
                <a href="{{ route('role.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('role.update', $role->idrole) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-4">
                        <label for="nama_role" class="form-label fw-semibold">Nama Role / Hak Akses <span class="text-danger">*</span></label>
                        <input type="text" name="nama_role" id="nama_role" class="form-control @error('nama_role') is-invalid @enderror" value="{{ old('nama_role', $role->nama_role) }}" maxlength="100" required autofocus>
                        @error('nama_role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('role.index') }}" class="btn btn-light border px-4">Batal</a>
                        <button type="submit" class="btn btn-warning px-4 fw-semibold text-dark">
                            <i class="bi bi-check2-circle me-1"></i> Perbarui Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
