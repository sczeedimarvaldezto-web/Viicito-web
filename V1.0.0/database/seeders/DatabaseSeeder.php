<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Categoria;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ==========================================
        // CREAR ROLES
        // ==========================================
        $ownerRole = Role::firstOrCreate([
            'name' => 'owner',
        ], [
            'label' => 'Propietario',
        ]);

        $employeeRole = Role::firstOrCreate([
            'name' => 'employee',
        ], [
            'label' => 'Empleado',
        ]);

        // ==========================================
        // CREAR USUARIOS
        // ==========================================
        User::create([
            'name' => 'Edimar',
            'email' => 'edimartorrezlobo@gmail.com',
            'password' => bcrypt('password123'),
            'role_id' => $ownerRole->id,
        ]);

        User::create([
            'name' => 'Kevin',
            'email' => 'kevin0202valdez@gmail.com',
            'password' => bcrypt('password123'),
            'role_id' => $employeeRole->id,
        ]);

        // ==========================================
        // CREAR CATEGORÍAS
        // ==========================================
        $categorias = [
            'Ron',
            'Vodka',
            'Whisky',
            'Tequila',
            'Ginebra',
            'Vino',
            'Cerveza',
            'Licores',
        ];

        foreach ($categorias as $nombreCategoria) {
            Categoria::firstOrCreate([
                'nombre_categoria' => $nombreCategoria,
            ]);
        }
    }
}
