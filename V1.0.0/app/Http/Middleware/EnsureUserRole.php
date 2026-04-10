<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserRole
{
    /**
     * Maneja la petición y comprueba el rol del usuario.
     */
    public function handle(Request $request, Closure $next, string $roles)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado.',
            ], 401);
        }

        $user = Auth::user();
        $allowedRoles = explode(',', $roles);

        if (!$user->role || !in_array($user->role->name, $allowedRoles, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Acceso denegado.',
            ], 403);
        }

        return $next($request);
    }
}
