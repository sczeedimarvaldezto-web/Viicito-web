<?php

namespace Database\Seeders;

use App\Models\User;
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
        // Crear roles
        $ownerRole = \App\Models\Role::firstOrCreate([
            'name' => 'owner',
        ], [
            'label' => 'Propietario',
        ]);

        $employeeRole = \App\Models\Role::firstOrCreate([
            'name' => 'employee',
        ], [
            'label' => 'Empleado',
        ]);

        // Crear usuario de prueba
        \App\Models\User::firstOrCreate([
            'email' => 'byplamm@gmail.com',
        ], [
            'name' => 'Owner Viicito',
            'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
            'role_id' => $ownerRole->id,
        ]);

        $this->call(CategoriaSeeder::class);
    }
}
