<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Factura;
use App\Exports\ImpuestosExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ImpuestosController extends Controller
{
    public function index(Request $request)
    {
        $empresa     = Empresa::obtener();
        $periodoTipo = $request->periodo  ?? 'bimestral';
        $anio        = $request->anio     ?? now()->year;
        $bimestre    = $request->bimestre ?? ceil(now()->month / 2);

        [$fechaDesde, $fechaHasta] = $this->calcularRango($periodoTipo, $anio, $bimestre, $request);

        $datos = $this->obtenerDatos($fechaDesde, $fechaHasta);

        $aniosDisponibles = Factura::selectRaw('EXTRACT(YEAR FROM fecha_emision) as anio')
            ->groupBy('anio')
            ->orderByDesc('anio')
            ->pluck('anio');

        return view('impuestos.index', array_merge($datos, compact(
            'empresa', 'fechaDesde', 'fechaHasta',
            'anio', 'bimestre', 'periodoTipo', 'aniosDisponibles'
        )));
    }

    public function pdf(Request $request)
    {
        $empresa     = Empresa::obtener();
        $periodoTipo = $request->periodo  ?? 'bimestral';
        $anio        = $request->anio     ?? now()->year;
        $bimestre    = $request->bimestre ?? ceil(now()->month / 2);

        [$fechaDesde, $fechaHasta] = $this->calcularRango($periodoTipo, $anio, $bimestre, $request);

        $datos = $this->obtenerDatos($fechaDesde, $fechaHasta);

        $pdf = Pdf::loadView('impuestos.pdf', array_merge($datos, compact(
            'empresa', 'fechaDesde', 'fechaHasta', 'periodoTipo', 'anio', 'bimestre'
        )))->setPaper('a4', 'portrait');

        return $pdf->stream('impuestos-dian-'.$anio.'.pdf');
    }

    public function excel(Request $request)
    {
        $periodoTipo = $request->periodo  ?? 'bimestral';
        $anio        = $request->anio     ?? now()->year;
        $bimestre    = $request->bimestre ?? ceil(now()->month / 2);

        [$fechaDesde, $fechaHasta] = $this->calcularRango($periodoTipo, $anio, $bimestre, $request);

        return Excel::download(
            new ImpuestosExport($fechaDesde, $fechaHasta),
            'impuestos-dian-'.$anio.'.xlsx'
        );
    }

    // ── Datos compartidos entre index() y pdf() ───────────────

    private function obtenerDatos(string $fechaDesde, string $fechaHasta): array
    {
        $facturas = Factura::whereBetween('fecha_emision', [$fechaDesde, $fechaHasta])
            ->whereNotIn('estado', ['anulada', 'borrador'])
            ->get();

        $resumen = [
            'num_facturas'   => $facturas->count(),
            'base_gravable'  => $facturas->sum('subtotal'),
            'total_iva'      => $facturas->sum('iva'),
            'total_rete'     => $facturas->sum('retefuente'),
            'total_reteiva'  => $facturas->sum('reteiva'),
            'total_reteica'  => $facturas->sum('reteica'),
            'total_ventas'   => $facturas->sum('total'),
        ];

        $ivaPorTasa = DB::table('factura_items')
            ->join('facturas', 'facturas.id', '=', 'factura_items.factura_id')
            ->whereBetween('facturas.fecha_emision', [$fechaDesde, $fechaHasta])
            ->whereNotIn('facturas.estado', ['anulada', 'borrador'])
            ->whereIn('factura_items.factura_id', $facturas->pluck('id'))
            ->select(
                'factura_items.iva_pct',
                DB::raw('SUM(factura_items.subtotal) as base'),
                DB::raw('SUM(factura_items.iva) as iva'),
                DB::raw('COUNT(*) as num_items')
            )
            ->groupBy('factura_items.iva_pct')
            ->orderByDesc('factura_items.iva_pct')
            ->get();

        $ventasPorMes = Factura::whereBetween('fecha_emision', [$fechaDesde, $fechaHasta])
            ->whereNotIn('estado', ['anulada', 'borrador'])
            ->select(
                DB::raw("TO_CHAR(fecha_emision, 'YYYY-MM') as mes"),
                DB::raw('SUM(subtotal) as base'),
                DB::raw('SUM(iva) as iva'),
                DB::raw('SUM(retefuente) as retefuente'),
                DB::raw('SUM(reteiva) as reteiva'),
                DB::raw('SUM(reteica) as reteica'),
                DB::raw('SUM(total) as total'),
                DB::raw('COUNT(*) as num_facturas')
            )
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $retencionesPorCliente = Factura::whereBetween('fecha_emision', [$fechaDesde, $fechaHasta])
            ->whereNotIn('estado', ['anulada', 'borrador'])
            ->where(function ($q) {
                $q->where('retefuente', '>', 0)
                  ->orWhere('reteiva', '>', 0)
                  ->orWhere('reteica', '>', 0);
            })
            ->select(
                'cliente_nombre',
                'cliente_documento',
                DB::raw('SUM(subtotal) as base'),
                DB::raw('SUM(iva) as iva'),
                DB::raw('SUM(retefuente) as retefuente'),
                DB::raw('SUM(reteiva) as reteiva'),
                DB::raw('SUM(reteica) as reteica'),
                DB::raw('COUNT(*) as facturas')
            )
            ->groupBy('cliente_nombre', 'cliente_documento')
            ->orderByDesc('retefuente')
            ->get()
            ->map(function ($r) {
                $r->total_retenciones = $r->retefuente + $r->reteiva + $r->reteica;
                return $r;
            });

        $topClientesIva = Factura::whereBetween('fecha_emision', [$fechaDesde, $fechaHasta])
            ->whereNotIn('estado', ['anulada', 'borrador'])
            ->select(
                'cliente_nombre',
                DB::raw('SUM(subtotal) as base'),
                DB::raw('SUM(iva) as iva'),
                DB::raw('COUNT(*) as facturas')
            )
            ->groupBy('cliente_nombre')
            ->orderByDesc('iva')
            ->limit(10)
            ->get();

        return compact(
            'facturas', 'resumen', 'ivaPorTasa',
            'ventasPorMes', 'retencionesPorCliente', 'topClientesIva'
        );
    }

    // ── Cálculo de rango de fechas ────────────────────────────

    private function calcularRango(string $tipo, int $anio, int $bimestre, Request $request): array
    {
        if ($tipo === 'bimestral') {
            $mesInicio = ($bimestre - 1) * 2 + 1;
            $mesFin    = $mesInicio + 1;
            $desde     = "$anio-".str_pad($mesInicio, 2, '0', STR_PAD_LEFT)."-01";
            $hasta     = Carbon::create($anio, $mesFin, 1)->endOfMonth()->format('Y-m-d');
        } elseif ($tipo === 'mensual') {
            $mes   = $request->mes ?? now()->month;
            $desde = "$anio-".str_pad($mes, 2, '0', STR_PAD_LEFT)."-01";
            $hasta = Carbon::create($anio, $mes, 1)->endOfMonth()->format('Y-m-d');
        } else {
            $desde = "$anio-01-01";
            $hasta = "$anio-12-31";
        }

        return [$desde, $hasta];
    }
}
