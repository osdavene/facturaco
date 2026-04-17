<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo_documento'      => 'required|in:NIT,CC,CE',
            'numero_documento'    => 'required|string|max:20|unique:proveedores',
            'digito_verificacion' => 'nullable|string|max:1',
            'razon_social'        => 'required|string|max:255',
            'nombre_contacto'     => 'nullable|string|max:150',
            'cargo_contacto'      => 'nullable|string|max:100',
            'email'               => 'nullable|email|max:255',
            'telefono'            => 'nullable|string|max:20',
            'celular'             => 'nullable|string|max:20',
            'departamento'        => 'nullable|string|max:100',
            'municipio'           => 'nullable|string|max:100',
            'direccion'           => 'nullable|string|max:255',
            'regimen'             => 'required|in:simple,responsable_iva',
            'gran_contribuyente'  => 'boolean',
            'autoretenedor'       => 'boolean',
            'retefuente_pct'      => 'numeric|min:0|max:100',
            'reteiva_pct'         => 'numeric|min:0|max:100',
            'reteica_pct'         => 'numeric|min:0|max:100',
            'plazo_pago'          => 'integer|min:0|max:365',
            'cuenta_bancaria'     => 'nullable|string|max:30',
            'banco'               => 'nullable|string|max:100',
            'tipo_cuenta'         => 'nullable|in:ahorros,corriente',
            'observaciones'       => 'nullable|string',
        ];
    }
}
