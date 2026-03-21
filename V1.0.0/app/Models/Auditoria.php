<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Auditoria Model
 * 
 * Log de cambios importantes en el sistema
 */
class Auditoria extends Model
{
    use HasFactory;

    protected $table = 'auditoria';
    protected $primaryKey = 'id_auditoria';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'tabla',
        'tipo_operacion',
        'id_registro',
        'datos_anteriores',
        'datos_nuevos',
        'fecha_hora',
    ];

    protected $casts = [
        'datos_anteriores' => 'json',
        'datos_nuevos' => 'json',
        'fecha_hora' => 'datetime',
    ];

    // Relationships
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    // Scopes
    public function scopePorUsuario($query, $id_usuario)
    {
        return $query->where('id_usuario', $id_usuario);
    }

    public function scopePorTabla($query, $tabla)
    {
        return $query->where('tabla', $tabla);
    }

    public function scopePorOperacion($query, $tipo)
    {
        return $query->where('tipo_operacion', $tipo);
    }

    public function scopeRangoFechas($query, $fecha_inicio, $fecha_final)
    {
        return $query->whereBetween('fecha_hora', [$fecha_inicio, $fecha_final]);
    }
}
