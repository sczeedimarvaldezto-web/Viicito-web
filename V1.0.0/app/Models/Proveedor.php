<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Proveedor Model
 * 
 * Proveedores de licores y bebidas
 * 
 * IMPLEMENTA:
 * - Soft Deletes: Eliminación lógica para preservar integridad referencial
 * - Foreign Key Constraints: Previene eliminar proveedor si tiene compras asociadas
 */
class Proveedor extends Model
{
    use HasFactory, SoftDeletes;

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
        'deleted_at' => 'datetime',
    ];

    protected $dates = ['deleted_at'];

    // Relationships
    /**
     * Un proveedor puede tener muchas compras
     * Relación One-to-Many
     */
    public function compras()
    {
        return $this->hasMany(Compra::class, 'id_proveedor', 'id_proveedor');
    }

    // Scopes
    /**
     * Obtener solo proveedores activos (no eliminados lógicamente)
     */
    public function scopeActivos($query)
    {
        return $query->where('estado_proveedor', 'Activo');
    }

    /**
     * Obtener proveedores incluyendo eliminados lógicamente
     */
    public function scopeConEliminados($query)
    {
        return $query->withTrashed();
    }

    /**
     * Verificar si el proveedor puede ser eliminado
     * Retorna false si tiene compras asociadas
     */
    public function puedeSerEliminado(): bool
    {
        return !$this->compras()->exists();
    }

    /**
     * Obtener mensaje de error si no puede ser eliminado
     */
    public function obtenerRazonNoEliminacion(): ?string
    {
        if ($this->compras()->exists()) {
            $cantidadCompras = $this->compras()->count();
            return "No se puede eliminar este proveedor porque tiene {$cantidadCompras} compra(s) asociada(s).";
        }
        return null;
    }
}
