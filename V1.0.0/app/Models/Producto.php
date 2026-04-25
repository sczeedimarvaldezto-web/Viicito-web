<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Producto Model
 * 
 * Inventario de licores y bebidas
 */
class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'producto';
    protected $primaryKey = 'id_producto';
    public $timestamps = true;

    protected $fillable = [
        'id_categoria',
        'codigo_barras',
        'nombre_producto',
        'descripcion',
        'precio_compra',
        'precio_venta',
        'stock_actual',
        'estado',
        'sku',
        'stock_minimo',
        'stock_maximo',
        'volumen_ml',
        'grado_alcohol',
    ];

    protected $casts = [
        'precio_compra' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'grado_alcohol' => 'decimal:2',
        'stock_actual' => 'integer',
        'stock_minimo' => 'integer',
        'stock_maximo' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $dates = ['deleted_at'];

    // Relationships
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    public function detallesVenta()
    {
        return $this->hasMany(DetalleVenta::class, 'id_producto', 'id_producto');
    }

    public function detallesCompra()
    {
        return $this->hasMany(DetalleCompra::class, 'id_producto', 'id_producto');
    }

    // Accessors
    public function getMargenGananciaAttribute()
    {
        if ($this->precio_compra == 0) return 0;
        return round((($this->precio_venta - $this->precio_compra) / $this->precio_compra) * 100, 2);
    }

    public function getEstadoStockAttribute()
    {
        if ($this->stock_actual <= $this->stock_minimo) {
            return 'Bajo';
        } elseif ($this->stock_actual > $this->stock_maximo) {
            return 'Exceso';
        }
        return 'Normal';
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('estado', 'Activo');
    }

    public function scopeStockBajo($query)
    {
        return $query->whereRaw('stock_actual <= stock_minimo');
    }

    public function scopePorCategoria($query, $id_categoria)
    {
        return $query->where('id_categoria', $id_categoria);
    }

    public function scopePorCodigoBarras($query, $codigo)
    {
        return $query->where('codigo_barras', $codigo);
    }
}
