<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFacturaRequest;
use App\Http\Requests\UpdateFacturaRequest;
use App\Jobs\EnviarFacturaJob;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Factura;
use App\Models\Producto;
use App\Services\ContabilidadService;
use App\Services\DianService;
use App\Services\FacturaService;
use App\Services\MailService;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FacturaController extends Controller
{
    public function __construct(
        private FacturaService $facturas,
        private PdfService     $pdf,
        private DianService    $dian,
    ) {}

    // ── INDEX ─────────────────────────────────────────────────

    public function index(Request $request)
    {
        $facturas = Factura::with('cliente')
            ->when($request->buscar, fn($q) => $q->where(function ($inner) use ($request) {
                $inner->where('numero',          'ilike', '%'.$request->buscar.'%')
                      ->orWhere('cliente_nombre', 'ilike', '%'.$request->buscar.'%');
            }))
            ->when($request->estado,      fn($q) => $q->where('estado', $request->estado))
            ->when($request->fecha_desde, fn($q) => $q->whereDate('fecha_emision', '>=', $request->fecha_desde))
            ->when($request->fecha_hasta, fn($q) => $q->whereDate('fecha_emision', '<=', $request->fecha_hasta))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $totales = (object) [
            'total'       => Factura::count(),
            'pagadas'     => Factura::where('estado', 'pagada')->count(),
            'emitidas'    => Factura::where('estado', 'emitida')->count(),
            'vencidas'    => Factura::where('estado', 'vencida')->count(),
            'monto_total' => Factura::where('estado', '!=', 'anulada')->sum('total'),
            'cartera'     => Factura::whereIn('estado', ['emitida', 'vencida'])
                                ->selectRaw('COALESCE(SUM(total - total_pagado), 0) as cartera')
                                ->value('cartera') ?? 0,
        ];

        return view('facturas.index', compact('facturas', 'totales'));
    }

    // ── CREATE ────────────────────────────────────────────────

    public function create()
    {
        $empresa     = Empresa::obtener();
        $consecutivo = Factura::siguienteConsecutivo($empresa->prefijo_factura);
        $clientes    = Cliente::where('activo', true)->orderBy('razon_social')->get();
        $productos   = Producto::where('activo', true)->orderBy('nombre')->get();

        return view('facturas.create', compact('consecutivo', 'clientes', 'productos', 'empresa'));
    }

    // ── STORE ─────────────────────────────────────────────────

    public function store(StoreFacturaRequest $request)
    {
        $factura = $this->facturas->crear($request, Empresa::obtener(), Auth::id());

        try {
            (new ContabilidadService())->asientoFactura($factura);
        } catch (\Throwable) {}

        return redirect()->route('facturas.index')
            ->with('success', 'Factura creada correctamente.');
    }

    // ── SHOW ──────────────────────────────────────────────────

    public function show(Factura $factura)
    {
        $factura->load(['items.producto', 'cliente', 'usuario']);
        $empresa          = Empresa::obtener();
        $dianConfigurado  = $this->dian->estaConfigurado();

        return view('facturas.show', compact('factura', 'empresa', 'dianConfigurado'));
    }

    // ── EDIT ──────────────────────────────────────────────────

    public function edit(Factura $factura)
    {
        if ($factura->estado === 'anulada') {
            return redirect()->route('facturas.show', $factura)
                ->with('error', 'No puedes editar una factura anulada.');
        }

        $factura->load('items');
        $empresa   = Empresa::obtener();
        $clientes  = Cliente::where('activo', true)->orderBy('razon_social')->get();
        $productos = Producto::where('activo', true)->orderBy('nombre')->get();

        return view('facturas.create', compact('factura', 'clientes', 'productos', 'empresa'));
    }

    // ── UPDATE ────────────────────────────────────────────────

    public function update(UpdateFacturaRequest $request, Factura $factura)
    {
        $this->facturas->actualizar($factura, $request, Auth::id());

        try {
            (new ContabilidadService())->anularAsientosDe(Factura::class, $factura->id);
            (new ContabilidadService())->asientoFactura($factura->fresh());
        } catch (\Throwable) {}

        return redirect()->route('facturas.show', $factura)
            ->with('success', 'Factura actualizada correctamente.');
    }

    // ── DESTROY ───────────────────────────────────────────────

    public function destroy(Factura $factura)
    {
        $this->facturas->anular($factura, Auth::id());

        return redirect()->route('facturas.index')
            ->with('success', 'Factura anulada correctamente.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('warning', 'No se seleccionó ningún elemento.');
        }

        $count = Factura::whereIn('id', $ids)->where('estado', '!=', 'anulada')->count();

        foreach (Factura::whereIn('id', $ids)->where('estado', '!=', 'anulada')->get() as $factura) {
            $this->facturas->anular($factura, Auth::id());
        }

        return redirect()->route('facturas.index')
            ->with('success', "{$count} factura(s) anulada(s) correctamente.");
    }

    // ── CAMBIAR ESTADO ────────────────────────────────────────

    public function cambiarEstado(Request $request, Factura $factura)
    {
        $request->validate([
            'estado' => 'required|in:borrador,emitida,pagada,vencida,anulada',
        ]);

        $factura->update(['estado' => $request->estado]);

        return back()->with('success', 'Estado actualizado correctamente.');
    }

    // ── PDF ───────────────────────────────────────────────────

    public function pdf(Factura $factura)
    {
        $factura->load(['items', 'cliente']);
        $empresa = Empresa::obtener();

        $qrBase64 = $this->pdf->qrBase64([
            'Factura: ' . $factura->numero,
            'NIT: '     . $empresa->nit_formateado,
            'Cliente: ' . $factura->cliente_nombre,
            'Fecha: '   . $factura->fecha_emision->format('d/m/Y'),
            'Total: $'  . number_format($factura->total, 0, ',', '.'),
            'Estado: '  . strtoupper($factura->estado),
        ]);

        return $this->pdf->stream(
            'facturas.pdf',
            compact('factura', 'empresa', 'qrBase64'),
            'factura-'.$factura->numero.'.pdf',
        );
    }

    // ── FORMULARIO ENVIAR EMAIL ───────────────────────────────

    public function formEnviar(Factura $factura)
    {
        $factura->load(['items', 'cliente']);
        $empresa = Empresa::obtener();

        return view('facturas.enviar', compact('factura', 'empresa'));
    }

    // ── ENVIAR EMAIL ──────────────────────────────────────────

    public function enviar(Request $request, Factura $factura, MailService $mail)
    {
        $request->validate([
            'email'   => 'required|email',
            'mensaje' => 'nullable|string|max:500',
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email'    => 'El correo electrónico no es válido.',
        ]);

        $empresa = Empresa::obtener();

        if (! $mail->estaConfigurado($empresa)) {
            return back()->with('error', 'El correo SMTP no está configurado. Ve a Empresa → Configuración de Correo y completa los datos.');
        }

        EnviarFacturaJob::dispatch($factura, $empresa, $request->email, $request->mensaje ?? '');

        if ($factura->estado === 'borrador') {
            $factura->update(['estado' => 'emitida']);
        }

        return redirect()->route('facturas.show', $factura)
            ->with('success', 'Factura en cola de envío a '.$request->email.'. Llegará en unos momentos.');
    }
}
