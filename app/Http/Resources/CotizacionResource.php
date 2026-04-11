<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CotizacionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'numero'            => $this->numero,
            'cliente_id'        => $this->cliente_id,
            'cliente_nombre'    => $this->cliente_nombre,
            'fecha_emision'     => $this->fecha_emision?->toDateString(),
            'fecha_vencimiento' => $this->fecha_vencimiento?->toDateString(),
            'subtotal'          => (float) $this->subtotal,
            'descuento'         => (float) $this->descuento,
            'iva'               => (float) $this->iva,
            'total'             => (float) $this->total,
            'estado'            => $this->estado,
            'observaciones'     => $this->observaciones,
            'items'             => $this->whenLoaded('items', fn () =>
                $this->items->map(fn ($item) => [
                    'id'              => $item->id,
                    'codigo'          => $item->codigo,
                    'descripcion'     => $item->descripcion,
                    'cantidad'        => (float) $item->cantidad,
                    'precio_unitario' => (float) $item->precio_unitario,
                    'iva_pct'         => (float) $item->iva_pct,
                    'total'           => (float) $item->total,
                ])
            ),
            'created_at'        => $this->created_at?->toISOString(),
            'updated_at'        => $this->updated_at?->toISOString(),
        ];
    }
}
