<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * CierreCaja Model
 * 
 * Registro de cierres de caja diarios
 */
class CierreCaja extends Model
{
    use HasFactory;

    protected $table = 'cierre_caja';
    protected $primaryKey = 'id_cierre';
    public $timestamps = true;

    protected $fillable = [
        'fecha_cierre',
        'total_efectivo',
        'total_qr',
        'total_ventas',
        'cantidad_transacciones',
        'observaciones',
        'estado',
    ];

    protected $casts = [
        'fecha_cierre' => 'date',
        'total_efectivo' => 'decimal:2',
        'total_qr' => 'decimal:2',
        'total_ventas' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Scopes
    public function scopeRecientes($query)
    {
        return $query->orderBy('fecha_cierre', 'desc');
    }

    public function scopeCompletados($query)
    {
        return $query->where('estado', 'Completado');
    }
}
