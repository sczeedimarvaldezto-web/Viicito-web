<?php

namespace App\Http\Controllers\Api;

use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * ProductoController
 * 
 * API REST para gestión de productos
 */
class ProductoController extends Controller
{
    /**
     * GET /api/productos - Listar productos
     */
    public function index(Request $request)
    {
        $query = Producto::with('categoria');

        // Filtros
        if ($request->has('categoria')) {
            $query->where('id_categoria', $request->categoria);
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('stock_bajo')) {
            $query->whereRaw('stock_actual <= stock_minimo');
        }

        if ($request->has('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre_producto', 'like', "%{$buscar}%")
                  ->orWhere('codigo_barras', 'like', "%{$buscar}%")
                  ->orWhere('sku', 'like', "%{$buscar}%");
            });
        }

        // Excluir productos eliminados (Soft Deletes)
        $query->whereNull('deleted_at');

        // Paginación
        $productos = $query->paginate($request->get('per_page', 15));

        return response()->json($productos);
    }

    /**
     * GET /api/productos/{id} - Obtener un producto
     */
    public function show(Producto $producto)
    {
        return response()->json($producto->load('categoria'));
    }

    /**
     * POST /api/productos - Crear producto
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_categoria' => 'required|exists:categoria,id_categoria',
            'nombre_producto' => 'required|string|max:100',
            'codigo_barras' => 'nullable|unique:producto,codigo_barras',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'stock_actual' => 'nullable|integer|min:0',
            'descripcion' => 'nullable|string',
            'estado' => 'nullable|in:Activo,Inactivo,Descontinuado',
        ]);

        // Asignar estado por defecto si no viene
        if (!isset($validated['estado'])) {
            $validated['estado'] = 'Activo';
        }

        $producto = Producto::create($validated);

        return response()->json($producto->load('categoria'), Response::HTTP_CREATED);
    }

    /**
     * PUT /api/productos/{id} - Actualizar producto
     */
    public function update(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            'id_categoria' => 'exists:categoria,id_categoria',
            'nombre_producto' => 'string|max:100',
            'codigo_barras' => 'nullable|unique:producto,codigo_barras,' . $producto->id_producto . ',id_producto',
            'precio_compra' => 'numeric|min:0',
            'precio_venta' => 'numeric|min:0',
            'stock_actual' => 'integer|min:0',
            'estado' => 'in:Activo,Inactivo,Descontinuado',
            'descripcion' => 'nullable|string',
        ]);

        $producto->update($validated);

        return response()->json($producto->load('categoria'));
    }

    /**
     * DELETE /api/productos/{id} - Eliminar producto (Soft Delete)
     */
    public function destroy(Producto $producto)
    {
        $producto->delete();
        return response()->json([
            'message' => 'Producto eliminado correctamente',
            'success' => true
        ]);
    }

    /**
     * GET /api/productos/stock/bajo - Productos con stock bajo
     */
    public function stockBajo()
    {
        $productos = Producto::stockBajo()->with('categoria')->paginate(15);
        return response()->json($productos);
    }
}
