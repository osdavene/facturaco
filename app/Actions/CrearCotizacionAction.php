<?php

namespace App\Actions;

use App\Models\Cotizacion;
use App\Models\CotizacionItem;
use App\Services\DocumentoService;
use Illuminate\Support\Facades\DB;

class CrearCotizacionAction
{
    public static function execute(array $data): Cotizacion
    {
        $userId = $data['user_id'] ?? auth()->id();
    $documentos = app(\App\Services\DocumentoService::class);

        return DB::transaction(function () use ($data, $userId, $documentos) {
            $consecutivo = Cotizacion::siguienteConsecutivo();
            $calc = $documentos->calcularItems($data['items']);

            $cotizacion = Cotizacion::create([
                'numero'            => $consecutivo['numero'],
                'consecutivo'       => $consecutivo['consecutivo'],
                'cliente_id'        => $data['cliente_id'],
                'cliente_nombre'    => strtoupper($data['cliente_nombre']),
                'cliente_documento' => $data['cliente_documento'],
                'cliente_email'     => $data['cliente_email'],
                'cliente_telefono'  => $data['cliente_telefono'],
                'cliente_direccion' => $data['cliente_direccion'],
                'fecha_emision'     => $data['fecha_emision'],
                'fecha_vencimiento' => $data['fecha_vencimiento'],
                'subtotal'          => $calc['subtotal'],
                'descuento'         => $calc['descuento'],
                'iva'               => $calc['iva'],
                'total'             => $calc['total'],
                'estado'            => $data['estado'] ?? 'borrador',
                'forma_pago'        => $data['forma_pago'] ?? 'contado',
                'plazo_pago'        => $data['plazo_pago'] ?? 0,
                'observaciones'     => $data['observaciones'],
                'terminos'          => $data['terminos'],
                'user_id'           => $userId,
            ]);

            foreach ($calc['items'] as $item) {
                CotizacionItem::create([
                    'cotizacion_id'   => $cotizacion->id,
                    'producto_id'     => $item['producto_id'] ?? null,
                    'codigo'          => $item['codigo'] ?? null,
                    'descripcion'     => strtoupper($item['descripcion']),
                    'unidad'          => $item['unidad'] ?? 'UN',
                    'cantidad'        => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'descuento_pct'   => $item['descuento_pct'],
                    'descuento'       => $item['descuento'],
                    'subtotal'        => $item['subtotal'],
                    'iva_pct'         => $item['iva_pct'],
                    'iva'             => $item['iva'],
                    'total'           => $item['total'],
                    'orden'           => $item['orden'],
                ]);
            }

            return $cotizacion;
        });
    }
}
