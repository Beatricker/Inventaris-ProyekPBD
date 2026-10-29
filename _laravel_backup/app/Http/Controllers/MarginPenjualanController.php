<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarginPenjualanController extends Controller
{
    /**
     * Tampilkan daftar master margin penjualan menggunakan VIEW v_margin.
     */
    public function index()
    {
        $margin = DB::select("SELECT * FROM v_margin ORDER BY idmargin_penjualan DESC");
        return view('admin.margin.index', compact('margin'));
    }

    /**
     * Form tambah margin penjualan baru.
     */
    public function create()
    {
        $users = DB::select("SELECT iduser, username FROM v_user ORDER BY username ASC");
        return view('admin.margin.create', compact('users'));
    }

    /**
     * Simpan margin penjualan baru dengan Raw SQL INSERT.
     */
    public function store(Request $request)
    {
        $request->validate([
            'persen' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:0,1',
            'iduser' => 'required|integer',
        ]);

        DB::insert(
            "INSERT INTO margin_penjualan (persen, status, iduser) VALUES (?, ?, ?)",
            [
                $request->persen,
                $request->status,
                $request->iduser,
            ]
        );

        return redirect()->route('margin.index')->with('success', 'Margin penjualan baru berhasil ditambahkan!');
    }

    /**
     * Form edit margin penjualan.
     */
    public function edit($id)
    {
        $margin = DB::select("SELECT * FROM margin_penjualan WHERE idmargin_penjualan = ? LIMIT 1", [$id]);

        if (empty($margin)) {
            return redirect()->route('margin.index')->with('error', 'Data margin penjualan tidak ditemukan!');
        }

        $users = DB::select("SELECT iduser, username FROM v_user ORDER BY username ASC");

        return view('admin.margin.edit', [
            'margin' => $margin[0],
            'users' => $users,
        ]);
    }

    /**
     * Update data margin penjualan dengan Raw SQL UPDATE.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'persen' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:0,1',
            'iduser' => 'required|integer',
        ]);

        DB::update(
            "UPDATE margin_penjualan SET persen = ?, status = ?, iduser = ? WHERE idmargin_penjualan = ?",
            [
                $request->persen,
                $request->status,
                $request->iduser,
                $id,
            ]
        );

        return redirect()->route('margin.index')->with('success', 'Data margin penjualan berhasil diperbarui!');
    }

    /**
     * Hapus margin penjualan dengan Raw SQL DELETE.
     */
    public function destroy($id)
    {
        try {
            DB::delete("DELETE FROM margin_penjualan WHERE idmargin_penjualan = ?", [$id]);
            return redirect()->route('margin.index')->with('success', 'Data margin penjualan berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('margin.index')->with('error', 'Gagal menghapus margin penjualan!');
        }
    }
}
