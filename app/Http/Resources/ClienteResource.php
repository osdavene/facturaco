<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClienteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'tipo_persona'        => $this->tipo_persona,
            'tipo_documento'      => $this->tipo_documento,
            'numero_documento'    => $this->numero_documento,
            'digito_verificacion' => $this->digito_verificacion,
            'razon_social'        => $this->razon_social,
            'nombres'             => $this->nombres,
            'apellidos'           => $this->apellidos,
            'nombre_completo'     => $this->razon_social ?? trim("{$this->nombres} {$this->apellidos}"),
            'regimen'             => $this->regimen,
            'responsable_iva'     => $this->responsable_iva,
            'retefuente_pct'      => $this->retefuente_pct,
            'reteiva_pct'         => $this->reteiva_pct,
            'reteica_pct'         => $this->reteica_pct,
            'email'               => $this->email,
            'telefono'            => $this->telefono,
            'celular'             => $this->celular,
            'departamento'        => $this->departamento,
            'municipio'           => $this->municipio,
            'direccion'           => $this->direccion,
            'plazo_pago'          => $this->plazo_pago,
            'cupo_credito'        => $this->cupo_credito,
            'activo'              => $this->activo,
            'observaciones'       => $this->observaciones,
            'created_at'          => $this->created_at?->toISOString(),
            'updated_at'          => $this->updated_at?->toISOString(),
        ];
    }
}
