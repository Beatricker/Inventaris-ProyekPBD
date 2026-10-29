<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenjualanController extends Controller
{
    /**
     * Tampilkan riwayat transaksi penjualan.
     */
    public function index()
    {
        // Query riwayat penjualan dengan Raw SQL murni
        $penjualan = DB::select(
            "SELECT p.*, u.username, m.persen 
             FROM penjualan p 
             JOIN user u ON p.iduser = u.iduser 
             LEFT JOIN margin_penjualan m ON p.idmargin_penjualan = m.idmargin_penjualan 
             ORDER BY p.idpenjualan DESC"
        );

        return view('admin.penjualan.index', compact('penjualan'));
    }

    /**
     * Form kasir untuk mencatat transaksi penjualan baru.
     */
    public function create()
    {
        // Ambil barang yang aktif dari VIEW v_barang
        $barang = DB::select("SELECT * FROM v_barang WHERE status = 1 ORDER BY nama_barang ASC");

        // Ambil margin penjualan aktif
        $margin = DB::select("SELECT * FROM v_margin WHERE status = 1 ORDER BY idmargin_penjualan DESC LIMIT 1");
        $activeMargin = !empty($margin) ? $margin[0] : null;

        return view('admin.penjualan.create', compact('barang', 'activeMargin'));
    }

    /**
     * Simpan transaksi penjualan dan detailnya ke database menggunakan Raw SQL.
     */
    public function store(Request $request)
    {
        $request->validate([
            'idbarang' => 'required|array|min:1',
            'idbarang.*' => 'required|integer',
            'jumlah' => 'required|array|min:1',
            'jumlah.*' => 'required|integer|min:1',
            'idmargin_penjualan' => 'nullable|integer',
        ]);

        $barangIds = $request->idbarang;
        $quantities = $request->jumlah;
        $idmargin = $request->idmargin_penjualan ?: null;

        $subtotalNilai = 0;
        $itemsToInsert = [];

        // Hitung harga setiap barang dengan Raw SQL
        foreach ($barangIds as $index => $idb) {
            $qty = (int) $quantities[$index];
            $barang = DB::select("SELECT idbarang, harga, nama_barang FROM v_barang WHERE idbarang = ? LIMIT 1", [$idb]);
            
            if (empty($barang)) {
                continue;
            }

            $harga = (int) $barang[0]->harga;
            $subtotalItem = $harga * $qty;
            $subtotalNilai += $subtotalItem;

            $itemsToInsert[] = [
                'idbarang' => $idb,
                'harga_satuan' => $harga,
                'jumlah' => $qty,
                'subtotal' => $subtotalItem,
            ];
        }

        if (empty($itemsToInsert)) {
            return redirect()->back()->with('error', 'Tidak ada barang valid yang dipilih untuk transaksi!');
        }

        // PPN 11%
        $ppn = (int) round($subtotalNilai * 0.11);
        $totalNilai = $subtotalNilai + $ppn;
        $iduser = session('iduser', 1);

        // 1. INSERT ke tabel penjualan (Raw SQL)
        DB::insert(
            "INSERT INTO penjualan (created_at, subtotal_nilai, ppn, total_nilai, iduser, idmargin_penjualan) 
             VALUES (NOW(), ?, ?, ?, ?, ?)",
            [$subtotalNilai, $ppn, $totalNilai, $iduser, $idmargin]
        );

        $idpenjualan = DB::getPdo()->lastInsertId();

        // 2. INSERT ke tabel detail_penjualan (Raw SQL)
        foreach ($itemsToInsert as $item) {
            DB::insert(
                "INSERT INTO detail_penjualan (harga_satuan, jumlah, subtotal, penjualan_idpenjualan, idbarang) 
                 VALUES (?, ?, ?, ?, ?)",
                [
                    $item['harga_satuan'],
                    $item['jumlah'],
                    $item['subtotal'],
                    $idpenjualan,
                    $item['idbarang'],
                ]
            );
        }

        return redirect()->route('penjualan.show', $idpenjualan)->with('success', 'Transaksi penjualan berhasil disimpan!');
    }

    /**
     * Tampilkan detail struk transaksi penjualan.
     */
    public function show($id)
    {
        $penjualan = DB::select(
            "SELECT p.*, u.username, m.persen 
             FROM penjualan p 
             JOIN user u ON p.iduser = u.iduser 
             LEFT JOIN margin_penjualan m ON p.idmargin_penjualan = m.idmargin_penjualan 
             WHERE p.idpenjualan = ? 
             LIMIT 1",
            [$id]
        );

        if (empty($penjualan)) {
            return redirect()->route('penjualan.index')->with('error', 'Data transaksi tidak ditemukan!');
        }

        $detail = DB::select(
            "SELECT dp.*, b.nama_barang, b.nama_satuan, b.jenis 
             FROM detail_penjualan dp 
             JOIN v_barang b ON dp.idbarang = b.idbarang 
             WHERE dp.penjualan_idpenjualan = ?",
            [$id]
        );

        return view('admin.penjualan.show', [
            'penjualan' => $penjualan[0],
            'detail' => $detail,
        ]);
    }
}
