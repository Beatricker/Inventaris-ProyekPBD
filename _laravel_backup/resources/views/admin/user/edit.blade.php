@extends('layouts.admin')

@section('title', 'Edit User')
@section('page_title', 'Edit Data Pengguna')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit User: {{ $user->username }} (ID #{{ $user->iduser }})</span>
                <a href="{{ route('user.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('user.update', $user->iduser) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="username" class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $user->username) }}" maxlength="45" required autofocus>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="idrole" class="form-label fw-semibold">Role / Hak Akses <span class="text-danger">*</span></label>
                        <select name="idrole" id="idrole" class="form-select @error('idrole') is-invalid @enderror" required>
                            @foreach($roles as $r)
                                <option value="{{ $r->idrole }}" {{ old('idrole', $user->idrole) == $r->idrole ? 'selected' : '' }}>
                                    {{ $r->nama_role }}
                                </option>
                            @endforeach
                        </select>
                        @error('idrole')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">Ganti Password (Opsional)</label>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Biarkan kosong jika tidak ingin mengubah password" minlength="4">
                        <small class="text-muted">Hanya isi bidang ini jika Anda ingin memperbarui password dengan hash SHA2-256 baru.</small>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('user.index') }}" class="btn btn-light border px-4">Batal</a>
                        <button type="submit" class="btn btn-warning px-4 fw-semibold text-dark">
                            <i class="bi bi-check2-circle me-1"></i> Perbarui User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
