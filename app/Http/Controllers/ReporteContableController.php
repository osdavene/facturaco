<?php

namespace App\Http\Controllers;

use App\Models\PlanCuenta;
use App\Services\ContabilidadService;
use Illuminate\Http\Request;

class ReporteContableController extends Controller
{
    public function __construct(private ContabilidadService $contabilidad) {}

    public function balancePrueba(Request $request)
    {
        $empresaId = session('empresa_activa_id');
        $desde     = $request->desde ?? now()->startOfMonth()->toDateString();
        $hasta     = $request->hasta ?? now()->toDateString();

        $data = $this->contabilidad->balancePrueba($empresaId, $desde, $hasta);

        return view('contabilidad.reportes.balance-prueba', array_merge($data, [
            'desde' => $desde,
            'hasta' => $hasta,
        ]));
    }

    public function auxiliar(Request $request)
    {
        $empresaId = session('empresa_activa_id');
        $desde     = $request->desde ?? now()->startOfMonth()->toDateString();
        $hasta     = $request->hasta ?? now()->toDateString();

        $cuentas = PlanCuenta::deEmpresa($empresaId)
            ->activas()
            ->conMovimientos()
            ->orderBy('codigo')
            ->get(['id', 'codigo', 'nombre']);

        $cuentaSeleccionadaId = $request->cuenta_id ?: $cuentas->first()?->id;

        $data = $cuentaSeleccionadaId
            ? $this->contabilidad->auxiliarCuenta($empresaId, (int) $cuentaSeleccionadaId, $desde, $hasta)
            : ['cuenta' => null, 'saldo_anterior' => 0, 'movimientos' => [], 'total_debito' => 0, 'total_credito' => 0, 'saldo_final' => 0];

        return view('contabilidad.reportes.auxiliar', array_merge($data, [
            'cuentas'  => $cuentas,
            'cuentaId' => $cuentaSeleccionadaId,
            'desde'    => $desde,
            'hasta'    => $hasta,
        ]));
    }

    public function balance(Request $request)
    {
        $empresaId = session('empresa_activa_id');
        $hasta     = $request->hasta ?? now()->toDateString();

        $data = $this->contabilidad->balance($empresaId, $hasta);

        $totalActivo     = array_sum(array_column($data['activo'],     'saldo'));
        $totalPasivo     = array_sum(array_column($data['pasivo'],     'saldo'));
        $totalPatrimonio = array_sum(array_column($data['patrimonio'], 'saldo'));

        return view('contabilidad.reportes.balance', array_merge($data, [
            'hasta'           => $hasta,
            'totalActivo'     => $totalActivo,
            'totalPasivo'     => $totalPasivo,
            'totalPatrimonio' => $totalPatrimonio,
        ]));
    }

    public function pyg(Request $request)
    {
        $empresaId = session('empresa_activa_id');
        $desde     = $request->desde ?? now()->startOfYear()->toDateString();
        $hasta     = $request->hasta ?? now()->toDateString();

        $data = $this->contabilidad->estadoResultados($empresaId, $desde, $hasta);

        return view('contabilidad.reportes.pyg', array_merge($data, [
            'desde' => $desde,
            'hasta' => $hasta,
        ]));
    }

    public function exportarBalancePrueba(Request $request)
    {
        $empresaId = session('empresa_activa_id');
        $desde     = $request->desde ?? now()->startOfMonth()->toDateString();
        $hasta     = $request->hasta ?? now()->toDateString();

        $data = $this->contabilidad->balancePrueba($empresaId, $desde, $hasta);

        $filename = "Balance_Prueba_{$desde}_al_{$hasta}.csv";

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($file, ['Código', 'Cuenta PUC', 'Tipo', 'Naturaleza', 'Saldo Ant. Débito', 'Saldo Ant. Crédito', 'Mov. Débito', 'Mov. Crédito', 'Saldo Final Débito', 'Saldo Final Crédito'], ';');

            foreach ($data['filas'] as $f) {
                fputcsv($file, [
                    $f['codigo'],
                    $f['nombre'],
                    ucfirst($f['tipo']),
                    ucfirst($f['naturaleza']),
                    number_format($f['ant_debito'], 2, ',', ''),
                    number_format($f['ant_credito'], 2, ',', ''),
                    number_format($f['debito'], 2, ',', ''),
                    number_format($f['credito'], 2, ',', ''),
                    number_format($f['fin_debito'], 2, ',', ''),
                    number_format($f['fin_credito'], 2, ',', ''),
                ], ';');
            }

            fputcsv($file, [
                'TOTALES', '', '', '',
                number_format($data['totales']['anterior_debito'], 2, ',', ''),
                number_format($data['totales']['anterior_credito'], 2, ',', ''),
                number_format($data['totales']['debito'], 2, ',', ''),
                number_format($data['totales']['credito'], 2, ',', ''),
                number_format($data['totales']['final_debito'], 2, ',', ''),
                number_format($data['totales']['final_credito'], 2, ',', ''),
            ], ';');

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportarAuxiliar(Request $request)
    {
        $empresaId = session('empresa_activa_id');
        $desde     = $request->desde ?? now()->startOfMonth()->toDateString();
        $hasta     = $request->hasta ?? now()->toDateString();
        $cuentaId  = (int) $request->cuenta_id;

        $data = $this->contabilidad->auxiliarCuenta($empresaId, $cuentaId, $desde, $hasta);

        $codigo = $data['cuenta']->codigo ?? 'Cuenta';
        $filename = "Auxiliar_{$codigo}_{$desde}_al_{$hasta}.csv";

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, ['Cuenta:', $data['cuenta']->codigo . ' - ' . $data['cuenta']->nombre], ';');
            fputcsv($file, ['Saldo Anterior:', number_format($data['saldo_anterior'], 2, ',', '')], ';');
            fputcsv($file, [], ';');

            fputcsv($file, ['Fecha', 'Comprobante N°', 'Tipo', 'Concepto / Detalle', 'Débito', 'Crédito', 'Saldo Acumulado'], ';');

            foreach ($data['movimientos'] as $m) {
                fputcsv($file, [
                    $m['fecha']->format('Y-m-d'),
                    $m['asiento_numero'],
                    ucfirst($m['tipo']),
                    $m['concepto'],
                    number_format($m['debito'], 2, ',', ''),
                    number_format($m['credito'], 2, ',', ''),
                    number_format($m['saldo'], 2, ',', ''),
                ], ';');
            }

            fputcsv($file, [
                'TOTALES', '', '', '',
                number_format($data['total_debito'], 2, ',', ''),
                number_format($data['total_credito'], 2, ',', ''),
                number_format($data['saldo_final'], 2, ',', ''),
            ], ';');

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
