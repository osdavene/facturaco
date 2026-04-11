<?php
namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\FacturaItem;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\FacturaMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class FacturaController extends Controller
{
    // ── INDEX ─────────────────────────────────────────────────

    public function index(Request $request)
    {
        $facturas = Factura::with('cliente')
            ->when($request->buscar, function($q) use ($request) {
                $q->where('numero',         'like', '%'.$request->buscar.'%')
                  ->orWhere('cliente_nombre','like', '%'.$request->buscar.'%');
            })
            ->when($request->estado,      fn($q) => $q->where('estado', $request->estado))
            ->when($request->fecha_desde, fn($q) => $q->whereDate('fecha_emision', '>=', $request->fecha_desde))
            ->when($request->fecha_hasta, fn($q) => $q->whereDate('fecha_emision', '<=', $request->fecha_hasta))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $totales = DB::selectOne("
            SELECT
                COUNT(*)::int                                                          AS total,
                SUM(CASE WHEN estado='pagada'  THEN 1 ELSE 0 END)::int               AS pagadas,
                SUM(CASE WHEN estado='emitida' THEN 1 ELSE 0 END)::int               AS emitidas,
                SUM(CASE WHEN estado='vencida' THEN 1 ELSE 0 END)::int               AS vencidas,
                COALESCE(SUM(CASE WHEN estado != 'anulada' THEN total ELSE 0 END),0)  AS monto_total,
                COALESCE(SUM(CASE WHEN estado IN ('emitida','vencida')
                               THEN (total - total_pagado) ELSE 0 END),0)             AS cartera
            FROM facturas
            WHERE deleted_at IS NULL
        ");

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

    public function store(Request $request)
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

        $userId  = Auth::id();
        $empresa = Empresa::obtener();

        DB::transaction(function() use ($request, $userId, $empresa) {
            $cliente     = Cliente::findOrFail($request->cliente_id);
            $prefijo     = $empresa->prefijo_factura ?? 'FE';
            $consecutivo = Factura::siguienteConsecutivo($prefijo);

            $subtotal  = 0;
            $totalIva  = 0;
            $totalDesc = 0;

            foreach ($request->items as $item) {
                $cant    = floatval($item['cantidad']);
                $precio  = floatval($item['precio_unitario']);
                $descPct = floatval($item['descuento_pct'] ?? 0);
                $ivaPct  = floatval($item['iva_pct'] ?? 19);

                $sub  = $cant * $precio;
                $desc = $sub * ($descPct / 100);
                $base = $sub - $desc;
                $iva  = $base * ($ivaPct / 100);

                $subtotal  += $base;
                $totalIva  += $iva;
                $totalDesc += $desc;
            }

            $retefuente = $subtotal  * ($cliente->retefuente_pct / 100);
            $reteiva    = $totalIva  * ($cliente->reteiva_pct    / 100);
            $reteica    = $subtotal  * ($cliente->reteica_pct    / 100);
            $total      = $subtotal + $totalIva - $retefuente - $reteiva - $reteica;

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
                'subtotal'          => $subtotal,
                'descuento'         => $totalDesc,
                'base_iva'          => $subtotal,
                'iva'               => $totalIva,
                'retefuente'        => $retefuente,
                'reteiva'           => $reteiva,
                'reteica'           => $reteica,
                'total'             => $total,
                'total_pagado'      => 0,
                'estado'            => $request->estado ?? 'borrador',
                'forma_pago'        => $request->forma_pago ?? 'contado',
                'plazo_pago'        => $request->plazo_pago ?? 0,
                'observaciones'     => $request->observaciones,
                'user_id'           => $userId,
            ]);

            foreach ($request->items as $i => $item) {
                $cant    = floatval($item['cantidad']);
                $precio  = floatval($item['precio_unitario']);
                $descPct = floatval($item['descuento_pct'] ?? 0);
                $ivaPct  = floatval($item['iva_pct'] ?? 19);

                $sub  = $cant * $precio;
                $desc = $sub * ($descPct / 100);
                $base = $sub - $desc;
                $iva  = $base * ($ivaPct / 100);
                $tot  = $base + $iva;

                FacturaItem::create([
                    'factura_id'      => $factura->id,
                    'producto_id'     => $item['producto_id'] ?? null,
                    'codigo'          => $item['codigo']      ?? 'SIN-COD',
                    'descripcion'     => $item['descripcion'],
                    'unidad'          => $item['unidad']      ?? 'UN',
                    'cantidad'        => $cant,
                    'precio_unitario' => $precio,
                    'descuento_pct'   => $descPct,
                    'descuento'       => $desc,
                    'subtotal'        => $base,
                    'iva_pct'         => $ivaPct,
                    'iva'             => $iva,
                    'total'           => $tot,
                    'orden'           => $i,
                ]);

                if (!empty($item['producto_id'])) {
                    $producto = Producto::find($item['producto_id']);
                    if ($producto && !$producto->es_servicio) {
                        $producto->decrement('stock_actual', $cant);
                    }
                }
            }
        });

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

    public function update(Request $request, Factura $factura)
    {
        return redirect()->route('facturas.show', $factura)
            ->with('success', 'Factura actualizada.');
    }

    // ── DESTROY ───────────────────────────────────────────────

    public function destroy(Factura $factura)
    {
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
        $count = Factura::whereIn('id', $ids)->where('estado', 'borrador')->count();
        Factura::whereIn('id', $ids)->where('estado', 'borrador')->delete();
        return redirect()->route('facturas.index')
            ->with('success', "{$count} factura(s) eliminada(s) correctamente.");
    }

    // ── CAMBIAR ESTADO ────────────────────────────────────────

    public function cambiarEstado(Request $request, Factura $factura)
    {
        $request->validate([
            'estado' => 'required|in:borrador,emitida,pagada,vencida,anulada'
        ]);

        $factura->update(['estado' => $request->estado]);
        return back()->with('success', 'Estado actualizado correctamente.');
    }

    // ── PDF ───────────────────────────────────────────────────

    public function pdf(Factura $factura)
    {
        $factura->load(['items', 'cliente']);
        $empresa = Empresa::obtener();

        $qrData = implode("\n", [
            'Factura: ' . $factura->numero,
            'NIT: '     . $empresa->nit_formateado,
            'Cliente: ' . $factura->cliente_nombre,
            'Fecha: '   . $factura->fecha_emision->format('d/m/Y'),
            'Total: $'  . number_format($factura->total, 0, ',', '.'),
            'Estado: '  . strtoupper($factura->estado),
        ]);

        $qr       = \Endroid\QrCode\QrCode::create($qrData)->setSize(120)->setMargin(4);
        $writer   = new \Endroid\QrCode\Writer\PngWriter();
        $result   = $writer->write($qr);
        $qrBase64 = base64_encode($result->getString());

        $pdf = Pdf::loadView('facturas.pdf', compact('factura', 'empresa', 'qrBase64'))
                  ->setPaper('a4', 'portrait');

        return $pdf->stream('factura-'.$factura->numero.'.pdf');
    }

    // ── FORMULARIO ENVIAR EMAIL ───────────────────────────────

    public function formEnviar(Factura $factura)
    {
        $factura->load(['items', 'cliente']);
        $empresa = Empresa::obtener();
        return view('facturas.enviar', compact('factura', 'empresa'));
    }

    // ── ENVIAR EMAIL ──────────────────────────────────────────

    public function enviar(Request $request, Factura $factura)
    {
        $request->validate([
            'email'   => 'required|email',
            'mensaje' => 'nullable|string|max:500',
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email'    => 'El correo electrónico no es válido.',
        ]);

        $empresa = Empresa::obtener();
        $factura->load(['items', 'cliente']);

        try {
            Mail::to($request->email)
                ->send(new FacturaMail($factura, $empresa, $request->mensaje ?? ''));

            if ($factura->estado === 'borrador') {
                $factura->update(['estado' => 'emitida']);
            }

            return redirect()->route('facturas.show', $factura)
                ->with('success', 'Factura enviada correctamente a ' . $request->email);

        } catch (\Exception $e) {
            return back()
                ->with('error', 'No se pudo enviar el correo. Verifica la configuración de mail.')
                ->withInput();
        }
    }
}