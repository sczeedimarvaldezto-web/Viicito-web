<?php

namespace App\Http\Controllers\Api;

use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

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
            'nombre_producto' => 'required|string|max:100|unique:producto,nombre_producto',
            'codigo_barras' => 'nullable|string|max:50|unique:producto,codigo_barras',
            'sku' => 'nullable|string|max:50|unique:producto,sku',
            'precio_compra' => 'nullable|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0.01',
            'stock_actual' => 'required|integer|min:0',
            'stock_minimo' => 'nullable|integer|min:0',
            'stock_maximo' => 'nullable|integer|min:0',
            'grado_alcohol' => 'nullable|numeric|min:0|max:100',
            'imagen_producto' => 'nullable|image|mimes:jpg,png,webp|max:2048',
            'descripcion' => 'nullable|string',
        ]);

        if ($request->hasFile('imagen_producto')) {
            $uploadPath = public_path('uploads/productos');
            if (!File::exists($uploadPath)) {
                File::makeDirectory($uploadPath, 0755, true);
            }

            $file = $request->file('imagen_producto');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadPath, $filename);
            $validated['imagen_url'] = 'uploads/productos/' . $filename;
        }

        $validated['precio_compra'] = $validated['precio_compra'] ?? 0;
        $validated['stock_minimo'] = $validated['stock_minimo'] ?? 0;
        $validated['stock_maximo'] = $validated['stock_maximo'] ?? 0;

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
            'nombre_producto' => 'string|max:100|unique:producto,nombre_producto,' . $producto->id_producto . ',id_producto',
            'codigo_barras' => 'nullable|string|max:50|unique:producto,codigo_barras,' . $producto->id_producto . ',id_producto',
            'sku' => 'nullable|string|max:50|unique:producto,sku,' . $producto->id_producto . ',id_producto',
            'precio_compra' => 'nullable|numeric|min:0',
            'precio_venta' => 'nullable|numeric|min:0.01',
            'stock_actual' => 'nullable|integer|min:0',
            'stock_minimo' => 'nullable|integer|min:0',
            'stock_maximo' => 'nullable|integer|min:0',
            'grado_alcohol' => 'nullable|numeric|min:0|max:100',
            'imagen_producto' => 'nullable|image|mimes:jpg,png,webp|max:2048',
            'estado' => 'in:Activo,Descontinuado,Suspendido',
            'descripcion' => 'nullable|string',
        ]);

        if ($request->hasFile('imagen_producto')) {
            $uploadPath = public_path('uploads/productos');
            if (!File::exists($uploadPath)) {
                File::makeDirectory($uploadPath, 0755, true);
            }

            $file = $request->file('imagen_producto');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadPath, $filename);
            $validated['imagen_url'] = 'uploads/productos/' . $filename;
        }

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
