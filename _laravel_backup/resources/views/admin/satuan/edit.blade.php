@extends('layouts.admin')

@section('title', 'Edit Satuan')
@section('page_title', 'Edit Data Satuan')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Satuan: {{ $satuan->nama_satuan }} (ID #{{ $satuan->idsatuan }})</span>
                <a href="{{ route('satuan.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('satuan.update', $satuan->idsatuan) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="nama_satuan" class="form-label fw-semibold">Nama Satuan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_satuan" id="nama_satuan" class="form-control @error('nama_satuan') is-invalid @enderror" value="{{ old('nama_satuan', $satuan->nama_satuan) }}" maxlength="45" required autofocus>
                        @error('nama_satuan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="1" {{ old('status', $satuan->status) == '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ old('status', $satuan->status) == '0' ? 'selected' : '' }}>Non-Aktif</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('satuan.index') }}" class="btn btn-light border px-4">Batal</a>
                        <button type="submit" class="btn btn-warning px-4 fw-semibold text-dark">
                            <i class="bi bi-check2-circle me-1"></i> Perbarui Satuan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
