<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Factura;
use App\Models\FacturaItem;
use App\Models\Remision;
use App\Models\RemisionItem;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RemisionController extends Controller
{
    public function __construct(
        private PdfService $pdf,
    ) {}

    public function index(Request $request)
    {
        $remisiones = Remision::with('cliente')
            ->when($request->buscar, fn ($q) =>
                $q->where('numero',          'like', '%'.$request->buscar.'%')
                  ->orWhere('cliente_nombre', 'like', '%'.$request->buscar.'%'))
            ->when($request->estado, fn ($q) => $q->where('estado', $request->estado))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $totales = [
            'total'     => Remision::count(),
            'borrador'  => Remision::where('estado', 'borrador')->count(),
            'enviada'   => Remision::where('estado', 'enviada')->count(),
            'entregada' => Remision::where('estado', 'entregada')->count(),
            'facturada' => Remision::where('estado', 'facturada')->count(),
        ];

        return view('remisiones.index', compact('remisiones', 'totales'));
    }

    public function create()
    {
        $consecutivo = Remision::siguienteConsecutivo();
        $empresa     = Empresa::obtener();

        return view('remisiones.create', compact('consecutivo', 'empresa'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_nombre'      => 'required|string|max:255',
            'fecha_emision'       => 'required|date',
            'items'               => 'required|array|min:1',
            'items.*.descripcion' => 'required|string',
            'items.*.cantidad'    => 'required|numeric|min:0.001',
        ]);

        $userId = auth()->id();

        DB::transaction(function () use ($request, $userId) {
            $consecutivo = Remision::siguienteConsecutivo();

            $subtotal = collect($request->items)->sum(
                fn ($i) => (float) $i['cantidad'] * (float) ($i['precio_unitario'] ?? 0)
            );

            $remision = Remision::create([
                'numero'            => $consecutivo['numero'],
                'consecutivo'       => $consecutivo['consecutivo'],
                'cliente_id'        => $request->cliente_id,
                'cliente_nombre'    => strtoupper($request->cliente_nombre),
                'cliente_documento' => $request->cliente_documento,
                'cliente_email'     => $request->cliente_email,
                'cliente_direccion' => $request->cliente_direccion
                                        ? strtoupper($request->cliente_direccion) : null,
                'cliente_telefono'  => $request->cliente_telefono,
                'fecha_emision'     => $request->fecha_emision,
                'fecha_entrega'     => $request->fecha_entrega,
                'lugar_entrega'     => $request->lugar_entrega
                                        ? strtoupper($request->lugar_entrega) : null,
                'transportador'     => $request->transportador
                                        ? strtoupper($request->transportador) : null,
                'guia'              => $request->guia,
                'subtotal'          => $subtotal,
                'total'             => $subtotal,
                'estado'            => $request->estado ?? 'borrador',
                'observaciones'     => $request->observaciones
                                        ? strtoupper($request->observaciones) : null,
                'user_id'           => $userId,
            ]);

            foreach ($request->items as $i => $item) {
                $cant   = (float) $item['cantidad'];
                $precio = (float) ($item['precio_unitario'] ?? 0);

                RemisionItem::create([
                    'remision_id'     => $remision->id,
                    'producto_id'     => $item['producto_id'] ?? null,
                    'codigo'          => $item['codigo']      ?? null,
                    'descripcion'     => strtoupper($item['descripcion']),
                    'unidad'          => $item['unidad']      ?? 'UN',
                    'cantidad'        => $cant,
                    'precio_unitario' => $precio,
                    'total'           => $cant * $precio,
                    'orden'           => $i,
                ]);
            }
        });

        return redirect()->route('remisiones.index')
            ->with('success', 'Remisión creada correctamente.');
    }

    public function show(Remision $remision)
    {
        $remision->load(['items.producto', 'cliente', 'usuario', 'factura']);

        return view('remisiones.show', compact('remision'));
    }

    public function cambiarEstado(Request $request, Remision $remision)
    {
        $request->validate([
            'estado' => 'required|in:borrador,enviada,entregada,anulada',
        ]);

        $remision->update(['estado' => $request->estado]);

        return back()->with('success', 'Estado actualizado.');
    }

    // ── CONVERTIR A FACTURA ───────────────────────────────────

    public function convertir(Remision $remision)
    {
        if ($remision->estado === 'facturada') {
            return back()->with('error', 'Esta remisión ya fue facturada.');
        }
        if ($remision->estado === 'anulada') {
            return back()->with('error', 'No puedes facturar una remisión anulada.');
        }

        $userId = auth()->id();

        DB::transaction(function () use ($remision, $userId) {
            $consecutivo = Factura::siguienteConsecutivo();

            $subtotal = $remision->items->sum(fn ($i) => $i->cantidad * $i->precio_unitario);
            $iva      = $remision->items->sum(fn ($i) => $i->cantidad * $i->precio_unitario * 0.19);

            $factura = Factura::create([
                'numero'            => $consecutivo['numero'],
                'consecutivo'       => $consecutivo['consecutivo'],
                'tipo'              => 'factura',
                'cliente_id'        => $remision->cliente_id,
                'cliente_nombre'    => $remision->cliente_nombre,
                'cliente_documento' => $remision->cliente_documento ?? '',
                'cliente_email'     => $remision->cliente_email,
                'cliente_direccion' => $remision->cliente_direccion,
                'fecha_emision'     => now(),
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
                'observaciones'     => 'GENERADA DESDE REMISIÓN '.$remision->numero,
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
        });

        return redirect()->route('remisiones.show', $remision)
            ->with('success', '¡Remisión convertida a factura exitosamente!');
    }

    public function destroy(Remision $remision)
    {
        $remision->update(['estado' => 'anulada']);
        $remision->delete();

        return redirect()->route('remisiones.index')
            ->with('success', 'Remisión anulada.');
    }

    public function pdf(Remision $remision)
    {
        $remision->load(['items', 'usuario']);
        $empresa = Empresa::obtener();

        return $this->pdf->stream(
            'remisiones.pdf',
            compact('remision', 'empresa'),
            'remision-'.$remision->numero.'.pdf',
        );
    }
}
