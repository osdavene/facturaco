<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Factura;
use App\Models\FacturaItem;
use App\Models\Producto;
use App\Services\DocumentoService;
use App\Services\InventarioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function __construct(
        private DocumentoService  $documentos,
        private InventarioService $inventario,
    ) {}

    // ── INTERFAZ POS ─────────────────────────────────────────

    public function index()
    {
        $empresa    = Empresa::obtener();
        $categorias = Categoria::where('activo', true)->orderBy('nombre')->get();
        $productos  = Producto::where('activo', true)
                        ->with('categoria')
                        ->orderBy('nombre')
                        ->select([
                            'id', 'codigo', 'codigo_barras', 'nombre',
                            'precio_venta', 'precio_venta2', 'precio_venta3',
                            'iva_pct', 'incluye_iva',
                            'stock_actual', 'es_servicio',
                            'imagen', 'categoria_id',
                        ])
                        ->get();

        return view('pos.index', compact('empresa', 'categorias', 'productos'));
    }

    // ── GUARDAR VENTA ─────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'items'                   => 'required|array|min:1',
            'items.*.descripcion'     => 'required|string',
            'items.*.cantidad'        => 'required|numeric|min:0.001',
            'items.*.precio_unitario' => 'required|numeric|min:0',
            'forma_pago'              => 'required|in:contado,tarjeta,transferencia,nequi',
        ]);

        $userId  = Auth::id();
        $empresa = Empresa::obtener();

        $facturaId = DB::transaction(function () use ($request, $userId, $empresa) {

            // Cliente: usar seleccionado o Consumidor Final
            if ($request->filled('cliente_id')) {
                $cliente = Cliente::findOrFail($request->cliente_id);
            } else {
                $cliente = Cliente::firstOrCreate(
                    ['numero_documento' => '222222222'],
                    [
                        'tipo_persona'    => 'natural',
                        'tipo_documento'  => 'CC',
                        'razon_social'    => 'CONSUMIDOR FINAL',
                        'nombres'         => 'Consumidor',
                        'apellidos'       => 'Final',
                        'email'           => 'consumidor@final.co',
                        'activo'          => true,
                        'retefuente_pct'  => 0,
                        'reteiva_pct'     => 0,
                        'reteica_pct'     => 0,
                        'lista_precio'    => 'general',
                    ]
                );
            }

            $prefijo     = $empresa->prefijo_factura ?? 'FE';
            $consecutivo = Factura::siguienteConsecutivo($prefijo);
            $calc        = $this->documentos->calcularItems($request->items);
            $ret         = $this->documentos->calcularRetenciones(
                $calc['subtotal'],
                $calc['iva'],
                (float) ($cliente->retefuente_pct ?? 0),
                (float) ($cliente->reteiva_pct    ?? 0),
                (float) ($cliente->reteica_pct    ?? 0),
            );

            $factura = Factura::create([
                'numero'            => $consecutivo['numero'],
                'prefijo'           => $prefijo,
                'consecutivo'       => $consecutivo['consecutivo'],
                'tipo'              => 'factura',
                'cliente_id'        => $cliente->id,
                'cliente_nombre'    => $cliente->nombre_completo,
                'cliente_documento' => $cliente->tipo_documento.': '.$cliente->documento_formateado,
                'cliente_direccion' => $cliente->direccion ?? '',
                'cliente_email'     => $cliente->email    ?? '',
                'fecha_emision'     => today(),
                'fecha_vencimiento' => today(),
                'subtotal'          => $calc['subtotal'],
                'descuento'         => $calc['descuento'],
                'base_iva'          => $calc['subtotal'],
                'iva'               => $calc['iva'],
                'retefuente'        => $ret['retefuente'],
                'reteiva'           => $ret['reteiva'],
                'reteica'           => $ret['reteica'],
                'total'             => $ret['total_neto'],
                'total_pagado'      => $ret['total_neto'],
                'estado'            => 'pagada',
                'forma_pago'        => $request->forma_pago,
                'plazo_pago'        => 0,
                'observaciones'     => 'Venta POS',
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
                    'descuento_pct'   => $item['descuento_pct']   ?? 0,
                    'descuento'       => $item['descuento']        ?? 0,
                    'subtotal'        => $item['subtotal'],
                    'iva_pct'         => $item['iva_pct'],
                    'iva'             => $item['iva'],
                    'total'           => $item['total'],
                    'orden'           => $item['orden'],
                ]);

                if (!empty($item['producto_id'])) {
                    $producto = Producto::find($item['producto_id']);
                    if ($producto && !$producto->es_servicio) {
                        $this->inventario->registrarSalida(
                            $producto,
                            $item['cantidad'],
                            $factura->numero,
                            $userId,
                            'Venta POS',
                        );
                    }
                }
            }

            return $factura->id;
        });

        return response()->json([
            'success'     => true,
            'factura_id'  => $facturaId,
            'ticket_url'  => route('pos.ticket', $facturaId),
        ]);
    }

    // ── TICKET 80MM ───────────────────────────────────────────

    public function ticket(Factura $factura)
    {
        $factura->load(['items', 'cliente']);
        $empresa  = Empresa::obtener();
        $efectivo = (float) request('efectivo', 0);
        $vuelto   = max(0, $efectivo - $factura->total);

        return view('pos.ticket', compact('factura', 'empresa', 'efectivo', 'vuelto'));
    }
}
