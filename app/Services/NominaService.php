<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\Nomina;
use App\Models\NominaEmpleado;

/**
 * Servicio de cálculo de nómina colombiana 2025
 *
 * Constantes vigentes 2025:
 *  - SMMLV: $1.423.500
 *  - Auxilio de transporte: $202.050
 *  - Tope auxilio transporte: 2 SMMLV ($2.847.000)
 */
class NominaService
{
    // ── Constantes 2025 ──────────────────────────────────────────
    const SMMLV               = 1_423_500;
    const AUXILIO_TRANSPORTE  = 202_050;
    const TOPE_AUXILIO        = 2_847_000; // 2 x SMMLV

    // Tarifas ARL por nivel de riesgo
    const ARL_TARIFAS = [
        1 => 0.00522,
        2 => 0.01044,
        3 => 0.02436,
        4 => 0.04350,
        5 => 0.06960,
    ];

    // ── Calcular liquidación de un empleado ───────────────────────

    /**
     * Calcula todos los conceptos de nómina para un empleado.
     *
     * @param  Empleado  $empleado
     * @param  array     $input  Horas extras, días, novedades
     * @return array     Datos listos para insertar en nomina_empleado
     */
    public function calcularLiquidacion(Empleado $empleado, array $input): array
    {
        $diasTrabajados           = (float) ($input['dias_trabajados']            ?? 30);
        $diasVacaciones           = (float) ($input['dias_vacaciones']            ?? 0);
        $diasIncapacidad          = (float) ($input['dias_incapacidad']            ?? 0);
        $diasLicencia             = (float) ($input['dias_licencia_remunerada']   ?? 0);
        $hedHoras                 = (float) ($input['horas_extras_diurnas']       ?? 0);
        $henHoras                 = (float) ($input['horas_extras_nocturnas']     ?? 0);
        $hefdHoras                = (float) ($input['horas_extras_fest_diurnas']  ?? 0);
        $hefnHoras                = (float) ($input['horas_extras_fest_nocturnas']?? 0);
        $rnHoras                  = (float) ($input['horas_recargo_nocturno']     ?? 0);
        $comisiones               = (float) ($input['comisiones']                 ?? 0);
        $bonificaciones           = (float) ($input['bonificaciones']             ?? 0);
        $otrosDevengados          = (float) ($input['otros_devengados']           ?? 0);
        $otrasDeducciones         = (float) ($input['otras_deducciones']          ?? 0);
        $retencionFuente          = (float) ($input['retencion_fuente']           ?? 0);

        $salarioBase = (float) $empleado->salario_base;
        $esIntegral  = $empleado->tipo_salario === 'integral';

        // Valor hora (mes de 240 horas: 30 días x 8 horas)
        $valorHora = $salarioBase / 240;

        // ── SALARIO BÁSICO (proporcional) ─────────────────────────
        $salarioBasico = round(($salarioBase / 30) * $diasTrabajados);

        // ── AUXILIO DE TRANSPORTE ─────────────────────────────────
        // No aplica: salario integral, salario > 2 SMMLV, vacaciones, incapacidad
        $auxilioTransporte = 0;
        if (!$esIntegral && $salarioBase <= self::TOPE_AUXILIO && $diasTrabajados > 0) {
            $auxilioTransporte = round((self::AUXILIO_TRANSPORTE / 30) * $diasTrabajados);
        }

        // ── HORAS EXTRAS ─────────────────────────────────────────
        $hed  = round($hedHoras  * $valorHora * 1.25); // diurna +25%
        $hen  = round($henHoras  * $valorHora * 1.75); // nocturna +75%
        $hefd = round($hefdHoras * $valorHora * 2.00); // festiva diurna +100%
        $hefn = round($hefnHoras * $valorHora * 2.50); // festiva nocturna +150%
        $rn   = round($rnHoras   * $valorHora * 0.35); // recargo nocturno +35%

        $valorHorasExtras = $hed + $hen + $hefd + $hefn + $rn;

        // ── TOTAL DEVENGADO ───────────────────────────────────────
        $totalDevengado = $salarioBasico + $auxilioTransporte + $valorHorasExtras
                        + $comisiones + $bonificaciones + $otrosDevengados;

        // ── IBC (Ingreso Base de Cotización) ──────────────────────
        // Para cotización SS: devengado SIN auxilio de transporte, mínimo 1 SMMLV
        $ibc = max(self::SMMLV, $salarioBasico + $valorHorasExtras + $comisiones + $bonificaciones + $otrosDevengados);

        // Para salario integral: IBC = 70% del salario integral
        if ($esIntegral) {
            $ibc = round($salarioBase * 0.70);
        }

        // ── DEDUCCIONES EMPLEADO ─────────────────────────────────
        $dedSalud   = round($ibc * 0.04);
        $dedPension = round($ibc * 0.04);

        // Fondo de Solidaridad Pensional: 1% si IBC > 4 SMMLV, +0.2% adicional por cada SMMLV adicional hasta 20 SMMLV
        $fondoSolidaridad = 0;
        if ($ibc > 4 * self::SMMLV) {
            $fondoSolidaridad = round($ibc * 0.01);
            // Subcuenta de subsistencia: 0.2% adicional por cada SMMLV > 16 SMMLV (simplificado)
        }

        $totalDeducciones = $dedSalud + $dedPension + $fondoSolidaridad
                          + $retencionFuente + $otrasDeducciones;

        $netoPagar = $totalDevengado - $totalDeducciones;

        // ── APORTES EMPLEADOR ─────────────────────────────────────
        $aporSaludEmp  = round($ibc * 0.085);
        $aporPensEmp   = round($ibc * 0.12);
        $nivelArl      = $empleado->nivel_riesgo_arl ?? 1;
        $tarifaArl     = self::ARL_TARIFAS[$nivelArl] ?? self::ARL_TARIFAS[1];
        $aporArl       = round($ibc * $tarifaArl);

        // Parafiscales (sobre total devengado SIN auxilio transporte)
        $baseParafiscal = $salarioBasico + $valorHorasExtras + $comisiones + $bonificaciones + $otrosDevengados;
        $aporCaja       = round($baseParafiscal * 0.04);
        $aporSena       = round($baseParafiscal * 0.02);
        $aporIcbf       = round($baseParafiscal * 0.03);

        $totalAportesEmpleador = $aporSaludEmp + $aporPensEmp + $aporArl
                               + $aporCaja + $aporSena + $aporIcbf;

        // ── ACUMULADOS PRESTACIONES SOCIALES ──────────────────────
        // Base prestaciones = salario devengado + auxilio de transporte
        $basePS = $salarioBasico + $auxilioTransporte + $valorHorasExtras + $comisiones + $bonificaciones;

        // Cesantías: 8.33% (1/12 mensual)
        $cesantias = round($basePS / 12);

        // Intereses sobre cesantías: 12% anual = 1% mensual
        $intCesantias = round($cesantias * 0.01);

        // Prima de servicios: 8.33% (1/12 mensual)
        $prima = round($basePS / 12);

        // Vacaciones: 4.17% sobre salario básico (solo, sin auxilio)
        $vacaciones = round($salarioBasico / 24); // 15 días/año = 1.25 días/mes

        return [
            // Tiempo
            'dias_trabajados'               => $diasTrabajados,
            'dias_vacaciones'               => $diasVacaciones,
            'dias_incapacidad'              => $diasIncapacidad,
            'dias_licencia_remunerada'      => $diasLicencia,
            'horas_extras_diurnas'          => $hedHoras,
            'horas_extras_nocturnas'        => $henHoras,
            'horas_extras_fest_diurnas'     => $hefdHoras,
            'horas_extras_fest_nocturnas'   => $hefnHoras,
            'horas_recargo_nocturno'        => $rnHoras,
            // Devengados
            'salario_basico'                => $salarioBasico,
            'auxilio_transporte'            => $auxilioTransporte,
            'valor_horas_extras'            => $valorHorasExtras,
            'comisiones'                    => $comisiones,
            'bonificaciones'                => $bonificaciones,
            'otros_devengados'              => $otrosDevengados,
            'total_devengado'               => $totalDevengado,
            // Deducciones
            'ibc'                           => $ibc,
            'deduccion_salud'               => $dedSalud,
            'deduccion_pension'             => $dedPension,
            'fondo_solidaridad'             => $fondoSolidaridad,
            'retencion_fuente'              => $retencionFuente,
            'otras_deducciones'             => $otrasDeducciones,
            'total_deducciones'             => $totalDeducciones,
            // Neto
            'neto_pagar'                    => $netoPagar,
            // Aportes empleador
            'aporte_salud_empleador'        => $aporSaludEmp,
            'aporte_pension_empleador'      => $aporPensEmp,
            'aporte_arl'                    => $aporArl,
            'aporte_caja_compensacion'      => $aporCaja,
            'aporte_sena'                   => $aporSena,
            'aporte_icbf'                   => $aporIcbf,
            'total_aportes_empleador'       => $totalAportesEmpleador,
            // Prestaciones acumuladas
            'acumulado_cesantias'           => $cesantias,
            'acumulado_intereses_cesantias' => $intCesantias,
            'acumulado_prima'               => $prima,
            'acumulado_vacaciones'          => $vacaciones,
        ];
    }

    /**
     * Recalcula los totales generales de una nómina.
     */
    public function recalcularTotalesNomina(Nomina $nomina): void
    {
        $totales = NominaEmpleado::where('nomina_id', $nomina->id)
            ->selectRaw('
                SUM(total_devengado) as total_devengado,
                SUM(total_deducciones) as total_deducciones,
                SUM(neto_pagar) as total_neto,
                SUM(total_aportes_empleador) as total_aportes_empleador
            ')
            ->first();

        $nomina->update([
            'total_devengado'         => $totales->total_devengado ?? 0,
            'total_deducciones'       => $totales->total_deducciones ?? 0,
            'total_neto'              => $totales->total_neto ?? 0,
            'total_aportes_empleador' => $totales->total_aportes_empleador ?? 0,
        ]);
    }
}
