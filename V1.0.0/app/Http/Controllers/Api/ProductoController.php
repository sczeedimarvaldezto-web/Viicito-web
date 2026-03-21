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
            'sku' => 'nullable|unique:producto,sku',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|gt:precio_compra',
            'stock_actual' => 'integer|min:0',
            'stock_minimo' => 'integer|min:0',
            'stock_maximo' => 'integer|min:0',
            'volumen_ml' => 'nullable|integer',
            'grado_alcohol' => 'nullable|numeric',
            'descripcion' => 'nullable|string',
        ]);

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
            'sku' => 'nullable|unique:producto,sku,' . $producto->id_producto . ',id_producto',
            'precio_compra' => 'numeric|min:0',
            'precio_venta' => 'numeric|min:0',
            'stock_actual' => 'integer|min:0',
            'stock_minimo' => 'integer|min:0',
            'stock_maximo' => 'integer|min:0',
            'volumen_ml' => 'nullable|integer',
            'grado_alcohol' => 'nullable|numeric',
            'estado' => 'in:Activo,Descontinuado,Suspendido',
            'descripcion' => 'nullable|string',
        ]);

        $producto->update($validated);

        return response()->json($producto->load('categoria'));
    }

    /**
     * DELETE /api/productos/{id} - Eliminar producto
     */
    public function destroy(Producto $producto)
    {
        $producto->delete();
        return response()->noContent();
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
