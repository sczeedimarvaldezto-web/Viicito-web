<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Compra Model
 * 
 * Órdenes de compra a proveedores
 */
class Compra extends Model
{
    use HasFactory;

    protected $table = 'compra';
    protected $primaryKey = 'id_compra';
    public $timestamps = true;

    protected $fillable = [
        'id_usuario',
        'id_proveedor',
        'numero_orden',
        'numero_factura_proveedor',
        'fecha_orden',
        'fecha_entrega',
        'total_compra',
        'estado',
        'observacion',
    ];

    protected $casts = [
        'total_compra' => 'decimal:2',
        'fecha_orden' => 'datetime',
        'fecha_entrega' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor', 'id_proveedor');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleCompra::class, 'id_compra', 'id_compra');
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado', 'Pendiente');
    }

    public function scopeCompletadas($query)
    {
        return $query->where('estado', 'Completada');
    }

    public function scopePorProveedor($query, $id_proveedor)
    {
        return $query->where('id_proveedor', $id_proveedor);
    }

    public function scopeRangoFechas($query, $fecha_inicio, $fecha_final)
    {
        return $query->whereBetween('fecha_orden', [$fecha_inicio, $fecha_final]);
    }
}
