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

    public function fiscal(Request $request)
    {
        $empresa = \App\Models\Empresa::obtener();
        $filtros = [
            'fecha_desde' => $request->fecha_desde ?? now()->startOfMonth()->format('Y-m-d'),
            'fecha_hasta' => $request->fecha_hasta ?? now()->format('Y-m-d'),
        ];
        $datos = $this->reportes->fiscal($filtros);

        return view('reportes.fiscal', array_merge([
            'empresa' => $empresa,
        ], $filtros, $datos));
    }

    public function fiscalExcel(Request $request)
    {
        $filtros = [
            'fecha_desde' => $request->fecha_desde ?? now()->startOfMonth()->format('Y-m-d'),
            'fecha_hasta' => $request->fecha_hasta ?? now()->format('Y-m-d'),
        ];
        $datos = $this->reportes->fiscal($filtros);

        $filename = "Reporte_Fiscal_DIAN_{$filtros['fecha_desde']}_al_{$filtros['fecha_hasta']}.csv";

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($datos, $filtros) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, ['INFORME FISCAL Y TRIBUTARIO (DIAN) — PERIODO: ' . $filtros['fecha_desde'] . ' al ' . $filtros['fecha_hasta']], ';');
            fputcsv($file, [], ';');

            fputcsv($file, ['CONCEPTO TRIBUTARIO', 'BASE GRAVABLE (COP)', 'IMPUESTO GENERADO (COP)'], ';');
            fputcsv($file, ['Ventas Gravadas Tarifa General (19%)', number_format($datos['totales']['base_19'], 2, ',', ''), number_format($datos['totales']['iva_19'], 2, ',', '')], ';');
            fputcsv($file, ['Ventas Gravadas Tarifa Especial (5%)', number_format($datos['totales']['base_5'], 2, ',', ''), number_format($datos['totales']['iva_5'], 2, ',', '')], ';');
            fputcsv($file, ['Ventas Exentas / Excluidas (0%)', number_format($datos['totales']['base_0'], 2, ',', ''), '0,00'], ';');
            fputcsv($file, ['TOTAL IVA GENERADO', '', number_format($datos['totales']['total_iva_generado'], 2, ',', '')], ';');
            fputcsv($file, [], ';');

            fputcsv($file, ['RETENCIONES EN LA FUENTE PRACTICADAS'], ';');
            fputcsv($file, ['ReteFuente Ventas (Pasivo)', '', number_format($datos['totales']['total_retefuente'], 2, ',', '')], ';');
            fputcsv($file, ['ReteICA Ventas', '', number_format($datos['totales']['total_reteica'], 2, ',', '')], ';');
            fputcsv($file, ['ReteIVA Ventas', '', number_format($datos['totales']['total_reteiva'], 2, ',', '')], ';');
            fputcsv($file, [], ';');

            fputcsv($file, ['Factura N°', 'Fecha', 'Cliente', 'NIT / CC', 'Subtotal', 'IVA', 'ReteFuente', 'ReteICA', 'Total Factura'], ';');
            foreach ($datos['facturas'] as $f) {
                fputcsv($file, [
                    $f->numero,
                    $f->fecha_emision ? $f->fecha_emision->format('Y-m-d') : '',
                    $f->cliente_nombre,
                    $f->cliente_documento,
                    number_format($f->subtotal, 2, ',', ''),
                    number_format($f->iva, 2, ',', ''),
                    number_format($f->retefuente, 2, ',', ''),
                    number_format($f->reteica, 2, ',', ''),
                    number_format($f->total, 2, ',', ''),
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function compras(Request $request)
    {
        $empresa = \App\Models\Empresa::obtener();
        $filtros = [
            'fecha_desde' => $request->fecha_desde ?? now()->startOfMonth()->format('Y-m-d'),
            'fecha_hasta' => $request->fecha_hasta ?? now()->format('Y-m-d'),
            'estado'      => $request->estado ?? '',
        ];
        $datos = $this->reportes->compras($filtros);

        return view('reportes.compras', array_merge([
            'empresa' => $empresa,
        ], $filtros, $datos));
    }

    public function comprasExcel(Request $request)
    {
        $filtros = [
            'fecha_desde' => $request->fecha_desde ?? now()->startOfMonth()->format('Y-m-d'),
            'fecha_hasta' => $request->fecha_hasta ?? now()->format('Y-m-d'),
            'estado'      => $request->estado ?? '',
        ];
        $datos = $this->reportes->compras($filtros);

        $filename = "Reporte_Compras_{$filtros['fecha_desde']}_al_{$filtros['fecha_hasta']}.csv";

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($datos) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, ['N° Orden / Factura', 'Fecha Emisión', 'Proveedor', 'NIT / Documento', 'Estado', 'Subtotal', 'IVA Compras', 'Total'], ';');

            foreach ($datos['ordenes'] as $o) {
                fputcsv($file, [
                    $o->numero,
                    $o->fecha_emision ? $o->fecha_emision->format('Y-m-d') : '',
                    $o->proveedor_nombre,
                    $o->proveedor_documento,
                    ucfirst($o->estado),
                    number_format($o->subtotal, 2, ',', ''),
                    number_format($o->iva, 2, ',', ''),
                    number_format($o->total, 2, ',', ''),
                ], ';');
            }

            fputcsv($file, [
                'TOTALES', '', '', '', '',
                number_format($datos['totales']['subtotal'], 2, ',', ''),
                number_format($datos['totales']['iva'], 2, ',', ''),
                number_format($datos['totales']['total'], 2, ',', ''),
            ], ';');

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function rentabilidad(Request $request)
    {
        $empresa = \App\Models\Empresa::obtener();
        $filtros = [
            'fecha_desde' => $request->fecha_desde ?? now()->startOfMonth()->format('Y-m-d'),
            'fecha_hasta' => $request->fecha_hasta ?? now()->format('Y-m-d'),
        ];
        $datos = $this->reportes->rentabilidad($filtros);

        return view('reportes.rentabilidad', array_merge([
            'empresa' => $empresa,
        ], $filtros, $datos));
    }

    public function rentabilidadExcel(Request $request)
    {
        $filtros = [
            'fecha_desde' => $request->fecha_desde ?? now()->startOfMonth()->format('Y-m-d'),
            'fecha_hasta' => $request->fecha_hasta ?? now()->format('Y-m-d'),
        ];
        $datos = $this->reportes->rentabilidad($filtros);

        $filename = "Reporte_Rentabilidad_{$filtros['fecha_desde']}_al_{$filtros['fecha_hasta']}.csv";

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($datos) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, ['Producto', 'Código', 'Categoría', 'Cant. Vendida', 'Ingreso por Ventas', 'Costo Total', 'Utilidad Bruta (COP)', 'Margen (%)'], ';');

            foreach ($datos['filas'] as $r) {
                fputcsv($file, [
                    $r['producto'],
                    $r['codigo'],
                    $r['categoria'],
                    $r['cantidad'],
                    number_format($r['ingreso'], 2, ',', ''),
                    number_format($r['costo'], 2, ',', ''),
                    number_format($r['utilidad'], 2, ',', ''),
                    number_format($r['margen_pct'], 1, ',', '') . '%',
                ], ';');
            }

            fputcsv($file, [
                'TOTALES', '', '', '',
                number_format($datos['totales']['total_ingreso'], 2, ',', ''),
                number_format($datos['totales']['total_costo'], 2, ',', ''),
                number_format($datos['totales']['total_utilidad'], 2, ',', ''),
                number_format($datos['totales']['margen_global'], 1, ',', '') . '%',
            ], ';');

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
