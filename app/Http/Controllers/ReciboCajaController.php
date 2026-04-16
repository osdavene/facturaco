<?php
namespace App\Http\Controllers;

use App\Models\ReciboCaja;
use App\Models\Factura;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Services\ContabilidadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReciboCajaController extends Controller
{
    public function index(Request $request)
    {
        $recibos = ReciboCaja::with(['cliente', 'factura'])
            ->when($request->buscar, function($q) use ($request) {
                $q->where('numero', 'like', '%'.$request->buscar.'%')
                  ->orWhere('cliente_nombre', 'like', '%'.$request->buscar.'%');
            })
            ->when($request->fecha_desde, fn($q) => $q->whereDate('fecha', '>=', $request->fecha_desde))
            ->when($request->fecha_hasta, fn($q) => $q->whereDate('fecha', '<=', $request->fecha_hasta))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $totalMes = ReciboCaja::whereMonth('fecha', now()->month)
                              ->whereYear('fecha', now()->year)
                              ->where('estado', 'activo')
                              ->sum('valor');

        $totalHoy = ReciboCaja::whereDate('fecha', today())
                              ->where('estado', 'activo')
                              ->sum('valor');

        return view('recibos.index', compact('recibos', 'totalMes', 'totalHoy'));
    }

    public function create(Request $request)
    {
        $consecutivo = ReciboCaja::siguienteConsecutivo();
        $clientes    = Cliente::where('activo', true)->orderBy('razon_social')->get();
        $facturaId   = $request->factura_id;
        $factura     = $facturaId ? Factura::find($facturaId) : null;

        return view('recibos.create', compact('consecutivo', 'clientes', 'factura'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id'    => 'required|exists:clientes,id',
            'fecha'         => 'required|date',
            'valor'         => 'required|numeric|min:1',
            'forma_pago'    => 'required|in:efectivo,transferencia,cheque,tarjeta,consignacion',
            'concepto'      => 'required|string|max:255',
            'factura_id'    => 'nullable|exists:facturas,id',
            'banco'         => 'nullable|string|max:100',
            'num_referencia'=> 'nullable|string|max:50',
            'observaciones' => 'nullable|string',
        ]);

        $userId = auth()->id();

        $reciboCreado = DB::transaction(function() use ($request, $userId) {
            $cliente     = Cliente::findOrFail($request->cliente_id);
            $consecutivo = ReciboCaja::siguienteConsecutivo();

            $recibo = ReciboCaja::create([
                'numero'          => $consecutivo['numero'],
                'consecutivo'     => $consecutivo['consecutivo'],
                'cliente_id'      => $cliente->id,
                'cliente_nombre'  => $cliente->nombre_completo,
                'cliente_documento'=> $cliente->tipo_documento.': '.$cliente->documento_formateado,
                'factura_id'      => $request->factura_id,
                'fecha'           => $request->fecha,
                'valor'           => $request->valor,
                'forma_pago'      => $request->forma_pago,
                'banco'           => $request->banco,
                'num_referencia'  => $request->num_referencia,
                'concepto'        => strtoupper($request->concepto),
                'observaciones'   => $request->observaciones ? strtoupper($request->observaciones) : null,
                'estado'          => 'activo',
                'user_id'         => $userId,
            ]);

            // Actualizar total_pagado en la factura
            if ($request->factura_id) {
                $factura = Factura::find($request->factura_id);
                if ($factura) {
                    $totalPagado = $factura->total_pagado + $request->valor;
                    $nuevoEstado = $totalPagado >= $factura->total ? 'pagada' : $factura->estado;
                    $factura->update([
                        'total_pagado' => min($totalPagado, $factura->total),
                        'estado'       => $nuevoEstado,
                    ]);
                }
            }

            return $recibo;
        });

        try {
            if ($reciboCreado) {
                (new ContabilidadService())->asientoRecibo($reciboCreado);
            }
        } catch (\Throwable) {}

        return redirect()->route('recibos.index')
            ->with('success', 'Recibo de caja creado correctamente.');
    }

    public function show(ReciboCaja $recibo)
    {
        $recibo->load(['cliente', 'factura', 'usuario']);
        return view('recibos.show', compact('recibo'));
    }

    public function destroy(ReciboCaja $recibo)
    {
        if ($recibo->estado === 'anulado') {
            return back()->with('error', 'El recibo ya está anulado.');
        }

        DB::transaction(function() use ($recibo) {
            // Revertir pago en factura si aplica
            if ($recibo->factura_id) {
                $factura = Factura::find($recibo->factura_id);
                if ($factura) {
                    $nuevoTotal  = max(0, $factura->total_pagado - $recibo->valor);
                    $nuevoEstado = $nuevoTotal < $factura->total ? 'emitida' : 'pagada';
                    $factura->update([
                        'total_pagado' => $nuevoTotal,
                        'estado'       => $nuevoEstado,
                    ]);
                }
            }
            // Solo anular — nunca borrar
            $recibo->update(['estado' => 'anulado']);
        });

        return redirect()->route('recibos.index')
            ->with('success', 'Recibo anulado correctamente.');
    }

    public function pdf(ReciboCaja $recibo)
    {
        $recibo->load(['cliente', 'factura', 'usuario']);
        $empresa = Empresa::obtener();
        $pdf     = Pdf::loadView('recibos.pdf', compact('recibo', 'empresa'))
                      ->setPaper([0, 0, 595, 420], 'portrait'); // media carta
        return $pdf->stream('recibo-'.$recibo->numero.'.pdf');
    }
}