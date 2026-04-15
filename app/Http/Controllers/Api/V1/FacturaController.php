<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\FacturaResource;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\FacturaItem;
use App\Models\Producto;
use App\Services\DocumentoService;
use App\Services\InventarioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FacturaController extends Controller
{
    public function __construct(
        private DocumentoService  $documentos,
        private InventarioService $inventario,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $facturas = Factura::with('cliente')
            ->when($request->buscar, fn ($q) => $q
                ->where('numero', 'ilike', "%{$request->buscar}%")
                ->orWhere('cliente_nombre', 'ilike', "%{$request->buscar}%"))
            ->when($request->estado, fn ($q) => $q->where('estado', $request->estado))
            ->when($request->fecha_desde, fn ($q) => $q->whereDate('fecha_emision', '>=', $request->fecha_desde))
            ->when($request->fecha_hasta, fn ($q) => $q->whereDate('fecha_emision', '<=', $request->fecha_hasta))
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json(FacturaResource::collection($facturas)->response()->getData(true));
    }

    public function show(Factura $factura): JsonResponse
    {
        return response()->json(['data' => new FacturaResource($factura->load('items'))]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'cliente_id'              => 'required|exists:clientes,id',
            'fecha_emision'           => 'required|date',
            'fecha_vencimiento'       => 'required|date|after_or_equal:fecha_emision',
            'items'                   => 'required|array|min:1',
            'items.*.descripcion'     => 'required|string',
            'items.*.cantidad'        => 'required|numeric|min:0.001',
            'items.*.precio_unitario' => 'required|numeric|min:0',
            'items.*.iva_pct'         => 'nullable|numeric|min:0|max:100',
            'items.*.descuento_pct'   => 'nullable|numeric|min:0|max:100',
            'forma_pago'              => 'nullable|in:contado,credito,transferencia,cheque',
            'plazo_pago'              => 'nullable|integer|min:0',
            'observaciones'           => 'nullable|string',
        ]);

        $empresa = $request->attributes->get('empresa');
        $userId  = $request->user()->id;

        $factura = DB::transaction(function () use ($request, $empresa, $userId) {
            $cliente     = Cliente::findOrFail($request->cliente_id);
            $prefijo     = $empresa->prefijo_factura ?? 'FE';
            $consecutivo = Factura::siguienteConsecutivo($prefijo);

            $calc = $this->documentos->calcularItems($request->items);
            $ret  = $this->documentos->calcularRetenciones(
                $calc['subtotal'], $calc['iva'],
                $cliente->retefuente_pct, $cliente->reteiva_pct, $cliente->reteica_pct,
            );

            $factura = Factura::create([
                'empresa_id'        => $empresa->id,
                'numero'            => $consecutivo['numero'],
                'prefijo'           => $prefijo,
                'consecutivo'       => $consecutivo['consecutivo'],
                'tipo'              => 'factura',
                'cliente_id'        => $cliente->id,
                'cliente_nombre'    => $cliente->nombre_completo,
                'cliente_documento' => $cliente->tipo_documento.': '.$cliente->numero_documento,
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
                'total_pagado'      => 0,
                'estado'            => $request->estado ?? 'borrador',
                'forma_pago'        => $request->forma_pago ?? 'contado',
                'plazo_pago'        => $request->plazo_pago ?? 0,
                'observaciones'     => $request->observaciones,
                'user_id'           => $userId,
            ]);

            foreach ($calc['items'] as $item) {
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
                        $this->inventario->registrarSalida($producto, $item['cantidad'], $factura->numero, $userId, 'Venta API');
                    }
                }
            }

            return $factura;
        });

        return response()->json(['data' => new FacturaResource($factura->load('items'))], 201);
    }

    public function update(Request $request, Factura $factura): JsonResponse
    {
        $data = $request->validate([
            'fecha_vencimiento' => 'nullable|date',
            'forma_pago'        => 'nullable|in:contado,credito,transferencia,cheque',
            'plazo_pago'        => 'nullable|integer|min:0',
            'observaciones'     => 'nullable|string',
        ]);

        $factura->update($data);

        return response()->json(['data' => new FacturaResource($factura->fresh()->load('items'))]);
    }

    public function estado(Request $request, Factura $factura): JsonResponse
    {
        $request->validate([
            'estado' => 'required|in:borrador,emitida,pagada,vencida,anulada',
        ]);

        $factura->update(['estado' => $request->estado]);

        return response()->json(['data' => new FacturaResource($factura->fresh())]);
    }

    public function destroy(Factura $factura): JsonResponse
    {
        $factura->update(['estado' => 'anulada']);

        return response()->json(['message' => 'Factura anulada correctamente.']);
    }
}
