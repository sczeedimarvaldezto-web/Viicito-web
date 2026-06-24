<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante {{ $venta->numero_documento }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1a1a1a;
            line-height: 1.5;
            padding: 30px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #ffbf00;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 22px;
            color: #1a1a1a;
            letter-spacing: 2px;
            margin-bottom: 4px;
        }
        .header p {
            color: #555;
            font-size: 11px;
        }
        .info-grid {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-grid td {
            padding: 4px 0;
            vertical-align: top;
        }
        .info-grid .label {
            font-weight: bold;
            color: #333;
            width: 120px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table thead th {
            background-color: #1a1a1a;
            color: #ffbf00;
            padding: 8px 6px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }
        .items-table tbody td {
            padding: 8px 6px;
            border-bottom: 1px solid #ddd;
        }
        .items-table .text-right { text-align: right; }
        .items-table .text-center { text-align: center; }
        .totals {
            width: 280px;
            margin-left: auto;
            margin-bottom: 24px;
        }
        .totals table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals td {
            padding: 5px 0;
        }
        .totals .total-row {
            border-top: 2px solid #1a1a1a;
            font-size: 14px;
            font-weight: bold;
            padding-top: 8px;
        }
        .totals .total-row td:last-child {
            color: #1a1a1a;
        }
        .footer {
            text-align: center;
            border-top: 1px dashed #aaa;
            padding-top: 16px;
            color: #666;
            font-size: 10px;
        }
        .badge-pago {
            display: inline-block;
            background: #f0f0f0;
            border: 1px solid #ccc;
            padding: 3px 10px;
            border-radius: 4px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>VIICITO</h1>
        <p>Sistema de Gestión de Licorería</p>
        <p><strong>COMPROBANTE DE VENTA</strong></p>
    </div>

    <table class="info-grid">
        <tr>
            <td class="label">N° Transacción:</td>
            <td>{{ $venta->numero_documento }} (ID: {{ $venta->id_venta }})</td>
            <td class="label">Fecha/Hora:</td>
            <td>{{ $venta->fecha_hora?->format('d/m/Y H:i:s') }}</td>
        </tr>
        <tr>
            <td class="label">Cajero/Vendedor:</td>
            <td>{{ $nombreCajero }}</td>
            <td class="label">Método de Pago:</td>
            <td><span class="badge-pago">{{ $venta->metodo_pago }}</span></td>
        </tr>
        <tr>
            <td class="label">Estado:</td>
            <td>{{ $venta->estado }}</td>
            @if($venta->observacion)
            <td class="label">Observación:</td>
            <td>{{ $venta->observacion }}</td>
            @endif
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 40%">Producto</th>
                <th class="text-center" style="width: 10%">Cant.</th>
                <th class="text-right" style="width: 18%">P. Unitario</th>
                <th class="text-right" style="width: 14%">Descuento</th>
                <th class="text-right" style="width: 18%">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->detalles as $detalle)
            <tr>
                <td>{{ $detalle->producto?->nombre_producto ?? 'Producto #' . $detalle->id_producto }}</td>
                <td class="text-center">{{ $detalle->cantidad }}</td>
                <td class="text-right">Bs. {{ number_format($detalle->precio_unitario, 2) }}</td>
                <td class="text-right">
                    @if($detalle->descuento > 0)
                        - Bs. {{ number_format($detalle->descuento, 2) }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-right">Bs. {{ number_format($detalle->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">Bs. {{ number_format($venta->subtotal, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td>TOTAL A PAGAR:</td>
                <td class="text-right">Bs. {{ number_format($venta->total_venta, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Gracias por su compra</p>
        <p>Documento generado el {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
