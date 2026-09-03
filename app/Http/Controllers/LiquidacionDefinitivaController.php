<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Empresa;
use App\Services\NominaService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LiquidacionDefinitivaController extends Controller
{
    public function index(Request $request)
    {
        $empleados = Empleado::orderBy('apellidos')->get();
        $empleadoSeleccionado = null;
        $calculo = null;

        if ($request->filled('empleado_id')) {
            $empleadoSeleccionado = Empleado::findOrFail($request->empleado_id);
            $calculo = $this->procesarCalculo($empleadoSeleccionado, $request);
        }

        return view('nomina.liquidacion-definitiva.index', compact('empleados', 'empleadoSeleccionado', 'calculo'));
    }

    public function imprimir(Request $request)
    {
        $request->validate([
            'empleado_id'  => 'required|exists:empleados,id',
            'fecha_retiro' => 'required|date',
        ]);

        $empleado = Empleado::findOrFail($request->empleado_id);
        $empresa  = Empresa::obtener();
        $calculo  = $this->procesarCalculo($empleado, $request);

        return view('nomina.liquidacion-definitiva.imprimir', compact('empleado', 'empresa', 'calculo'));
    }

    private function procesarCalculo(Empleado $empleado, Request $request): array
    {
        $fechaIngreso = $empleado->fecha_ingreso ?: now()->subMonths(6);
        $fechaRetiro  = $request->filled('fecha_retiro') ? Carbon::parse($request->fecha_retiro) : ($empleado->fecha_retiro ?: now());
        $motivo       = $request->input('motivo_retiro', 'renuncia'); // renuncia, fin_contrato, despido_sin_justa_causa, despido_con_justa_causa

        // Salario y auxilio
        $salarioBase = (float) $empleado->salario_base;
        $smmlv       = (float) \App\Models\ConfiguracionPlataforma::get('smmlv', NominaService::SMMLV);
        $auxTrans    = (float) \App\Models\ConfiguracionPlataforma::get('auxilio_transporte', NominaService::AUXILIO_TRANSPORTE);

        $aplicaAuxilio = ($salarioBase <= ($smmlv * 2)) && ($empleado->tipo_salario !== 'integral');
        $basePrestaciones = $salarioBase + ($aplicaAuxilio ? $auxTrans : 0);

        // Días totales trabajados
        $diasTotales = max(1, $fechaIngreso->diffInDays($fechaRetiro) + 1);

        // Días pendientes de cesantías e intereses (año en curso desde 1 de enero o fecha ingreso)
        $inicioAnio = $fechaRetiro->copy()->startOfYear();
        $fechaInicioCesantias = $fechaIngreso->gt($inicioAnio) ? $fechaIngreso : $inicioAnio;
        $diasCesantias = $request->filled('dias_cesantias')
            ? (int) $request->dias_cesantias
            : max(1, $fechaInicioCesantias->diffInDays($fechaRetiro) + 1);

        // Días pendientes de prima (semestre en curso: 1 Ene - 30 Jun o 1 Jul - 31 Dic)
        $inicioSemestre = $fechaRetiro->month <= 6
            ? $fechaRetiro->copy()->startOfYear()
            : $fechaRetiro->copy()->month(7)->startOfMonth();
        $fechaInicioPrima = $fechaIngreso->gt($inicioSemestre) ? $fechaIngreso : $inicioSemestre;
        $diasPrima = $request->filled('dias_prima')
            ? (int) $request->dias_prima
            : max(1, $fechaInicioPrima->diffInDays($fechaRetiro) + 1);

        // Días pendientes de vacaciones
        $diasVacaciones = $request->filled('dias_vacaciones')
            ? (int) $request->dias_vacaciones
            : round(($diasTotales * 15) / 360);

        // ── Cálculos de Ley ──────────────────────────────────────────
        // Cesantías: (Base * Días) / 360
        $valorCesantias = round(($basePrestaciones * $diasCesantias) / 360);

        // Intereses sobre cesantías: (Cesantías * Días * 0.12) / 360
        $valorIntereses = round(($valorCesantias * $diasCesantias * 0.12) / 360);

        // Prima de servicios: (Base * Días) / 360
        $valorPrima = round(($basePrestaciones * $diasPrima) / 360);

        // Vacaciones: (Salario Base * Días pendientes) / 720 (o Salario / 30 * dias)
        $valorVacaciones = round(($salarioBase * $diasVacaciones) / 30);

        // Indemnización por despido sin justa causa (art. 64 CST)
        $valorIndemnizacion = 0;
        if ($motivo === 'despido_sin_justa_causa') {
            if ($empleado->tipo_contrato === 'fijo') {
                // Salarios restantes del contrato
                $mesesRestantes = (int) $request->input('meses_restantes_contrato', 1);
                $valorIndemnizacion = round($salarioBase * $mesesRestantes);
            } else {
                // Indefinido:
                // Menos de 10 SMMLV: 30 días primer año + 20 días por año siguiente
                // 10 o más SMMLV: 20 días primer año + 15 días por año siguiente
                $anios = $diasTotales / 360;
                if ($salarioBase < (10 * $smmlv)) {
                    $diasIndem = 30 + max(0, ($anios - 1) * 20);
                } else {
                    $diasIndem = 20 + max(0, ($anios - 1) * 15);
                }
                $valorIndemnizacion = round(($salarioBase / 30) * $diasIndem);
            }
        }

        // Otros conceptos
        $salarioPendiente = (float) $request->input('salario_pendiente', 0);
        $bonificaciones   = (float) $request->input('otras_bonificaciones', 0);
        $deducciones      = (float) $request->input('descuentos_prestamos', 0);

        $totalDevengado = $valorCesantias + $valorIntereses + $valorPrima + $valorVacaciones + $valorIndemnizacion + $salarioPendiente + $bonificaciones;
        $netoPagar      = max(0, $totalDevengado - $deducciones);

        return [
            'fecha_ingreso'       => $fechaIngreso,
            'fecha_retiro'        => $fechaRetiro,
            'motivo'              => $motivo,
            'dias_totales'        => $diasTotales,
            'salario_base'        => $salarioBase,
            'auxilio_transporte'  => $aplicaAuxilio ? $auxTrans : 0,
            'base_prestaciones'   => $basePrestaciones,
            'dias_cesantias'      => $diasCesantias,
            'valor_cesantias'     => $valorCesantias,
            'valor_intereses'     => $valorIntereses,
            'dias_prima'          => $diasPrima,
            'valor_prima'         => $valorPrima,
            'dias_vacaciones'     => $diasVacaciones,
            'valor_vacaciones'    => $valorVacaciones,
            'valor_indemnizacion' => $valorIndemnizacion,
            'salario_pendiente'   => $salarioPendiente,
            'bonificaciones'      => $bonificaciones,
            'deducciones'         => $deducciones,
            'total_devengado'     => $totalDevengado,
            'neto_pagar'          => $netoPagar,
        ];
    }
}
