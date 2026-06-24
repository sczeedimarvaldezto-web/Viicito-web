<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productoId = $this->route('producto')->id_producto;

        return [
            'id_categoria' => 'required|exists:categoria,id_categoria',
            'id_marca' => 'nullable|exists:marca,id_marca',
            'nombre_producto' => 'string|max:100|unique:producto,nombre_producto,' . $productoId . ',id_producto,deleted_at,NULL',
            'codigo_barras' => 'nullable|string|max:50|unique:producto,codigo_barras,' . $productoId . ',id_producto,deleted_at,NULL',
            'sku' => 'nullable|string|max:50|unique:producto,sku,' . $productoId . ',id_producto,deleted_at,NULL',
            'precio_compra' => 'nullable|numeric|min:0',
            'precio_venta' => 'nullable|numeric|min:0.01',
            'stock_actual' => 'nullable|integer|min:0',
            'stock_minimo' => 'nullable|integer|min:0',
            'stock_maximo' => 'nullable|integer|min:0',
            'grado_alcohol' => 'nullable|numeric|min:0|max:100',
            'imagen_producto' => 'nullable|image|mimes:jpg,png,webp|max:2048',
            'estado' => 'in:Activo,Descontinuado,Suspendido',
            'descripcion' => 'nullable|string',
        ];
    }
}
