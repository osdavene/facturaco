<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReciboCajaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente_id'     => 'required|exists:clientes,id',
            'fecha'          => 'required|date',
            'valor'          => 'required|numeric|min:1',
            'forma_pago'     => 'required|in:efectivo,transferencia,cheque,tarjeta,consignacion',
            'concepto'       => 'required|string|max:255',
            'factura_id'     => 'nullable|exists:facturas,id',
            'banco'          => 'nullable|string|max:100',
            'num_referencia' => 'nullable|string|max:50',
            'observaciones'  => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.required'  => 'Debes seleccionar un cliente.',
            'cliente_id.exists'    => 'El cliente seleccionado no existe.',
            'fecha.required'       => 'La fecha es obligatoria.',
            'valor.required'       => 'El valor es obligatorio.',
            'valor.min'            => 'El valor debe ser mayor a cero.',
            'forma_pago.required'  => 'La forma de pago es obligatoria.',
            'forma_pago.in'        => 'La forma de pago no es válida.',
            'concepto.required'    => 'El concepto es obligatorio.',
            'concepto.max'         => 'El concepto no puede superar 255 caracteres.',
            'factura_id.exists'    => 'La factura seleccionada no existe.',
            'banco.max'            => 'El banco no puede superar 100 caracteres.',
            'num_referencia.max'   => 'La referencia no puede superar 50 caracteres.',
        ];
    }
}
