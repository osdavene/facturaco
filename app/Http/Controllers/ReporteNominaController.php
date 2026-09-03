<?php

namespace App\Http\Controllers;

use App\Models\Nomina;
use App\Models\NominaEmpleado;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReporteNominaController extends Controller
{
    public function index(Request $request)
    {
        $anio = $request->input('anio', now()->year);

        $nominas = Nomina::whereYear('periodo_inicio', $anio)
            ->whereIn('estado', ['procesada', 'pagada'])
            ->with('liquidaciones.empleado')
            ->orderBy('periodo_inicio')
            ->get();

        $totales = [
            'devengado'       => $nominas->sum('total_devengado'),
            'deducciones'     => $nominas->sum('total_deducciones'),
            'neto'            => $nominas->sum('total_neto'),
            'aportes_empresa' => $nominas->sum('total_aportes_empleador'),
        ];

        // Desglose PILA acumulado del año
        $pila = [
            'salud_empleado'  => 0,
            'salud_empresa'   => 0,
            'pension_empleado'=> 0,
            'pension_empresa' => 0,
            'arl'             => 0,
            'caja'            => 0,
            'sena'            => 0,
            'icbf'            => 0,
            'total_pila'      => 0,
        ];

        // Provisiones acumuladas
        $provisiones = [
            'cesantias'          => 0,
            'intereses_cesantias'=> 0,
            'prima'              => 0,
            'vacaciones'         => 0,
            'total_provisiones'  => 0,
        ];

        foreach ($nominas as $nom) {
            foreach ($nom->liquidaciones as $liq) {
                $pila['salud_empleado']   += (float) $liq->deduccion_salud;
                $pila['salud_empresa']    += (float) $liq->aporte_salud_empleador;
                $pila['pension_empleado'] += (float) ($liq->deduccion_pension + $liq->fondo_solidaridad);
                $pila['pension_empresa']  += (float) $liq->aporte_pension_empleador;
                $pila['arl']              += (float) $liq->aporte_arl;
                $pila['caja']             += (float) $liq->aporte_caja_compensacion;
                $pila['sena']             += (float) $liq->aporte_sena;
                $pila['icbf']             += (float) $liq->aporte_icbf;

                $provisiones['cesantias']           += (float) $liq->acumulado_cesantias;
                $provisiones['intereses_cesantias'] += (float) $liq->acumulado_intereses_cesantias;
                $provisiones['prima']               += (float) $liq->acumulado_prima;
                $provisiones['vacaciones']          += (float) $liq->acumulado_vacaciones;
            }
        }

        $pila['total_pila'] = array_sum($pila);
        $provisiones['total_provisiones'] = array_sum($provisiones);

        $costoTotalEmpresa = $totales['devengado'] + $totales['aportes_empresa'] + $provisiones['total_provisiones'];

        return view('nomina.reportes.index', compact(
            'anio', 'nominas', 'totales', 'pila', 'provisiones', 'costoTotalEmpresa'
        ));
    }

    public function exportar(Request $request)
    {
        $anio = $request->input('anio', now()->year);

        $nominas = Nomina::whereYear('periodo_inicio', $anio)
            ->whereIn('estado', ['procesada', 'pagada'])
            ->with('liquidaciones.empleado')
            ->orderBy('periodo_inicio')
            ->get();

        $filename = "Reporte_Nomina_PILA_{$anio}.csv";

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($nominas, $anio) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($file, ['INFORME ANUAL CONSOLIDADO DE NÓMINA Y SEGURIDAD SOCIAL (PILA) — AÑO ' . $anio], ';');
            fputcsv($file, [], ';');

            fputcsv($file, [
                'Período', 'Fecha Pago', 'Empleados', 'Devengado', 'Deducciones', 'Neto Pagado',
                'Salud Total', 'Pensión Total', 'ARL', 'Caja Comp.', 'Sena', 'ICBF',
                'Cesantías', 'Int. Cesantías', 'Prima', 'Vacaciones'
            ], ';');

            foreach ($nominas as $nom) {
                $saludTot   = $nom->liquidaciones->sum('deduccion_salud') + $nom->liquidaciones->sum('aporte_salud_empleador');
                $pensTot    = $nom->liquidaciones->sum('deduccion_pension') + $nom->liquidaciones->sum('fondo_solidaridad') + $nom->liquidaciones->sum('aporte_pension_empleador');
                $arl        = $nom->liquidaciones->sum('aporte_arl');
                $caja       = $nom->liquidaciones->sum('aporte_caja_compensacion');
                $sena       = $nom->liquidaciones->sum('aporte_sena');
                $icbf       = $nom->liquidaciones->sum('aporte_icbf');
                $cesantias  = $nom->liquidaciones->sum('acumulado_cesantias');
                $intCes     = $nom->liquidaciones->sum('acumulado_intereses_cesantias');
                $prima      = $nom->liquidaciones->sum('acumulado_prima');
                $vacaciones = $nom->liquidaciones->sum('acumulado_vacaciones');

                fputcsv($file, [
                    $nom->nombre,
                    $nom->fecha_pago ? $nom->fecha_pago->format('Y-m-d') : 'Pendiente',
                    $nom->liquidaciones->count(),
                    number_format($nom->total_devengado, 2, ',', ''),
                    number_format($nom->total_deducciones, 2, ',', ''),
                    number_format($nom->total_neto, 2, ',', ''),
                    number_format($saludTot, 2, ',', ''),
                    number_format($pensTot, 2, ',', ''),
                    number_format($arl, 2, ',', ''),
                    number_format($caja, 2, ',', ''),
                    number_format($sena, 2, ',', ''),
                    number_format($icbf, 2, ',', ''),
                    number_format($cesantias, 2, ',', ''),
                    number_format($intCes, 2, ',', ''),
                    number_format($prima, 2, ',', ''),
                    number_format($vacaciones, 2, ',', ''),
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
