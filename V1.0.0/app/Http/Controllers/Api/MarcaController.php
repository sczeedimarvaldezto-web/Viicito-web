<?php

namespace App\Http\Controllers\Api;

use App\Models\Marca;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * MarcaController
 *
 * API REST para gestión de marcas de productos
 */
class MarcaController extends Controller
{
    /**
     * GET /api/marcas - Listar marcas
     */
    public function index(Request $request)
    {
        $query = Marca::query()->orderBy('nombre_marca');

        if ($request->boolean('con_productos')) {
            $query->with('productos');
        }

        $marcas = $query->paginate($request->get('per_page', 50));

        return response()->json($marcas);
    }

    /**
     * GET /api/marcas/{id} - Obtener una marca
     */
    public function show(Marca $marca)
    {
        return response()->json(
            $marca->load('productos')
        );
    }

    /**
     * POST /api/marcas - Crear marca
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_marca' => 'required|string|max:50|unique:marca,nombre_marca',
        ]);

        $marca = Marca::create($validated);

        return response()->json($marca, Response::HTTP_CREATED);
    }

    /**
     * PUT /api/marcas/{id} - Actualizar marca
     */
    public function update(Request $request, Marca $marca)
    {
        $validated = $request->validate([
            'nombre_marca' => 'required|string|max:50|unique:marca,nombre_marca,' . $marca->id_marca . ',id_marca',
        ]);

        $marca->update($validated);

        return response()->json($marca);
    }

    /**
     * DELETE /api/marcas/{id} - Eliminar marca
     */
    public function destroy(Marca $marca)
    {
        if ($marca->productos()->count() > 0) {
            return response()->json([
                'error' => 'No se puede eliminar una marca con productos asignados',
                'message' => 'No se puede eliminar una marca con productos asignados',
            ], Response::HTTP_FORBIDDEN);
        }

        $marca->delete();

        return response()->noContent();
    }

    /**
     * GET /api/marcas/{id}/productos - Productos de una marca
     */
    public function productos(Marca $marca, Request $request)
    {
        $productos = $marca->productos()
            ->with(['categoria', 'marca'])
            ->activos()
            ->paginate($request->get('per_page', 20));

        return response()->json($productos);
    }
}
