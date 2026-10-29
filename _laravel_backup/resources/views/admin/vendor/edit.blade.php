@extends('layouts.admin')

@section('title', 'Edit Vendor')
@section('page_title', 'Edit Data Vendor')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Vendor: {{ $vendor->nama_vendor }} (ID #{{ $vendor->idvendor }})</span>
                <a href="{{ route('vendor.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('vendor.update', $vendor->idvendor) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="nama_vendor" class="form-label fw-semibold">Nama Vendor / Perusahaan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_vendor" id="nama_vendor" class="form-control @error('nama_vendor') is-invalid @enderror" value="{{ old('nama_vendor', $vendor->nama_vendor) }}" maxlength="100" required autofocus>
                        @error('nama_vendor')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="badan_hukum" class="form-label fw-semibold">Badan Hukum <span class="text-danger">*</span></label>
                            <select name="badan_hukum" id="badan_hukum" class="form-select @error('badan_hukum') is-invalid @enderror" required>
                                <option value="P" {{ old('badan_hukum', $vendor->badan_hukum) == 'P' ? 'selected' : '' }}>P - PT (Perseroan Terbatas)</option>
                                <option value="C" {{ old('badan_hukum', $vendor->badan_hukum) == 'C' ? 'selected' : '' }}>C - CV (Persekutuan Komanditer)</option>
                                <option value="U" {{ old('badan_hukum', $vendor->badan_hukum) == 'U' ? 'selected' : '' }}>U - UD (Usaha Dagang)</option>
                            </select>
                            @error('badan_hukum')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label fw-semibold">Status Vendor <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="1" {{ old('status', $vendor->status) == '1' || old('status', $vendor->status) == 'A' ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('status', $vendor->status) == '0' ? 'selected' : '' }}>Non-Aktif</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('vendor.index') }}" class="btn btn-light border px-4">Batal</a>
                        <button type="submit" class="btn btn-warning px-4 fw-semibold text-dark">
                            <i class="bi bi-check2-circle me-1"></i> Perbarui Vendor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
