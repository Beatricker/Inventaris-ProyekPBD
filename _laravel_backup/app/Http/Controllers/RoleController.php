<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Tampilkan daftar master role menggunakan VIEW v_role.
     */
    public function index()
    {
        $role = DB::select("SELECT * FROM v_role ORDER BY idrole ASC");
        return view('admin.role.index', compact('role'));
    }

    /**
     * Form tambah role baru.
     */
    public function create()
    {
        return view('admin.role.create');
    }

    /**
     * Simpan role baru dengan Raw SQL INSERT.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_role' => 'required|string|max:100',
        ]);

        DB::insert(
            "INSERT INTO role (nama_role) VALUES (?)",
            [$request->nama_role]
        );

        return redirect()->route('role.index')->with('success', 'Role baru berhasil ditambahkan!');
    }

    /**
     * Form edit role.
     */
    public function edit($id)
    {
        $role = DB::select("SELECT * FROM role WHERE idrole = ? LIMIT 1", [$id]);

        if (empty($role)) {
            return redirect()->route('role.index')->with('error', 'Data role tidak ditemukan!');
        }

        return view('admin.role.edit', ['role' => $role[0]]);
    }

    /**
     * Update data role dengan Raw SQL UPDATE.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_role' => 'required|string|max:100',
        ]);

        DB::update(
            "UPDATE role SET nama_role = ? WHERE idrole = ?",
            [$request->nama_role, $id]
        );

        return redirect()->route('role.index')->with('success', 'Data role berhasil diperbarui!');
    }

    /**
     * Hapus role dengan Raw SQL DELETE.
     */
    public function destroy($id)
    {
        try {
            DB::delete("DELETE FROM role WHERE idrole = ?", [$id]);
            return redirect()->route('role.index')->with('success', 'Data role berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('role.index')->with('error', 'Gagal menghapus role! Role ini masih digunakan oleh data user.');
        }
    }
}
