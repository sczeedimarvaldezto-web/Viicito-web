<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations - Sincronizar usuarios de 'users' a 'usuario'
     */
    public function up(): void
    {
        // Copiar usuarios de la tabla 'users' a 'usuario'
        $users = DB::table('users')->get();
        
        foreach ($users as $user) {
            // Verificar si el usuario ya existe en la tabla usuario
            $exists = DB::table('usuario')
                ->where('id_usuario', $user->id)
                ->exists();
            
            if (!$exists) {
                DB::table('usuario')->insert([
                    'id_usuario' => $user->id,
                    'nombre_completo' => $user->name ?? 'Sin nombre',
                    'username' => $user->email, // Usar email como username temporal
                    'password_hash' => $user->password,
                    'rol' => 'empleado',
                    'estado' => 'Activo',
                    'created_at' => $user->created_at ?? now(),
                    'updated_at' => $user->updated_at ?? now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No eliminar usuarios, es destructivo
    }
};
