<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            ['nombre_categoria' => 'Vodka', 'descripcion' => 'Bebidas espirituosas de vodka', 'estado' => 'Activo'],
            ['nombre_categoria' => 'Ron', 'descripcion' => 'Bebidas espirituosas de ron', 'estado' => 'Activo'],
            ['nombre_categoria' => 'Whisky', 'descripcion' => 'Bebidas espirituosas de whisky', 'estado' => 'Activo'],
            ['nombre_categoria' => 'Tequila', 'descripcion' => 'Bebidas espirituosas de tequila', 'estado' => 'Activo'],
            ['nombre_categoria' => 'Gin', 'descripcion' => 'Bebidas espirituosas de ginebra', 'estado' => 'Activo'],
            ['nombre_categoria' => 'Cerveza', 'descripcion' => 'Cervezas nacionales e importadas', 'estado' => 'Activo'],
            ['nombre_categoria' => 'Vino', 'descripcion' => 'Vinos tintos, blancos y rosados', 'estado' => 'Activo'],
            ['nombre_categoria' => 'Refresco', 'descripcion' => 'Bebidas no alcohólicas', 'estado' => 'Activo'],
            ['nombre_categoria' => 'Energizante', 'descripcion' => 'Bebidas energéticas', 'estado' => 'Activo'],
            ['nombre_categoria' => 'Otro', 'descripcion' => 'Otras categorías', 'estado' => 'Activo'],
        ];

        foreach ($categorias as $categoria) {
            \App\Models\Categoria::firstOrCreate([
                'nombre_categoria' => $categoria['nombre_categoria'],
            ], $categoria);
        }
    }
}