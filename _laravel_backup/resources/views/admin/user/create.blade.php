@extends('layouts.admin')

@section('title', 'Tambah User')
@section('page_title', 'Tambah Pengguna Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-person-plus me-2"></i>Form Input Master User</span>
                <a href="{{ route('user.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('user.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="username" class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" placeholder="Contoh: kasir_tiga" maxlength="45" required autofocus>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimal 4 karakter (otomatis di-hash SHA2-256)" minlength="4" required>
                        <small class="text-muted">Password akan dienkripsi secara aman menggunakan fungsi SQL <code>SHA2(?, 256)</code>.</small>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="idrole" class="form-label fw-semibold">Role / Hak Akses <span class="text-danger">*</span></label>
                        <select name="idrole" id="idrole" class="form-select @error('idrole') is-invalid @enderror" required>
                            <option value="" disabled selected>-- Pilih Role --</option>
                            @foreach($roles as $r)
                                <option value="{{ $r->idrole }}" {{ old('idrole') == $r->idrole ? 'selected' : '' }}>
                                    {{ $r->nama_role }}
                                </option>
                            @endforeach
                        </select>
                        @error('idrole')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('user.index') }}" class="btn btn-light border px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-save me-1"></i> Simpan User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
