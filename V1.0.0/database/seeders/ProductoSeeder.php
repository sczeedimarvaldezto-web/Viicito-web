<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Primero crear categorías
        $categorias = [
            ['id_categoria' => 1, 'nombre_categoria' => 'Ron', 'created_at' => now(), 'updated_at' => now()],
            ['id_categoria' => 2, 'nombre_categoria' => 'Vodka', 'created_at' => now(), 'updated_at' => now()],
            ['id_categoria' => 3, 'nombre_categoria' => 'Whisky', 'created_at' => now(), 'updated_at' => now()],
            ['id_categoria' => 4, 'nombre_categoria' => 'Cerveza', 'created_at' => now(), 'updated_at' => now()],
            ['id_categoria' => 5, 'nombre_categoria' => 'Vino', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('categoria')->insertOrIgnore($categorias);

        // Luego crear productos
        $productos = [
            [
                'id_categoria' => 1,
                'codigo_barras' => 'RON001',
                'nombre_producto' => 'Ron Blanco Premium',
                'precio_compra' => 15.00,
                'precio_venta' => 25.00,
                'stock_actual' => 50,
                'stock_minimo' => 10,
                'estado' => 'Activo',
                'sku' => 'RON-BLP-001',
                'grado_alcohol' => 40,
                'imagen_url' => '/uploads/productos/1775952213_69dae15549f2b.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_categoria' => 1,
                'codigo_barras' => 'RON002',
                'nombre_producto' => 'Ron Oscuro Añejo',
                'precio_compra' => 20.00,
                'precio_venta' => 35.00,
                'stock_actual' => 30,
                'stock_minimo' => 8,
                'estado' => 'Activo',
                'sku' => 'RON-OSC-002',
                'grado_alcohol' => 37,
                'imagen_url' => '/uploads/productos/default.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_categoria' => 2,
                'codigo_barras' => 'VOD001',
                'nombre_producto' => 'Vodka Premium Ruso',
                'precio_compra' => 18.00,
                'precio_venta' => 30.00,
                'stock_actual' => 40,
                'stock_minimo' => 10,
                'estado' => 'Activo',
                'sku' => 'VOD-PRM-001',
                'grado_alcohol' => 40,
                'imagen_url' => '/uploads/productos/default.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_categoria' => 3,
                'codigo_barras' => 'WHI001',
                'nombre_producto' => 'Whisky Escocés 12 Años',
                'precio_compra' => 35.00,
                'precio_venta' => 55.00,
                'stock_actual' => 20,
                'stock_minimo' => 5,
                'estado' => 'Activo',
                'sku' => 'WHI-ESC-012',
                'grado_alcohol' => 43,
                'imagen_url' => '/uploads/productos/default.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_categoria' => 4,
                'codigo_barras' => 'CER001',
                'nombre_producto' => 'Cerveza Lager Pack 6',
                'precio_compra' => 4.50,
                'precio_venta' => 8.00,
                'stock_actual' => 100,
                'stock_minimo' => 30,
                'estado' => 'Activo',
                'sku' => 'CER-LAG-PKG6',
                'grado_alcohol' => 5,
                'imagen_url' => '/uploads/productos/default.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_categoria' => 5,
                'codigo_barras' => 'VIN001',
                'nombre_producto' => 'Vino Tinto Reserva',
                'precio_compra' => 12.00,
                'precio_venta' => 22.00,
                'stock_actual' => 60,
                'stock_minimo' => 15,
                'estado' => 'Activo',
                'sku' => 'VIN-TNT-RES',
                'grado_alcohol' => 14,
                'imagen_url' => '/uploads/productos/default.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('producto')->insertOrIgnore($productos);
    }
}
