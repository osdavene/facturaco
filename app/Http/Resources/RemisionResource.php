<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RemisionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'numero'            => $this->numero,
            'cliente_id'        => $this->cliente_id,
            'cliente_nombre'    => $this->cliente_nombre,
            'fecha_emision'     => $this->fecha_emision?->toDateString(),
            'subtotal'          => (float) $this->subtotal,
            'total'             => (float) $this->total,
            'estado'            => $this->estado,
            'observaciones'     => $this->observaciones,
            'items'             => $this->whenLoaded('items', fn () =>
                $this->items->map(fn ($item) => [
                    'id'              => $item->id,
                    'codigo'          => $item->codigo,
                    'descripcion'     => $item->descripcion,
                    'unidad'          => $item->unidad,
                    'cantidad'        => (float) $item->cantidad,
                    'precio_unitario' => (float) $item->precio_unitario,
                    'total'           => (float) $item->total,
                ])
            ),
            'created_at'        => $this->created_at?->toISOString(),
            'updated_at'        => $this->updated_at?->toISOString(),
        ];
    }
}
