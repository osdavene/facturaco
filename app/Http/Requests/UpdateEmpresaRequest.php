<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmpresaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'razon_social'           => 'required|string|max:255',
            'nombre_comercial'       => 'nullable|string|max:255',
            'nit'                    => 'required|string|max:20',
            'digito_verificacion'    => 'nullable|string|max:1',
            'tipo_persona'           => 'required|in:natural,juridica',
            'regimen'                => 'required|in:simple,responsable_iva',
            'email'                  => 'nullable|email',
            'telefono'               => 'nullable|string|max:20',
            'celular'                => 'nullable|string|max:20',
            'sitio_web'              => 'nullable|string|max:255',
            'departamento'           => 'nullable|string|max:100',
            'municipio'              => 'nullable|string|max:100',
            'direccion'              => 'nullable|string|max:255',
            'prefijo_factura'        => 'required|string|max:10',
            'resolucion_numero'      => 'nullable|integer',
            'resolucion_fecha'       => 'nullable|date',
            'resolucion_vencimiento' => 'nullable|date',
            'consecutivo_desde'      => 'nullable|integer|min:1',
            'consecutivo_hasta'      => 'nullable|integer|min:1',
            'clave_tecnica'          => 'nullable|string',
            'factura_electronica'    => 'boolean',
            'pie_factura'            => 'nullable|string',
            'terminos_condiciones'   => 'nullable|string',
            'iva_defecto'            => 'numeric|min:0|max:100',
            'retefuente_defecto'     => 'numeric|min:0|max:100',
            'reteica_defecto'        => 'numeric|min:0|max:100',
            'logo'                   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'mail_mailer'            => 'nullable|string|max:20',
            'mail_host'              => 'nullable|string|max:255',
            'mail_port'              => 'nullable|integer',
            'mail_username'          => 'nullable|string|max:255',
            'mail_password'          => 'nullable|string|max:255',
            'mail_encryption'        => 'nullable|string|max:10',
            'mail_from_address'      => 'nullable|email',
            'mail_from_name'         => 'nullable|string|max:255',
            'wompi_public_key'       => 'nullable|string|max:255',
            'wompi_currency'         => 'nullable|string|max:10',
            'wompi_events_key'       => 'nullable|string|max:255',
            'timezone'               => 'nullable|string|timezone',
        ];
    }

    public function messages(): array
    {
        return [
            'logo.image'              => 'El archivo del logo debe ser una imagen.',
            'logo.mimes'              => 'El logo debe estar en formato JPG, PNG o WEBP.',
            'logo.max'                => 'El logo no puede superar los 2 MB.',
            'mail_from_address.email' => 'El correo remitente no es válido.',
        ];
    }
}
