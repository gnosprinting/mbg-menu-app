<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/** Mencatat setiap aksi tulis (POST/PUT/DELETE) ke audit_log. */
class AuditLog
{
    /** Catat request tulis ke audit_log lalu naikkan versi cache. */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $response;
        }

        // Jangan rekam data sensitif.
        $input = collect($request->except(['_token', 'password', 'password_confirmation', 'logo']))
            ->map(fn ($v) => is_array($v) ? count($v).' item' : mb_substr((string) $v, 0, 80))
            ->take(8)->all();

        $user = $request->user();
        $modul = $request->route()?->getName() ?? $request->path();

        try {
            DB::table('audit_log')->insert([
                'user_id' => $user?->id,
                'user_nama' => $user?->name ?? ($request->input('email') ?: 'tamu'),
                'peran' => $user?->peran ?? 'tamu',
                'aksi' => strtolower($request->method()).' '.($request->isMethod('post') ? 'submit' : 'ubah'),
                'modul' => mb_substr((string) $modul, 0, 40),
                'target' => mb_substr(trim($request->path(), '/'), 0, 160),
                'detail' => json_encode($input, JSON_UNESCAPED_UNICODE),
                'ip' => $request->ip(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Audit tidak boleh memblokir aplikasi.
        }

        // Setiap aksi tulis membatalkan cache agregat (Beranda).
        try {
            Cache::forever('mbg.cache_version', ((int) Cache::get('mbg.cache_version', 1)) + 1);
        } catch (\Throwable $e) {
        }

        return $response;
    }
}
