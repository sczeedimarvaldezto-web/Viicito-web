<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * AuthController
 *
 * Maneja la autenticación de usuarios (registro y login)
 */
class AuthController extends Controller
{
    /**
     * Registrar un nuevo usuario
     */
    public function register(Request $request)
    {
        // Validación de entrada
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'sometimes|string|in:owner,employee,vendedor,auditor',
        ], [
            'email.unique' => 'Este correo electrónico ya está en uso.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'name.required' => 'El nombre es requerido.',
            'email.required' => 'El correo electrónico es requerido.',
            'password.required' => 'La contraseña es requerida.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Por favor, revisa los errores en el formulario.',
                'errors' => $validator->errors()->flatten()->all()
            ], 422);
        }

        try {
            $isFirstUser = User::count() === 0;
            $isOwnerRegistering = Auth::check() && Auth::user()->hasRole('owner');
            
            $roleName = 'employee';
            if ($isFirstUser) {
                $roleName = 'owner';
            } elseif ($isOwnerRegistering && $request->filled('role')) {
                $roleName = $request->role;
            }

            $roleLabel = match ($roleName) {
                'owner' => 'Propietario',
                'employee' => 'Empleado',
                'vendedor' => 'Vendedor',
                'auditor' => 'Auditor',
                default => 'Empleado',
            };
            $role = Role::firstOrCreate([
                'name' => $roleName,
            ], [
                'label' => $roleLabel,
            ]);

            // Crear usuario con contraseña hasheada y rol asignado
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $role->id,
                'estado' => 'Activo',
            ]);

            $user->load('role');
            
            // Solo auto-login si es el primer usuario
            if ($isFirstUser) {
                Auth::login($user);
            }

            return response()->json([
                'success' => true,
                'message' => $isOwnerRegistering ? 'Usuario creado exitosamente.' : 'Usuario registrado exitosamente.',
                'user' => [
                    'id_usuario' => $user->id,
                    'nombre_completo' => $user->name,
                    'email' => $user->email,
                    'username' => $user->name,
                    'rol' => $user->role?->name ?? 'owner',
                    'estado' => $user->estado ?? 'Activo',
                ],
                'role' => $user->role?->name,
                'isNewUser' => $isFirstUser,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Iniciar sesión
     */
    public function login(Request $request)
    {
        // Validación de entrada
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales incorrectas.'
            ], 422);
        }

        // Intentar autenticar
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            if (strtolower((string) $user->estado) === 'bloqueado') {
                Auth::logout();

                return response()->json([
                    'success' => false,
                    'message' => 'Esta cuenta está bloqueada. Contacte al propietario.'
                ], 403);
            }

            $request->session()->regenerate();
            $user = $user->load('role');

            return response()->json([
                'success' => true,
                'message' => 'Inicio de sesión exitoso.',
                'user' => [
                    'id_usuario' => $user->id,
                    'nombre_completo' => $user->name,
                    'email' => $user->email,
                    'username' => $user->name,
                    'rol' => $user->role?->name ?? 'owner',
                    'estado' => $user->estado ?? 'Activo',
                ],
                'role' => $user->role?->name,
                'redirect' => $user->hasRole('owner') ? '/owner-panel' : '/ventas'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Credenciales incorrectas.'
        ], 401);
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente.'
        ]);
    }

    /**
     * Obtener usuario autenticado
     */
    public function user(Request $request)
    {
        if (!$request->user()) {
            return response()->json([
                'error' => 'No autenticado'
            ], 401);
        }

        $user = $request->user()->load('role');
        
        return response()->json([
            'id_usuario' => $user->id,
            'nombre_completo' => $user->name,
            'email' => $user->email,
            'username' => $user->name,
            'rol' => $user->role?->name ?? 'owner',
            'estado' => $user->estado ?? 'Activo',
        ]);
    }
}