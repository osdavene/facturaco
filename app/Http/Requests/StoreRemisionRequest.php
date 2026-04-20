<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRemisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente_nombre'      => 'required|string|max:255',
            'fecha_emision'       => 'required|date',
            'fecha_entrega'       => 'nullable|date|after_or_equal:fecha_emision',
            'items'               => 'required|array|min:1',
            'items.*.descripcion' => 'required|string',
            'items.*.cantidad'    => 'required|numeric|min:0.001',
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_nombre.required'      => 'El nombre del cliente es obligatorio.',
            'fecha_emision.required'       => 'La fecha de emisión es obligatoria.',
            'fecha_entrega.after_or_equal' => 'La fecha de entrega debe ser igual o posterior a la de emisión.',
            'items.required'               => 'Debes agregar al menos un ítem.',
            'items.min'                    => 'Debes agregar al menos un ítem.',
            'items.*.descripcion.required' => 'Cada ítem requiere una descripción.',
            'items.*.cantidad.required'    => 'Cada ítem requiere una cantidad.',
            'items.*.cantidad.min'         => 'La cantidad debe ser mayor a cero.',
        ];
    }
}
