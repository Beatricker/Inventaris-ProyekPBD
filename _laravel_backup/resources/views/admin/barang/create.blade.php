@extends('layouts.admin')

@section('title', 'Tambah Barang')
@section('page_title', 'Tambah Barang Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-plus-circle me-2"></i>Form Input Master Barang</span>
                <a href="{{ route('barang.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('barang.store') }}" method="POST">
                    @csrf
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="jenis" class="form-label fw-semibold">Jenis Barang <span class="text-danger">*</span></label>
                            <select name="jenis" id="jenis" class="form-select @error('jenis') is-invalid @enderror" required>
                                <option value="" disabled selected>-- Pilih Jenis --</option>
                                <option value="B" {{ old('jenis') == 'B' ? 'selected' : '' }}>B - Barang Jadi / Dagangan</option>
                                <option value="K" {{ old('jenis') == 'K' ? 'selected' : '' }}>K - Komponen / Konsumsi</option>
                            </select>
                            @error('jenis')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="idsatuan" class="form-label fw-semibold">Satuan Barang <span class="text-danger">*</span></label>
                            <select name="idsatuan" id="idsatuan" class="form-select @error('idsatuan') is-invalid @enderror" required>
                                <option value="" disabled selected>-- Pilih Satuan --</option>
                                @foreach($satuan as $s)
                                    <option value="{{ $s->idsatuan }}" {{ old('idsatuan') == $s->idsatuan ? 'selected' : '' }}>
                                        {{ $s->nama_satuan }}
                                    </option>
                                @endforeach
                            </select>
                            @error('idsatuan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="nama" class="form-label fw-semibold">Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" name="nama" id="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama') }}" placeholder="Contoh: Buku Tulis 58 Lembar" maxlength="45" required>
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="harga" class="form-label fw-semibold">Harga Jual (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="harga" id="harga" class="form-control @error('harga') is-invalid @enderror" value="{{ old('harga') }}" placeholder="0" min="0" required>
                            </div>
                            @error('harga')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label fw-semibold">Status Ketersediaan <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Aktif (Tersedia untuk Dijual)</option>
                                <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Non-Aktif (Disembunyikan)</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('barang.index') }}" class="btn btn-light border px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-save me-1"></i> Simpan Data Barang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
