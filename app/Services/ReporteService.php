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
            ->when(!empty($filtros['estado']), fn($q) => $q->where('estado', $filtros['estado']))
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
    }

    public function inventario(array $filtros): array
    {
        $query = Producto::with(['categoria', 'unidadMedida'])
            ->when(($filtros['filtro'] ?? null) === 'bajo_stock', fn($q) => $q->whereColumn('stock_actual', '<=', 'stock_minimo')->where('es_servicio', false))
            ->when(($filtros['filtro'] ?? null) === 'sin_stock', fn($q) => $q->where('stock_actual', 0)->where('es_servicio', false))
            ->when(!empty($filtros['categoria_id']), fn($q) => $q->where('categoria_id', $filtros['categoria_id']))
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

    public function fiscal(array $filtros): array
    {
        $facturas = Factura::with('items')
            ->whereBetween('fecha_emision', [$filtros['fecha_desde'], $filtros['fecha_hasta']])
            ->where('estado', '!=', 'anulada')
            ->orderBy('fecha_emision')
            ->get();

        $base19 = 0; $iva19 = 0;
        $base5  = 0; $iva5  = 0;
        $base0  = 0; $iva0  = 0;

        foreach ($facturas as $f) {
            foreach ($f->items as $item) {
                $pct = (float) $item->iva_pct;
                $sub = (float) $item->subtotal;
                $iv  = (float) $item->iva;

                if ($pct >= 18) {
                    $base19 += $sub;
                    $iva19  += $iv;
                } elseif ($pct > 0) {
                    $base5 += $sub;
                    $iva5  += $iv;
                } else {
                    $base0 += $sub;
                    $iva0  += $iv;
                }
            }
        }

        $totales = [
            'total_ventas_brutas' => $facturas->sum('subtotal'),
            'total_descuentos'    => $facturas->sum('descuento'),
            'base_19'             => $base19,
            'iva_19'              => $iva19,
            'base_5'              => $base5,
            'iva_5'               => $iva5,
            'base_0'              => $base0,
            'iva_0'               => $iva0,
            'total_iva_generado'  => $facturas->sum('iva'),
            'total_retefuente'    => $facturas->sum('retefuente'),
            'total_reteica'       => $facturas->sum('reteica'),
            'total_reteiva'       => $facturas->sum('reteiva'),
            'total_neto_facturado'=> $facturas->sum('total'),
            'num_facturas'        => $facturas->count(),
        ];

        return compact('facturas', 'totales');
    }

    public function compras(array $filtros): array
    {
        $query = \App\Models\OrdenCompra::with('proveedor')
            ->whereBetween('fecha_emision', [$filtros['fecha_desde'], $filtros['fecha_hasta']])
            ->when(!empty($filtros['estado']), fn($q) => $q->where('estado', $filtros['estado']))
            ->where('estado', '!=', 'anulada')
            ->orderByDesc('fecha_emision');

        $ordenes = $query->get();

        $totales = [
            'subtotal'    => $ordenes->sum('subtotal'),
            'iva'         => $ordenes->sum('iva'),
            'descuento'   => $ordenes->sum('descuento'),
            'total'       => $ordenes->sum('total'),
            'count'       => $ordenes->count(),
        ];

        return compact('ordenes', 'totales');
    }

    public function rentabilidad(array $filtros): array
    {
        $items = DB::table('factura_items')
            ->join('facturas', 'facturas.id', '=', 'factura_items.factura_id')
            ->leftJoin('productos', 'productos.id', '=', 'factura_items.producto_id')
            ->leftJoin('categorias', 'categorias.id', '=', 'productos.categoria_id')
            ->whereBetween('facturas.fecha_emision', [$filtros['fecha_desde'], $filtros['fecha_hasta']])
            ->where('facturas.estado', '!=', 'anulada')
            ->select(
                'factura_items.descripcion',
                'productos.codigo as producto_codigo',
                'categorias.nombre as categoria_nombre',
                DB::raw('SUM(factura_items.cantidad) as total_cantidad'),
                DB::raw('SUM(factura_items.subtotal) as total_ingreso'),
                DB::raw('SUM(factura_items.cantidad * COALESCE(productos.precio_compra, 0)) as total_costo')
            )
            ->groupBy('factura_items.descripcion', 'productos.codigo', 'categorias.nombre')
            ->orderByDesc('total_ingreso')
            ->get();

        $filas = [];
        $totalIngreso = 0;
        $totalCosto = 0;

        foreach ($items as $it) {
            $ing = (float) $it->total_ingreso;
            $cos = (float) $it->total_costo;
            $utilidad = $ing - $cos;
            $margen = $ing > 0 ? round(($utilidad / $ing) * 100, 1) : 0;

            $totalIngreso += $ing;
            $totalCosto += $cos;

            $filas[] = [
                'producto'   => $it->descripcion,
                'codigo'     => $it->producto_codigo ?? '—',
                'categoria'  => $it->categoria_nombre ?? 'General',
                'cantidad'   => (float) $it->total_cantidad,
                'ingreso'    => $ing,
                'costo'      => $cos,
                'utilidad'   => $utilidad,
                'margen_pct' => $margen,
            ];
        }

        $totalUtilidad = $totalIngreso - $totalCosto;
        $margenGlobal = $totalIngreso > 0 ? round(($totalUtilidad / $totalIngreso) * 100, 1) : 0;

        $totales = [
            'total_ingreso'  => $totalIngreso,
            'total_costo'    => $totalCosto,
            'total_utilidad' => $totalUtilidad,
            'margen_global'  => $margenGlobal,
            'num_productos'  => count($filas),
        ];

        return compact('filas', 'totales');
    }
}


