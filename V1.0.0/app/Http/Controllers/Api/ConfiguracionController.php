<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ConfiguracionController
{
    /**
     * Obtener configuración general
     */
    public function obtener()
    {
        // Obtener de cache o base de datos
        $configuracion = Cache::get('configuracion_general', [
            'nombre_tienda' => 'Viicito',
            'telefono' => '',
            'email' => '',
            'direccion' => '',
            'moneda' => 'Bs',
        ]);

        return response()->json([
            'success' => true,
            'data' => $configuracion
        ]);
    }

    /**
     * Guardar configuración general
     */
    public function guardar(Request $request)
    {
        $validated = $request->validate([
            'nombre_tienda' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string',
            'moneda' => 'nullable|in:Bs,USD,EUR|default:Bs',
        ]);

        // Guardar en cache (para SPA rápido)
        // En producción, usar base de datos de configuración
        Cache::put('configuracion_general', $validated, now()->addDays(365));

        return response()->json([
            'success' => true,
            'message' => 'Configuración guardada correctamente',
            'data' => $validated
        ]);
    }
}
