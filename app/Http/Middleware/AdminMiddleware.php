<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AdminMiddleware (Anggota 6)
 *
 * Melindungi route /api/admin/*: hanya user dengan role "admin" yang boleh lewat.
 * Dipasang SETELAH auth:sanctum, contoh: ->middleware(['auth:sanctum', 'admin'])
 */
class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Silakan login terlebih dahulu.',
            ], 401);
        }

        if (! $user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Halaman ini khusus admin.',
            ], 403);
        }

        return $next($request);
    }
}
