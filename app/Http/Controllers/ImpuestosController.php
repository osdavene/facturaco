<?php
namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\ImpuestosExport;
use Maatwebsite\Excel\Facades\Excel;

class ImpuestosController extends Controller
{
    public function index(Request $request)
    {
        $empresa = Empresa::obtener();

        // Período seleccionado
        $anio       = $request->anio      ?? now()->year;
        $bimestre   = $request->bimestre  ?? ceil(now()->month / 2);
        $periodoTipo= $request->periodo   ?? 'bimestral';

        // Calcular rango de fechas
        [$fechaDesde, $fechaHasta] = $this->calcularRango($periodoTipo, $anio, $bimestre, $request);

        // Facturas del período
        $facturas = Factura::whereBetween('fecha_emision', [$fechaDesde, $fechaHasta])
                           ->whereNotIn('estado', ['anulada', 'borrador'])
                           ->get();

        // Totales
        $resumen = [
            'num_facturas'  => $facturas->count(),
            'base_gravable' => $facturas->sum('subtotal'),
            'total_iva'     => $facturas->sum('iva'),
            'total_rete'    => $facturas->sum('retefuente'),
            'total_reteica' => $facturas->sum('reteica'),
            'total_ventas'  => $facturas->sum('total'),
        ];

        // IVA por tasa
        $ivaPorTasa = DB::table('factura_items')
            ->join('facturas', 'facturas.id', '=', 'factura_items.factura_id')
            ->whereBetween('facturas.fecha_emision', [$fechaDesde, $fechaHasta])
            ->whereNotIn('facturas.estado', ['anulada','borrador'])
            ->select(
                'factura_items.iva_pct',
                DB::raw('SUM(factura_items.subtotal) as base'),
                DB::raw('SUM(factura_items.iva) as iva'),
                DB::raw('COUNT(*) as num_items')
            )
            ->groupBy('factura_items.iva_pct')
            ->orderByDesc('factura_items.iva_pct')
            ->get();

        // Ventas por mes en el período
        $ventasPorMes = Factura::whereBetween('fecha_emision', [$fechaDesde, $fechaHasta])
            ->whereNotIn('estado', ['anulada','borrador'])
            ->select(
                DB::raw("TO_CHAR(fecha_emision, 'YYYY-MM') as mes"),
                DB::raw('SUM(subtotal) as base'),
                DB::raw('SUM(iva) as iva'),
                DB::raw('SUM(retefuente) as retefuente'),
                DB::raw('SUM(total) as total'),
                DB::raw('COUNT(*) as num_facturas')
            )
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        // Top clientes con más IVA generado
        $topClientesIva = Factura::whereBetween('fecha_emision', [$fechaDesde, $fechaHasta])
            ->whereNotIn('estado', ['anulada','borrador'])
            ->select('cliente_nombre',
                DB::raw('SUM(subtotal) as base'),
                DB::raw('SUM(iva) as iva'),
                DB::raw('COUNT(*) as facturas'))
            ->groupBy('cliente_nombre')
            ->orderByDesc('iva')
            ->limit(10)
            ->get();

        // Años disponibles para filtro
        $aniosDisponibles = Factura::selectRaw('EXTRACT(YEAR FROM fecha_emision) as anio')
            ->groupBy('anio')
            ->orderByDesc('anio')
            ->pluck('anio');

        return view('impuestos.index', compact(
            'empresa', 'resumen', 'ivaPorTasa', 'ventasPorMes',
            'topClientesIva', 'fechaDesde', 'fechaHasta',
            'anio', 'bimestre', 'periodoTipo', 'aniosDisponibles', 'facturas'
        ));
    }

    public function pdf(Request $request)
    {
        $empresa    = Empresa::obtener();
        $anio       = $request->anio     ?? now()->year;
        $bimestre   = $request->bimestre ?? ceil(now()->month / 2);
        $periodoTipo= $request->periodo  ?? 'bimestral';

        [$fechaDesde, $fechaHasta] = $this->calcularRango($periodoTipo, $anio, $bimestre, $request);

        $facturas = Factura::whereBetween('fecha_emision', [$fechaDesde, $fechaHasta])
                           ->whereNotIn('estado', ['anulada','borrador'])
                           ->orderBy('fecha_emision')
                           ->get();

        $resumen = [
            'num_facturas'  => $facturas->count(),
            'base_gravable' => $facturas->sum('subtotal'),
            'total_iva'     => $facturas->sum('iva'),
            'total_rete'    => $facturas->sum('retefuente'),
            'total_reteica' => $facturas->sum('reteica'),
            'total_ventas'  => $facturas->sum('total'),
        ];

        $ivaPorTasa = DB::table('factura_items')
            ->join('facturas', 'facturas.id', '=', 'factura_items.factura_id')
            ->whereBetween('facturas.fecha_emision', [$fechaDesde, $fechaHasta])
            ->whereNotIn('facturas.estado', ['anulada','borrador'])
            ->select(
                'factura_items.iva_pct',
                DB::raw('SUM(factura_items.subtotal) as base'),
                DB::raw('SUM(factura_items.iva) as iva')
            )
            ->groupBy('factura_items.iva_pct')
            ->orderByDesc('factura_items.iva_pct')
            ->get();

        $pdf = Pdf::loadView('impuestos.pdf',
                    compact('empresa','facturas','resumen','ivaPorTasa',
                            'fechaDesde','fechaHasta','periodoTipo','anio','bimestre'))
                  ->setPaper('a4', 'portrait');

        return $pdf->stream('impuestos-dian-'.$anio.'.pdf');
    }

    private function calcularRango($tipo, $anio, $bimestre, $request): array
    {
        if ($tipo === 'bimestral') {
            $mesInicio = ($bimestre - 1) * 2 + 1;
            $mesFin    = $mesInicio + 1;
            $desde     = "$anio-" . str_pad($mesInicio, 2, '0', STR_PAD_LEFT) . "-01";
            $hasta     = \Carbon\Carbon::create($anio, $mesFin, 1)->endOfMonth()->format('Y-m-d');
        } elseif ($tipo === 'mensual') {
            $mes   = $request->mes ?? now()->month;
            $desde = "$anio-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-01";
            $hasta = \Carbon\Carbon::create($anio, $mes, 1)->endOfMonth()->format('Y-m-d');
        } else {
            // Anual
            $desde = "$anio-01-01";
            $hasta = "$anio-12-31";
        }
        return [$desde, $hasta];
    }

    public function excel(Request $request)
{
    $anio       = $request->anio     ?? now()->year;
    $bimestre   = $request->bimestre ?? ceil(now()->month / 2);
    $periodoTipo= $request->periodo  ?? 'bimestral';

    [$fechaDesde, $fechaHasta] = $this->calcularRango($periodoTipo, $anio, $bimestre, $request);

    return Excel::download(
        new ImpuestosExport($fechaDesde, $fechaHasta),
        'impuestos-dian-' . $anio . '.xlsx'
    );
}

}