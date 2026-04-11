<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotaCreditoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'numero'            => $this->numero,
            'factura_id'        => $this->factura_id,
            'cliente_id'        => $this->cliente_id,
            'cliente_nombre'    => $this->cliente_nombre,
            'fecha'             => $this->fecha?->toDateString(),
            'subtotal'          => (float) $this->subtotal,
            'iva'               => (float) $this->iva,
            'total'             => (float) $this->total,
            'motivo'            => $this->motivo,
            'estado'            => $this->estado,
            'items'             => $this->whenLoaded('items', fn () =>
                $this->items->map(fn ($item) => [
                    'id'              => $item->id,
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
