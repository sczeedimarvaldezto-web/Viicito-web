<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Venta;
use App\Models\Producto;
use App\Models\Usuario;
use App\Models\CierreCaja;
use App\Models\AuditoriaReinicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * DashboardController
 * 
 * API para obtener métricas del dashboard
 */
class DashboardController extends Controller
{
    /**
     * GET /api/dashboard/resumen - Resumen general del dashboard
     */
    public function resumen(Request $request)
    {
        $fecha_inicio = $request->get('fecha_inicio', now()->startOfMonth());
        $fecha_final = $request->get('fecha_final', now());

        // Ventas del período
        $ventas = Venta::completadas()
            ->rangoFechas($fecha_inicio, $fecha_final)
            ->get();

        // Productos
        $productos_total = Producto::activos()->count();
        $productos_bajo_stock = Producto::stockBajo()->count();
        $valor_inventario = Producto::activos()
            ->selectRaw('SUM(stock_actual * precio_venta) as total')
            ->first()?->total ?? 0;

        // Estadísticas de ventas
        $total_ventas = $ventas->sum('total_venta');
        $promedio_venta = $ventas->count() > 0 ? $total_ventas / $ventas->count() : 0;
        $cantidad_transacciones = $ventas->count();

        // Ventas por método de pago
        $ventas_efectivo = $ventas->where('metodo_pago', 'Efectivo')->sum('total_venta');
        $ventas_qr = $ventas->where('metodo_pago', 'QR')->sum('total_venta');

        // Top productos vendidos
        $top_productos = Producto::with('detallesVenta')
            ->activos()
            ->selectRaw('producto.*, SUM(detalle_venta.cantidad) as total_vendido')
            ->join('detalle_venta', 'producto.id_producto', '=', 'detalle_venta.id_producto')
            ->whereBetween('detalle_venta.created_at', [$fecha_inicio, $fecha_final])
            ->groupBy('producto.id_producto')
            ->orderByRaw('total_vendido DESC')
            ->limit(5)
            ->get();

        return response()->json([
            'periodo' => [
                'fecha_inicio' => $fecha_inicio,
                'fecha_final' => $fecha_final,
            ],
            'ventas' => [
                'cantidad_transacciones' => $cantidad_transacciones,
                'total_ventas' => round($total_ventas, 2),
                'promedio_venta' => round($promedio_venta, 2),
                'efectivo' => round($ventas_efectivo, 2),
                'qr' => round($ventas_qr, 2),
            ],
            'inventario' => [
                'productos_activos' => $productos_total,
                'productos_bajo_stock' => $productos_bajo_stock,
                'valor_inventario' => round($valor_inventario, 2),
            ],
            'top_productos' => $top_productos->map(fn ($p) => [
                'id' => $p->id_producto,
                'nombre' => $p->nombre_producto,
                'cantidad_vendida' => $p->total_vendido,
                'precio_venta' => $p->precio_venta,
            ]),
        ]);
    }

    /**
     * GET /api/dashboard/vendedores - Performance de vendedores
     */
    public function vendedores(Request $request)
    {
        $fecha_inicio = $request->get('fecha_inicio', now()->startOfMonth());
        $fecha_final = $request->get('fecha_final', now());

        $vendedores = Usuario::vendedores()
            ->selectRaw('
                usuario.id_usuario,
                usuario.nombre_completo,
                COUNT(venta.id_venta) as total_ventas,
                SUM(venta.total_venta) as total_vendido
            ')
            ->leftJoin('venta', 'usuario.id_usuario', '=', 'venta.id_usuario')
            ->whereBetween('venta.fecha_hora', [$fecha_inicio, $fecha_final])
            ->groupBy('usuario.id_usuario', 'usuario.nombre_completo')
            ->orderByRaw('total_vendido DESC')
            ->get();

        return response()->json($vendedores);
    }

    /**
     * GET /api/dashboard/tendencia-ventas - Tendencia de ventas por día
     */
    public function tendenciaVentas(Request $request)
    {
        $dias = $request->get('dias', 30);
        $fecha_inicio = now()->subDays($dias);

        $ventas = Venta::completadas()
            ->where('fecha_hora', '>=', $fecha_inicio)
            ->selectRaw('DATE(fecha_hora) as fecha, COUNT(*) as transacciones, SUM(total_venta) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        return response()->json([
            'dias' => $dias,
            'datos' => $ventas,
        ]);
    }

    /**
     * GET /api/dashboard/alerta-stock - Alertas de stock
     */
    public function alertasStock()
    {
        $total = Producto::stockBajo()->count();

        $productos = Producto::stockBajo()
            ->with('categoria')
            ->selectRaw('*, (stock_minimo - stock_actual) as faltante')
            ->orderByRaw('faltante DESC')
            ->limit(20)
            ->get();

        return response()->json([
            'total_alertas' => $total,
            'productos' => $productos,
        ]);
    }

    /**
     * GET /api/empleados - Lista de empleados con estadísticas
     */
    public function empleados(Request $request)
    {
        $fecha_inicio = $request->get('fecha_inicio', now()->startOfMonth());
        $fecha_final = $request->get('fecha_final', now());

        // Obtener todos los empleados (rol != owner)
        $empleados = User::with('role')
            ->whereHas('role', function ($query) {
                $query->where('name', '!=', 'owner');
            })
            ->get()
            ->map(fn ($emp) => [
                'id' => $emp->id,
                'nombre' => $emp->name,
                'email' => $emp->email,
                'rol' => $emp->role?->label ?? 'Empleado',
                'total_ventas' => 0,  // Próximo: conectar con tabla de ventas
                'total_vendido' => 0,
                'promedio_venta' => 0,
                'fecha_registro' => $emp->created_at->format('d/m/Y'),
            ]);

        return response()->json([
            'total_empleados' => $empleados->count(),
            'empleados' => $empleados,
        ]);
    }

    /**
     * POST /api/dashboard/cierre-caja - Registrar cierre de caja
     */
    public function cerrarCaja(Request $request)
    {
        try {
            $validated = $request->validate([
                'fecha_cierre' => 'required|date',
                'total_efectivo' => 'required|numeric|min:0',
                'total_qr' => 'required|numeric|min:0',
                'total_ventas' => 'required|numeric|min:0',
                'cantidad_transacciones' => 'required|integer|min:0',
                'observaciones' => 'nullable|string',
            ]);

            // Verificar si ya existe cierre para esa fecha
            $existente = CierreCaja::where('fecha_cierre', $validated['fecha_cierre'])->first();
            
            if ($existente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un cierre de caja para esta fecha',
                    'data' => $existente
                ], 409);
            }

            // Crear nuevo cierre
            $cierre = CierreCaja::create([
                'fecha_cierre' => $validated['fecha_cierre'],
                'total_efectivo' => $validated['total_efectivo'],
                'total_qr' => $validated['total_qr'],
                'total_ventas' => $validated['total_ventas'],
                'cantidad_transacciones' => $validated['cantidad_transacciones'],
                'observaciones' => $validated['observaciones'] ?? null,
                'estado' => 'Completado',
            ]);

            \Log::info('✓ Cierre de caja registrado', [
                'fecha' => $validated['fecha_cierre'],
                'total' => $validated['total_ventas'],
            ]);

            return response()->json([
                'success' => true,
                'message' => '✓ Cierre de caja registrado correctamente',
                'data' => $cierre
            ], 201);

        } catch (\Exception $e) {
            \Log::error('❌ Error al cerrar caja:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el cierre de caja: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/dashboard/reiniciar - Reiniciar ventas del día
     */
    public function reiniciarVentas(Request $request)
    {
        try {
            $validated = $request->validate([
                'guardar_cierre' => 'required|boolean',
                'total_efectivo' => 'nullable|numeric|min:0',
                'total_qr' => 'nullable|numeric|min:0',
                'total_ventas' => 'nullable|numeric|min:0',
                'observaciones' => 'nullable|string',
            ]);

            // Si quiere guardar el cierre antes de reiniciar
            if ($validated['guardar_cierre']) {
                CierreCaja::create([
                    'fecha_cierre' => now()->toDateString(),
                    'total_efectivo' => $validated['total_efectivo'] ?? 0,
                    'total_qr' => $validated['total_qr'] ?? 0,
                    'total_ventas' => $validated['total_ventas'] ?? 0,
                    'cantidad_transacciones' => Venta::whereDate('created_at', now())->count(),
                    'observaciones' => $validated['observaciones'] ?? null,
                    'estado' => 'Completado',
                ]);
            }

            // Contar ventas antes de borrar
            $totalVentasBorradas = Venta::count();

            // Eliminar todas las ventas
            DB::transaction(function () use ($totalVentasBorradas) {
                Venta::truncate();
            });

            // Registrar en auditoría
            $usuario = auth()->user();
            AuditoriaReinicio::create([
                'fecha_reinicio' => now(),
                'id_usuario' => $usuario?->id ?? null,
                'total_ventas_borradas' => $totalVentasBorradas,
                'razon' => 'Reinicio del sistema de ventas por orden del usuario',
            ]);

            \Log::warning('⚠️ Sistema reiniciado', [
                'ventas_borradas' => $totalVentasBorradas,
                'usuario' => $usuario?->name ?? 'Sistema',
                'timestamp' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => '✓ Sistema reiniciado correctamente. Se borraron ' . $totalVentasBorradas . ' ventas.',
                'data' => [
                    'ventas_borradas' => $totalVentasBorradas,
                    'timestamp' => now(),
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('❌ Error al reiniciar sistema:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al reiniciar el sistema: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/dashboard/historial-cierres - Historial de cierres de caja
     */
    public function historialCierres(Request $request)
    {
        $dias = $request->get('dias', 30);
        $fecha_inicio = now()->subDays($dias)->startOfDay();

        $cierres = CierreCaja::where('fecha_cierre', '>=', $fecha_inicio)
            ->recientes()
            ->get();

        return response()->json([
            'dias' => $dias,
            'total_registros' => $cierres->count(),
            'cierres' => $cierres,
        ]);
    }

}
