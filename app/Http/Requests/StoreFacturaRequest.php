<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFacturaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente_id'              => 'required|exists:clientes,id',
            'fecha_emision'           => 'required|date',
            'fecha_vencimiento'       => 'required|date|after_or_equal:fecha_emision',
            'items'                   => 'required|array|min:1',
            'items.*.descripcion'     => 'required|string',
            'items.*.cantidad'        => 'required|numeric|min:0.001',
            'items.*.precio_unitario' => 'required|numeric|min:0',
            'estado'                  => 'nullable|in:borrador,emitida',
            'forma_pago'              => 'nullable|in:contado,credito,mixto',
            'plazo_pago'              => 'nullable|integer|min:0|max:365',
            'observaciones'           => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.required'              => 'Debes seleccionar un cliente.',
            'cliente_id.exists'                => 'El cliente seleccionado no existe.',
            'fecha_vencimiento.after_or_equal'  => 'La fecha de vencimiento debe ser igual o posterior a la fecha de emisión.',
            'items.required'                   => 'La factura debe tener al menos un ítem.',
            'items.min'                        => 'La factura debe tener al menos un ítem.',
            'items.*.descripcion.required'     => 'Cada ítem debe tener una descripción.',
            'items.*.cantidad.min'             => 'La cantidad debe ser mayor a cero.',
            'items.*.precio_unitario.min'      => 'El precio unitario no puede ser negativo.',
        ];
    }
}
