<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo_persona'        => 'required|in:natural,juridica',
            'tipo_documento'      => 'required|in:CC,NIT,CE,PP,TI,PEP',
            'numero_documento'    => 'required|string|max:20|unique:clientes',
            'digito_verificacion' => 'nullable|string|max:1',
            'razon_social'        => 'nullable|string|max:255',
            'nombres'             => 'nullable|string|max:100',
            'apellidos'           => 'nullable|string|max:100',
            'regimen'             => 'required|in:simple,responsable_iva',
            'responsable_iva'     => 'boolean',
            'gran_contribuyente'  => 'boolean',
            'autoretenedor'       => 'boolean',
            'actividad_economica' => 'nullable|string|max:10',
            'retefuente_pct'      => 'numeric|min:0|max:100',
            'reteiva_pct'         => 'numeric|min:0|max:100',
            'reteica_pct'         => 'numeric|min:0|max:100',
            'email'               => 'nullable|email|max:255',
            'telefono'            => 'nullable|string|max:20',
            'celular'             => 'nullable|string|max:20',
            'departamento'        => 'nullable|string|max:100',
            'municipio'           => 'nullable|string|max:100',
            'direccion'           => 'nullable|string|max:255',
            'plazo_pago'          => 'integer|min:0|max:365',
            'cupo_credito'        => 'numeric|min:0',
            'observaciones'       => 'nullable|string',
        ];
    }
}
