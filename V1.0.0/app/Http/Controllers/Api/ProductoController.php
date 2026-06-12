<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Models\Producto;
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
        $query = Producto::queryInventario($request);

        $productos = $query->paginate($request->get('per_page', 15));

        return response()->json($productos);
    }

    /**
     * GET /api/productos/{id} - Obtener un producto
     */
    public function show(Producto $producto)
    {
        return response()->json($producto->load(['categoria', 'marca']));
    }

    /**
     * POST /api/productos - Crear producto
     */
    public function store(StoreProductoRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('imagen_producto')) {
            $validated['imagen_url'] = $this->guardarImagen($request);
        }

        $validated['precio_compra'] = $validated['precio_compra'] ?? 0;
        $validated['stock_minimo'] = $validated['stock_minimo'] ?? 0;
        $validated['stock_maximo'] = $validated['stock_maximo'] ?? 0;

        $producto = Producto::create($validated);

        return response()->json($producto->load(['categoria', 'marca']), Response::HTTP_CREATED);
    }

    /**
     * PUT /api/productos/{id} - Actualizar producto
     */
    public function update(UpdateProductoRequest $request, Producto $producto)
    {
        $validated = $request->validated();

        if ($request->hasFile('imagen_producto')) {
            $validated['imagen_url'] = $this->guardarImagen($request);
        }

        $producto->update($validated);

        return response()->json($producto->load(['categoria', 'marca']));
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
        $productos = Producto::stockBajo()->with(['categoria', 'marca'])->paginate(15);
        return response()->json($productos);
    }

    private function guardarImagen(Request $request): string
    {
        $uploadPath = public_path('uploads/productos');
        if (!File::exists($uploadPath)) {
            File::makeDirectory($uploadPath, 0755, true);
        }

        $file = $request->file('imagen_producto');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($uploadPath, $filename);

        return 'uploads/productos/' . $filename;
    }
}
