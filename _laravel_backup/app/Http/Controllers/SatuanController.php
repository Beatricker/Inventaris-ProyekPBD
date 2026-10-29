<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SatuanController extends Controller
{
    /**
     * Tampilkan daftar master satuan menggunakan VIEW v_satuan.
     */
    public function index()
    {
        $satuan = DB::select("SELECT * FROM v_satuan ORDER BY idsatuan DESC");
        return view('admin.satuan.index', compact('satuan'));
    }

    /**
     * Form tambah satuan baru.
     */
    public function create()
    {
        return view('admin.satuan.create');
    }

    /**
     * Simpan data satuan baru dengan Raw SQL INSERT.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_satuan' => 'required|string|max:45',
            'status' => 'required|in:0,1',
        ]);

        DB::insert(
            "INSERT INTO satuan (nama_satuan, status) VALUES (?, ?)",
            [$request->nama_satuan, $request->status]
        );

        return redirect()->route('satuan.index')->with('success', 'Satuan baru berhasil ditambahkan!');
    }

    /**
     * Form edit satuan.
     */
    public function edit($id)
    {
        $satuan = DB::select("SELECT * FROM satuan WHERE idsatuan = ? LIMIT 1", [$id]);

        if (empty($satuan)) {
            return redirect()->route('satuan.index')->with('error', 'Data satuan tidak ditemukan!');
        }

        return view('admin.satuan.edit', ['satuan' => $satuan[0]]);
    }

    /**
     * Update data satuan dengan Raw SQL UPDATE.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_satuan' => 'required|string|max:45',
            'status' => 'required|in:0,1',
        ]);

        DB::update(
            "UPDATE satuan SET nama_satuan = ?, status = ? WHERE idsatuan = ?",
            [$request->nama_satuan, $request->status, $id]
        );

        return redirect()->route('satuan.index')->with('success', 'Data satuan berhasil diperbarui!');
    }

    /**
     * Hapus satuan dengan Raw SQL DELETE.
     */
    public function destroy($id)
    {
        try {
            DB::delete("DELETE FROM satuan WHERE idsatuan = ?", [$id]);
            return redirect()->route('satuan.index')->with('success', 'Data satuan berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('satuan.index')->with('error', 'Gagal menghapus satuan! Satuan ini masih digunakan oleh data barang.');
        }
    }
}
