<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * GET /api/usuarios
     */
    public function index(Request $request)
    {
        $usuarios = User::with('role')
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'nombre' => $user->name,
                'email' => $user->email,
                'rol' => $user->role?->name ?? 'employee',
                'rol_label' => $user->role?->label ?? 'Empleado',
                'estado' => $user->estado ?? 'Activo',
                'fecha_registro' => $user->created_at?->format('d/m/Y'),
                'total_ventas' => 0,
                'total_vendido' => 0,
                'promedio_venta' => 0,
            ]);

        return response()->json([
            'total_usuarios' => $usuarios->count(),
            'empleados' => $usuarios,
        ]);
    }

    /**
     * PUT /api/usuarios/{usuario}
     */
    public function update(Request $request, User $usuario)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $usuario->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'sometimes|string|in:owner,employee,vendedor,auditor',
            'estado' => 'sometimes|string|in:Activo,Bloqueado',
        ]);

        if ($request->filled('name')) {
            $usuario->name = $validated['name'];
        }

        if ($request->filled('email')) {
            $usuario->email = $validated['email'];
        }

        if ($request->filled('password')) {
            $usuario->password = Hash::make($validated['password']);
        }

        if ($request->filled('role')) {
            $roleName = $validated['role'];
            $roleLabel = match ($roleName) {
                'owner' => 'Propietario',
                'employee' => 'Empleado',
                'vendedor' => 'Vendedor',
                'auditor' => 'Auditor',
                default => 'Empleado',
            };

            $role = Role::firstOrCreate(
                ['name' => $roleName],
                ['label' => $roleLabel]
            );

            $usuario->role_id = $role->id;
        }

        if ($request->filled('estado')) {
            $usuario->estado = $validated['estado'];
        }

        $usuario->save();
        $usuario->load('role');

        return response()->json([
            'message' => 'Usuario actualizado correctamente.',
            'usuario' => [
                'id' => $usuario->id,
                'nombre' => $usuario->name,
                'email' => $usuario->email,
                'rol' => $usuario->role?->name ?? 'employee',
                'rol_label' => $usuario->role?->label ?? 'Empleado',
                'estado' => $usuario->estado ?? 'Activo',
            ],
        ]);
    }
}
