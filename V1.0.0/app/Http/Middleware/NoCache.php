<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NoCache
{
    /**
     * Prevenir caché HTTP en el navegador.
     * 
     * Crítico para SPA where diferentes usuarios pueden usar el mismo navegador.
     * Sin estos headers, el navegador devolvería datos cacheados del usuario anterior.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
        $response->header('Pragma', 'no-cache');
        $response->header('Expires', '0');

        return $response;
    }
}
