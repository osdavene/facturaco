<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'codigo'           => $this->codigo,
            'codigo_barras'    => $this->codigo_barras,
            'nombre'           => $this->nombre,
            'descripcion'      => $this->descripcion,
            'tipo'             => $this->tipo,
            'precio_venta'     => $this->precio_venta,
            'precio_compra'    => $this->precio_compra,
            'iva_pct'          => $this->iva_pct,
            'stock_actual'     => $this->stock_actual,
            'stock_minimo'     => $this->stock_minimo,
            'unidad'           => $this->unidad,
            'categoria_id'     => $this->categoria_id,
            'categoria'        => $this->whenLoaded('categoria', fn () => $this->categoria?->nombre),
            'activo'           => $this->activo,
            'created_at'       => $this->created_at?->toISOString(),
            'updated_at'       => $this->updated_at?->toISOString(),
        ];
    }
}
