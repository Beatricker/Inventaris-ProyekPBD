@extends('layouts.admin')

@section('title', 'Tambah Margin Penjualan')
@section('page_title', 'Tambah Margin Penjualan Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-plus-circle me-2"></i>Form Input Margin Penjualan</span>
                <a href="{{ route('margin.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('margin.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="persen" class="form-label fw-semibold">Persentase Margin Keuntungan (%) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="persen" id="persen" class="form-control @error('persen') is-invalid @enderror" value="{{ old('persen') }}" placeholder="Contoh: 10 atau 15.5" min="0" max="100" required autofocus>
                            <span class="input-group-text">%</span>
                        </div>
                        @error('persen')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="iduser" class="form-label fw-semibold">User Penanggung Jawab <span class="text-danger">*</span></label>
                        <select name="iduser" id="iduser" class="form-select @error('iduser') is-invalid @enderror" required>
                            <option value="" disabled selected>-- Pilih User --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->iduser }}" {{ (old('iduser') == $u->iduser || (old('iduser') == null && session('iduser') == $u->iduser)) ? 'selected' : '' }}>
                                    {{ $u->username }}
                                </option>
                            @endforeach
                        </select>
                        @error('iduser')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="status" class="form-label fw-semibold">Status Margin <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Non-Aktif</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('margin.index') }}" class="btn btn-light border px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-save me-1"></i> Simpan Margin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
