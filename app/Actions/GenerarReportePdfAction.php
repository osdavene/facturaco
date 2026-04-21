<?php

namespace App\Actions;

use App\Models\Empresa;
use App\Services\ReporteService;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerarReportePdfAction
{
    public function __construct(
        private ReporteService $reportes
    ) {}

    public function ventasPdf(array $filtros): \Illuminate\Http\Response
    {
        $empresa = Empresa::obtener();
        $datos = $this->reportes->ventas($filtros);

        $pdf = Pdf::loadView('reportes.pdf_ventas', [
            'empresa' => $empresa,
            'facturas' => $datos['facturas'],
            'totales' => $datos['totales'],
            'fechaDesde' => $filtros['fecha_desde'],
            'fechaHasta' => $filtros['fecha_hasta']
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('reporte-ventas-'.now()->format('Ymd').'.pdf');
    }

    public function inventarioPdf(): \Illuminate\Http\Response
    {
        $empresa = Empresa::obtener();
        $datos = $this->reportes->inventario([]);

        $pdf = Pdf::loadView('reportes.pdf_inventario', [
            'empresa' => $empresa,
            'productos' => $datos['productos'],
            'valorInventario' => $datos['valorInventario']
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('reporte-inventario-'.now()->format('Ymd').'.pdf');
    }

    public function carteraPdf(): \Illuminate\Http\Response
    {
        $empresa = Empresa::obtener();
        $datos = $this->reportes->cartera(['estado' => 'pendiente']);

        $pdf = Pdf::loadView('reportes.pdf_cartera', [
            'empresa' => $empresa,
            'facturas' => $datos['facturas'],
            'totales' => $datos['totales']
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('reporte-cartera-'.now()->format('Ymd').'.pdf');
    }
}
