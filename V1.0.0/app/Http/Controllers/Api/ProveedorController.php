<?php

namespace App\Http\Controllers\Api;

use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController
{
    /**
     * Listar todos los proveedores
     */
    public function index(Request $request)
    {
        $query = Proveedor::query();

        // Filtro de búsqueda
        if ($request->has('buscar')) {
            $buscar = $request->input('buscar');
            $query->where('nombre_empresa', 'like', "%{$buscar}%")
                  ->orWhere('telefono', 'like', "%{$buscar}%");
        }

        // Paginación
        $proveedores = $query->paginate($request->input('per_page', 20));

        return response()->json($proveedores);
    }

    /**
     * Crear nuevo proveedor
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_empresa' => 'required|string|max:255',
            'contacto_nombre' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'ciudad' => 'nullable|string|max:255',
            'estado_proveedor' => 'nullable|in:Activo,Inactivo|default:Activo',
        ]);

        $proveedor = Proveedor::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Proveedor creado correctamente',
            'data' => $proveedor
        ], 201);
    }

    /**
     * Ver proveedor específico
     */
    public function show(Proveedor $proveedor)
    {
        return response()->json($proveedor);
    }

    /**
     * Actualizar proveedor
     */
    public function update(Request $request, Proveedor $proveedor)
    {
        $validated = $request->validate([
            'nombre_empresa' => 'required|string|max:255',
            'contacto_nombre' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'ciudad' => 'nullable|string|max:255',
            'estado_proveedor' => 'nullable|in:Activo,Inactivo',
        ]);

        $proveedor->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Proveedor actualizado correctamente',
            'data' => $proveedor
        ]);
    }

    /**
     * Eliminar proveedor
     */
    public function destroy(Proveedor $proveedor)
    {
        $proveedor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Proveedor eliminado correctamente'
        ]);
    }
}
