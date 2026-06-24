<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EnsureUserRole
{
    /**
     * Maneja la petición y comprueba el rol del usuario de forma flexible.
     * Usamos el operador variadic (...$roles) para atrapar TODOS los roles separados por coma.
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // 1. Verificar si está logueado
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado.',
            ], 401);
        }

        $user = Auth::user();

        // 2. Verificar si el usuario tiene una relación con la tabla roles
        if (!$user->role) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario no tiene un rol asignado en la base de datos.',
            ], 403);
        }

        // 3. Como usamos ...$roles, la variable ya es un array (ej: ['owner', 'employee']).
        // Solo la limpiamos para evitar errores de espacios o mayúsculas.
        $allowedRoles = array_map('strtolower', array_map('trim', $roles));
        
        // 4. Obtener el nombre del rol del usuario de la BD y limpiarlo
        $userRoleName = strtolower(trim($user->role->name));

        // 5. Mapeo de seguridad (Traductor Español -> Inglés)
        if ($userRoleName === 'empleado' || $userRoleName === 'cajero') {
            $userRoleName = 'employee';
        }
        if ($userRoleName === 'propietario' || $userRoleName === 'administrador') {
            $userRoleName = 'owner';
        }

        // 6. Validación final
        if (!in_array($userRoleName, $allowedRoles, true)) {
            // Guardamos un log para diagnóstico
            Log::warning('🛑 Acceso denegado por Middleware EnsureUserRole', [
                'usuario_id' => $user->id,
                'rol_en_bd' => $user->role->name,
                'rol_evaluado' => $userRoleName,
                'roles_permitidos' => $allowedRoles, // Ahora sí dirá ["owner", "employee"]
                'url' => $request->url()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Acceso denegado. No tienes permisos para ver esto.',
            ], 403);
        }

        // Si todo está bien, la petición pasa al controlador
        return $next($request);
    }
}