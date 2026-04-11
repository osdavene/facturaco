<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmpresaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'razon_social'        => $this->razon_social,
            'nombre_comercial'    => $this->nombre_comercial,
            'nit'                 => $this->nit,
            'digito_verificacion' => $this->digito_verificacion,
            'nit_formateado'      => $this->nit_formateado,
            'tipo_persona'        => $this->tipo_persona,
            'regimen'             => $this->regimen,
            'email'               => $this->email,
            'telefono'            => $this->telefono,
            'celular'             => $this->celular,
            'pais'                => $this->pais,
            'departamento'        => $this->departamento,
            'municipio'           => $this->municipio,
            'direccion'           => $this->direccion,
            'prefijo_factura'     => $this->prefijo_factura,
            'moneda'              => $this->moneda,
            'factura_electronica' => $this->factura_electronica,
        ];
    }
}
