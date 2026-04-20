<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Factura;
use App\Models\FacturaItem;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacturaService
{
    public function __construct(
        private DocumentoService  $documentos,
        private InventarioService $inventario,
    ) {}

    public function crear(Request $request, Empresa $empresa, int $userId): Factura
    {
        return DB::transaction(function () use ($request, $empresa, $userId) {
            $cliente     = Cliente::findOrFail($request->cliente_id);
            $prefijo     = $empresa->prefijo_factura ?? 'FE';
            $consecutivo = Factura::siguienteConsecutivo($prefijo);
            $calc        = $this->documentos->calcularItems($request->items);
            $ret         = $this->documentos->calcularRetenciones(
                $calc['subtotal'], $calc['iva'],
                $cliente->retefuente_pct, $cliente->reteiva_pct, $cliente->reteica_pct,
            );

            $factura = Factura::create([
                'numero'            => $consecutivo['numero'],
                'prefijo'           => $prefijo,
                'consecutivo'       => $consecutivo['consecutivo'],
                'tipo'              => 'factura',
                'cliente_id'        => $cliente->id,
                'cliente_nombre'    => $cliente->nombre_completo,
                'cliente_documento' => $cliente->tipo_documento.': '.$cliente->documento_formateado,
                'cliente_direccion' => $cliente->direccion,
                'cliente_email'     => $cliente->email,
                'fecha_emision'     => $request->fecha_emision,
                'hora_emision'      => now('America/Bogota')->format('H:i:s'),
                'fecha_vencimiento' => $request->fecha_vencimiento,
                'subtotal'          => $calc['subtotal'],
                'descuento'         => $calc['descuento'],
                'base_iva'          => $calc['subtotal'],
                'iva'               => $calc['iva'],
                'retefuente'        => $ret['retefuente'],
                'reteiva'           => $ret['reteiva'],
                'reteica'           => $ret['reteica'],
                'total'             => $ret['total_neto'],
                'total_pagado'      => 0,
                'estado'            => $request->estado ?? 'borrador',
                'forma_pago'        => $request->forma_pago ?? 'contado',
                'plazo_pago'        => $request->plazo_pago ?? 0,
                'observaciones'     => $request->observaciones,
                'user_id'           => $userId,
            ]);

            $this->guardarItems($factura, $calc['items'], $userId);

            return $factura;
        });
    }

    public function actualizar(Factura $factura, Request $request, int $userId): void
    {
        DB::transaction(function () use ($factura, $request, $userId) {
            foreach ($factura->items as $item) {
                if ($item->producto_id) {
                    $producto = Producto::find($item->producto_id);
                    if ($producto) {
                        $this->inventario->registrarEntrada(
                            $producto, $item->cantidad, $factura->numero,
                            $userId, 'Ajuste por edición de factura',
                        );
                    }
                }
            }

            $factura->items()->delete();

            $cliente = Cliente::findOrFail($request->cliente_id);
            $calc    = $this->documentos->calcularItems($request->items);
            $ret     = $this->documentos->calcularRetenciones(
                $calc['subtotal'], $calc['iva'],
                $cliente->retefuente_pct, $cliente->reteiva_pct, $cliente->reteica_pct,
            );

            $factura->update([
                'cliente_id'        => $cliente->id,
                'cliente_nombre'    => $cliente->nombre_completo,
                'cliente_documento' => $cliente->tipo_documento.': '.$cliente->documento_formateado,
                'cliente_direccion' => $cliente->direccion,
                'cliente_email'     => $cliente->email,
                'fecha_emision'     => $request->fecha_emision,
                'fecha_vencimiento' => $request->fecha_vencimiento,
                'subtotal'          => $calc['subtotal'],
                'descuento'         => $calc['descuento'],
                'base_iva'          => $calc['subtotal'],
                'iva'               => $calc['iva'],
                'retefuente'        => $ret['retefuente'],
                'reteiva'           => $ret['reteiva'],
                'reteica'           => $ret['reteica'],
                'total'             => $ret['total_neto'],
                'forma_pago'        => $request->forma_pago ?? $factura->forma_pago,
                'plazo_pago'        => $request->plazo_pago ?? $factura->plazo_pago,
                'observaciones'     => $request->observaciones,
            ]);

            $this->guardarItems($factura, $calc['items'], $userId);
        });
    }

    public function anular(Factura $factura, int $userId): void
    {
        $factura->loadMissing('items.producto');

        foreach ($factura->items as $item) {
            if ($item->producto_id && $item->producto && ! $item->producto->es_servicio) {
                $this->inventario->registrarEntrada(
                    $item->producto, $item->cantidad, $factura->numero,
                    $userId, 'Anulación',
                );
            }
        }

        $factura->update(['estado' => 'anulada']);
    }

    private function guardarItems(Factura $factura, array $calcItems, int $userId): void
    {
        foreach ($calcItems as $item) {
            FacturaItem::create([
                'factura_id'      => $factura->id,
                'producto_id'     => $item['producto_id'] ?? null,
                'codigo'          => $item['codigo']      ?? 'SIN-COD',
                'descripcion'     => $item['descripcion'],
                'unidad'          => $item['unidad']      ?? 'UN',
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

            if (! empty($item['producto_id'])) {
                $producto = Producto::find($item['producto_id']);
                if ($producto) {
                    $this->inventario->registrarSalida(
                        $producto, $item['cantidad'], $factura->numero,
                        $userId, 'Venta',
                    );
                }
            }
        }
    }
}
