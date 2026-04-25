<?php

namespace App\Http\Controllers\Api;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * CompraController
 * 
 * API REST para gestión de compras
 */
class CompraController extends Controller
{
    /**
     * GET /api/compras - Listar compras
     */
    public function index(Request $request)
    {
        $query = Compra::with(['usuario', 'proveedor', 'detalles.producto']);

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('proveedor')) {
            $query->where('id_proveedor', $request->proveedor);
        }

        if ($request->has('fecha_inicio') && $request->has('fecha_final')) {
            $query->rangoFechas($request->fecha_inicio, $request->fecha_final);
        }

        $compras = $query->orderBy('fecha_orden', 'desc')->paginate($request->get('per_page', 15));

        return response()->json($compras);
    }

    /**
     * GET /api/compras/{id} - Obtener una compra
     */
    public function show(Compra $compra)
    {
        return response()->json(
            $compra->load(['usuario', 'proveedor', 'detalles.producto'])
        );
    }

    /**
     * POST /api/compras - Crear compra
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_usuario' => 'required|exists:usuario,id_usuario',
            'id_proveedor' => 'required|exists:proveedor,id_proveedor',
            'numero_factura_proveedor' => 'nullable|string',
            'items' => 'required|array',
            'items.*.id_producto' => 'required|exists:producto,id_producto',
            'items.*.cantidad_ordenada' => 'required|integer|min:1',
            'items.*.costo_unitario' => 'required|numeric|min:0',
            'observacion' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Generar número de orden
            $ultimo = Compra::latest('id_compra')->first();
            $numero_orden = 'CMP-' . str_pad(($ultimo?->id_compra ?? 0) + 1, 8, '0', STR_PAD_LEFT);

            // Crear compra
            $compra = Compra::create([
                'id_usuario' => $validated['id_usuario'],
                'id_proveedor' => $validated['id_proveedor'],
                'numero_orden' => $numero_orden,
                'numero_factura_proveedor' => $validated['numero_factura_proveedor'] ?? null,
                'fecha_orden' => now(),
                'estado' => 'Pendiente',
                'observacion' => $validated['observacion'] ?? null,
            ]);

            // Crear detalles
            $total = 0;
            foreach ($validated['items'] as $item) {
                $subtotal = $item['cantidad_ordenada'] * $item['costo_unitario'];

                DetalleCompra::create([
                    'id_compra' => $compra->id_compra,
                    'id_producto' => $item['id_producto'],
                    'cantidad_ordenada' => $item['cantidad_ordenada'],
                    'cantidad_recibida' => 0,
                    'costo_unitario' => $item['costo_unitario'],
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            $compra->update(['total_compra' => $total]);

            DB::commit();

            return response()->json(
                $compra->load(['usuario', 'proveedor', 'detalles.producto']),
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * PUT /api/compras/{id} - Actualizar compra
     */
    public function update(Request $request, Compra $compra)
    {
        $validated = $request->validate([
            'estado' => 'in:Pendiente,Parcial,Completada,Cancelada',
            'fecha_entrega' => 'nullable|date',
            'observacion' => 'nullable|string',
        ]);

        $compra->update($validated);

        return response()->json($compra->load(['usuario', 'proveedor', 'detalles.producto']));
    }

    /**
     * POST /api/compras/{id}/recibir - Recibir items de compra
     */
    public function recibirItems(Request $request, Compra $compra)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id_detalle_compra' => 'required|exists:detalle_compra,id_detalle_compra',
            'items.*.cantidad_recibida' => 'required|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['items'] as $item) {
                $detalle = DetalleCompra::find($item['id_detalle_compra']);
                $cantidad_recibida = $item['cantidad_recibida'];

                // Actualizar cantidad recibida
                $detalle->update(['cantidad_recibida' => $cantidad_recibida]);

                // Actualizar stock del producto
                $detalle->producto->increment('stock_actual', $cantidad_recibida);
            }

            // Actualizar estado de compra
            $todas_recibidas = $compra->detalles->every(function ($detalle) {
                return $detalle->cantidad_recibida >= $detalle->cantidad_ordenada;
            });

            $compra->update([
                'estado' => $todas_recibidas ? 'Completada' : 'Parcial',
                'fecha_entrega' => now(),
            ]);

            DB::commit();

            return response()->json($compra->load(['usuario', 'proveedor', 'detalles.producto']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * DELETE /api/compras/{id} - Eliminar compra
     */
    public function destroy(Compra $compra)
    {
        if ($compra->estado !== 'Pendiente') {
            return response()->json(
                ['error' => 'Solo se pueden eliminar compras pendientes'],
                Response::HTTP_FORBIDDEN
            );
        }

        $compra->delete();
        return response()->noContent();
    }
}
