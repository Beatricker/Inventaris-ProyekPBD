<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorController extends Controller
{
    /**
     * Tampilkan daftar master vendor menggunakan VIEW v_vendor.
     */
    public function index()
    {
        $vendor = DB::select("SELECT * FROM v_vendor ORDER BY idvendor DESC");
        return view('admin.vendor.index', compact('vendor'));
    }

    /**
     * Form tambah vendor baru.
     */
    public function create()
    {
        return view('admin.vendor.create');
    }

    /**
     * Simpan vendor baru dengan Raw SQL INSERT.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_vendor' => 'required|string|max:100',
            'badan_hukum' => 'required|in:P,C,U',
            'status' => 'required|in:0,1',
        ]);

        DB::insert(
            "INSERT INTO vendor (nama_vendor, badan_hukum, status) VALUES (?, ?, ?)",
            [
                $request->nama_vendor,
                $request->badan_hukum,
                $request->status,
            ]
        );

        return redirect()->route('vendor.index')->with('success', 'Vendor baru berhasil ditambahkan!');
    }

    /**
     * Form edit vendor.
     */
    public function edit($id)
    {
        $vendor = DB::select("SELECT * FROM vendor WHERE idvendor = ? LIMIT 1", [$id]);

        if (empty($vendor)) {
            return redirect()->route('vendor.index')->with('error', 'Data vendor tidak ditemukan!');
        }

        return view('admin.vendor.edit', ['vendor' => $vendor[0]]);
    }

    /**
     * Update data vendor dengan Raw SQL UPDATE.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_vendor' => 'required|string|max:100',
            'badan_hukum' => 'required|in:P,C,U',
            'status' => 'required|in:0,1',
        ]);

        DB::update(
            "UPDATE vendor SET nama_vendor = ?, badan_hukum = ?, status = ? WHERE idvendor = ?",
            [
                $request->nama_vendor,
                $request->badan_hukum,
                $request->status,
                $id,
            ]
        );

        return redirect()->route('vendor.index')->with('success', 'Data vendor berhasil diperbarui!');
    }

    /**
     * Hapus vendor dengan Raw SQL DELETE.
     */
    public function destroy($id)
    {
        try {
            DB::delete("DELETE FROM vendor WHERE idvendor = ?", [$id]);
            return redirect()->route('vendor.index')->with('success', 'Data vendor berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('vendor.index')->with('error', 'Gagal menghapus vendor! Data vendor masih terikat dengan data pengadaan.');
        }
    }
}
