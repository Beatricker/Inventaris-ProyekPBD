@extends('layouts.admin')

@section('title', 'Tambah Satuan')
@section('page_title', 'Tambah Satuan Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-plus-circle me-2"></i>Form Input Master Satuan</span>
                <a href="{{ route('satuan.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('satuan.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="nama_satuan" class="form-label fw-semibold">Nama Satuan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_satuan" id="nama_satuan" class="form-control @error('nama_satuan') is-invalid @enderror" value="{{ old('nama_satuan') }}" placeholder="Contoh: Pcs, Box, Kg, Liter" maxlength="45" required autofocus>
                        @error('nama_satuan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Non-Aktif</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('satuan.index') }}" class="btn btn-light border px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-save me-1"></i> Simpan Satuan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
