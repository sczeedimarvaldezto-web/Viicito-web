<?php

namespace App\Http\Controllers\Api;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Services\ComprobanteVentaPdfService;
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
    public function __construct(
        private ComprobanteVentaPdfService $pdfService
    ) {}

    /**
     * GET /api/ventas - Listar ventas
     */
    public function index(Request $request)
    {
        $query = Venta::with(['usuario', 'detalles.producto']);

        // Filtros
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('vendedor')) {
            $query->where('id_usuario', $request->vendedor);
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
            $venta->load(['usuario', 'detalles.producto'])
        );
    }

    /**
     * POST /api/ventas - Crear venta
     */
    public function store(Request $request)
    {
        \Log::info('📊 VentaController@store - Petición recibida', [
            'method' => $request->method(),
            'url' => $request->url(),
            'headers' => $request->headers->all(),
            'all_data' => $request->all(),
            'auth_user' => auth()->user()?->id,
            'session_id' => session()->getId(),
        ]);

        $validated = $request->validate([
            'id_usuario' => 'required|integer|exists:usuario,id_usuario',  // ✅ Validar en tabla 'usuario' con columna 'id_usuario'
            'metodo_pago' => 'required|in:Efectivo,Tarjeta,Cheque,Crédito',
            'observacion' => 'nullable|string',
            'items' => 'required|array',
            'items.*.id_producto' => 'required|exists:producto,id_producto',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.precio_unitario' => 'required|numeric|min:0',
            'items.*.descuento' => 'nullable|numeric|min:0',
        ]);

        \Log::info('✅ Validación exitosa', ['validated' => $validated]);

        try {
            DB::beginTransaction();

            // ✅ PASO 1: Calcular totales ANTES de crear la venta
            $subtotal = 0;
            $items_procesados = [];
            
            foreach ($validated['items'] as $item) {
                $producto = Producto::findOrFail($item['id_producto']);
                $descuento = $item['descuento'] ?? 0;
                $subtotal_item = ($item['cantidad'] * $item['precio_unitario']) - $descuento;
                
                $items_procesados[] = [
                    'producto' => $producto,
                    'descuento' => $descuento,
                    'subtotal_item' => $subtotal_item,
                    'item' => $item,
                ];
                
                $subtotal += $subtotal_item;
            }

            // Calcular impuesto (21% IVA)
            $impuesto = $subtotal * 0.21;
            $total = $subtotal + $impuesto;

            // Generar número de documento
            $ultimo = Venta::latest('id_venta')->first();
            $numero_documento = 'VTA-' . str_pad(($ultimo?->id_venta ?? 0) + 1, 8, '0', STR_PAD_LEFT);

            // ✅ PASO 2: Crear venta CON los totales calculados
            $venta = Venta::create([
                'id_usuario' => $validated['id_usuario'],
                'numero_documento' => $numero_documento,
                'metodo_pago' => $validated['metodo_pago'],
                'observacion' => $validated['observacion'] ?? null,
                'fecha_hora' => now(),
                'estado' => 'Completada',
                'subtotal' => $subtotal,
                'impuesto' => $impuesto,
                'total_venta' => $total,
            ]);

            // ✅ PASO 3: Crear detalles y actualizar stock
            foreach ($items_procesados as $procesado) {
                DetalleVenta::create([
                    'id_venta' => $venta->id_venta,
                    'id_producto' => $procesado['item']['id_producto'],
                    'cantidad' => $procesado['item']['cantidad'],
                    'precio_unitario' => $procesado['item']['precio_unitario'],
                    'descuento' => $procesado['descuento'],
                    'subtotal' => $procesado['subtotal_item'],
                ]);

                // Actualizar stock
                $procesado['producto']->decrement('stock_actual', $procesado['item']['cantidad']);
            }

            DB::commit();

            $venta->load(ComprobanteVentaPdfService::RELACIONES);

            // Generar comprobante PDF tras el pago exitoso
            $this->pdfService->verificarGeneracion($venta);

            return response()->json([
                'venta' => $venta,
                'pdf_url' => url("/api/ventas/{$venta->id_venta}/comprobante"),
                'sale_id' => $venta->id_venta,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('❌ Error al crear venta', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => $e->getMessage(),
                'type' => class_basename($e),
            ], Response::HTTP_BAD_REQUEST);
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

        return response()->json($venta->load(['usuario', 'detalles.producto']));
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
     * GET /api/ventas/{venta}/comprobante - Comprobante PDF de una venta
     * GET /api/ventas/comprobante?sale_id= - Regenerar PDF por ID de venta
     */
    public function comprobante(Request $request, ?Venta $venta = null)
    {
        if ($request->filled('sale_id')) {
            $venta = $this->pdfService->cargarVenta((int) $request->input('sale_id'));
        } elseif ($venta) {
            $venta = $this->pdfService->cargarVenta($venta->id_venta);
        } else {
            return response()->json(['error' => 'Se requiere sale_id o id de venta'], Response::HTTP_BAD_REQUEST);
        }

        $inline = $request->boolean('inline', true);

        return $this->pdfService->responder($venta, $inline);
    }

    /**
     * GET /api/ventas/reporte/diario - Reporte de ventas diarias
     */
    public function reporteDiario(Request $request)
    {
        $fecha = $request->get('fecha', now()->toDateString());

        $ventas = Venta::whereDate('fecha_hora', $fecha)
            ->completadas()
            ->with(['usuario'])
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
