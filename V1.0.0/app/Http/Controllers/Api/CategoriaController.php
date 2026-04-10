<?php

namespace App\Http\Controllers\Api;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * CategoriaController
 * 
 * API REST para gestión de categorías
 */
class CategoriaController extends Controller
{
    /**
     * GET /api/categorias - Listar categorías
     */
    public function index()
    {
        $categorias = Categoria::with('productos')
            ->activas()
            ->paginate(50);

        return response()->json($categorias);
    }

    /**
     * GET /api/categorias/{id} - Obtener una categoría
     */
    public function show(Categoria $categoria)
    {
        return response()->json(
            $categoria->load('productos')
        );
    }

    /**
     * POST /api/categorias - Crear categoría
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_categoria' => 'required|unique:categoria,nombre_categoria',
            'descripcion' => 'nullable|string',
        ]);

        $categoria = Categoria::create($validated);

        return response()->json($categoria, Response::HTTP_CREATED);
    }

    /**
     * PUT /api/categorias/{id} - Actualizar categoría
     */
    public function update(Request $request, Categoria $categoria)
    {
        $validated = $request->validate([
            'nombre_categoria' => 'unique:categoria,nombre_categoria,' . $categoria->id_categoria . ',id_categoria',
            'descripcion' => 'nullable|string',
            'estado' => 'in:Activo,Inactivo',
        ]);

        $categoria->update($validated);

        return response()->json($categoria);
    }

    /**
     * DELETE /api/categorias/{id} - Eliminar categoría
     */
    public function destroy(Categoria $categoria)
    {
        if ($categoria->productos()->count() > 0) {
            return response()->json(
                ['error' => 'No se puede eliminar una categoría con productos'],
                Response::HTTP_FORBIDDEN
            );
        }

        $categoria->delete();
        return response()->noContent();
    }

    /**
     * GET /api/categorias/{id}/productos - Productos de una categoría
     */
    public function productos(Categoria $categoria, Request $request)
    {
        $productos = $categoria->productos()
            ->activos()
            ->paginate($request->get('per_page', 20));

        return response()->json($productos);
    }
}
