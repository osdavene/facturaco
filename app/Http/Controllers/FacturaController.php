<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFacturaRequest;
use App\Http\Requests\UpdateFacturaRequest;
use App\Jobs\EnviarFacturaJob;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Factura;
use App\Models\FacturaItem;
use App\Models\Producto;
use App\Services\ContabilidadService;
use App\Services\DocumentoService;
use App\Services\InventarioService;
use App\Services\MailService;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class FacturaController extends Controller
{
    public function __construct(
        private DocumentoService  $documentos,
        private InventarioService $inventario,
        private PdfService        $pdf,
    ) {}

    // ── INDEX ─────────────────────────────────────────────────

    public function index(Request $request)
    {
        $facturas = Factura::with('cliente')
            ->when($request->buscar, function ($q) use ($request) {
                $q->where('numero',          'like', '%'.$request->buscar.'%')
                  ->orWhere('cliente_nombre', 'like', '%'.$request->buscar.'%');
            })
            ->when($request->estado,      fn ($q) => $q->where('estado', $request->estado))
            ->when($request->fecha_desde, fn ($q) => $q->whereDate('fecha_emision', '>=', $request->fecha_desde))
            ->when($request->fecha_hasta, fn ($q) => $q->whereDate('fecha_emision', '<=', $request->fecha_hasta))
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

        $userId  = Auth::id();
        $empresa = Empresa::obtener();

        $facturaCreada = DB::transaction(function () use ($request, $userId, $empresa) {
            $cliente     = Cliente::findOrFail($request->cliente_id);
            $prefijo     = $empresa->prefijo_factura ?? 'FE';
            $consecutivo = Factura::siguienteConsecutivo($prefijo);

            $calc = $this->documentos->calcularItems($request->items);
            $ret  = $this->documentos->calcularRetenciones(
                $calc['subtotal'],
                $calc['iva'],
                $cliente->retefuente_pct,
                $cliente->reteiva_pct,
                $cliente->reteica_pct,
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

                if (!empty($item['producto_id'])) {
                    $producto = Producto::find($item['producto_id']);
                    if ($producto) {
                        $this->inventario->registrarSalida(
                            $producto,
                            $item['cantidad'],
                            $factura->numero,
                            $userId,
                            'Venta',
                        );
                    }
                }
            }

            return $factura;
        });

        // Asiento contable automático (silencioso — no interrumpe si falla)
        try {
            if ($facturaCreada) {
                (new ContabilidadService())->asientoFactura($facturaCreada);
            }
        } catch (\Throwable) {}

        return redirect()->route('facturas.index')
            ->with('success', 'Factura creada correctamente.');
    }

    // ── SHOW ──────────────────────────────────────────────────

    public function show(Factura $factura)
    {
        $factura->load(['items.producto', 'cliente', 'usuario']);
        $empresa = Empresa::obtener();

        return view('facturas.show', compact('factura', 'empresa'));
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
        $userId  = Auth::id();
        $empresa = Empresa::obtener();

        DB::transaction(function () use ($request, $factura, $userId, $empresa) {
            // Revertir movimientos de inventario de los ítems anteriores
            foreach ($factura->items as $itemAnterior) {
                if ($itemAnterior->producto_id) {
                    $producto = Producto::find($itemAnterior->producto_id);
                    if ($producto) {
                        $this->inventario->registrarEntrada(
                            $producto,
                            $itemAnterior->cantidad,
                            $factura->numero,
                            $userId,
                            'Ajuste por edición de factura',
                        );
                    }
                }
            }

            $factura->items()->delete();

            $cliente = Cliente::findOrFail($request->cliente_id);
            $calc    = $this->documentos->calcularItems($request->items);
            $ret     = $this->documentos->calcularRetenciones(
                $calc['subtotal'],
                $calc['iva'],
                $cliente->retefuente_pct,
                $cliente->reteiva_pct,
                $cliente->reteica_pct,
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

                if (!empty($item['producto_id'])) {
                    $producto = Producto::find($item['producto_id']);
                    if ($producto) {
                        $this->inventario->registrarSalida(
                            $producto,
                            $item['cantidad'],
                            $factura->numero,
                            $userId,
                            'Venta',
                        );
                    }
                }
            }
        });

        // Regenerar asiento contable
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
        $userId = Auth::id();

        $factura->load('items.producto');

        foreach ($factura->items as $item) {
            if ($item->producto_id && $item->producto && ! $item->producto->es_servicio) {
                $this->inventario->registrarEntrada(
                    $item->producto,
                    $item->cantidad,
                    $factura->numero,
                    $userId,
                    'Anulación',
                );
            }
        }

        $factura->update(['estado' => 'anulada']);

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
        Factura::whereIn('id', $ids)->where('estado', '!=', 'anulada')
            ->update(['estado' => 'anulada']);

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
