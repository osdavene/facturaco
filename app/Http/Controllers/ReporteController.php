<?php
namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\FacturasExport;
use App\Exports\InventarioExport;
use App\Exports\CarteraExport;
use Maatwebsite\Excel\Facades\Excel;

class ReporteController extends Controller
{
    public function index()
    {
        $empresa = Empresa::obtener();

        // KPIs generales
        $ventasMes     = Factura::whereMonth('fecha_emision', now()->month)
                                ->whereYear('fecha_emision', now()->year)
                                ->where('estado', '!=', 'anulada')
                                ->sum('total');

        $ventasAnio    = Factura::whereYear('fecha_emision', now()->year)
                                ->where('estado', '!=', 'anulada')
                                ->sum('total');

        $carteraPendiente = Factura::whereIn('estado', ['emitida', 'vencida'])
                                   ->sum(DB::raw('total - total_pagado'));

        $facturasMes   = Factura::whereMonth('fecha_emision', now()->month)
                                ->whereYear('fecha_emision', now()->year)
                                ->count();

        $productosStock = Producto::whereColumn('stock_actual', '<=', 'stock_minimo')
                                  ->where('es_servicio', false)
                                  ->count();

        // Ventas por mes (últimos 6 meses)
        $ventasPorMes = collect();
        for ($i = 5; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $total = Factura::whereMonth('fecha_emision', $fecha->month)
                            ->whereYear('fecha_emision', $fecha->year)
                            ->where('estado', '!=', 'anulada')
                            ->sum('total');
            $ventasPorMes->push([
                'mes'   => $fecha->locale('es')->monthName,
                'total' => $total,
            ]);
        }

        // Top 5 clientes
        $topClientes = Factura::select('cliente_nombre', DB::raw('SUM(total) as total_compras'), DB::raw('COUNT(*) as num_facturas'))
                              ->where('estado', '!=', 'anulada')
                              ->groupBy('cliente_nombre')
                              ->orderByDesc('total_compras')
                              ->limit(5)
                              ->get();

        // Top 5 productos vendidos
        $topProductos = DB::table('factura_items')
                          ->join('facturas', 'facturas.id', '=', 'factura_items.factura_id')
                          ->where('facturas.estado', '!=', 'anulada')
                          ->select('factura_items.descripcion',
                                   DB::raw('SUM(factura_items.cantidad) as total_cantidad'),
                                   DB::raw('SUM(factura_items.total) as total_ventas'))
                          ->groupBy('factura_items.descripcion')
                          ->orderByDesc('total_ventas')
                          ->limit(5)
                          ->get();

        return view('reportes.index', compact(
            'empresa', 'ventasMes', 'ventasAnio', 'carteraPendiente',
            'facturasMes', 'productosStock', 'ventasPorMes',
            'topClientes', 'topProductos'
        ));
    }

    public function ventas(Request $request)
    {
        $empresa     = Empresa::obtener();
        $fechaDesde  = $request->fecha_desde ?? now()->startOfMonth()->format('Y-m-d');
        $fechaHasta  = $request->fecha_hasta ?? now()->format('Y-m-d');
        $estado      = $request->estado ?? '';

        $facturas = Factura::with('cliente')
            ->whereBetween('fecha_emision', [$fechaDesde, $fechaHasta])
            ->when($estado, fn($q) => $q->where('estado', $estado))
            ->where('estado', '!=', 'anulada')
            ->orderByDesc('fecha_emision')
            ->get();

        $totales = [
            'subtotal'   => $facturas->sum('subtotal'),
            'iva'        => $facturas->sum('iva'),
            'retefuente' => $facturas->sum('retefuente'),
            'reteica'    => $facturas->sum('reteica'),
            'total'      => $facturas->sum('total'),
            'count'      => $facturas->count(),
        ];

        return view('reportes.ventas', compact(
            'empresa', 'facturas', 'totales', 'fechaDesde', 'fechaHasta', 'estado'
        ));
    }

    public function inventario(Request $request)
    {
        $empresa    = Empresa::obtener();
        $filtro     = $request->filtro ?? 'todos';
        $categoria  = $request->categoria_id ?? '';

        $productos = Producto::with(['categoria', 'unidadMedida'])
            ->when($filtro === 'bajo_stock', fn($q) => $q->whereColumn('stock_actual', '<=', 'stock_minimo')->where('es_servicio', false))
            ->when($filtro === 'sin_stock',  fn($q) => $q->where('stock_actual', 0)->where('es_servicio', false))
            ->when($categoria, fn($q) => $q->where('categoria_id', $categoria))
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $valorInventario = $productos->where('es_servicio', false)
                                     ->sum(fn($p) => $p->stock_actual * $p->precio_compra);

        $categorias = \App\Models\Categoria::where('activo', true)->orderBy('nombre')->get();

        return view('reportes.inventario', compact(
            'empresa', 'productos', 'valorInventario',
            'filtro', 'categoria', 'categorias'
        ));
    }

    public function cartera(Request $request)
    {
        $empresa    = Empresa::obtener();
        $estado     = $request->estado ?? 'pendiente';

        $facturas = Factura::with('cliente')
            ->when($estado === 'pendiente', fn($q) => $q->whereIn('estado', ['emitida', 'vencida']))
            ->when($estado === 'vencida',   fn($q) => $q->where('estado', 'vencida'))
            ->when($estado === 'pagada',    fn($q) => $q->where('estado', 'pagada'))
            ->orderBy('fecha_vencimiento')
            ->get();

        $totales = [
            'total'    => $facturas->sum('total'),
            'pagado'   => $facturas->sum('total_pagado'),
            'pendiente'=> $facturas->sum(fn($f) => max(0, $f->total - $f->total_pagado)),
            'count'    => $facturas->count(),
        ];

        return view('reportes.cartera', compact(
            'empresa', 'facturas', 'totales', 'estado'
        ));
    }

    public function ventasPdf(Request $request)
    {
        $empresa    = Empresa::obtener();
        $fechaDesde = $request->fecha_desde ?? now()->startOfMonth()->format('Y-m-d');
        $fechaHasta = $request->fecha_hasta ?? now()->format('Y-m-d');

        $facturas = Factura::whereBetween('fecha_emision', [$fechaDesde, $fechaHasta])
                           ->where('estado', '!=', 'anulada')
                           ->orderByDesc('fecha_emision')
                           ->get();

        $totales = [
            'subtotal' => $facturas->sum('subtotal'),
            'iva'      => $facturas->sum('iva'),
            'total'    => $facturas->sum('total'),
            'count'    => $facturas->count(),
        ];

        $pdf = Pdf::loadView('reportes.pdf_ventas', compact(
            'empresa', 'facturas', 'totales', 'fechaDesde', 'fechaHasta'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream('reporte-ventas-'.now()->format('Ymd').'.pdf');
    }

    public function inventarioPdf(Request $request)
    {
        $empresa   = Empresa::obtener();
        $productos = Producto::with(['categoria', 'unidadMedida'])
                             ->where('activo', true)
                             ->orderBy('nombre')
                             ->get();

        $valorInventario = $productos->where('es_servicio', false)
                                     ->sum(fn($p) => $p->stock_actual * $p->precio_compra);

        $pdf = Pdf::loadView('reportes.pdf_inventario', compact(
            'empresa', 'productos', 'valorInventario'
        ))->setPaper('a4', 'portrait');

        return $pdf->stream('reporte-inventario-'.now()->format('Ymd').'.pdf');
    }

    public function carteraPdf(Request $request)
    {
        $empresa  = Empresa::obtener();
        $facturas = Factura::whereIn('estado', ['emitida', 'vencida'])
                           ->orderBy('fecha_vencimiento')
                           ->get();

        $totales = [
            'total'    => $facturas->sum('total'),
            'pendiente'=> $facturas->sum(fn($f) => max(0, $f->total - $f->total_pagado)),
        ];

        $pdf = Pdf::loadView('reportes.pdf_cartera', compact(
            'empresa', 'facturas', 'totales'
        ))->setPaper('a4', 'portrait');

        return $pdf->stream('reporte-cartera-'.now()->format('Ymd').'.pdf');
    }

    public function ventasExcel(Request $request)
{
    $fechaDesde = $request->fecha_desde ?? now()->startOfMonth()->format('Y-m-d');
    $fechaHasta = $request->fecha_hasta ?? now()->format('Y-m-d');
    $estado     = $request->estado ?? null;

    return Excel::download(
        new FacturasExport($fechaDesde, $fechaHasta, $estado),
        'ventas-' . now()->format('Ymd') . '.xlsx'
    );
}

public function inventarioExcel()
{
    return Excel::download(
        new InventarioExport(),
        'inventario-' . now()->format('Ymd') . '.xlsx'
    );
}

public function carteraExcel()
{
    return Excel::download(
        new CarteraExport(),
        'cartera-' . now()->format('Ymd') . '.xlsx'
    );
}
}