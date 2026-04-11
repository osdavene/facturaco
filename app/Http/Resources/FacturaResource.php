<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacturaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'numero'            => $this->numero,
            'prefijo'           => $this->prefijo,
            'consecutivo'       => $this->consecutivo,
            'tipo'              => $this->tipo,
            'cliente_id'        => $this->cliente_id,
            'cliente_nombre'    => $this->cliente_nombre,
            'cliente_documento' => $this->cliente_documento,
            'cliente_email'     => $this->cliente_email,
            'fecha_emision'     => $this->fecha_emision?->toDateString(),
            'fecha_vencimiento' => $this->fecha_vencimiento?->toDateString(),
            'subtotal'          => (float) $this->subtotal,
            'descuento'         => (float) $this->descuento,
            'base_iva'          => (float) $this->base_iva,
            'iva'               => (float) $this->iva,
            'retefuente'        => (float) $this->retefuente,
            'reteiva'           => (float) $this->reteiva,
            'reteica'           => (float) $this->reteica,
            'total'             => (float) $this->total,
            'total_pagado'      => (float) $this->total_pagado,
            'saldo'             => (float) ($this->total - $this->total_pagado),
            'estado'            => $this->estado,
            'forma_pago'        => $this->forma_pago,
            'plazo_pago'        => $this->plazo_pago,
            'observaciones'     => $this->observaciones,
            'items'             => FacturaItemResource::collection($this->whenLoaded('items')),
            'created_at'        => $this->created_at?->toISOString(),
            'updated_at'        => $this->updated_at?->toISOString(),
        ];
    }
}
