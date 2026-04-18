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
            'codigo'           => 'required|string|max:50|unique:productos',
            'codigo_barras'    => 'nullable|string|max:50|unique:productos',
            'nombre'           => 'required|string|max:255',
            'descripcion'      => 'nullable|string',
            'categoria_id'     => 'nullable|exists:categorias,id',
            'unidad_medida_id' => 'nullable|exists:unidades_medida,id',
            'precio_compra'    => 'numeric|min:0',
            'precio_venta'     => 'required|numeric|min:0',
            'precio_venta2'    => 'numeric|min:0',
            'precio_venta3'    => 'numeric|min:0',
            'iva_pct'          => 'numeric|min:0|max:100',
            'incluye_iva'      => 'boolean',
            'stock_actual'     => 'numeric|min:0',
            'stock_minimo'     => 'numeric|min:0',
            'stock_maximo'     => 'numeric|min:0',
            'ubicacion'        => 'nullable|string|max:100',
            'es_servicio'      => 'boolean',
            'observaciones'    => 'nullable|string',
            'imagen'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }
}
