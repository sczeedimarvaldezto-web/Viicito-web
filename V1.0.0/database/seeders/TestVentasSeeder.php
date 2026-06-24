<?php

namespace Database\Seeders;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TestVentasSeeder extends Seeder
{
    public function run()
    {
        // Obtener usuario de prueba o el primero disponible
        $user = User::first() ?? User::factory()->create(['role_id' => 2]); // role_id 2 = employee
        
        // Crear 5 ventas para hoy (2026-06-15)
        $hoy = Carbon::now()->startOfDay();
        
        for ($i = 0; $i < 5; $i++) {
            $total = rand(100, 500);
            $impuesto = $total * 0.13;
            $subtotal = $total - $impuesto;
            
            $venta = Venta::create([
                'id_usuario' => $user->id,
                'numero_documento' => 'DOC-' . date('YmdHis') . '-' . $i,
                'fecha_hora' => $hoy->copy()->addHours($i),
                'total_venta' => $total,
                'subtotal' => $subtotal,
                'impuesto' => $impuesto,
                'metodo_pago' => $i % 2 == 0 ? 'Efectivo' : 'QR',
                'estado' => 'Completada',
                'observacion' => 'Venta de prueba #' . ($i + 1),
            ]);
            
            // Crear detalles de venta
            $productos = Producto::limit(3)->get();
            foreach ($productos as $producto) {
                $cantidad = rand(1, 5);
                $precio_unitario = $producto->precio_venta;
                $subtotal = $cantidad * $precio_unitario;
                
                DetalleVenta::create([
                    'id_venta' => $venta->id_venta,
                    'id_producto' => $producto->id_producto,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio_unitario,
                    'subtotal' => $subtotal,
                    'descuento' => 0,
                ]);
            }
        }
        
        // Crear 3 ventas para ayer (2026-06-14)
        $ayer = $hoy->copy()->subDay();
        for ($i = 0; $i < 3; $i++) {
            $total = rand(150, 400);
            $impuesto = $total * 0.13;
            $subtotal = $total - $impuesto;
            
            Venta::create([
                'id_usuario' => $user->id,
                'numero_documento' => 'DOC-' . date('YmdHis', $ayer->timestamp) . '-' . $i,
                'fecha_hora' => $ayer->copy()->addHours($i + 10),
                'total_venta' => $total,
                'subtotal' => $subtotal,
                'impuesto' => $impuesto,
                'metodo_pago' => rand(0, 1) == 0 ? 'Efectivo' : 'QR',
                'estado' => 'Completada',
            ]);
        }
        
        // Crear 2 ventas para mañana (2026-06-16)
        $manana = $hoy->copy()->addDay();
        for ($i = 0; $i < 2; $i++) {
            $total = rand(120, 350);
            $impuesto = $total * 0.13;
            $subtotal = $total - $impuesto;
            
            Venta::create([
                'id_usuario' => $user->id,
                'numero_documento' => 'DOC-' . date('YmdHis', $manana->timestamp) . '-' . $i,
                'fecha_hora' => $manana->copy()->addHours($i + 12),
                'total_venta' => $total,
                'subtotal' => $subtotal,
                'impuesto' => $impuesto,
                'metodo_pago' => $i % 2 == 0 ? 'Efectivo' : 'QR',
                'estado' => 'Completada',
            ]);
        }
        
        $this->command->info('✓ Ventas de prueba creadas correctamente para el rango 2026-06-14 a 2026-06-16');
    }
}
