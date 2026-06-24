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
        'id_marca',
        'codigo_barras',
        'sku',
        'nombre_producto',
        'descripcion',
        'precio_compra',
        'precio_venta',
        'stock_actual',
        'stock_minimo',
        'stock_maximo',
        'volumen_ml',
        'grado_alcohol',
        'imagen_url',
        'estado',
        'propietario_id',
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

    // Relationships
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'id_marca', 'id_marca');
    }

    public function propietario()
    {
        return $this->belongsTo(User::class, 'propietario_id', 'id');
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

    public function scopePorMarca($query, $id_marca)
    {
        return $query->where('id_marca', $id_marca);
    }

    /**
     * Construye la consulta base del inventario con relaciones y filtros.
     * Soporta ?category= / ?categoria= y ?brand= / ?marca=
     * 
     * IMPORTANTE: Retorna TODOS los productos del negocio para que empleados 
     * puedan ver los productos creados por el propietario.
     */
    public static function queryInventario($request)
    {
        // Iniciar query con relaciones necesarias
        // Nota: Laravel automáticamente excluye soft-deleted items
        $query = static::with(['categoria', 'marca', 'propietario']);

        // Filtros opcionales por parámetros
        $categoryId = $request->input('category', $request->input('categoria'));
        $brandId = $request->input('brand', $request->input('marca'));

        if ($categoryId) {
            $query->where('id_categoria', $categoryId);
        }

        if ($brandId) {
            $query->where('id_marca', $brandId);
        }

        // Si se especifica estado, filtrar por él; sino, devolver TODOS
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('stock_bajo')) {
            $query->whereRaw('stock_actual <= stock_minimo');
        }

        if ($request->has('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre_producto', 'like', "%{$buscar}%")
                  ->orWhere('codigo_barras', 'like', "%{$buscar}%")
                  ->orWhere('sku', 'like', "%{$buscar}%");
            });
        }

        return $query;
    }

    public function scopePorCodigoBarras($query, $codigo)
    {
        return $query->where('codigo_barras', $codigo);
    }
}
