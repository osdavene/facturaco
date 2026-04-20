<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNotaCreditoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'factura_id'              => 'required|exists:facturas,id',
            'motivo'                  => 'required|string',
            'tipo'                    => 'required|in:total,parcial',
            'fecha'                   => 'required|date',
            'observaciones'           => 'nullable|string|max:500',
            'items'                   => 'required|array|min:1',
            'items.*.descripcion'     => 'required|string',
            'items.*.cantidad'        => 'required|numeric|min:0.001',
            'items.*.precio_unitario' => 'required|numeric|min:0',
            'items.*.devolver_stock'  => 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'factura_id.required'              => 'Debes seleccionar una factura.',
            'factura_id.exists'                => 'La factura seleccionada no existe.',
            'motivo.required'                  => 'El motivo de la nota de crédito es obligatorio.',
            'tipo.required'                    => 'Debes indicar si es total o parcial.',
            'tipo.in'                          => 'El tipo debe ser total o parcial.',
            'fecha.required'                   => 'La fecha es obligatoria.',
            'observaciones.max'                => 'Las observaciones no pueden superar 500 caracteres.',
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
