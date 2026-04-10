<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Proveedor Model
 * 
 * Proveedores de licores y bebidas
 */
class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedor';
    protected $primaryKey = 'id_proveedor';
    public $timestamps = true;

    protected $fillable = [
        'nombre_empresa',
        'contacto_nombre',
        'email',
        'telefono',
        'ciudad',
        'estado_proveedor',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function compras()
    {
        return $this->hasMany(Compra::class, 'id_proveedor', 'id_proveedor');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('estado_proveedor', 'Activo');
    }
}
