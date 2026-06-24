<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdateUser extends Command
{
    protected $signature = 'user:verify';
    protected $description = 'Verify user data';

    public function handle()
    {
        $user = User::where('email', 'edimartorrezlobo@gmail.com')->first();

        if ($user) {
            $this->info('Usuario encontrado:');
            $this->info('ID: ' . $user->id);
            $this->info('Nombre: ' . $user->name);
            $this->info('Email: ' . $user->email);
            $this->info('Password hash: ' . substr($user->password, 0, 40) . '...');
            
            // Verificar si la contraseña "12345678" funciona
            $test1 = Hash::check('12345678', $user->password);
            $this->info('¿Contraseña "12345678" funciona? ' . ($test1 ? 'SÍ' : 'NO'));
            
            // Verificar si "password" funciona
            $test2 = Hash::check('password', $user->password);
            $this->info('¿Contraseña "password" funciona? ' . ($test2 ? 'SÍ' : 'NO'));
            
            // Regenerar la contraseña
            $newPassword = Hash::make('demo1234');
            $this->info('Generando nuevo hash para contraseña: demo1234');
            $user->password = $newPassword;
            $user->save();
            $this->info('Contraseña actualizada. Prueba con "demo1234"');
        } else {
            $this->error('Usuario no encontrado');
        }
    }
}
