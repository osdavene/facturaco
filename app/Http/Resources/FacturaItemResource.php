<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacturaItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'producto_id'      => $this->producto_id,
            'codigo'           => $this->codigo,
            'descripcion'      => $this->descripcion,
            'unidad'           => $this->unidad,
            'cantidad'         => (float) $this->cantidad,
            'precio_unitario'  => (float) $this->precio_unitario,
            'descuento_pct'    => (float) $this->descuento_pct,
            'descuento'        => (float) $this->descuento,
            'subtotal'         => (float) $this->subtotal,
            'iva_pct'          => (float) $this->iva_pct,
            'iva'              => (float) $this->iva,
            'total'            => (float) $this->total,
        ];
    }
}
