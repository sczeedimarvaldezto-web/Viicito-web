<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_categoria' => 'required|exists:categoria,id_categoria',
            'id_marca' => 'nullable|exists:marca,id_marca',
            'nombre_producto' => 'required|string|max:100|unique:producto,nombre_producto,NULL,id_producto,deleted_at,NULL',
            'codigo_barras' => 'nullable|string|max:50|unique:producto,codigo_barras,NULL,id_producto,deleted_at,NULL',
            'sku' => 'nullable|string|max:50|unique:producto,sku,NULL,id_producto,deleted_at,NULL',
            'precio_compra' => 'nullable|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0.01',
            'stock_actual' => 'required|integer|min:0',
            'stock_minimo' => 'nullable|integer|min:0',
            'stock_maximo' => 'nullable|integer|min:0',
            'grado_alcohol' => 'nullable|numeric|min:0|max:100',
            'imagen_producto' => 'nullable|image|mimes:jpg,png,webp|max:2048',
            'descripcion' => 'nullable|string',
        ];
    }
}
