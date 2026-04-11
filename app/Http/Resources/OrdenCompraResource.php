<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrdenCompraResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'numero'            => $this->numero,
            'proveedor_id'      => $this->proveedor_id,
            'proveedor_nombre'  => $this->proveedor_nombre,
            'fecha_emision'     => $this->fecha_emision?->toDateString(),
            'fecha_esperada'    => $this->fecha_esperada?->toDateString(),
            'subtotal'          => (float) $this->subtotal,
            'iva'               => (float) $this->iva,
            'total'             => (float) $this->total,
            'estado'            => $this->estado,
            'observaciones'     => $this->observaciones,
            'items'             => $this->whenLoaded('items', fn () =>
                $this->items->map(fn ($item) => [
                    'id'               => $item->id,
                    'codigo'           => $item->codigo,
                    'descripcion'      => $item->descripcion,
                    'cantidad'         => (float) $item->cantidad,
                    'cantidad_recibida' => (float) $item->cantidad_recibida,
                    'precio_unitario'  => (float) $item->precio_unitario,
                    'iva_pct'          => (float) $item->iva_pct,
                    'total'            => (float) $item->total,
                ])
            ),
            'created_at'        => $this->created_at?->toISOString(),
            'updated_at'        => $this->updated_at?->toISOString(),
        ];
    }
}
