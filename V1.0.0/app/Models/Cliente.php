<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Cliente Model
 * 
 * Clientes que realizan compras en la licorería
 */
class Cliente extends Model
{
    use HasFactory;

    protected $table = 'cliente';
    protected $primaryKey = 'id_cliente';
    public $timestamps = true;

    protected $fillable = [
        'nombre_razon_social',
        'tipo_cliente',
        'nit_ci',
        'email',
        'telefono',
        'vendedor_asignado',
        'saldo_actual',
        'limite_credito',
        'ciudad',
        'estado',
    ];

    protected $casts = [
        'saldo_actual' => 'decimal:2',
        'limite_credito' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function vendedor()
    {
        return $this->belongsTo(Usuario::class, 'vendedor_asignado', 'id_usuario');
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'id_cliente', 'id_cliente');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('estado', 'Activo');
    }

    public function scopeConDeuda($query)
    {
        return $query->where('saldo_actual', '>', 0);
    }

    public function scopeNaturales($query)
    {
        return $query->where('tipo_cliente', 'Natural');
    }

    public function scopeJuridicas($query)
    {
        return $query->where('tipo_cliente', 'Jurídica');
    }
}
