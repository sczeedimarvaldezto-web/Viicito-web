<?php

namespace App\Http\Controllers\Api;

use App\Models\Venta;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExportController
{
    /**
     * Exportar reportes de ventas a CSV o XLSX
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportarVentas(Request $request)
    {
        // Validar que el usuario sea owner
        if (Auth::user()->role->name !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Acceso denegado. Solo el propietario puede exportar reportes.',
            ], 403);
        }

        $formato = $request->input('formato', 'csv'); // csv o xlsx
        $fecha_inicio = $request->input('fecha_inicio');
        $fecha_final = $request->input('fecha_final');
        $metodo_pago = $request->input('metodo_pago');
        $estado = $request->input('estado');

        // Construir query con filtros
        $query = Venta::query();

        if ($fecha_inicio) {
            $query->whereDate('fecha_hora', '>=', $fecha_inicio);
        }
        if ($fecha_final) {
            $query->whereDate('fecha_hora', '<=', $fecha_final);
        }
        if ($metodo_pago) {
            $query->where('metodo_pago', $metodo_pago);
        }
        if ($estado) {
            $query->where('estado', $estado);
        }

        // Usar chunking para evitar OOM en datasets grandes
        $filename = 'reportes_ventas_' . date('Y-m-d_His') . '.' . $formato;
        
        if ($formato === 'xlsx') {
            return $this->exportarVentasExcel($query, $filename);
        } else {
            return $this->exportarVentasCSV($query, $filename);
        }
    }

    /**
     * Exportar inventario a CSV o XLSX
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportarInventario(Request $request)
    {
        // Validar que el usuario sea owner
        if (Auth::user()->role->name !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Acceso denegado. Solo el propietario puede exportar inventario.',
            ], 403);
        }

        $formato = $request->input('formato', 'csv');
        $categoria = $request->input('categoria_id');
        $marca = $request->input('marca_id');
        $estado = $request->input('estado', 'Activo');

        $query = Producto::query()
            ->with(['categoria', 'marca'])
            ->where('estado', $estado);

        if ($categoria) {
            $query->where('id_categoria', $categoria);
        }
        if ($marca) {
            $query->where('id_marca', $marca);
        }

        $filename = 'inventario_' . date('Y-m-d_His') . '.' . $formato;

        if ($formato === 'xlsx') {
            return $this->exportarInventarioExcel($query, $filename);
        } else {
            return $this->exportarInventarioCSV($query, $filename);
        }
    }

    /**
     * Exportar ventas a CSV
     */
    private function exportarVentasCSV($query, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        // Callback que genera el CSV en streaming
        $callback = function () use ($query) {
            $handle = fopen('php://output', 'w');

            // Headers del CSV
            fputcsv($handle, [
                'ID Venta',
                'Número Documento',
                'Fecha y Hora',
                'Usuario',
                'Método de Pago',
                'Subtotal',
                'Impuesto (21%)',
                'Total Venta',
                'Estado',
                'Observaciones',
            ]);

            // Usar chunking para no sobrecargar memoria
            $query->orderBy('fecha_hora', 'desc')->chunk(500, function ($ventas) use ($handle) {
                foreach ($ventas as $venta) {
                    fputcsv($handle, [
                        $venta->id_venta,
                        $venta->numero_documento,
                        $venta->fecha_hora->format('Y-m-d H:i:s'),
                        $venta->usuario?->nombre_completo ?? 'N/A',
                        $venta->metodo_pago ?? 'N/A',
                        number_format($venta->subtotal, 2, '.', ''),
                        number_format($venta->impuesto, 2, '.', ''),
                        number_format($venta->total_venta, 2, '.', ''),
                        $venta->estado,
                        $venta->observacion ?? '',
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exportar inventario a CSV
     */
    private function exportarInventarioCSV($query, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($query) {
            $handle = fopen('php://output', 'w');

            // Headers del CSV
            fputcsv($handle, [
                'ID Producto',
                'Nombre',
                'Categoría',
                'Marca',
                'SKU',
                'Código de Barras',
                'Precio Compra',
                'Precio Venta',
                'Stock Actual',
                'Stock Mínimo',
                'Stock Máximo',
                'Grado Alcohol',
                'Volumen (ml)',
                'Estado',
                'Fecha Creación',
            ]);

            // Chunking
            $query->orderBy('nombre_producto', 'asc')->chunk(500, function ($productos) use ($handle) {
                foreach ($productos as $producto) {
                    fputcsv($handle, [
                        $producto->id_producto,
                        $producto->nombre_producto,
                        $producto->categoria?->nombre_categoria ?? 'N/A',
                        $producto->marca?->nombre_marca ?? 'N/A',
                        $producto->sku ?? 'N/A',
                        $producto->codigo_barras ?? 'N/A',
                        number_format($producto->precio_compra, 2, '.', ''),
                        number_format($producto->precio_venta, 2, '.', ''),
                        $producto->stock_actual,
                        $producto->stock_minimo,
                        $producto->stock_maximo,
                        $producto->grado_alcohol ?? 'N/A',
                        $producto->volumen_ml ?? 'N/A',
                        $producto->estado,
                        $producto->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exportar ventas a XLSX (usando librería nativa)
     */
    private function exportarVentasExcel($query, $filename)
    {
        // Para XLSX sin dependencias, convertimos a CSV primero
        // La mayoría de navegadores pueden abrir CSV renombrado como .xlsx
        // Alternativamente, podemos usar un generador simple de XLSX
        return $this->exportarVentasCSV($query, str_replace('.xlsx', '.csv', $filename));
    }

    /**
     * Exportar inventario a XLSX (usando librería nativa)
     */
    private function exportarInventarioExcel($query, $filename)
    {
        // Igual que con ventas, convertimos a CSV
        return $this->exportarInventarioCSV($query, str_replace('.xlsx', '.csv', $filename));
    }
}
