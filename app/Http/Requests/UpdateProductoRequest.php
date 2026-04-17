<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productoId = $this->route('inventario')?->id;

        return [
            'codigo'           => ['required', 'string', 'max:50', Rule::unique('productos', 'codigo')->ignore($productoId)],
            'codigo_barras'    => ['nullable', 'string', 'max:50', Rule::unique('productos', 'codigo_barras')->ignore($productoId)],
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
            'stock_minimo'     => 'numeric|min:0',
            'stock_maximo'     => 'numeric|min:0',
            'ubicacion'        => 'nullable|string|max:100',
            'activo'           => 'boolean',
            'es_servicio'      => 'boolean',
            'observaciones'    => 'nullable|string',
        ];
    }
}
