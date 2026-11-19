<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * PeranMiddleware — penjaga akses berbasis peran di level rute.
 *
 * Contoh: middleware('peran:admin,gizi') hanya meloloskan dua peran itu;
 * peran lain mendapat 403 sebelum controller dijalankan.
 */
class PeranMiddleware
{
    /** Cek peran pengguna terhadap daftar peran yang diizinkan rute. */
    public function handle(Request $request, Closure $next, string ...$peran): Response
    {
        $user = $request->user();
        if (! $user || ! in_array($user->peran, $peran, true)) {
            abort(403, 'Peran Anda tidak punya akses untuk tindakan ini.');
        }

        return $next($request);
    }
}
