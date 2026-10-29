<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Tampilkan form login sederhana untuk penjual / pengelola sistem.
     */
    public function showLogin()
    {
        if (session()->has('user')) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Proses autentikasi user dengan Raw SQL dan SHA2(password, 256).
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Verifikasi login menggunakan Raw SQL murni
        $users = DB::select(
            "SELECT u.iduser, u.username, u.idrole, r.nama_role 
             FROM user u 
             JOIN role r ON u.idrole = r.idrole 
             WHERE u.username = ? AND u.password = SHA2(?, 256) 
             LIMIT 1",
            [$request->username, $request->password]
        );

        if (empty($users)) {
            return back()
                ->withInput($request->only('username'))
                ->with('error', 'Username atau password salah! Silakan coba lagi.');
        }

        $user = $users[0];

        // Simpan data user ke dalam session
        session([
            'user' => $user,
            'iduser' => $user->iduser,
            'username' => $user->username,
            'role' => $user->nama_role,
            'idrole' => $user->idrole,
        ]);

        return redirect()->route('dashboard')->with('success', "Selamat datang, {$user->username} ({$user->nama_role})!");
    }

    /**
     * Logout dan bersihkan session.
     */
    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
    }
}
