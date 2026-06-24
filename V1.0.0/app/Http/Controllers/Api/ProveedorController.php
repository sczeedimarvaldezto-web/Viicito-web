<?php

namespace App\Http\Controllers\Api;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * ProveedorController
 * 
 * Gestión de proveedores con validaciones:
 * - Soft Deletes: Eliminación lógica para preservar auditoría
 * - Foreign Key Protection: Impide eliminar proveedor si tiene compras
 */
class ProveedorController
{
    /**
     * GET /api/proveedores - Listar todos los proveedores
     * 
     * Opciones:
     * - ?incluir_eliminados=true : Incluir proveedores eliminados lógicamente
     * - ?estado=Activo : Filtrar por estado
     * - ?buscar=texto : Búsqueda por nombre o teléfono
     */
    public function index(Request $request)
    {
        $query = Proveedor::query();

        // Incluir eliminados lógicamente si se solicita
        if ($request->boolean('incluir_eliminados')) {
            $query->withTrashed();
        }

        // Filtro por estado
        if ($request->has('estado')) {
            $query->where('estado_proveedor', $request->input('estado'));
        }

        // Búsqueda
        if ($request->has('buscar')) {
            $buscar = $request->input('buscar');
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre_empresa', 'like', "%{$buscar}%")
                  ->orWhere('contacto_nombre', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%")
                  ->orWhere('telefono', 'like', "%{$buscar}%")
                  ->orWhere('ciudad', 'like', "%{$buscar}%");
            });
        }

        // Ordenamiento
        $query->orderBy('nombre_empresa', 'asc');

        // Paginación
        $proveedores = $query->paginate($request->input('per_page', 20));

        return response()->json($proveedores);
    }

    /**
     * POST /api/proveedores - Crear nuevo proveedor
     * 
     * Validaciones:
     * - nombre_empresa: requerido, máximo 100 caracteres
     * - email: validación de email si se proporciona
     * - estado_proveedor: enum (Activo, Inactivo, Suspendido)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_empresa' => 'required|string|max:100|unique:proveedor,nombre_empresa',
            'contacto_nombre' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:100|unique:proveedor,email',
            'telefono' => 'nullable|string|max:20',
            'ciudad' => 'nullable|string|max:50',
            'estado_proveedor' => 'nullable|in:Activo,Inactivo,Suspendido',
        ]);

        // Asignar estado por defecto
        $validated['estado_proveedor'] = $validated['estado_proveedor'] ?? 'Activo';

        try {
            $proveedor = Proveedor::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Proveedor creado correctamente',
                'data' => $proveedor
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el proveedor: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * GET /api/proveedores/{id} - Obtener proveedor específico
     */
    public function show(Proveedor $proveedor)
    {
        $proveedor->load('compras');
        return response()->json($proveedor);
    }

    /**
     * PUT /api/proveedores/{id} - Actualizar proveedor
     * 
     * Validaciones:
     * - No permite actualizar si está eliminado lógicamente
     * - Verifica unicidad de nombre_empresa y email
     */
    public function update(Request $request, Proveedor $proveedor)
    {
        // Verificar que el proveedor no esté eliminado lógicamente
        if ($proveedor->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede actualizar un proveedor eliminado'
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'nombre_empresa' => 'required|string|max:100|unique:proveedor,nombre_empresa,' . $proveedor->id_proveedor . ',id_proveedor',
            'contacto_nombre' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:100|unique:proveedor,email,' . $proveedor->id_proveedor . ',id_proveedor',
            'telefono' => 'nullable|string|max:20',
            'ciudad' => 'nullable|string|max:50',
            'estado_proveedor' => 'nullable|in:Activo,Inactivo,Suspendido',
        ]);

        try {
            $proveedor->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Proveedor actualizado correctamente',
                'data' => $proveedor
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el proveedor: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * DELETE /api/proveedores/{id} - Eliminar proveedor
     * 
     * VALIDACIONES:
     * - NO se puede eliminar si tiene compras asociadas
     * - Utiliza soft delete (eliminación lógica) para preservar auditoría
     * - Foreign Key Protection: Previene violación de integridad referencial
     */
    public function destroy(Proveedor $proveedor)
    {
        // Verificar si tiene compras asociadas
        if (!$proveedor->puedeSerEliminado()) {
            $razon = $proveedor->obtenerRazonNoEliminacion();
            
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el proveedor',
                'reason' => $razon,
                'compras_asociadas' => $proveedor->compras()->count()
            ], Response::HTTP_CONFLICT);
        }

        try {
            // Usar soft delete (eliminación lógica)
            $proveedor->delete();

            return response()->json([
                'success' => true,
                'message' => 'Proveedor eliminado correctamente (eliminación lógica)'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el proveedor: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * POST /api/proveedores/{id}/restaurar - Restaurar proveedor eliminado
     * 
     * Permiso: Solo administradores
     */
    public function restaurar(Proveedor $proveedor)
    {
        if (!$proveedor->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'El proveedor no está eliminado'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $proveedor->restore();

            return response()->json([
                'success' => true,
                'message' => 'Proveedor restaurado correctamente',
                'data' => $proveedor
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar el proveedor: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
