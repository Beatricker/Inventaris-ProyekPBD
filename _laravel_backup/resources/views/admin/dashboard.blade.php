@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard ' . session('role'))

@section('content')
@php
    $userRole = strtolower(trim(session('role', '')));
@endphp

<!-- Banner Sambutan & Hak Akses Role -->
<div class="alert alert-white border shadow-sm d-flex align-items-center justify-content-between mb-4 p-3 bg-white">
    <div class="d-flex align-items-center gap-3">
        <div class="p-3 bg-primary-subtle text-primary rounded-circle fs-3">
            @if($userRole === 'admin')
                <i class="bi bi-shield-check"></i>
            @elseif($userRole === 'kasir')
                <i class="bi bi-cart-check"></i>
            @elseif($userRole === 'gudang')
                <i class="bi bi-box-seam"></i>
            @else
                <i class="bi bi-person"></i>
            @endif
        </div>
        <div>
            <h5 class="fw-bold mb-1">Halo, {{ session('username') }}!</h5>
            <p class="mb-0 text-secondary small">
                Anda masuk sebagai 
                <strong>
                    @if($userRole === 'admin')
                        Super Administrator (Hak Akses Penuh ke 6 Master Tabel)
                    @elseif($userRole === 'kasir')
                        Petugas Kasir (Akses Informasi Katalog Produk & Margin)
                    @elseif($userRole === 'gudang')
                        Petugas Gudang (Fokus Pengelolaan Barang, Satuan, dan Vendor)
                    @else
                        {{ session('role') }}
                    @endif
                </strong>
            </p>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- 1. TAMPILAN DASHBOARD UNTUK KASIR -->
<!-- ============================================== -->
@if($userRole === 'kasir')
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card h-100 border-start border-primary border-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small fw-semibold text-uppercase">Katalog Barang Aktif</span>
                        <h2 class="fw-bold my-1 text-primary">{{ $totalBarang }}</h2>
                        <a href="{{ route('barang.index') }}" class="small text-primary text-decoration-none">
                            Lihat Katalog Harga &rarr;
                        </a>
                    </div>
                    <div class="bg-primary-subtle text-primary p-3 rounded-3 fs-3">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100 border-start border-info border-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small fw-semibold text-uppercase">Margin Penjualan</span>
                        <h2 class="fw-bold my-1 text-dark">{{ $totalMargin }}</h2>
                        <a href="{{ route('margin.index') }}" class="small text-info text-decoration-none">
                            Cek Margin Aktif &rarr;
                        </a>
                    </div>
                    <div class="bg-info-subtle text-info p-3 rounded-3 fs-3">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Katalog Barang untuk Kasir -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-bold"><i class="bi bi-box-seam me-2"></i> Katalog Harga Barang (dari <code>v_barang</code>)</span>
            <a href="{{ route('barang.index') }}" class="btn btn-sm btn-outline-primary">Semua Barang</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Nama Barang</th>
                            <th>Jenis</th>
                            <th>Satuan</th>
                            <th>Harga Jual</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBarang as $b)
                            <tr>
                                <td class="ps-3 fw-semibold">{{ $b->nama_barang }}</td>
                                <td>
                                    <span class="badge {{ $b->jenis == 'B' ? 'bg-primary' : 'bg-warning text-dark' }}">
                                        {{ $b->jenis }}
                                    </span>
                                </td>
                                <td>{{ $b->nama_satuan }}</td>
                                <td>Rp {{ number_format($b->harga, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ $b->status == 1 ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $b->status == 1 ? 'Aktif' : 'Non-Aktif' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-3 text-muted">Belum ada data barang.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<!-- ============================================== -->
<!-- 2. TAMPILAN DASHBOARD UNTUK GUDANG -->
<!-- ============================================== -->
@elseif($userRole === 'gudang')
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card h-100 border-start border-primary border-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small fw-semibold text-uppercase">1. Master Satuan</span>
                        <h2 class="fw-bold my-1 text-dark">{{ $totalSatuan }}</h2>
                        <a href="{{ route('satuan.index') }}" class="small text-primary text-decoration-none">
                            Kelola Satuan &rarr;
                        </a>
                    </div>
                    <div class="bg-primary-subtle text-primary p-3 rounded-3 fs-3">
                        <i class="bi bi-rulers"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 border-start border-success border-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small fw-semibold text-uppercase">2. Master Barang</span>
                        <h2 class="fw-bold my-1 text-dark">{{ $totalBarang }}</h2>
                        <a href="{{ route('barang.index') }}" class="small text-success text-decoration-none">
                            Kelola Barang &rarr;
                        </a>
                    </div>
                    <div class="bg-success-subtle text-success p-3 rounded-3 fs-3">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 border-start border-info border-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small fw-semibold text-uppercase">3. Master Vendor</span>
                        <h2 class="fw-bold my-1 text-dark">{{ $totalVendor }}</h2>
                        <a href="{{ route('vendor.index') }}" class="small text-info text-decoration-none">
                            Kelola Vendor &rarr;
                        </a>
                    </div>
                    <div class="bg-info-subtle text-info p-3 rounded-3 fs-3">
                        <i class="bi bi-truck"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Barang dari v_barang -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-bold"><i class="bi bi-box-seam me-2"></i> Daftar Barang Terbaru (dari <code>v_barang</code>)</span>
            <a href="{{ route('barang.index') }}" class="btn btn-sm btn-outline-primary">Semua Barang</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Nama Barang</th>
                            <th>Jenis</th>
                            <th>Satuan</th>
                            <th>Harga Jual</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBarang as $b)
                            <tr>
                                <td class="ps-3 fw-semibold">{{ $b->nama_barang }}</td>
                                <td>
                                    <span class="badge {{ $b->jenis == 'B' ? 'bg-primary' : 'bg-warning text-dark' }}">
                                        {{ $b->jenis }}
                                    </span>
                                </td>
                                <td>{{ $b->nama_satuan }}</td>
                                <td>Rp {{ number_format($b->harga, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ $b->status == 1 ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $b->status == 1 ? 'Aktif' : 'Non-Aktif' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-3 text-muted">Belum ada data barang.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<!-- ============================================== -->
<!-- 3. TAMPILAN DASHBOARD UNTUK SUPER ADMIN -->
<!-- ============================================== -->
@else
    <div class="row g-3 mb-4">
        <!-- 1. Satuan -->
        <div class="col-sm-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small fw-semibold text-uppercase">1. Master Satuan</span>
                        <h2 class="fw-bold my-1 text-dark">{{ $totalSatuan }}</h2>
                        <a href="{{ route('satuan.index') }}" class="small text-primary text-decoration-none">Kelola &rarr;</a>
                    </div>
                    <div class="bg-primary-subtle text-primary p-3 rounded-3 fs-3"><i class="bi bi-rulers"></i></div>
                </div>
            </div>
        </div>

        <!-- 2. Barang -->
        <div class="col-sm-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small fw-semibold text-uppercase">2. Master Barang</span>
                        <h2 class="fw-bold my-1 text-dark">{{ $totalBarang }}</h2>
                        <a href="{{ route('barang.index') }}" class="small text-primary text-decoration-none">Kelola &rarr;</a>
                    </div>
                    <div class="bg-primary-subtle text-primary p-3 rounded-3 fs-3"><i class="bi bi-box-seam"></i></div>
                </div>
            </div>
        </div>

        <!-- 3. Vendor -->
        <div class="col-sm-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small fw-semibold text-uppercase">3. Master Vendor</span>
                        <h2 class="fw-bold my-1 text-dark">{{ $totalVendor }}</h2>
                        <a href="{{ route('vendor.index') }}" class="small text-primary text-decoration-none">Kelola &rarr;</a>
                    </div>
                    <div class="bg-primary-subtle text-primary p-3 rounded-3 fs-3"><i class="bi bi-truck"></i></div>
                </div>
            </div>
        </div>

        <!-- 4. User -->
        <div class="col-sm-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small fw-semibold text-uppercase">4. Master User</span>
                        <h2 class="fw-bold my-1 text-dark">{{ $totalUser }}</h2>
                        <a href="{{ route('user.index') }}" class="small text-primary text-decoration-none">Kelola &rarr;</a>
                    </div>
                    <div class="bg-primary-subtle text-primary p-3 rounded-3 fs-3"><i class="bi bi-people"></i></div>
                </div>
            </div>
        </div>

        <!-- 5. Role -->
        <div class="col-sm-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small fw-semibold text-uppercase">5. Master Role</span>
                        <h2 class="fw-bold my-1 text-dark">{{ $totalRole }}</h2>
                        <a href="{{ route('role.index') }}" class="small text-primary text-decoration-none">Kelola &rarr;</a>
                    </div>
                    <div class="bg-primary-subtle text-primary p-3 rounded-3 fs-3"><i class="bi bi-shield-lock"></i></div>
                </div>
            </div>
        </div>

        <!-- 6. Margin Penjualan -->
        <div class="col-sm-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small fw-semibold text-uppercase">6. Margin Penjualan</span>
                        <h2 class="fw-bold my-1 text-dark">{{ $totalMargin }}</h2>
                        <a href="{{ route('margin.index') }}" class="small text-primary text-decoration-none">Kelola &rarr;</a>
                    </div>
                    <div class="bg-primary-subtle text-primary p-3 rounded-3 fs-3"><i class="bi bi-graph-up-arrow"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ringkasan Master Data untuk Super Admin -->
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-box-seam me-2"></i> Daftar Barang Terbaru (dari <code>v_barang</code>)</span>
                    <a href="{{ route('barang.index') }}" class="btn btn-sm btn-outline-primary">Kelola Barang</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Nama Barang</th>
                                    <th>Jenis</th>
                                    <th>Satuan</th>
                                    <th>Harga</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentBarang as $b)
                                    <tr>
                                        <td class="ps-3 fw-semibold">{{ $b->nama_barang }}</td>
                                        <td>
                                            <span class="badge {{ $b->jenis == 'B' ? 'bg-primary' : 'bg-warning text-dark' }}">
                                                {{ $b->jenis }}
                                            </span>
                                        </td>
                                        <td>{{ $b->nama_satuan }}</td>
                                        <td>Rp {{ number_format($b->harga, 0, ',', '.') }}</td>
                                        <td>
                                            <span class="badge {{ $b->status == 1 ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $b->status == 1 ? 'Aktif' : 'Non-Aktif' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-3 text-muted">Belum ada data barang.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-people me-2"></i> Pengguna Sistem (dari <code>v_user</code>)</span>
                    <a href="{{ route('user.index') }}" class="btn btn-sm btn-outline-primary">Semua User</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Username</th>
                                    <th>Role</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentUser as $u)
                                    <tr>
                                        <td class="ps-3 fw-semibold">
                                            <i class="bi bi-person-circle text-muted me-1"></i> {{ $u->username }}
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary border">
                                                {{ $u->nama_role }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center py-3 text-muted">Belum ada user.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection
