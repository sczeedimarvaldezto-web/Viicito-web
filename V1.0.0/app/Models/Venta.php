<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Venta Model
 * 
 * Transacciones de venta
 */
class Venta extends Model
{
    use HasFactory;

    protected $table = 'venta';
    protected $primaryKey = 'id_venta';
    public $timestamps = true;

    protected $fillable = [
        'id_usuario',
        'numero_documento',
        'fecha_hora',
        'total_venta',
        'subtotal',
        'impuesto',
        'metodo_pago',
        'estado',
        'observacion',
    ];

    protected $casts = [
        'total_venta' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'impuesto' => 'decimal:2',
        'fecha_hora' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'id_venta', 'id_venta');
    }

    // Scopes
    public function scopeCompletadas($query)
    {
        return $query->where('estado', 'Completada');
    }

    public function scopePorFecha($query, $fecha)
    {
        return $query->whereDate('fecha_hora', $fecha);
    }

    public function scopePorVendedor($query, $id_usuario)
    {
        return $query->where('id_usuario', $id_usuario);
    }

    public function scopePorMetodoPago($query, $metodo)
    {
        return $query->where('metodo_pago', $metodo);
    }

    public function scopeRangoFechas($query, $fecha_inicio, $fecha_final)
    {
        $inicio = Carbon::parse($fecha_inicio)->startOfDay();
        $fin = Carbon::parse($fecha_final)->endOfDay();

        return $query->whereBetween('fecha_hora', [$inicio, $fin]);
    }
}
