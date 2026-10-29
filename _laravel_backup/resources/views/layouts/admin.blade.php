<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PutPutMart') - Panel Penjual</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: #f8fafc;
        }
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: #1e293b;
            color: #e2e8f0;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }
        .sidebar .brand {
            padding: 20px;
            font-size: 1.25rem;
            font-weight: 700;
            color: #fff;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar .nav-link {
            color: #94a3b8;
            padding: 12px 20px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 3px solid transparent;
            transition: all 0.2s;
            text-decoration: none;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.08);
            border-left-color: #3b82f6;
        }
        .sidebar .nav-header {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            padding: 16px 20px 8px;
            font-weight: 700;
        }
        .main-wrapper {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .topbar {
            height: 64px;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .content-area {
            padding: 28px;
            flex: 1;
        }
        .card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600;
            padding: 14px 20px;
        }
        .footer {
            background: #fff;
            border-top: 1px solid #e2e8f0;
            padding: 16px 28px;
            color: #64748b;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>

    @php
        $userRole = strtolower(trim(session('role', '')));
    @endphp

    <!-- Sidebar Penjual Berbasis Role -->
    <aside class="sidebar">
        <div class="brand">
            <i class="bi bi-shop text-primary fs-4"></i>
            <span>PutPutMart</span>
        </div>

        <nav class="nav flex-column py-2">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            <!-- Menu Master Logistik / Inventaris: Gudang & Admin -->
            @if(in_array($userRole, ['admin', 'gudang']))
                <div class="nav-header">Logistik & Barang</div>
                <a class="nav-link {{ request()->routeIs('satuan.*') ? 'active' : '' }}" href="{{ route('satuan.index') }}">
                    <i class="bi bi-rulers"></i> 1. Master Satuan
                </a>
                <a class="nav-link {{ request()->routeIs('barang.*') ? 'active' : '' }}" href="{{ route('barang.index') }}">
                    <i class="bi bi-box-seam"></i> 2. Master Barang
                </a>
                <a class="nav-link {{ request()->routeIs('vendor.*') ? 'active' : '' }}" href="{{ route('vendor.index') }}">
                    <i class="bi bi-truck"></i> 3. Master Vendor
                </a>
            @elseif($userRole === 'kasir')
                <!-- Kasir hanya diizinkan melihat katalog barang -->
                <div class="nav-header">Katalog Produk</div>
                <a class="nav-link {{ request()->routeIs('barang.*') ? 'active' : '' }}" href="{{ route('barang.index') }}">
                    <i class="bi bi-box-seam"></i> Katalog Harga Barang
                </a>
            @endif

            <!-- Menu Manajemen Pengguna & Finansial: Super Admin -->
            @if($userRole === 'admin')
                <div class="nav-header">Pengaturan Super Admin</div>
                <a class="nav-link {{ request()->routeIs('user.*') ? 'active' : '' }}" href="{{ route('user.index') }}">
                    <i class="bi bi-people"></i> 4. Master User
                </a>
                <a class="nav-link {{ request()->routeIs('role.*') ? 'active' : '' }}" href="{{ route('role.index') }}">
                    <i class="bi bi-shield-lock"></i> 5. Master Role
                </a>
                <a class="nav-link {{ request()->routeIs('margin.*') ? 'active' : '' }}" href="{{ route('margin.index') }}">
                    <i class="bi bi-graph-up-arrow"></i> 6. Margin Penjualan
                </a>
            @elseif($userRole === 'kasir')
                <!-- Kasir bisa melihat info margin aktif -->
                <a class="nav-link {{ request()->routeIs('margin.*') ? 'active' : '' }}" href="{{ route('margin.index') }}">
                    <i class="bi bi-graph-up-arrow"></i> Info Margin Penjualan
                </a>
            @endif
        </nav>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="main-wrapper">
        <!-- Topbar -->
        <header class="topbar">
            <div>
                <h5 class="mb-0 fw-bold">@yield('page_title', 'Dashboard')</h5>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-weight: 600;">
                        {{ strtoupper(substr(session('username', 'U'), 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-semibold lh-1">{{ session('username', 'User') }}</div>
                        <small>
                            @if($userRole === 'admin')
                                <span class="badge bg-danger">Super Admin</span>
                            @elseif($userRole === 'kasir')
                                <span class="badge bg-success">Petugas Kasir</span>
                            @elseif($userRole === 'gudang')
                                <span class="badge bg-warning text-dark">Petugas Gudang</span>
                            @else
                                <span class="badge bg-secondary">{{ session('role') }}</span>
                            @endif
                        </small>
                    </div>
                </div>
                <div class="vr"></div>
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="content-area">
            <!-- Flash Message Alert -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-check-circle-fill"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <div class="fw-bold mb-1"><i class="bi bi-exclamation-circle"></i> Terjadi kesalahan input:</div>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="footer d-flex justify-content-between align-items-center">
            <span>&copy; {{ date('Y') }} <strong>PutPutMart</strong> - Pemrograman Basis Data (Praktikum)</span>
            <span class="badge bg-light text-secondary border">Role: {{ session('role') }} &bull; Raw SQL Active</span>
        </footer>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
