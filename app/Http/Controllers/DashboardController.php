<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\Empresa;
use App\Models\Factura;
use App\Models\FacturaItem;
use App\Models\OrdenCompra;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $empresa = Empresa::obtener();

        [$ventas, $deltas, $series] = $this->metricas();
        $productosStockBajo = $this->stockBajo();
        $cotizacionesPend   = $this->cotizacionesPendientes();
        $ordenesPend        = $this->ordenesPendientes();

        return view('dashboard', array_merge(
            compact('empresa', 'productosStockBajo', 'cotizacionesPend', 'ordenesPend'),
            $ventas,
            $deltas,
            $series,
        ));
    }

    private function metricas(): array
    {
        try {
            $mes         = now()->month;
            $anio        = now()->year;
            $mesAnterior = now()->subMonth();

            $ventasHoy   = Factura::whereDate('fecha_emision', today())
                            ->where('estado', '!=', 'anulada')->sum('total');
            $ventasMes   = Factura::whereMonth('fecha_emision', $mes)
                            ->whereYear('fecha_emision', $anio)
                            ->where('estado', '!=', 'anulada')->sum('total');
            $ventasAno   = Factura::whereYear('fecha_emision', $anio)
                            ->where('estado', '!=', 'anulada')->sum('total');
            $cartera     = Factura::whereIn('estado', ['emitida', 'vencida'])
                            ->sum(DB::raw('total - total_pagado'));
            $facturasMes = Factura::whereMonth('fecha_emision', $mes)
                            ->whereYear('fecha_emision', $anio)->count();

            $facturasVencidas = Factura::where('estado', 'vencida')->count();
            $ultimasFacturas  = Factura::orderByDesc('created_at')->limit(6)->get();

            $ventasPorMes = collect();
            for ($i = 11; $i >= 0; $i--) {
                $fecha     = now()->subMonths($i);
                $fechaAnio = now()->subMonths($i)->subYear();
                $ventasPorMes->push([
                    'mes'        => $fecha->locale('es')->isoFormat('MMM'),
                    'anio'       => $fecha->year,
                    'total'      => (float) Factura::whereMonth('fecha_emision', $fecha->month)
                                    ->whereYear('fecha_emision', $fecha->year)
                                    ->where('estado', '!=', 'anulada')->sum('total'),
                    'total_anio' => (float) Factura::whereMonth('fecha_emision', $fechaAnio->month)
                                    ->whereYear('fecha_emision', $fechaAnio->year)
                                    ->where('estado', '!=', 'anulada')->sum('total'),
                ]);
            }

            $ventasSemana = collect();
            for ($i = 6; $i >= 0; $i--) {
                $dia = now()->subDays($i);
                $ventasSemana->push([
                    'dia'   => $dia->locale('es')->isoFormat('ddd D'),
                    'total' => (float) Factura::whereDate('fecha_emision', $dia)
                                ->where('estado', '!=', 'anulada')->sum('total'),
                ]);
            }

            $topClientes = Factura::select('cliente_nombre', DB::raw('SUM(total) as total_mes'))
                            ->whereMonth('fecha_emision', $mes)->whereYear('fecha_emision', $anio)
                            ->where('estado', '!=', 'anulada')
                            ->groupBy('cliente_nombre')->orderByDesc('total_mes')->limit(5)->get();

            $topProductos = FacturaItem::select(
                                'descripcion',
                                DB::raw('SUM(cantidad) as total_qty'),
                                DB::raw('SUM(total) as total_valor'),
                            )
                            ->whereHas('factura', fn($q) =>
                                $q->whereMonth('fecha_emision', $mes)
                                  ->whereYear('fecha_emision', $anio)
                                  ->where('estado', '!=', 'anulada')
                            )
                            ->groupBy('descripcion')->orderByDesc('total_valor')->limit(5)->get();

            $ventasPorEstado = Factura::select('estado', DB::raw('COUNT(*) as cantidad'))
                                ->whereMonth('fecha_emision', $mes)->whereYear('fecha_emision', $anio)
                                ->groupBy('estado')->get()->keyBy('estado');

            $ventasMesAnterior  = Factura::whereMonth('fecha_emision', $mesAnterior->month)
                                    ->whereYear('fecha_emision', $mesAnterior->year)
                                    ->where('estado', '!=', 'anulada')->sum('total');
            $ventasAyer         = Factura::whereDate('fecha_emision', today()->subDay())
                                    ->where('estado', '!=', 'anulada')->sum('total');
            $ventasAnoAnterior  = Factura::whereYear('fecha_emision', $anio - 1)
                                    ->where('estado', '!=', 'anulada')->sum('total');
            $facturasMesAnterior = Factura::whereMonth('fecha_emision', $mesAnterior->month)
                                    ->whereYear('fecha_emision', $mesAnterior->year)->count();

            $deltaHoy    = $this->delta($ventasHoy, $ventasAyer);
            $deltaMes    = $this->delta($ventasMes, $ventasMesAnterior);
            $deltaAno    = $this->delta($ventasAno, $ventasAnoAnterior);
            $deltaTicket = $this->delta($facturasMes, $facturasMesAnterior);
            $ticketPromedio = $facturasMes > 0 ? round($ventasMes / $facturasMes) : 0;

            $ventas = compact(
                'ventasHoy', 'ventasMes', 'ventasAno', 'cartera',
                'facturasMes', 'facturasVencidas', 'ultimasFacturas',
                'topClientes', 'topProductos', 'ventasPorEstado', 'ticketPromedio',
            );
            $deltas = compact('deltaHoy', 'deltaMes', 'deltaAno', 'deltaTicket');
            $series = compact('ventasPorMes', 'ventasSemana');
        } catch (\Throwable) {
            $ventas = [
                'ventasHoy' => 0, 'ventasMes' => 0, 'ventasAno' => 0, 'cartera' => 0,
                'facturasMes' => 0, 'facturasVencidas' => 0, 'ticketPromedio' => 0,
                'ultimasFacturas' => collect(), 'topClientes' => collect(),
                'topProductos' => collect(), 'ventasPorEstado' => collect(),
            ];
            $deltas = ['deltaHoy' => 0, 'deltaMes' => 0, 'deltaAno' => 0, 'deltaTicket' => 0];
            $series = ['ventasPorMes' => collect(), 'ventasSemana' => collect()];
        }

        return [$ventas, $deltas, $series];
    }

    private function delta(float|int $actual, float|int $anterior): float
    {
        if ($anterior > 0) {
            return round((($actual - $anterior) / $anterior) * 100, 1);
        }
        return $actual > 0 ? 100.0 : 0.0;
    }

    private function stockBajo(): int
    {
        try {
            return Producto::where('activo', true)
                ->where('es_servicio', false)
                ->whereColumn('stock_actual', '<=', 'stock_minimo')->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function cotizacionesPendientes(): int
    {
        try {
            return Cotizacion::whereIn('estado', ['enviada', 'aceptada'])->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function ordenesPendientes(): int
    {
        try {
            return OrdenCompra::where('estado', 'aprobada')->count();
        } catch (\Throwable) {
            return 0;
        }
    }
}
