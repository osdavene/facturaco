<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNotaCreditoRequest;
use App\Models\NotaCredito;
use App\Models\NotaCreditoItem;
use App\Models\Factura;
use App\Models\Empresa;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class NotaCreditoController extends Controller
{
    public function index(Request $request)
    {
        $notas = NotaCredito::with(['factura', 'cliente'])
            ->when($request->buscar, fn($q) =>
                $q->where('numero', 'like', '%'.$request->buscar.'%')
                  ->orWhere('cliente_nombre', 'like', '%'.$request->buscar.'%')
                  ->orWhere('factura_numero', 'like', '%'.$request->buscar.'%')
            )
            ->when($request->fecha_desde, fn($q) => $q->whereDate('fecha', '>=', $request->fecha_desde))
            ->when($request->fecha_hasta, fn($q) => $q->whereDate('fecha', '<=', $request->fecha_hasta))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('notas_credito.index', compact('notas'));
    }

    public function create(Request $request)
    {
        $factura = Factura::with(['items.producto', 'cliente'])
            ->findOrFail($request->factura_id);

        if (!in_array($factura->estado, ['emitida', 'pagada', 'vencida'])) {
            return redirect()->route('facturas.show', $factura)
                ->with('error', 'Solo se pueden crear notas de crédito para facturas emitidas, pagadas o vencidas.');
        }

        $empresa = Empresa::obtener();

        return view('notas_credito.create', compact('factura', 'empresa'));
    }

    public function store(StoreNotaCreditoRequest $request)
    {

        $factura = Factura::with('items.producto')->findOrFail($request->factura_id);
        $userId  = Auth::id();

        DB::transaction(function () use ($request, $factura, $userId) {
            $consec = NotaCredito::siguienteConsecutivo();

            $subtotal = 0;
            $totalIva = 0;

            foreach ($request->items as $item) {
                $cant     = floatval($item['cantidad']);
                $precio   = floatval($item['precio_unitario']);
                $ivaPct   = floatval($item['iva_pct'] ?? 0);
                $sub      = $cant * $precio;
                $iva      = $sub * ($ivaPct / 100);
                $subtotal += $sub;
                $totalIva += $iva;
            }

            $nota = NotaCredito::create([
                'numero'            => $consec['numero'],
                'prefijo'           => 'NC',
                'consecutivo'       => $consec['consecutivo'],
                'factura_id'        => $factura->id,
                'factura_numero'    => $factura->numero,
                'cliente_id'        => $factura->cliente_id,
                'cliente_nombre'    => $factura->cliente_nombre,
                'cliente_documento' => $factura->cliente_documento,
                'tipo'              => $request->tipo,
                'motivo'            => $request->motivo,
                'observaciones'     => $request->observaciones,
                'fecha'             => $request->fecha,
                'subtotal'          => $subtotal,
                'iva'               => $totalIva,
                'total'             => $subtotal + $totalIva,
                'estado'            => 'activa',
                'user_id'           => $userId,
            ]);

            foreach ($request->items as $i => $item) {
                $cant          = floatval($item['cantidad']);
                $precio        = floatval($item['precio_unitario']);
                $ivaPct        = floatval($item['iva_pct'] ?? 0);
                $sub           = $cant * $precio;
                $iva           = $sub * ($ivaPct / 100);
                $devolverStock = isset($item['devolver_stock']) && $item['devolver_stock'];

                NotaCreditoItem::create([
                    'nota_credito_id' => $nota->id,
                    'factura_item_id' => $item['factura_item_id'] ?? null,
                    'producto_id'     => $item['producto_id'] ?? null,
                    'codigo'          => $item['codigo'] ?? 'NC',
                    'descripcion'     => $item['descripcion'],
                    'unidad'          => $item['unidad'] ?? 'UN',
                    'cantidad'        => $cant,
                    'precio_unitario' => $precio,
                    'subtotal'        => $sub,
                    'iva_pct'         => $ivaPct,
                    'iva'             => $iva,
                    'total'           => $sub + $iva,
                    'devolver_stock'  => $devolverStock,
                    'orden'           => $i,
                ]);

                if ($devolverStock && !empty($item['producto_id'])) {
                    $producto = Producto::find($item['producto_id']);
                    if ($producto && !$producto->es_servicio) {
                        $producto->increment('stock_actual', $cant);
                    }
                }
            }

            if ($request->tipo === 'total') {
                $factura->update(['estado' => 'anulada']);
            } else {
                $nuevoPagado = max(0, $factura->total_pagado - ($subtotal + $totalIva));
                $factura->update(['total_pagado' => $nuevoPagado]);
            }
        });

        return redirect()->route('notas_credito.index')
            ->with('success', 'Nota de crédito generada correctamente.');
    }

    public function show(NotaCredito $nota)
    {
        $nota->load(['items.producto', 'factura', 'cliente', 'usuario']);
        $empresa = Empresa::obtener();
        return view('notas_credito.show', compact('nota', 'empresa'));
    }

    public function pdf(NotaCredito $nota)
    {
        $nota->load(['items', 'factura', 'cliente']);
        $empresa = Empresa::obtener();

        $pdf = Pdf::loadView('notas_credito.pdf', compact('nota', 'empresa'))
                  ->setPaper('a4', 'portrait');

        return $pdf->stream('NC-' . $nota->numero . '.pdf');
    }
}