<?php

namespace App\Services;

class ReportExportService
{
    public function salesHeaders(): array
    {
        return [
            'Documento',
            'Fecha',
            'Subtotal',
            'IVA',
            'Total',
            'Método de Pago',
            'Estado',
        ];
    }

    public function inventoryHeaders(): array
    {
        return [
            'Código',
            'Producto',
            'Categoría',
            'Marca',
            'Stock',
            'Costo Unitario',
            'Valor Total',
        ];
    }

    public function comprasHeaders(): array
    {
        return [
            'Orden',
            'Fecha',
            'Proveedor',
            'Total',
            'Estado',
        ];
    }

    public function filename(string $type = 'ventas'): string
    {
        return sprintf('reporte-%s-%s.csv', $type, now()->format('Ymd-His'));
    }
}
