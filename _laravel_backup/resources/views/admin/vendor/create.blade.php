@extends('layouts.admin')

@section('title', 'Tambah Vendor')
@section('page_title', 'Tambah Vendor Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-plus-circle me-2"></i>Form Input Master Vendor</span>
                <a href="{{ route('vendor.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('vendor.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="nama_vendor" class="form-label fw-semibold">Nama Vendor / Perusahaan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_vendor" id="nama_vendor" class="form-control @error('nama_vendor') is-invalid @enderror" value="{{ old('nama_vendor') }}" placeholder="Contoh: PT Sumber Makmur Sejahtera" maxlength="100" required autofocus>
                        @error('nama_vendor')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="badan_hukum" class="form-label fw-semibold">Badan Hukum <span class="text-danger">*</span></label>
                            <select name="badan_hukum" id="badan_hukum" class="form-select @error('badan_hukum') is-invalid @enderror" required>
                                <option value="" disabled selected>-- Pilih Badan Hukum --</option>
                                <option value="P" {{ old('badan_hukum') == 'P' ? 'selected' : '' }}>P - PT (Perseroan Terbatas)</option>
                                <option value="C" {{ old('badan_hukum') == 'C' ? 'selected' : '' }}>C - CV (Persekutuan Komanditer)</option>
                                <option value="U" {{ old('badan_hukum') == 'U' ? 'selected' : '' }}>U - UD (Usaha Dagang)</option>
                            </select>
                            @error('badan_hukum')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label fw-semibold">Status Vendor <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Non-Aktif</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('vendor.index') }}" class="btn btn-light border px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-save me-1"></i> Simpan Data Vendor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
