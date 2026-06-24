<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Middleware para debugging de peticiones API
 * Registra todas las peticiones y respuestas para análisis
 */
class DebugApiRequests
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Registrar información de la petición (con validación de acceso)
            Log::debug('🔵 INCOMING REQUEST', [
                'method' => $request->method(),
                'path' => $request->path(),
                'url' => $request->url(),
                'ip' => $request->ip(),
                'headers_subset' => [
                    'Accept' => $request->header('Accept'),
                    'Content-Type' => $request->header('Content-Type'),
                    'Cookie' => $request->header('Cookie') ? '***SET***' : 'NONE',
                ],
                'has_csrf_token' => (bool) $request->header('X-CSRF-TOKEN'),
                'payload_size' => strlen(json_encode($request->all())),
            ]);
        } catch (\Exception $e) {
            Log::warning('Debug middleware error:', ['error' => $e->getMessage()]);
        }

        // Procesar la petición
        $response = $next($request);

        try {
            // Registrar información de la respuesta
            Log::debug('🟢 RESPONSE', [
                'status' => $response->getStatusCode(),
                'path' => $request->path(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Response debug error:', ['error' => $e->getMessage()]);
        }

        return $response;
    }
}
