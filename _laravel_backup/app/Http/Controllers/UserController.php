<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Tampilkan daftar master user menggunakan VIEW v_user.
     */
    public function index()
    {
        $user = DB::select("SELECT * FROM v_user ORDER BY iduser ASC");
        return view('admin.user.index', compact('user'));
    }

    /**
     * Form tambah user baru.
     */
    public function create()
    {
        $roles = DB::select("SELECT * FROM v_role ORDER BY idrole ASC");
        return view('admin.user.create', compact('roles'));
    }

    /**
     * Simpan user baru dengan Raw SQL INSERT dan password hash SHA2 256.
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:45|unique:user,username',
            'password' => 'required|string|min:4',
            'idrole' => 'required|integer',
        ]);

        DB::insert(
            "INSERT INTO user (username, password, idrole) VALUES (?, SHA2(?, 256), ?)",
            [
                $request->username,
                $request->password,
                $request->idrole,
            ]
        );

        return redirect()->route('user.index')->with('success', 'User baru berhasil didaftarkan!');
    }

    /**
     * Form edit user.
     */
    public function edit($id)
    {
        $user = DB::select("SELECT * FROM user WHERE iduser = ? LIMIT 1", [$id]);

        if (empty($user)) {
            return redirect()->route('user.index')->with('error', 'Data user tidak ditemukan!');
        }

        $roles = DB::select("SELECT * FROM v_role ORDER BY idrole ASC");

        return view('admin.user.edit', [
            'user' => $user[0],
            'roles' => $roles,
        ]);
    }

    /**
     * Update data user dengan Raw SQL UPDATE.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'username' => 'required|string|max:45',
            'idrole' => 'required|integer',
            'password' => 'nullable|string|min:4',
        ]);

        if (!empty($request->password)) {
            DB::update(
                "UPDATE user SET username = ?, password = SHA2(?, 256), idrole = ? WHERE iduser = ?",
                [
                    $request->username,
                    $request->password,
                    $request->idrole,
                    $id,
                ]
            );
        } else {
            DB::update(
                "UPDATE user SET username = ?, idrole = ? WHERE iduser = ?",
                [
                    $request->username,
                    $request->idrole,
                    $id,
                ]
            );
        }

        return redirect()->route('user.index')->with('success', 'Data user berhasil diperbarui!');
    }

    /**
     * Hapus user dengan Raw SQL DELETE.
     */
    public function destroy($id)
    {
        if (session('iduser') == $id) {
            return redirect()->route('user.index')->with('error', 'Tidak dapat menghapus akun Anda sendiri yang sedang aktif login!');
        }

        try {
            DB::delete("DELETE FROM user WHERE iduser = ?", [$id]);
            return redirect()->route('user.index')->with('success', 'User berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('user.index')->with('error', 'Gagal menghapus user! User ini memiliki riwayat transaksi/aktivitas di database.');
        }
    }
}
