<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\Factura;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;

class ReporteService
{
    public function kpisGenerales(Empresa $empresa = null): array
    {
        $empresa ??= Empresa::obtener();

        // KPIs generales
        $ventasMes = Factura::whereMonth('fecha_emision', now()->month)
            ->whereYear('fecha_emision', now()->year)
            ->where('estado', '!=', 'anulada')
            ->sum('total');

        $ventasAnio = Factura::whereYear('fecha_emision', now()->year)
            ->where('estado', '!=', 'anulada')
            ->sum('total');

        $carteraPendiente = Factura::whereIn('estado', ['emitida', 'vencida'])
            ->sum(DB::raw('total - total_pagado'));

        $facturasMes = Factura::whereMonth('fecha_emision', now()->month)
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
                'mes' => $fecha->locale('es')->monthName,
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

        return compact(
            'ventasMes', 'ventasAnio', 'carteraPendiente',
            'facturasMes', 'productosStock', 'ventasPorMes',
            'topClientes', 'topProductos'
        );
    }

    public function ventas(array $filtros): array
    {
        $query = Factura::with('cliente')
            ->whereBetween('fecha_emision', [$filtros['fecha_desde'], $filtros['fecha_hasta']])
            ->when(isset($filtros['estado']), fn($q) => $q->where('estado', $filtros['estado']))
            ->where('estado', '!=', 'anulada')
            ->orderByDesc('fecha_emision');

        $facturas = $query->get();

        return [
            'facturas' => $facturas,
            'totales' => [
                'subtotal' => $facturas->sum('subtotal'),
                'iva' => $facturas->sum('iva'),
                'retefuente' => $facturas->sum('retefuente'),
            'reteica' => $facturas->sum('reteica'),
                'total' => $facturas->sum('total'),
                'count' => $facturas->count(),
            ]
        ];

        return $datos;
    }

    public function inventario(array $filtros): array
    {
        $query = Producto::with(['categoria', 'unidadMedida'])
            ->when($filtros['filtro'] === 'bajo_stock', fn($q) => $q->whereColumn('stock_actual', '<=', 'stock_minimo')->where('es_servicio', false))
            ->when($filtros['filtro'] === 'sin_stock', fn($q) => $q->where('stock_actual', 0)->where('es_servicio', false))
            ->when(isset($filtros['categoria_id']), fn($q) => $q->where('categoria_id', $filtros['categoria_id']))
            ->where('activo', true)
            ->orderBy('nombre');

        $productos = $query->get();
        $valorInventario = $productos->where('es_servicio', false)
            ->sum(fn($p) => $p->stock_actual * $p->precio_compra);

        return [
            'productos' => $productos,
            'valorInventario' => $valorInventario,
        ];
    }

    public function cartera(array $filtros): array
    {
        $query = Factura::with('cliente')
            ->when($filtros['estado'] === 'pendiente', fn($q) => $q->whereIn('estado', ['emitida', 'vencida']))
            ->when($filtros['estado'] === 'vencida', fn($q) => $q->where('estado', 'vencida'))
            ->when($filtros['estado'] === 'pagada', fn($q) => $q->where('estado', 'pagada'))
            ->orderBy('fecha_vencimiento');

        $facturas = $query->get();

        return [
            'facturas' => $facturas,
            'totales' => [
                'total' => $facturas->sum('total'),
                'pagado' => $facturas->sum('total_pagado'),
                'pendiente' => $facturas->sum(fn($f) => max(0, $f->total - $f->total_pagado)),
                'count' => $facturas->count(),
            ]
        ];
    }
}

