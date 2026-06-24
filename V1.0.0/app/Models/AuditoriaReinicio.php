<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * AuditoriaReinicio Model
 * 
 * Registro de auditoría cuando se reinician ventas
 */
class AuditoriaReinicio extends Model
{
    use HasFactory;

    protected $table = 'auditoria_reinicio';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'fecha_reinicio',
        'id_usuario',
        'total_ventas_borradas',
        'razon',
    ];

    protected $casts = [
        'fecha_reinicio' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}
