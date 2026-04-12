<?php

namespace App\Http\Controllers\Api;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * VentaController
 * 
 * API REST para gestión de ventas
 */
class VentaController extends Controller
{
    /**
     * GET /api/ventas - Listar ventas
     */
    public function index(Request $request)
    {
        $query = Venta::with(['usuario', 'cliente', 'detalles.producto']);

        // Filtros
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('vendedor')) {
            $query->where('id_usuario', $request->vendedor);
        }

        if ($request->has('cliente')) {
            $query->where('id_cliente', $request->cliente);
        }

        if ($request->has('fecha_inicio') && $request->has('fecha_final')) {
            $query->rangoFechas(
                $request->fecha_inicio,
                $request->fecha_final
            );
        }

        if ($request->has('metodo_pago')) {
            $query->where('metodo_pago', $request->metodo_pago);
        }

        $ventas = $query->orderBy('fecha_hora', 'desc')->paginate($request->get('per_page', 15));

        return response()->json($ventas);
    }

    /**
     * GET /api/ventas/{id} - Obtener una venta
     */
    public function show(Venta $venta)
    {
        return response()->json(
            $venta->load(['usuario', 'cliente', 'detalles.producto'])
        );
    }

    /**
     * POST /api/ventas - Crear venta
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_usuario' => 'required|exists:usuario,id_usuario',
            'id_cliente' => 'required|exists:cliente,id_cliente',
            'metodo_pago' => 'required|in:Efectivo,Tarjeta,Cheque,Crédito',
            'observacion' => 'nullable|string',
            'items' => 'required|array',
            'items.*.id_producto' => 'required|exists:producto,id_producto',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.precio_unitario' => 'required|numeric|min:0',
            'items.*.descuento' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Generar número de documento
            $ultimo = Venta::latest('id_venta')->first();
            $numero_documento = 'VTA-' . str_pad(($ultimo?->id_venta ?? 0) + 1, 8, '0', STR_PAD_LEFT);

            // Crear venta
            $venta = Venta::create([
                'id_usuario' => $validated['id_usuario'],
                'id_cliente' => $validated['id_cliente'],
                'numero_documento' => $numero_documento,
                'metodo_pago' => $validated['metodo_pago'],
                'observacion' => $validated['observacion'] ?? null,
                'fecha_hora' => now(),
                'estado' => 'Completada',
            ]);

            // Crear detalles y calcular totales
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $producto = Producto::find($item['id_producto']);
                
                $descuento = $item['descuento'] ?? 0;
                $subtotal_item = ($item['cantidad'] * $item['precio_unitario']) - $descuento;

                DetalleVenta::create([
                    'id_venta' => $venta->id_venta,
                    'id_producto' => $item['id_producto'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'descuento' => $descuento,
                    'subtotal' => $subtotal_item,
                ]);

                // Actualizar stock
                $producto->decrement('stock_actual', $item['cantidad']);
                $subtotal += $subtotal_item;
            }

            // Calcular impuesto (21% IVA)
            $impuesto = $subtotal * 0.21;
            $total = $subtotal + $impuesto;

            $venta->update([
                'subtotal' => $subtotal,
                'impuesto' => $impuesto,
                'total_venta' => $total,
            ]);

            DB::commit();

            return response()->json(
                $venta->load(['usuario', 'cliente', 'detalles.producto']),
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * PUT /api/ventas/{id} - Actualizar venta (solo estado)
     */
    public function update(Request $request, Venta $venta)
    {
        $validated = $request->validate([
            'estado' => 'in:Completada,Cancelada,Pendiente,Devuelta',
            'observacion' => 'nullable|string',
        ]);

        $venta->update($validated);

        return response()->json($venta->load(['usuario', 'cliente', 'detalles.producto']));
    }

    /**
     * DELETE /api/ventas/{id} - Eliminar venta (si está pendiente)
     */
    public function destroy(Venta $venta)
    {
        if ($venta->estado !== 'Pendiente') {
            return response()->json(
                ['error' => 'Solo se pueden eliminar ventas pendientes'],
                Response::HTTP_FORBIDDEN
            );
        }

        // Restaurar stock
        foreach ($venta->detalles as $detalle) {
            $detalle->producto->increment('stock_actual', $detalle->cantidad);
        }

        $venta->delete();
        return response()->noContent();
    }

    /**
     * GET /api/ventas/reporte/diario - Reporte de ventas diarias
     */
    public function reporteDiario(Request $request)
    {
        $fecha = $request->get('fecha', now()->toDateString());

        $ventas = Venta::whereDate('fecha_hora', $fecha)
            ->completadas()
            ->with(['usuario', 'cliente'])
            ->get();

        $totales = [
            'cantidad_transacciones' => $ventas->count(),
            'total_ventas' => $ventas->sum('total_venta'),
            'total_efectivo' => $ventas->where('metodo_pago', 'Efectivo')->sum('total_venta'),
            'total_tarjeta' => $ventas->where('metodo_pago', 'Tarjeta')->sum('total_venta'),
            'total_credito' => $ventas->where('metodo_pago', 'Crédito')->sum('total_venta'),
        ];

        return response()->json([
            'fecha' => $fecha,
            'ventas' => $ventas,
            'totales' => $totales,
        ]);
    }
}
