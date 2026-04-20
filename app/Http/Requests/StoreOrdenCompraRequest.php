<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrdenCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'proveedor_id'            => 'required|exists:proveedores,id',
            'fecha_emision'           => 'required|date',
            'fecha_esperada'          => 'nullable|date|after_or_equal:fecha_emision',
            'items'                   => 'required|array|min:1',
            'items.*.descripcion'     => 'required|string',
            'items.*.cantidad'        => 'required|numeric|min:0.001',
            'items.*.precio_unitario' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'proveedor_id.required'            => 'Debes seleccionar un proveedor.',
            'proveedor_id.exists'              => 'El proveedor seleccionado no existe.',
            'fecha_emision.required'           => 'La fecha de emisión es obligatoria.',
            'fecha_esperada.after_or_equal'    => 'La fecha esperada debe ser igual o posterior a la de emisión.',
            'items.required'                   => 'Debes agregar al menos un ítem.',
            'items.min'                        => 'Debes agregar al menos un ítem.',
            'items.*.descripcion.required'     => 'Cada ítem requiere una descripción.',
            'items.*.cantidad.required'        => 'Cada ítem requiere una cantidad.',
            'items.*.cantidad.min'             => 'La cantidad debe ser mayor a cero.',
            'items.*.precio_unitario.required' => 'Cada ítem requiere un precio unitario.',
            'items.*.precio_unitario.min'      => 'El precio unitario no puede ser negativo.',
        ];
    }
}
