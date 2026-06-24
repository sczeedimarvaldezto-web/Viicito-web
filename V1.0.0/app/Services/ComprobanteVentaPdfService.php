<?php

namespace App\Services;

use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ComprobanteVentaPdfService
{
    /**
     * Relaciones necesarias para el comprobante (evita consultas N+1).
     */
    public const RELACIONES = ['usuario', 'detalles.producto'];

    /**
     * Carga la venta con eager loading para el PDF.
     */
    public function cargarVenta(int $saleId): Venta
    {
        return Venta::with(self::RELACIONES)->findOrFail($saleId);
    }

    /**
     * Genera el PDF del comprobante de venta.
     */
    public function generar(Venta $venta)
    {
        if (!$venta->relationLoaded('usuario') || !$venta->relationLoaded('detalles')) {
            $venta->load(self::RELACIONES);
        }

        return Pdf::loadView('pdf.comprobante-venta', [
            'venta' => $venta,
            'nombreCajero' => $this->nombreCajero($venta),
        ])->setPaper('a4', 'portrait');
    }

    /**
     * Respuesta HTTP con cabeceras PDF correctas.
     */
    public function responder(Venta $venta, bool $inline = true): SymfonyResponse
    {
        $pdf = $this->generar($venta);
        $nombreArchivo = 'comprobante-' . ($venta->numero_documento ?? $venta->id_venta) . '.pdf';

        if ($inline) {
            return $pdf->stream($nombreArchivo);
        }

        return $pdf->download($nombreArchivo);
    }

    /**
     * Verifica que el PDF se pueda renderizar tras registrar la venta.
     */
    public function verificarGeneracion(Venta $venta): void
    {
        $this->generar($venta);
    }

    private function nombreCajero(Venta $venta): string
    {
        $usuario = $venta->usuario;

        if (!$usuario) {
            return 'N/A';
        }

        return $usuario->nombre_completo
            ?? $usuario->name
            ?? $usuario->username
            ?? 'N/A';
    }
}
