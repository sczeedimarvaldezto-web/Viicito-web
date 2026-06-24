<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Marca Model
 *
 * Marcas de productos (Bacardí, Johnnie Walker, etc.)
 */
class Marca extends Model
{
    use HasFactory;

    protected $table = 'marca';
    protected $primaryKey = 'id_marca';
    public $timestamps = true;

    protected $fillable = [
        'nombre_marca',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_marca', 'id_marca');
    }
}
