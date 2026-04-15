<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CotizacionResource;
use App\Models\Cotizacion;
use App\Models\CotizacionItem;
use App\Models\Cliente;
use App\Services\DocumentoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CotizacionController extends Controller
{
    public function __construct(private DocumentoService $documentos) {}

    public function index(Request $request): JsonResponse
    {
        $cotizaciones = Cotizacion::with('cliente')
            ->when($request->buscar, fn ($q) => $q
                ->where('numero', 'ilike', "%{$request->buscar}%")
                ->orWhere('cliente_nombre', 'ilike', "%{$request->buscar}%"))
            ->when($request->estado, fn ($q) => $q->where('estado', $request->estado))
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json(CotizacionResource::collection($cotizaciones)->response()->getData(true));
    }

    public function show(Cotizacion $cotizacion): JsonResponse
    {
        return response()->json(['data' => new CotizacionResource($cotizacion->load('items'))]);
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
        ]);

        $empresa = $request->attributes->get('empresa');

        $cotizacion = DB::transaction(function () use ($request, $empresa) {
            $cliente = Cliente::findOrFail($request->cliente_id);
            $ultimo  = Cotizacion::withTrashed()->max('consecutivo') ?? 0;
            $consec  = $ultimo + 1;

            $calc = $this->documentos->calcularItems($request->items);

            $cotizacion = Cotizacion::create([
                'empresa_id'        => $empresa->id,
                'numero'            => 'COT-'.date('Y').'-'.str_pad($consec, 4, '0', STR_PAD_LEFT),
                'consecutivo'       => $consec,
                'cliente_id'        => $cliente->id,
                'cliente_nombre'    => $cliente->nombre_completo,
                'fecha_emision'     => $request->fecha_emision,
                'fecha_vencimiento' => $request->fecha_vencimiento,
                'subtotal'          => $calc['subtotal'],
                'descuento'         => $calc['descuento'],
                'iva'               => $calc['iva'],
                'total'             => $calc['total'],
                'estado'            => 'borrador',
                'observaciones'     => $request->observaciones,
                'user_id'           => $request->user()->id,
            ]);

            foreach ($calc['items'] as $item) {
                CotizacionItem::create([
                    'cotizacion_id'   => $cotizacion->id,
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

            return $cotizacion;
        });

        return response()->json(['data' => new CotizacionResource($cotizacion->load('items'))], 201);
    }

    public function estado(Request $request, Cotizacion $cotizacion): JsonResponse
    {
        $request->validate([
            'estado' => 'required|in:borrador,enviada,aceptada,rechazada,vencida',
        ]);

        $cotizacion->update(['estado' => $request->estado]);

        return response()->json(['data' => new CotizacionResource($cotizacion->fresh())]);
    }

    public function destroy(Cotizacion $cotizacion): JsonResponse
    {
        $cotizacion->update(['estado' => 'anulada']);

        return response()->json(['message' => 'Cotización anulada correctamente.']);
    }
}
