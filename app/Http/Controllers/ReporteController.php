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
use App\Services\ReporteService;
use App\Actions\GenerarReportePdfAction;
use App\Actions\GenerarReporteExcelAction;
use App\Exports\CarteraExport;
use Maatwebsite\Excel\Facades\Excel;

class ReporteController extends Controller
{
    public function __construct(
        private ReporteService $reportes,
        private GenerarReportePdfAction $pdfAction,
        private GenerarReporteExcelAction $excelAction
    ) {}

    public function index()
    {
        $empresa = \App\Models\Empresa::obtener();
        $kpis = $this->reportes->kpisGenerales($empresa);

        return view('reportes.index', array_merge([
            'empresa' => $empresa,
        ], $kpis));
    }

    public function ventas(Request $request)
    {
        $empresa = \App\Models\Empresa::obtener();
        $filtros = [
            'fecha_desde' => $request->fecha_desde ?? now()->startOfMonth()->format('Y-m-d'),
            'fecha_hasta' => $request->fecha_hasta ?? now()->format('Y-m-d'),
            'estado' => $request->estado ?? '',
        ];
        $datos = $this->reportes->ventas($filtros);

        return view('reportes.ventas', array_merge([
            'empresa' => $empresa,
        ], $filtros, $datos));
    }

    public function inventario(Request $request)
    {
        $empresa = \App\Models\Empresa::obtener();
        $filtros = [
            'filtro' => $request->filtro ?? 'todos',
            'categoria_id' => $request->categoria_id ?? '',
        ];
        $datos = $this->reportes->inventario($filtros);
        $categorias = \App\Models\Categoria::where('activo', true)->orderBy('nombre')->get();

        return view('reportes.inventario', array_merge([
            'empresa' => $empresa,
            'categorias' => $categorias,
        ], $filtros, $datos));
    }

    public function cartera(Request $request)
    {
        $empresa = \App\Models\Empresa::obtener();
        $filtros = ['estado' => $request->estado ?? 'pendiente'];
        $datos = $this->reportes->cartera($filtros);

        return view('reportes.cartera', array_merge([
            'empresa' => $empresa,
        ], $filtros, $datos));
    }

    public function ventasPdf(Request $request)
    {
        $filtros = [
            'fecha_desde' => $request->fecha_desde ?? now()->startOfMonth()->format('Y-m-d'),
            'fecha_hasta' => $request->fecha_hasta ?? now()->format('Y-m-d'),
        ];

        return $this->pdfAction->ventasPdf($filtros);
    }

    public function inventarioPdf()
    {
        return $this->pdfAction->inventarioPdf();
    }

    public function carteraPdf()
    {
        return $this->pdfAction->carteraPdf();
    }

    public function ventasExcel(Request $request)
    {
        $filtros = [
            'fecha_desde' => $request->fecha_desde ?? now()->startOfMonth()->format('Y-m-d'),
            'fecha_hasta' => $request->fecha_hasta ?? now()->format('Y-m-d'),
            'estado' => $request->estado ?? null,
        ];

        return $this->excelAction->ventasExcel($filtros);
    }

    public function inventarioExcel()
    {
        return $this->excelAction->inventarioExcel();
    }

    public function carteraExcel()
    {
        return $this->excelAction->carteraExcel();
    }
}
