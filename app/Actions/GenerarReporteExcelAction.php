<?php

namespace App\Actions;

use App\Exports\FacturasExport;
use App\Exports\InventarioExport;
use App\Exports\CarteraExport;
use Maatwebsite\Excel\Facades\Excel;

class GenerarReporteExcelAction
{
    public function ventasExcel(array $filtros): \Illuminate\Http\Response
    {
        return Excel::download(
            new FacturasExport($filtros['fecha_desde'], $filtros['fecha_hasta'], $filtros['estado'] ?? null),
            'ventas-' . now()->format('Ymd') . '.xlsx'
        );
    }

    public function inventarioExcel(): \Illuminate\Http\Response
    {
        return Excel::download(
            new InventarioExport(),
            'inventario-' . now()->format('Ymd') . '.xlsx'
        );
    }

    public function carteraExcel(): \Illuminate\Http\Response
    {
        return Excel::download(
            new CarteraExport(),
            'cartera-' . now()->format('Ymd') . '.xlsx'
        );
    }
}
