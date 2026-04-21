<?php

namespace App\Actions;

use App\Models\Cotizacion;
use App\Models\Factura;
use App\Models\FacturaItem;
use Illuminate\Support\Facades\DB;

class ConvertirCotizacionAFacturaAction
{
    public static function execute(Cotizacion $cotizacion): Factura
    {
        if ($cotizacion->estado === 'convertida') {
            throw new \Exception('Esta cotización ya fue convertida.');
        }

        $userId = auth()->id();

        return DB::transaction(function () use ($cotizacion, $userId) {
            $consecutivo = Factura::siguienteConsecutivo();

            $factura = Factura::create([
                'numero'            => $consecutivo['numero'],
                'consecutivo'       => $consecutivo['consecutivo'],
                'tipo'              => 'factura',
                'cliente_id'        => $cotizacion->cliente_id,
                'cliente_nombre'    => $cotizacion->cliente_nombre,
                'cliente_documento' => $cotizacion->cliente_documento ?? '',
                'cliente_email'     => $cotizacion->cliente_email,
                'cliente_direccion' => $cotizacion->cliente_direccion,
                'fecha_emision'     => today(),
                'hora_emision'      => now('America/Bogota')->format('H:i:s'),
                'fecha_vencimiento' => now()->addDays($cotizacion->plazo_pago ?: 30),
                'subtotal'          => $cotizacion->subtotal,
                'descuento'         => $cotizacion->descuento,
                'iva'               => $cotizacion->iva,
                'retefuente'        => 0,
                'reteica'           => 0,
                'total'             => $cotizacion->total,
                'total_pagado'      => 0,
                'forma_pago'        => $cotizacion->forma_pago,
                'plazo_pago'        => $cotizacion->plazo_pago,
                'estado'            => 'emitida',
                'observaciones'     => 'GENERADA DESDE COTIZACIÓN ' . $cotizacion->numero,
                'user_id'           => $userId,
            ]);

            foreach ($cotizacion->items as $item) {
                FacturaItem::create([
                    'factura_id'      => $factura->id,
                    'producto_id'     => $item->producto_id,
                    'codigo'          => $item->codigo,
                    'descripcion'     => $item->descripcion,
                    'cantidad'        => $item->cantidad,
                    'precio_unitario' => $item->precio_unitario,
                    'descuento_pct'   => $item->descuento_pct,
                    'descuento'       => $item->descuento,
                    'subtotal'        => $item->subtotal,
                    'iva_pct'         => $item->iva_pct,
                    'iva'             => $item->iva,
                    'total'           => $item->total,
                ]);
            }

            $cotizacion->update([
                'estado'     => 'convertida',
                'factura_id' => $factura->id,
            ]);

            return $factura;
        });
    }
}
