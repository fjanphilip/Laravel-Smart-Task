<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class QueryTokenAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        // Paksa agar semua request yang melalui middleware ini dianggap meminta JSON.
        // Ini sangat krusial bagi EventSource (SSE) agar jika otentikasi gagal (misal token kedaluwarsa setelah migrate:fresh),
        // Laravel mengembalikan respon JSON 401 Unauthorized secara bersih, bukannya crash 500 karena mencari Route [login] yang tidak ada.
        $request->headers->set('Accept', 'application/json');

        \Log::info('QueryTokenAuthenticate running', [
            'has_token' => $request->has('token'),
            'token_val' => $request->query('token'),
            'has_auth_header' => $request->headers->has('Authorization'),
            'auth_header' => $request->headers->get('Authorization'),
        ]);

        if ($request->has('token') && !$request->headers->has('Authorization')) {
            $request->headers->set('Authorization', 'Bearer ' . $request->query('token'));

            \Log::info('Authorization header injected', [
                'injected_header' => $request->headers->get('Authorization'),
            ]);
        }

        return $next($request);
    }
}
