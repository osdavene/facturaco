<?php

namespace App\Actions;

use App\Models\Factura;
use App\Models\FacturaItem;
use App\Models\Remision;
use Illuminate\Support\Facades\DB;

class ConvertirRemisionAFacturaAction
{
    public static function execute(Remision $remision): Factura
    {
        if ($remision->estado === 'facturada') {
            throw new \Exception('Esta remisión ya fue facturada.');
        }
        if ($remision->estado === 'anulada') {
            throw new \Exception('No puedes facturar una remisión anulada.');
        }

        $userId = auth()->id();

        return DB::transaction(function () use ($remision, $userId) {
            $consecutivo = Factura::siguienteConsecutivo();

            $subtotal = $remision->items->sum(fn ($i) => $i->cantidad * $i->precio_unitario);
            $iva = $remision->items->sum(fn ($i) => $i->cantidad * $i->precio_unitario * 0.19);

            $factura = Factura::create([
                'numero'            => $consecutivo['numero'],
                'consecutivo'       => $consecutivo['consecutivo'],
                'tipo'              => 'factura',
                'cliente_id'        => $remision->cliente_id,
                'cliente_nombre'    => $remision->cliente_nombre,
                'cliente_documento' => $remision->cliente_documento ?? '',
                'cliente_email'     => $remision->cliente_email,
                'cliente_direccion' => $remision->cliente_direccion,
                'fecha_emision'     => today(),
                'hora_emision'      => now('America/Bogota')->format('H:i:s'),
                'fecha_vencimiento' => now()->addDays(30),
                'subtotal'          => $subtotal,
                'descuento'         => 0,
                'iva'               => $iva,
                'retefuente'        => 0,
                'reteica'           => 0,
                'total'             => $subtotal + $iva,
                'total_pagado'      => 0,
                'forma_pago'        => 'credito',
                'plazo_pago'        => 30,
                'estado'            => 'emitida',
                'observaciones'     => 'GENERADA DESDE REMISIÓN ' . $remision->numero,
                'user_id'           => $userId,
            ]);

            foreach ($remision->items as $item) {
                $base = $item->cantidad * $item->precio_unitario;
                $iva  = $base * 0.19;

                FacturaItem::create([
                    'factura_id'      => $factura->id,
                    'producto_id'     => $item->producto_id,
                    'codigo'          => $item->codigo,
                    'descripcion'     => $item->descripcion,
                    'cantidad'        => $item->cantidad,
                    'precio_unitario' => $item->precio_unitario,
                    'descuento_pct'   => 0,
                    'descuento'       => 0,
                    'subtotal'        => $base,
                    'iva_pct'         => 19,
                    'iva'             => $iva,
                    'total'           => $base + $iva,
                ]);
            }

            $remision->update([
                'estado'     => 'facturada',
                'factura_id' => $factura->id,
            ]);

            return $factura;
        });
    }
}
