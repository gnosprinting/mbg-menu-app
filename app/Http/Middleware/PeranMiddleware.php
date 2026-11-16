<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PeranMiddleware
{
    public function handle(Request $request, Closure $next, string ...$peran): Response
    {
        $user = $request->user();
        if (! $user || ! in_array($user->peran, $peran, true)) {
            abort(403, 'Peran Anda tidak punya akses untuk tindakan ini.');
        }

        return $next($request);
    }
}
