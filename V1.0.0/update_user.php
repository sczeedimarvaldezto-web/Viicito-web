<?php
// Cargar Laravel bootstrap
require __DIR__ . '/bootstrap/app.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Actualizar usuario
$user = \App\Models\Usuario::where('email', 'edimartorrezlobo@gmail.com')->first();

if ($user) {
    $user->nombre_completo = 'Edimar Torrez';
    $user->username = 'edimar';
    $user->rol = 'owner';
    $user->estado = 'Activo';
    $user->save();
    echo "Usuario actualizado: " . $user->nombre_completo . "\n";
} else {
    echo "Usuario no encontrado\n";
}
