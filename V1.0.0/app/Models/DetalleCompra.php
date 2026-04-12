<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * DetalleCompra Model
 * 
 * Items detallados de cada compra
 */
class DetalleCompra extends Model
{
    use HasFactory;

    protected $table = 'detalle_compra';
    protected $primaryKey = 'id_detalle_compra';
    public $timestamps = true;

    protected $fillable = [
        'id_compra',
        'id_producto',
        'cantidad_ordenada',
        'cantidad_recibida',
        'costo_unitario',
        'subtotal',
    ];

    protected $casts = [
        'cantidad_ordenada' => 'integer',
        'cantidad_recibida' => 'integer',
        'costo_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function compra()
    {
        return $this->belongsTo(Compra::class, 'id_compra', 'id_compra');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    // Scopes
    public function scopePorCompra($query, $id_compra)
    {
        return $query->where('id_compra', $id_compra);
    }

    public function scopePendientesRecibir($query)
    {
        return $query->whereRaw('cantidad_recibida < cantidad_ordenada');
    }
}
