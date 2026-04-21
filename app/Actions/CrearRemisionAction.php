<?php

namespace App\Actions;

use App\Models\Remision;
use App\Models\RemisionItem;
use Illuminate\Support\Facades\DB;

class CrearRemisionAction
{
    public static function execute(array $data): Remision
    {
        $userId = auth()->id();

        return DB::transaction(function () use ($data, $userId) {
            $consecutivo = Remision::siguienteConsecutivo();

            $subtotal = collect($data['items'])->sum(
                fn ($i) => (float) $i['cantidad'] * (float) ($i['precio_unitario'] ?? 0)
            );

            $remision = Remision::create([
                'numero'            => $consecutivo['numero'],
                'consecutivo'       => $consecutivo['consecutivo'],
                'cliente_id'        => $data['cliente_id'],
                'cliente_nombre'    => strtoupper($data['cliente_nombre']),
                'cliente_documento' => $data['cliente_documento'],
                'cliente_email'     => $data['cliente_email'],
                'cliente_direccion' => isset($data['cliente_direccion']) ? strtoupper($data['cliente_direccion']) : null,
                'cliente_telefono'  => $data['cliente_telefono'],
                'fecha_emision'     => $data['fecha_emision'],
                'fecha_entrega'     => $data['fecha_entrega'],
                'lugar_entrega'     => isset($data['lugar_entrega']) ? strtoupper($data['lugar_entrega']) : null,
                'transportador'     => isset($data['transportador']) ? strtoupper($data['transportador']) : null,
                'guia'              => $data['guia'] ?? null,
                'subtotal'          => $subtotal,
                'total'             => $subtotal,
                'estado'            => $data['estado'] ?? 'borrador',
                'observaciones'     => isset($data['observaciones']) ? strtoupper($data['observaciones']) : null,
                'user_id'           => $userId,
            ]);

            foreach ($data['items'] as $i => $item) {
                $cant   = (float) $item['cantidad'];
                $precio = (float) ($item['precio_unitario'] ?? 0);

                RemisionItem::create([
                    'remision_id'     => $remision->id,
                    'producto_id'     => $item['producto_id'] ?? null,
                    'codigo'          => $item['codigo'] ?? null,
                    'descripcion'     => strtoupper($item['descripcion']),
                    'unidad'          => $item['unidad'] ?? 'UN',
                    'cantidad'        => $cant,
                    'precio_unitario' => $precio,
                    'total'           => $cant * $precio,
                    'orden'           => $i,
                ]);
            }

            return $remision;
        });
    }
}
