<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request and verify role permissions.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->session()->has('user')) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $userRole = strtolower(trim(session('role', '')));

        // Normalisasi role yang diizinkan
        $allowedRoles = array_map(fn($r) => strtolower(trim($r)), $roles);

        // Admin selalu memiliki hak akses penuh ke seluruh modul sistem
        if ($userRole === 'admin' || in_array($userRole, $allowedRoles)) {
            return $next($request);
        }

        return redirect()->route('dashboard')->with('error', "Akses Ditolak! Role '{$request->session()->get('role')}' tidak memiliki izin mengakses halaman tersebut.");
    }
}
