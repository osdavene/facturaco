<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReciboCajaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'numero'           => $this->numero,
            'cliente_id'       => $this->cliente_id,
            'cliente_nombre'   => $this->cliente_nombre,
            'factura_id'       => $this->factura_id,
            'fecha'            => $this->fecha?->toDateString(),
            'valor'            => (float) $this->valor,
            'forma_pago'       => $this->forma_pago,
            'banco'            => $this->banco,
            'num_referencia'   => $this->num_referencia,
            'concepto'         => $this->concepto,
            'observaciones'    => $this->observaciones,
            'estado'           => $this->estado,
            'created_at'       => $this->created_at?->toISOString(),
            'updated_at'       => $this->updated_at?->toISOString(),
        ];
    }
}
