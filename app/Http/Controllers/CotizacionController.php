<?php
namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\CotizacionItem;
use App\Models\Factura;
use App\Models\FacturaItem;
use App\Models\Cliente;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class CotizacionController extends Controller
{
    public function index(Request $request)
    {
        $cotizaciones = Cotizacion::with('cliente')
            ->when($request->buscar, function($q) use ($request) {
                $q->where('numero',         'like', '%'.$request->buscar.'%')
                  ->orWhere('cliente_nombre','like', '%'.$request->buscar.'%');
            })
            ->when($request->estado, fn($q) => $q->where('estado', $request->estado))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $totales = [
            'total'      => Cotizacion::count(),
            'borrador'   => Cotizacion::where('estado', 'borrador')->count(),
            'enviada'    => Cotizacion::where('estado', 'enviada')->count(),
            'aceptada'   => Cotizacion::where('estado', 'aceptada')->count(),
            'convertida' => Cotizacion::where('estado', 'convertida')->count(),
        ];

        return view('cotizaciones.index', compact('cotizaciones', 'totales'));
    }

    public function create()
    {
        $consecutivo = Cotizacion::siguienteConsecutivo();
        $empresa     = Empresa::obtener();
        return view('cotizaciones.create', compact('consecutivo', 'empresa'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_nombre'   => 'required|string|max:255',
            'fecha_emision'    => 'required|date',
            'fecha_vencimiento'=> 'required|date|after_or_equal:fecha_emision',
            'items'            => 'required|array|min:1',
            'items.*.descripcion'    => 'required|string',
            'items.*.cantidad'       => 'required|numeric|min:0.001',
            'items.*.precio_unitario'=> 'required|numeric|min:0',
        ]);

        $userId = auth()->id();

        DB::transaction(function() use ($request, $userId) {
            $consecutivo = Cotizacion::siguienteConsecutivo();

            $subtotal = 0; $totalIva = 0; $totalDesc = 0;

            foreach ($request->items as $item) {
                $cant    = floatval($item['cantidad']);
                $precio  = floatval($item['precio_unitario']);
                $descPct = floatval($item['descuento_pct'] ?? 0);
                $ivaPct  = floatval($item['iva_pct'] ?? 19);
                $sub     = $cant * $precio;
                $desc    = $sub * ($descPct / 100);
                $base    = $sub - $desc;
                $iva     = $base * ($ivaPct / 100);
                $subtotal  += $base;
                $totalIva  += $iva;
                $totalDesc += $desc;
            }

            $cotizacion = Cotizacion::create([
                'numero'            => $consecutivo['numero'],
                'consecutivo'       => $consecutivo['consecutivo'],
                'cliente_id'        => $request->cliente_id,
                'cliente_nombre'    => strtoupper($request->cliente_nombre),
                'cliente_documento' => $request->cliente_documento,
                'cliente_email'     => $request->cliente_email,
                'cliente_telefono'  => $request->cliente_telefono,
                'cliente_direccion' => $request->cliente_direccion,
                'fecha_emision'     => $request->fecha_emision,
                'fecha_vencimiento' => $request->fecha_vencimiento,
                'subtotal'          => $subtotal,
                'descuento'         => $totalDesc,
                'iva'               => $totalIva,
                'total'             => $subtotal + $totalIva,
                'estado'            => $request->estado ?? 'borrador',
                'forma_pago'        => $request->forma_pago ?? 'contado',
                'plazo_pago'        => $request->plazo_pago ?? 0,
                'observaciones'     => $request->observaciones,
                'terminos'          => $request->terminos,
                'user_id'           => $userId,
            ]);

            foreach ($request->items as $i => $item) {
                $cant    = floatval($item['cantidad']);
                $precio  = floatval($item['precio_unitario']);
                $descPct = floatval($item['descuento_pct'] ?? 0);
                $ivaPct  = floatval($item['iva_pct'] ?? 19);
                $sub     = $cant * $precio;
                $desc    = $sub * ($descPct / 100);
                $base    = $sub - $desc;
                $iva     = $base * ($ivaPct / 100);

                CotizacionItem::create([
                    'cotizacion_id'   => $cotizacion->id,
                    'producto_id'     => $item['producto_id'] ?? null,
                    'codigo'          => $item['codigo'] ?? null,
                    'descripcion'     => strtoupper($item['descripcion']),
                    'unidad'          => $item['unidad'] ?? 'UN',
                    'cantidad'        => $cant,
                    'precio_unitario' => $precio,
                    'descuento_pct'   => $descPct,
                    'descuento'       => $desc,
                    'subtotal'        => $base,
                    'iva_pct'         => $ivaPct,
                    'iva'             => $iva,
                    'total'           => $base + $iva,
                    'orden'           => $i,
                ]);
            }
        });

        return redirect()->route('cotizaciones.index')
            ->with('success', 'Cotización creada correctamente.');
    }

    public function show(Cotizacion $cotizacion)
    {
        $cotizacion->load(['items.producto', 'cliente', 'usuario', 'factura']);
        return view('cotizaciones.show', compact('cotizacion'));
    }

    public function cambiarEstado(Request $request, Cotizacion $cotizacion)
    {
        $request->validate([
            'estado' => 'required|in:borrador,enviada,aceptada,rechazada'
        ]);
        $cotizacion->update(['estado' => $request->estado]);
        return back()->with('success', 'Estado actualizado.');
    }

    // ⭐ CONVERTIR A FACTURA CON UN CLIC
    public function convertir(Cotizacion $cotizacion)
    {
        if ($cotizacion->estado === 'convertida') {
            return back()->with('error', 'Esta cotización ya fue convertida.');
        }

        $userId = auth()->id();

        DB::transaction(function() use ($cotizacion, $userId) {
            // Crear factura a partir de la cotización
            $consecutivo = \App\Models\Factura::siguienteConsecutivo();

            $factura = Factura::create([
                'numero'             => $consecutivo['numero'],
                'consecutivo'        => $consecutivo['consecutivo'],
                'tipo'               => 'factura',
                'cliente_id'         => $cotizacion->cliente_id,
                'cliente_nombre'     => $cotizacion->cliente_nombre,
                'cliente_documento'  => $cotizacion->cliente_documento ?? '',
                'cliente_email'      => $cotizacion->cliente_email,
                'cliente_direccion'  => $cotizacion->cliente_direccion,
                'fecha_emision'      => now(),
                'fecha_vencimiento'  => now()->addDays($cotizacion->plazo_pago ?: 30),
                'subtotal'           => $cotizacion->subtotal,
                'descuento'          => $cotizacion->descuento,
                'iva'                => $cotizacion->iva,
                'retefuente'         => 0,
                'reteica'            => 0,
                'total'              => $cotizacion->total,
                'total_pagado'       => 0,
                'forma_pago'         => $cotizacion->forma_pago,
                'plazo_pago'         => $cotizacion->plazo_pago,
                'estado'             => 'emitida',
                'observaciones'      => 'GENERADA DESDE COTIZACIÓN '.$cotizacion->numero,
                'user_id'            => $userId,
            ]);

            // Copiar items
            foreach ($cotizacion->items as $item) {
                FacturaItem::create([
                    'factura_id'      => $factura->id,
                    'producto_id'     => $item->producto_id,
                    'codigo'          => $item->codigo,
                    'descripcion'     => $item->descripcion,
                    'cantidad'        => $item->cantidad,
                    'precio_unitario' => $item->precio_unitario,
                    'descuento_pct'   => $item->descuento_pct,
                    'descuento'       => $item->descuento,
                    'subtotal'        => $item->subtotal,
                    'iva_pct'         => $item->iva_pct,
                    'iva'             => $item->iva,
                    'total'           => $item->total,
                ]);
            }

            // Marcar cotización como convertida
            $cotizacion->update([
                'estado'     => 'convertida',
                'factura_id' => $factura->id,
            ]);
        });

        return redirect()->route('cotizaciones.show', $cotizacion)
            ->with('success', '¡Cotización convertida a factura exitosamente!');
    }

    public function pdf(Cotizacion $cotizacion)
    {
        $cotizacion->load(['items', 'usuario']);
        $empresa = Empresa::obtener();

        $qrData = implode(' | ', [
            'COTIZACIÓN: ' . $cotizacion->numero,
            'CLIENTE: '    . $cotizacion->cliente_nombre,
            'TOTAL: $'     . number_format($cotizacion->total, 0, ',', '.'),
            'VÁLIDA: '     . $cotizacion->fecha_vencimiento->format('d/m/Y'),
        ]);

        $qr       = \Endroid\QrCode\QrCode::create($qrData)
                        ->setSize(100)->setMargin(3);
        $writer   = new \Endroid\QrCode\Writer\PngWriter();
        $result   = $writer->write($qr);
        $qrBase64 = base64_encode($result->getString());

        $pdf = Pdf::loadView('cotizaciones.pdf',
                    compact('cotizacion', 'empresa', 'qrBase64'))
                ->setPaper('a4', 'portrait');

        return $pdf->stream('cotizacion-' . $cotizacion->numero . '.pdf');
    }

    public function destroy(Cotizacion $cotizacion)
    {
        $cotizacion->delete();
        return redirect()->route('cotizaciones.index')
            ->with('success', 'Cotización eliminada.');
    }
}