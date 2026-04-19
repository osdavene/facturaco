<?php

namespace App\Services;

use App\Models\OrdenCompra;
use App\Models\OrdenCompraItem;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdenCompraService
{
    public function __construct(
        private DocumentoService  $documentos,
        private InventarioService $inventario,
    ) {}

    public function crear(Request $request, int $userId): OrdenCompra
    {
        return DB::transaction(function () use ($request, $userId) {
            $proveedor   = Proveedor::findOrFail($request->proveedor_id);
            $consecutivo = OrdenCompra::siguienteConsecutivo();
            $calc        = $this->documentos->calcularItems($request->items);

            $orden = OrdenCompra::create([
                'numero'              => $consecutivo['numero'],
                'consecutivo'         => $consecutivo['consecutivo'],
                'proveedor_id'        => $proveedor->id,
                'proveedor_nombre'    => $proveedor->razon_social,
                'proveedor_documento' => $proveedor->tipo_documento.': '.$proveedor->documento_formateado,
                'fecha_emision'       => $request->fecha_emision,
                'fecha_esperada'      => $request->fecha_esperada,
                'subtotal'            => $calc['subtotal'],
                'iva'                 => $calc['iva'],
                'descuento'           => $calc['descuento'],
                'total'               => $calc['total'],
                'estado'              => $request->estado ?? 'borrador',
                'forma_pago'          => $request->forma_pago ?? 'credito',
                'plazo_pago'          => $request->plazo_pago ?? 30,
                'observaciones'       => $request->observaciones,
                'user_id'             => $userId,
            ]);

            $this->guardarItems($orden, $calc['items']);

            return $orden;
        });
    }

    public function actualizar(OrdenCompra $orden, Request $request): void
    {
        DB::transaction(function () use ($orden, $request) {
            $proveedor = Proveedor::findOrFail($request->proveedor_id);
            $calc      = $this->documentos->calcularItems($request->items);

            $orden->update([
                'proveedor_id'        => $proveedor->id,
                'proveedor_nombre'    => $proveedor->razon_social,
                'proveedor_documento' => $proveedor->tipo_documento.': '.$proveedor->documento_formateado,
                'fecha_emision'       => $request->fecha_emision,
                'fecha_esperada'      => $request->fecha_esperada,
                'subtotal'            => $calc['subtotal'],
                'iva'                 => $calc['iva'],
                'descuento'           => $calc['descuento'],
                'total'               => $calc['total'],
                'forma_pago'          => $request->forma_pago ?? $orden->forma_pago,
                'plazo_pago'          => $request->plazo_pago ?? $orden->plazo_pago,
                'observaciones'       => $request->observaciones,
            ]);

            $orden->items()->delete();
            $this->guardarItems($orden, $calc['items']);
        });
    }

    public function recibir(OrdenCompra $orden, Request $request, int $userId): void
    {
        DB::transaction(function () use ($orden, $request, $userId) {
            foreach ($orden->items as $item) {
                $cantRecibida = (float) $request->input('cantidad_'.$item->id, 0);

                if ($cantRecibida <= 0) {
                    continue;
                }

                $item->update(['cantidad_recibida' => $cantRecibida]);

                $producto = $this->resolverProducto($item, $orden->empresa_id, $userId);

                if ($producto) {
                    $this->inventario->registrarEntrada(
                        $producto,
                        $cantRecibida,
                        $orden->numero,
                        $userId,
                        'Recepción OC',
                        $item->precio_unitario,
                    );
                }
            }

            $orden->update([
                'estado'          => 'recibida',
                'fecha_recepcion' => now(),
                'notas_recepcion' => $request->notas_recepcion,
            ]);
        });
    }

    private function guardarItems(OrdenCompra $orden, array $calcItems): void
    {
        foreach ($calcItems as $item) {
            OrdenCompraItem::create([
                'orden_compra_id' => $orden->id,
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
        }
    }

    private function resolverProducto(OrdenCompraItem $item, int $empresaId, int $userId): ?Producto
    {
        if ($item->producto_id) {
            return Producto::find($item->producto_id);
        }

        $codigo = $item->codigo !== 'SIN-COD'
            ? $item->codigo
            : 'OC-'.strtoupper(preg_replace('/\s+/', '-', trim($item->descripcion)));

        $producto = Producto::create([
            'empresa_id'    => $empresaId,
            'codigo'        => $codigo,
            'nombre'        => $item->descripcion,
            'precio_compra' => $item->precio_unitario,
            'precio_venta'  => $item->precio_unitario,
            'stock_actual'  => 0,
            'stock_minimo'  => 0,
            'es_servicio'   => false,
            'activo'        => true,
            'created_by'    => $userId,
        ]);

        $item->update(['producto_id' => $producto->id]);

        return $producto;
    }
}
