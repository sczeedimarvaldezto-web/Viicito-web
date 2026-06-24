<?php

namespace App\Http\Controllers\Api;

use App\Models\Producto;
use Illuminate\Http\Request;

/**
 * InventarioController
 *
 * Endpoint dedicado para listado de inventario con filtros por categoría y marca.
 * Acepta parámetros: ?category=2&brand=5 (también ?categoria= y ?marca=)
 */
class InventarioController extends Controller
{
    /**
     * GET /api/inventario - Listar inventario con filtros
     */
    public function index(Request $request)
    {
        $query = Producto::queryInventario($request);

        $productos = $query->paginate($request->get('per_page', 15));

        return response()->json($productos);
    }
}
