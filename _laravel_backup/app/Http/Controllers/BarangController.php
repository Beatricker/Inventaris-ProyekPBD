<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{
    /**
     * Tampilkan daftar master barang menggunakan VIEW v_barang.
     */
    public function index()
    {
        $barang = DB::select("SELECT * FROM v_barang ORDER BY idbarang DESC");
        return view('admin.barang.index', compact('barang'));
    }

    /**
     * Form tambah barang baru.
     */
    public function create()
    {
        $satuan = DB::select("SELECT * FROM v_satuan WHERE status = 1 ORDER BY nama_satuan ASC");
        return view('admin.barang.create', compact('satuan'));
    }

    /**
     * Simpan data barang baru dengan Raw SQL INSERT.
     */
    public function store(Request $request)
    {
        $request->validate([
            'jenis' => 'required|in:B,K',
            'nama' => 'required|string|max:45',
            'idsatuan' => 'required|integer',
            'status' => 'required|in:0,1',
            'harga' => 'required|numeric|min:0',
        ]);

        DB::insert(
            "INSERT INTO barang (jenis, nama, idsatuan, status, harga) VALUES (?, ?, ?, ?, ?)",
            [
                $request->jenis,
                $request->nama,
                $request->idsatuan,
                $request->status,
                $request->harga,
            ]
        );

        return redirect()->route('barang.index')->with('success', 'Barang baru berhasil ditambahkan!');
    }

    /**
     * Form edit barang.
     */
    public function edit($id)
    {
        $barang = DB::select("SELECT * FROM barang WHERE idbarang = ? LIMIT 1", [$id]);

        if (empty($barang)) {
            return redirect()->route('barang.index')->with('error', 'Data barang tidak ditemukan!');
        }

        $satuan = DB::select("SELECT * FROM v_satuan WHERE status = 1 ORDER BY nama_satuan ASC");

        return view('admin.barang.edit', [
            'barang' => $barang[0],
            'satuan' => $satuan,
        ]);
    }

    /**
     * Update data barang dengan Raw SQL UPDATE.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'jenis' => 'required|in:B,K',
            'nama' => 'required|string|max:45',
            'idsatuan' => 'required|integer',
            'status' => 'required|in:0,1',
            'harga' => 'required|numeric|min:0',
        ]);

        DB::update(
            "UPDATE barang SET jenis = ?, nama = ?, idsatuan = ?, status = ?, harga = ? WHERE idbarang = ?",
            [
                $request->jenis,
                $request->nama,
                $request->idsatuan,
                $request->status,
                $request->harga,
                $id,
            ]
        );

        return redirect()->route('barang.index')->with('success', 'Data barang berhasil diperbarui!');
    }

    /**
     * Hapus barang dengan Raw SQL DELETE.
     */
    public function destroy($id)
    {
        try {
            DB::delete("DELETE FROM barang WHERE idbarang = ?", [$id]);
            return redirect()->route('barang.index')->with('success', 'Data barang berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('barang.index')->with('error', 'Gagal menghapus barang! Data ini kemungkinan terhubung dengan transaksi atau detail.');
        }
    }
}
