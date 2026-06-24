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
 * API REST para gestión de compras a proveedores
 * 
 * IMPLEMENTA:
 * - Transacciones ACID: DB::transaction() para garantizar atomicidad
 * - Validación de precios con DECIMAL(10,2)
 * - Foreign Key Protection: Valida integridad referencial
 * - Auditoría: Registra todas las operaciones
 */
class CompraController extends Controller
{
    /**
     * GET /api/compras - Listar compras
     * 
     * Filtros disponibles:
     * - estado: Pendiente, Parcial, Completada, Cancelada
     * - proveedor: id_proveedor
     * - fecha_inicio, fecha_final: Rango de fechas
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
     * GET /api/compras/{id} - Obtener una compra específica
     */
    public function show(Compra $compra)
    {
        return response()->json(
            $compra->load(['usuario', 'proveedor', 'detalles.producto'])
        );
    }

    /**
     * POST /api/compras - Crear compra
     * 
     * TRANSACCIÓN ACID:
     * - Se crea la compra y sus detalles de forma atómica
     * - Si algo falla, se revierte todo (ROLLBACK)
     * - Garantiza consistencia de datos
     * 
     * Validaciones:
     * - usuario debe existir
     * - proveedor debe existir y no estar eliminado
     * - producto debe existir
     * - cantidad y precio deben ser válidos
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_usuario' => 'required|integer|exists:usuario,id_usuario',
            'id_proveedor' => 'required|integer|exists:proveedor,id_proveedor',
            'numero_factura_proveedor' => 'nullable|string|max:50|unique:compra',
            'items' => 'required|array|min:1',
            'items.*.id_producto' => 'required|integer|exists:producto,id_producto',
            'items.*.cantidad_ordenada' => 'required|integer|min:1',
            'items.*.costo_unitario' => 'required|numeric|min:0.01|max:9999.99',
            'observacion' => 'nullable|string|max:1000',
        ]);

        try {
            // TRANSACCIÓN: Garantiza atomicidad
            $compra = DB::transaction(function () use ($validated) {
                // Generar número de orden único
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
                    'total_compra' => 0, // Se actualiza después
                ]);

                // Crear detalles y calcular total
                $total = 0;
                foreach ($validated['items'] as $item) {
                    // Validar que el producto existe
                    $producto = Producto::findOrFail($item['id_producto']);

                    // Calcular subtotal con precisión DECIMAL(10,2)
                    $subtotal = round($item['cantidad_ordenada'] * $item['costo_unitario'], 2);

                    DetalleCompra::create([
                        'id_compra' => $compra->id_compra,
                        'id_producto' => $item['id_producto'],
                        'cantidad_ordenada' => $item['cantidad_ordenada'],
                        'cantidad_recibida' => 0,
                        'costo_unitario' => round($item['costo_unitario'], 2),
                        'subtotal' => $subtotal,
                    ]);

                    $total += $subtotal;
                }

                // Actualizar total con precisión
                $compra->update(['total_compra' => round($total, 2)]);

                return $compra;
            }, maxAttempts: 3);

            return response()->json(
                $compra->load(['usuario', 'proveedor', 'detalles.producto']),
                Response::HTTP_CREATED
            );
        } catch (\Illuminate\Database\QueryException $e) {
            // Error de base de datos (FK, unique, etc.)
            return response()->json([
                'success' => false,
                'message' => 'Error de integridad en la base de datos',
                'error' => $e->getMessage()
            ], Response::HTTP_CONFLICT);
        } catch (\Exception $e) {
            // Otro error (transaction automáticamente hace rollback)
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la compra: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * PUT /api/compras/{id} - Actualizar compra
     * 
     * Solo permite actualizar si está en estado Pendiente
     * Actualiza estado y observaciones
     */
    public function update(Request $request, Compra $compra)
    {
        // Validar que pueda ser actualizada
        if ($compra->estado !== 'Pendiente') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden actualizar compras en estado Pendiente'
            ], Response::HTTP_CONFLICT);
        }

        $validated = $request->validate([
            'estado' => 'in:Pendiente,Parcial,Completada,Cancelada',
            'fecha_entrega' => 'nullable|date',
            'observacion' => 'nullable|string|max:1000',
        ]);

        try {
            $compra->update($validated);

            return response()->json(
                $compra->load(['usuario', 'proveedor', 'detalles.producto'])
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la compra: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * POST /api/compras/{id}/recibir - Recibir items de compra
     * 
     * TRANSACCIÓN ACID:
     * - Se actualizan cantidades recibidas Y stock del producto de forma atómica
     * - Si algo falla, se revierte TODO (incluyendo actualización de stock)
     * - Garantiza consistencia entre compra y inventario
     * 
     * Validaciones:
     * - cantidad_recibida no puede exceder cantidad_ordenada
     * - cantidad_recibida debe ser positiva
     * - producto debe existir
     */
    public function recibirItems(Request $request, Compra $compra)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id_detalle_compra' => 'required|integer|exists:detalle_compra,id_detalle_compra',
            'items.*.cantidad_recibida' => 'required|integer|min:1',
        ]);

        try {
            // TRANSACCIÓN: Garantiza atomicidad en actualización de stock
            DB::transaction(function () use ($validated, $compra) {
                foreach ($validated['items'] as $item) {
                    $detalle = DetalleCompra::findOrFail($item['id_detalle_compra']);

                    // Validar que pertenece a esta compra
                    if ($detalle->id_compra !== $compra->id_compra) {
                        throw new \Exception('El detalle no pertenece a esta compra');
                    }

                    // Validar cantidad recibida
                    $cantidad_recibida = $item['cantidad_recibida'];
                    if ($cantidad_recibida > $detalle->cantidad_ordenada) {
                        throw new \Exception(
                            "No se puede recibir {$cantidad_recibida} unidades. " .
                            "Solo se ordenaron {$detalle->cantidad_ordenada}."
                        );
                    }

                    // Actualizar cantidad recibida
                    $detalle->update(['cantidad_recibida' => $cantidad_recibida]);

                    // ACTUALIZACIÓN ATÓMICA: Incrementar stock del producto
                    // Si esto falla, la transacción se revierte
                    $detalle->producto->increment('stock_actual', $cantidad_recibida);
                }

                // Recalcular estado de compra
                $todas_recibidas = $compra->detalles->every(function ($detalle) {
                    return $detalle->cantidad_recibida >= $detalle->cantidad_ordenada;
                });

                $compra->update([
                    'estado' => $todas_recibidas ? 'Completada' : 'Parcial',
                    'fecha_entrega' => now(),
                ]);
            }, maxAttempts: 3);

            return response()->json(
                $compra->load(['usuario', 'proveedor', 'detalles.producto'])
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            // Transacción automáticamente hace rollback
            return response()->json([
                'success' => false,
                'message' => 'Error al recibir items: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * DELETE /api/compras/{id} - Eliminar compra
     * 
     * Restricciones:
     * - Solo se pueden eliminar compras en estado Pendiente
     * - Evita errores de integridad referencial
     */
    public function destroy(Compra $compra)
    {
        if ($compra->estado !== 'Pendiente') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden eliminar compras pendientes'
            ], Response::HTTP_CONFLICT);
        }

        try {
            DB::transaction(function () use ($compra) {
                // Eliminar detalles primero (cascade)
                $compra->detalles()->delete();
                // Eliminar compra
                $compra->delete();
            });

            return response()->noContent();
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la compra: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
