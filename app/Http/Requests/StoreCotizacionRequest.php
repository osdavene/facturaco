<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCotizacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('items') && is_array($this->items)) {
            $items = $this->items;
            foreach ($items as $k => $item) {
                if (isset($item['precio_unitario']) && is_string($item['precio_unitario'])) {
                    $items[$k]['precio_unitario'] = (float) str_replace(['.', '$', ' '], '', str_replace(',', '.', $item['precio_unitario']));
                }
                if (isset($item['cantidad']) && is_string($item['cantidad'])) {
                    $items[$k]['cantidad'] = (float) str_replace(['.', '$', ' '], '', str_replace(',', '.', $item['cantidad']));
                }
            }
            $this->merge(['items' => $items]);
        }
    }

    public function rules(): array
    {
        return [
            'cliente_id'              => 'nullable|integer|exists:clientes,id',
            'cliente_nombre'          => 'required|string|max:255',
            'cliente_documento'       => 'nullable|string|max:50',
            'cliente_email'           => 'nullable|email|max:150',
            'cliente_telefono'        => 'nullable|string|max:50',
            'cliente_direccion'       => 'nullable|string|max:255',
            'fecha_emision'           => 'required|date',
            'fecha_vencimiento'       => 'required|date|after_or_equal:fecha_emision',
            'forma_pago'              => 'nullable|string|max:50',
            'plazo_pago'              => 'nullable|integer|min:0',
            'observaciones'           => 'nullable|string|max:2000',
            'terminos'                => 'nullable|string|max:2000',
            'estado'                  => 'nullable|string|in:borrador,enviada,aceptada,rechazada',
            'items'                   => 'required|array|min:1',
            'items.*.producto_id'     => 'nullable|integer',
            'items.*.codigo'          => 'nullable|string|max:50',
            'items.*.descripcion'     => 'required|string|max:255',
            'items.*.unidad'          => 'nullable|string|max:20',
            'items.*.cantidad'        => 'required|numeric|min:0.001',
            'items.*.precio_unitario' => 'required|numeric|min:0',
            'items.*.descuento_pct'   => 'nullable|numeric|min:0|max:100',
            'items.*.iva_pct'         => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_nombre.required'          => 'El nombre del cliente es obligatorio.',
            'fecha_emision.required'           => 'La fecha de emisión es obligatoria.',
            'fecha_vencimiento.required'       => 'La fecha de vencimiento es obligatoria.',
            'fecha_vencimiento.after_or_equal' => 'La fecha de vencimiento debe ser igual o posterior a la de emisión.',
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
