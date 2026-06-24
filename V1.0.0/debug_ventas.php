<?php
$app = require_once 'bootstrap/app.php';
$db = $app->make('db');

echo "================================\n";
echo "DEBUG: Verificando tabla VENTA\n";
echo "================================\n\n";

$count = $db->table('venta')->count();
echo "📊 Total de registros: " . $count . "\n\n";

$ventas = $db->table('venta')->limit(10)->get();
foreach ($ventas as $venta) {
    echo "ID: {$venta->id_venta} | Doc: {$venta->numero_documento} | Fecha: {$venta->fecha_hora} | Total: {$venta->total_venta} | Metodo: {$venta->metodo_pago}\n";
}

echo "\n📅 Ventas por fecha:\n";
$porFecha = $db->table('venta')
    ->selectRaw('DATE(fecha_hora) as fecha, COUNT(*) as cantidad, SUM(total_venta) as total')
    ->groupBy('fecha')
    ->get();

foreach ($porFecha as $f) {
    echo "Fecha: {$f->fecha} | Cantidad: {$f->cantidad} | Total: {$f->total}\n";
}

echo "\n✓ Script completado\n";
