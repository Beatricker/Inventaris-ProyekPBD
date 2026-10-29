<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Tampilkan ringkasan dashboard yang disesuaikan dengan role pengguna.
     */
    public function index()
    {
        $role = strtolower(trim(session('role', '')));

        // Data statistik umum
        $totalBarang = DB::select("SELECT COUNT(*) AS total FROM barang")[0]->total ?? 0;
        $totalSatuan = DB::select("SELECT COUNT(*) AS total FROM satuan")[0]->total ?? 0;
        $totalVendor = DB::select("SELECT COUNT(*) AS total FROM vendor")[0]->total ?? 0;
        $totalUser   = DB::select("SELECT COUNT(*) AS total FROM user")[0]->total ?? 0;
        $totalRole   = DB::select("SELECT COUNT(*) AS total FROM role")[0]->total ?? 0;
        $totalMargin = DB::select("SELECT COUNT(*) AS total FROM margin_penjualan")[0]->total ?? 0;

        // Ambil data terbaru melalui VIEW untuk ringkasan master data
        $recentBarang = DB::select("SELECT * FROM v_barang ORDER BY idbarang DESC LIMIT 5");
        $recentUser   = DB::select("SELECT * FROM v_user ORDER BY iduser DESC LIMIT 5");

        return view('admin.dashboard', compact(
            'role',
            'totalBarang',
            'totalSatuan',
            'totalVendor',
            'totalUser',
            'totalRole',
            'totalMargin',
            'recentBarang',
            'recentUser'
        ));
    }
}
