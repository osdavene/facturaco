<?php

namespace App\Services;

use App\Models\AsientoContable;
use App\Models\AsientoLinea;
use App\Models\Factura;
use App\Models\PlanCuenta;
use App\Models\ReciboCaja;
use Illuminate\Support\Facades\DB;

class ContabilidadService
{
    // ── Cuentas estándar usadas en asientos automáticos ───────────
    // Código → se busca en plan_cuentas (empresa_id NULL o de la empresa)
    const CUENTA_CAJA           = '110505'; // Caja general
    const CUENTA_BANCOS         = '111005'; // Bancos nacionales
    const CUENTA_CLIENTES       = '130505'; // Clientes nacionales
    const CUENTA_INGRESOS_VENTA = '413505'; // Ingresos por ventas
    const CUENTA_INGRESOS_SERV  = '415505'; // Ingresos por servicios
    const CUENTA_IVA_GENERADO   = '240805'; // IVA generado
    const CUENTA_RETEFUENTE     = '236505'; // ReteFuente a favor (pasivo)
    const CUENTA_RETEICA        = '236805'; // ReteICA
    const CUENTA_RETEIVA        = '236905'; // ReteIVA

    // Cuentas de Nómina PUC Colombia
    const CUENTA_GASTO_SUELDOS        = '510506'; // Sueldos
    const CUENTA_GASTO_HORAS_EXTRAS   = '510515'; // Horas extras y recargos
    const CUENTA_GASTO_COMISIONES     = '510518'; // Comisiones
    const CUENTA_GASTO_AUX_TRANSPORTE = '510527'; // Auxilio de transporte
    const CUENTA_GASTO_CESANTIAS      = '510530'; // Cesantías
    const CUENTA_GASTO_INT_CESANTIAS  = '510533'; // Intereses sobre cesantías
    const CUENTA_GASTO_PRIMA          = '510536'; // Prima de servicios
    const CUENTA_GASTO_VACACIONES     = '510539'; // Vacaciones
    const CUENTA_GASTO_ARL            = '510568'; // Aportes a ARL
    const CUENTA_GASTO_SALUD_EMP      = '510569'; // Aportes a EPS
    const CUENTA_GASTO_PENSION_EMP    = '510570'; // Aportes a Pensión
    const CUENTA_GASTO_CAJA_COMP      = '510572'; // Aportes a Caja de Compensación
    const CUENTA_PASIVO_SALUD         = '237005'; // Aportes EPS por pagar
    const CUENTA_PASIVO_ARL           = '237006'; // ARL por pagar
    const CUENTA_PASIVO_CAJA          = '237010'; // Caja de Compensación por pagar
    const CUENTA_PASIVO_PENSION       = '238030'; // Fondos de Pensión por pagar
    const CUENTA_PASIVO_PROVISIONES   = '261005'; // Provisiones para prestaciones sociales
    const CUENTA_SALARIOS_POR_PAGAR   = '250505'; // Salarios por pagar

    // ── Asiento por factura de venta ──────────────────────────────

    public function asientoFactura(Factura $factura): ?AsientoContable
    {
        if ($factura->estado === 'anulada') return null;

        return DB::transaction(function () use ($factura) {
            $empresaId = $factura->empresa_id;

            $cuentaClientes = $this->cuenta($empresaId, self::CUENTA_CLIENTES);
            $cuentaIngresos = $this->cuenta($empresaId, self::CUENTA_INGRESOS_VENTA);
            $cuentaIva      = $this->cuenta($empresaId, self::CUENTA_IVA_GENERADO);

            if (!$cuentaClientes || !$cuentaIngresos) return null;

            $total    = (float) $factura->total;
            $subtotal = (float) $factura->subtotal - (float) $factura->descuento;
            $iva      = (float) $factura->iva;
            $reteFte  = (float) $factura->retefuente;
            $reteIca  = (float) $factura->reteica;
            $reteIva  = (float) $factura->reteiva;

            $asiento = $this->crearCabecera([
                'empresa_id'      => $empresaId,
                'fecha'           => $factura->fecha_emision,
                'descripcion'     => 'Factura ' . $factura->numero . ' — ' . $factura->cliente_nombre,
                'tipo'            => 'factura',
                'referencia_tipo' => 'Factura',
                'referencia_id'   => $factura->id,
            ]);

            $lineas = [];

            // DR Clientes: total bruto (lo que el cliente debe antes de retenciones)
            $lineas[] = ['cuenta_id' => $cuentaClientes->id, 'descripcion' => 'Clientes — ' . $factura->cliente_nombre, 'debito' => $total + $reteFte + $reteIca + $reteIva, 'credito' => 0];

            // CR Ingresos: base gravable
            $lineas[] = ['cuenta_id' => $cuentaIngresos->id, 'descripcion' => 'Ingresos ventas', 'debito' => 0, 'credito' => $subtotal];

            // CR IVA generado
            if ($iva > 0 && $cuentaIva) {
                $lineas[] = ['cuenta_id' => $cuentaIva->id, 'descripcion' => 'IVA generado', 'debito' => 0, 'credito' => $iva];
            }

            // CR ReteFuente (reduce lo que el cliente paga → es pasivo)
            if ($reteFte > 0) {
                $c = $this->cuenta($empresaId, self::CUENTA_RETEFUENTE);
                if ($c) $lineas[] = ['cuenta_id' => $c->id, 'descripcion' => 'ReteFuente', 'debito' => 0, 'credito' => $reteFte];
            }

            // CR ReteICA
            if ($reteIca > 0) {
                $c = $this->cuenta($empresaId, self::CUENTA_RETEICA);
                if ($c) $lineas[] = ['cuenta_id' => $c->id, 'descripcion' => 'ReteICA', 'debito' => 0, 'credito' => $reteIca];
            }

            // CR ReteIVA
            if ($reteIva > 0) {
                $c = $this->cuenta($empresaId, self::CUENTA_RETEIVA);
                if ($c) $lineas[] = ['cuenta_id' => $c->id, 'descripcion' => 'ReteIVA', 'debito' => 0, 'credito' => $reteIva];
            }

            return $this->guardarLineas($asiento, $lineas);
        });
    }

    // ── Asiento por recibo de caja ────────────────────────────────

    public function asientoRecibo(ReciboCaja $recibo): ?AsientoContable
    {
        if ($recibo->estado === 'anulado') return null;

        return DB::transaction(function () use ($recibo) {
            $empresaId = $recibo->empresa_id;

            $cuentaDestino  = $recibo->forma_pago === 'efectivo'
                ? $this->cuenta($empresaId, self::CUENTA_CAJA)
                : $this->cuenta($empresaId, self::CUENTA_BANCOS);
            $cuentaClientes = $this->cuenta($empresaId, self::CUENTA_CLIENTES);

            if (!$cuentaDestino || !$cuentaClientes) return null;

            $asiento = $this->crearCabecera([
                'empresa_id'      => $empresaId,
                'fecha'           => $recibo->fecha,
                'descripcion'     => 'Recibo de caja ' . $recibo->numero . ' — ' . $recibo->cliente_nombre,
                'tipo'            => 'recibo',
                'referencia_tipo' => 'ReciboCaja',
                'referencia_id'   => $recibo->id,
            ]);

            $lineas = [
                ['cuenta_id' => $cuentaDestino->id,  'descripcion' => 'Pago recibido — ' . $recibo->concepto, 'debito' => (float) $recibo->valor, 'credito' => 0],
                ['cuenta_id' => $cuentaClientes->id, 'descripcion' => 'Abono cliente — ' . $recibo->cliente_nombre, 'debito' => 0, 'credito' => (float) $recibo->valor],
            ];

            return $this->guardarLineas($asiento, $lineas);
        });
    }

    // ── Anular asiento vinculado a un documento ───────────────────

    public function anularAsientosDe(string $referenciaType, int $referenciaId): void
    {
        AsientoContable::where('referencia_tipo', $referenciaType)
            ->where('referencia_id', $referenciaId)
            ->where('estado', 'confirmado')
            ->update(['estado' => 'anulado']);
    }

    // ── Saldo de una cuenta por código ────────────────────────────

    public function saldoCuenta(int $empresaId, string $codigo, ?string $desde = null, ?string $hasta = null): float
    {
        $cuenta = $this->cuenta($empresaId, $codigo);
        return $cuenta ? $cuenta->saldo($desde, $hasta) : 0.0;
    }

    // ── Asiento por nota crédito ──────────────────────────────────

    public function asientoNotaCredito(\App\Models\NotaCredito $nota): ?AsientoContable
    {
        if ($nota->estado === 'anulada') return null;

        return DB::transaction(function () use ($nota) {
            $empresaId = $nota->empresa_id ?? session('empresa_activa_id') ?? 1;

            $cuentaClientes = $this->cuenta($empresaId, self::CUENTA_CLIENTES);
            $cuentaIngresos = $this->cuenta($empresaId, self::CUENTA_INGRESOS_VENTA);
            $cuentaIva      = $this->cuenta($empresaId, self::CUENTA_IVA_GENERADO);

            if (!$cuentaClientes || !$cuentaIngresos) return null;

            $subtotal = (float) $nota->subtotal;
            $iva      = (float) $nota->iva;
            $total    = (float) $nota->total;

            $asiento = $this->crearCabecera([
                'empresa_id'      => $empresaId,
                'fecha'           => $nota->fecha,
                'descripcion'     => 'Nota Crédito ' . $nota->numero . ' — ' . $nota->cliente_nombre . ' (' . $nota->motivo . ')',
                'tipo'            => 'nota_credito',
                'referencia_tipo' => 'NotaCredito',
                'referencia_id'   => $nota->id,
            ]);

            $lineas = [];

            // DR: Ingresos ventas (reversa el ingreso)
            $lineas[] = ['cuenta_id' => $cuentaIngresos->id, 'descripcion' => 'Devolución/Descuento sobre venta', 'debito' => $subtotal, 'credito' => 0];

            // DR: IVA generado (reversa el IVA)
            if ($iva > 0 && $cuentaIva) {
                $lineas[] = ['cuenta_id' => $cuentaIva->id, 'descripcion' => 'Reverso IVA por Nota Crédito', 'debito' => $iva, 'credito' => 0];
            }

            // CR: Clientes (disminuye la cuenta por cobrar)
            $lineas[] = ['cuenta_id' => $cuentaClientes->id, 'descripcion' => 'Abono por Nota Crédito — ' . $nota->cliente_nombre, 'debito' => 0, 'credito' => $total];

            return $this->guardarLineas($asiento, $lineas);
        });
    }

    // ── Asiento por liquidación de nómina ─────────────────────────

    public function asientoNomina(\App\Models\Nomina $nomina): ?AsientoContable
    {
        if ($nomina->estado === 'anulada') return null;

        return DB::transaction(function () use ($nomina) {
            $nomina->loadMissing('liquidaciones.empleado');
            $empresaId = session('empresa_activa_id') ?? 1;

            $totalSueldos       = (float) $nomina->liquidaciones->sum('salario_basico');
            $totalAuxTransporte = (float) $nomina->liquidaciones->sum('auxilio_transporte');
            $totalHorasExtras   = (float) $nomina->liquidaciones->sum('valor_horas_extras');
            $totalComisiones    = (float) $nomina->liquidaciones->sum('comisiones');
            $totalBonific       = (float) $nomina->liquidaciones->sum('bonificaciones');
            $totalOtrosDev      = (float) $nomina->liquidaciones->sum('otros_devengados');

            $totalDedSalud      = (float) $nomina->liquidaciones->sum('deduccion_salud');
            $totalDedPension    = (float) $nomina->liquidaciones->sum('deduccion_pension');
            $totalFondoSol      = (float) $nomina->liquidaciones->sum('fondo_solidaridad');
            $totalReteFte       = (float) $nomina->liquidaciones->sum('retencion_fuente');
            $totalOtrasDed      = (float) $nomina->liquidaciones->sum('otras_deducciones');

            $totalAporSaludEmp  = (float) $nomina->liquidaciones->sum('aporte_salud_empleador');
            $totalAporPensEmp   = (float) $nomina->liquidaciones->sum('aporte_pension_empleador');
            $totalAporArl       = (float) $nomina->liquidaciones->sum('aporte_arl');
            $totalAporCaja      = (float) $nomina->liquidaciones->sum('aporte_caja_compensacion');
            $totalAporSena      = (float) $nomina->liquidaciones->sum('aporte_sena');
            $totalAporIcbf      = (float) $nomina->liquidaciones->sum('aporte_icbf');

            $totalCesantias     = (float) $nomina->liquidaciones->sum('acumulado_cesantias');
            $totalIntCesantias  = (float) $nomina->liquidaciones->sum('acumulado_intereses_cesantias');
            $totalPrima         = (float) $nomina->liquidaciones->sum('acumulado_prima');
            $totalVacaciones    = (float) $nomina->liquidaciones->sum('acumulado_vacaciones');

            $totalNeto          = (float) $nomina->total_neto;

            $asiento = $this->crearCabecera([
                'empresa_id'      => $empresaId,
                'fecha'           => $nomina->fecha_pago ?? now()->toDateString(),
                'descripcion'     => 'Liquidación de Nómina — ' . $nomina->nombre . ' (' . $nomina->liquidaciones->count() . ' empleados)',
                'tipo'            => 'nomina',
                'referencia_tipo' => 'Nomina',
                'referencia_id'   => $nomina->id,
            ]);

            $lineas = [];

            // ── DÉBITOS (Gastos) ──
            $cSueldos = $this->cuenta($empresaId, self::CUENTA_GASTO_SUELDOS, 'Sueldos y Salarios', 'gasto', 'debito');
            if ($cSueldos && ($totalSueldos + $totalBonific + $totalOtrosDev) > 0) {
                $lineas[] = ['cuenta_id' => $cSueldos->id, 'descripcion' => 'Sueldos y devengados de nómina', 'debito' => $totalSueldos + $totalBonific + $totalOtrosDev, 'credito' => 0];
            }

            if ($totalAuxTransporte > 0) {
                $cAux = $this->cuenta($empresaId, self::CUENTA_GASTO_AUX_TRANSPORTE, 'Auxilio de Transporte', 'gasto', 'debito');
                if ($cAux) $lineas[] = ['cuenta_id' => $cAux->id, 'descripcion' => 'Auxilio de transporte empleados', 'debito' => $totalAuxTransporte, 'credito' => 0];
            }

            if (($totalHorasExtras + $totalComisiones) > 0) {
                $cHE = $this->cuenta($empresaId, self::CUENTA_GASTO_HORAS_EXTRAS, 'Horas Extras y Recargos', 'gasto', 'debito');
                if ($cHE) $lineas[] = ['cuenta_id' => $cHE->id, 'descripcion' => 'Horas extras y comisiones', 'debito' => $totalHorasExtras + $totalComisiones, 'credito' => 0];
            }

            // Aportes Empleador (Gastos)
            if ($totalAporSaludEmp > 0) {
                $c = $this->cuenta($empresaId, self::CUENTA_GASTO_SALUD_EMP, 'Aportes EPS Empleador', 'gasto', 'debito');
                if ($c) $lineas[] = ['cuenta_id' => $c->id, 'descripcion' => 'Aporte salud empleador (8.5%)', 'debito' => $totalAporSaludEmp, 'credito' => 0];
            }
            if ($totalAporPensEmp > 0) {
                $c = $this->cuenta($empresaId, self::CUENTA_GASTO_PENSION_EMP, 'Aportes Pensión Empleador', 'gasto', 'debito');
                if ($c) $lineas[] = ['cuenta_id' => $c->id, 'descripcion' => 'Aporte pensión empleador (12%)', 'debito' => $totalAporPensEmp, 'credito' => 0];
            }
            if ($totalAporArl > 0) {
                $c = $this->cuenta($empresaId, self::CUENTA_GASTO_ARL, 'Aportes ARL', 'gasto', 'debito');
                if ($c) $lineas[] = ['cuenta_id' => $c->id, 'descripcion' => 'Aporte ARL riesgos laborales', 'debito' => $totalAporArl, 'credito' => 0];
            }
            if (($totalAporCaja + $totalAporSena + $totalAporIcbf) > 0) {
                $c = $this->cuenta($empresaId, self::CUENTA_GASTO_CAJA_COMP, 'Parafiscales (Caja/Sena/ICBF)', 'gasto', 'debito');
                if ($c) $lineas[] = ['cuenta_id' => $c->id, 'descripcion' => 'Aporte parafiscales', 'debito' => $totalAporCaja + $totalAporSena + $totalAporIcbf, 'credito' => 0];
            }

            // Provisiones Prestaciones Sociales (Gastos)
            $totalProv = $totalCesantias + $totalIntCesantias + $totalPrima + $totalVacaciones;
            if ($totalCesantias > 0) {
                $c = $this->cuenta($empresaId, self::CUENTA_GASTO_CESANTIAS, 'Cesantías', 'gasto', 'debito');
                if ($c) $lineas[] = ['cuenta_id' => $c->id, 'descripcion' => 'Provisión mensual cesantías', 'debito' => $totalCesantias, 'credito' => 0];
            }
            if ($totalIntCesantias > 0) {
                $c = $this->cuenta($empresaId, self::CUENTA_GASTO_INT_CESANTIAS, 'Intereses sobre Cesantías', 'gasto', 'debito');
                if ($c) $lineas[] = ['cuenta_id' => $c->id, 'descripcion' => 'Provisión intereses cesantías', 'debito' => $totalIntCesantias, 'credito' => 0];
            }
            if ($totalPrima > 0) {
                $c = $this->cuenta($empresaId, self::CUENTA_GASTO_PRIMA, 'Prima de Servicios', 'gasto', 'debito');
                if ($c) $lineas[] = ['cuenta_id' => $c->id, 'descripcion' => 'Provisión prima de servicios', 'debito' => $totalPrima, 'credito' => 0];
            }
            if ($totalVacaciones > 0) {
                $c = $this->cuenta($empresaId, self::CUENTA_GASTO_VACACIONES, 'Vacaciones', 'gasto', 'debito');
                if ($c) $lineas[] = ['cuenta_id' => $c->id, 'descripcion' => 'Provisión vacaciones', 'debito' => $totalVacaciones, 'credito' => 0];
            }

            // ── CRÉDITOS (Pasivos y Bancos) ──
            // Salud por pagar (Empleado + Empresa)
            if (($totalDedSalud + $totalAporSaludEmp) > 0) {
                $c = $this->cuenta($empresaId, self::CUENTA_PASIVO_SALUD, 'Aportes a EPS por pagar', 'pasivo', 'credito');
                if ($c) $lineas[] = ['cuenta_id' => $c->id, 'descripcion' => 'Aportes EPS por pagar (Empresa + Empleado)', 'debito' => 0, 'credito' => $totalDedSalud + $totalAporSaludEmp];
            }

            // Pensión por pagar (Empleado + Empresa + Fondo Solidaridad)
            if (($totalDedPension + $totalAporPensEmp + $totalFondoSol) > 0) {
                $c = $this->cuenta($empresaId, self::CUENTA_PASIVO_PENSION, 'Fondos de Pensión por pagar', 'pasivo', 'credito');
                if ($c) $lineas[] = ['cuenta_id' => $c->id, 'descripcion' => 'Aportes Pensión por pagar (Empresa + Empleado)', 'debito' => 0, 'credito' => $totalDedPension + $totalAporPensEmp + $totalFondoSol];
            }

            // ARL por pagar
            if ($totalAporArl > 0) {
                $c = $this->cuenta($empresaId, self::CUENTA_PASIVO_ARL, 'ARL por pagar', 'pasivo', 'credito');
                if ($c) $lineas[] = ['cuenta_id' => $c->id, 'descripcion' => 'Aportes ARL por pagar', 'debito' => 0, 'credito' => $totalAporArl];
            }

            // Parafiscales por pagar
            if (($totalAporCaja + $totalAporSena + $totalAporIcbf) > 0) {
                $c = $this->cuenta($empresaId, self::CUENTA_PASIVO_CAJA, 'Parafiscales por pagar', 'pasivo', 'credito');
                if ($c) $lineas[] = ['cuenta_id' => $c->id, 'descripcion' => 'Caja de Compensación y parafiscales por pagar', 'debito' => 0, 'credito' => $totalAporCaja + $totalAporSena + $totalAporIcbf];
            }

            // Retefuente y otras deducciones
            if (($totalReteFte + $totalOtrasDed) > 0) {
                $c = $this->cuenta($empresaId, self::CUENTA_RETEFUENTE, 'Retenciones y deducciones por pagar', 'pasivo', 'credito');
                if ($c) $lineas[] = ['cuenta_id' => $c->id, 'descripcion' => 'Retención fuente y deducciones nómina', 'debito' => 0, 'credito' => $totalReteFte + $totalOtrasDed];
            }

            // Provisiones prestaciones sociales (Pasivo estimado)
            if ($totalProv > 0) {
                $c = $this->cuenta($empresaId, self::CUENTA_PASIVO_PROVISIONES, 'Provisiones prestaciones sociales', 'pasivo', 'credito');
                if ($c) $lineas[] = ['cuenta_id' => $c->id, 'descripcion' => 'Provisión pasivo prestaciones (Cesantías, Prima, Vacaciones)', 'debito' => 0, 'credito' => $totalProv];
            }

            // Salarios por pagar / Salida de Bancos (Neto pagado)
            $cBancos = $this->cuenta($empresaId, self::CUENTA_BANCOS, 'Bancos Nacionales', 'activo', 'debito');
            if ($cBancos && $totalNeto > 0) {
                $lineas[] = ['cuenta_id' => $cBancos->id, 'descripcion' => 'Pago neto de nómina a empleados', 'debito' => 0, 'credito' => $totalNeto];
            }

            return $this->guardarLineas($asiento, $lineas);
        });
    }

    // ── Crear Asiento Manual ──────────────────────────────────────

    public function crearAsientoManual(int $empresaId, string $fecha, string $descripcion, array $lineas, int $userId): AsientoContable
    {
        $totalDebito = 0;
        $totalCredito = 0;

        foreach ($lineas as $l) {
            $totalDebito  += (float) ($l['debito'] ?? 0);
            $totalCredito += (float) ($l['credito'] ?? 0);
        }

        if (abs($totalDebito - $totalCredito) > 0.01) {
            throw new \InvalidArgumentException("El asiento contable no está cuadrado. Total Débitos: $" . number_format($totalDebito, 2) . " != Total Créditos: $" . number_format($totalCredito, 2));
        }

        if ($totalDebito <= 0) {
            throw new \InvalidArgumentException("El asiento contable debe tener un valor mayor a cero.");
        }

        return DB::transaction(function () use ($empresaId, $fecha, $descripcion, $lineas, $userId) {
            $asiento = $this->crearCabecera([
                'empresa_id'  => $empresaId,
                'fecha'       => $fecha,
                'descripcion' => $descripcion,
                'tipo'        => 'manual',
            ]);

            return $this->guardarLineas($asiento, $lineas);
        });
    }

    // ── Balance de Prueba (Sumas y Saldos) ────────────────────────

    public function balancePrueba(int $empresaId, string $desde, string $hasta): array
    {
        $cuentas = PlanCuenta::deEmpresa($empresaId)
            ->activas()
            ->conMovimientos()
            ->orderBy('codigo')
            ->get();

        $diaAnterior = \Carbon\Carbon::parse($desde)->subDay()->toDateString();
        $filas = [];

        $totales = [
            'anterior_debito'  => 0,
            'anterior_credito' => 0,
            'debito'           => 0,
            'credito'          => 0,
            'final_debito'     => 0,
            'final_credito'    => 0,
        ];

        foreach ($cuentas as $cuenta) {
            // Saldo anterior
            $saldoAnt = $cuenta->saldo(null, $diaAnterior);

            // Movimientos periodo
            $qMov = $cuenta->lineas()
                ->join('asientos_contables', 'asiento_lineas.asiento_id', '=', 'asientos_contables.id')
                ->where('asientos_contables.estado', 'confirmado')
                ->where('asientos_contables.empresa_id', $empresaId)
                ->whereBetween('asientos_contables.fecha', [$desde, $hasta]);

            $debitoPeriodo  = (float) $qMov->sum('asiento_lineas.debito');
            $creditoPeriodo = (float) $qMov->sum('asiento_lineas.credito');

            // Saldo final
            $saldoFinal = $cuenta->naturaleza === 'debito'
                ? $saldoAnt + $debitoPeriodo - $creditoPeriodo
                : $saldoAnt + $creditoPeriodo - $debitoPeriodo;

            // Si no tiene saldo anterior ni movimientos en el periodo, omitir
            if (abs($saldoAnt) < 0.01 && abs($debitoPeriodo) < 0.01 && abs($creditoPeriodo) < 0.01) {
                continue;
            }

            $antDebito  = $cuenta->naturaleza === 'debito'  ? max(0, $saldoAnt) : (min(0, $saldoAnt) < 0 ? abs($saldoAnt) : 0);
            $antCredito = $cuenta->naturaleza === 'credito' ? max(0, $saldoAnt) : (min(0, $saldoAnt) < 0 ? abs($saldoAnt) : 0);

            $finDebito  = $cuenta->naturaleza === 'debito'  ? max(0, $saldoFinal) : (min(0, $saldoFinal) < 0 ? abs($saldoFinal) : 0);
            $finCredito = $cuenta->naturaleza === 'credito' ? max(0, $saldoFinal) : (min(0, $saldoFinal) < 0 ? abs($saldoFinal) : 0);

            $filas[] = [
                'cuenta_id'   => $cuenta->id,
                'codigo'      => $cuenta->codigo,
                'nombre'      => $cuenta->nombre,
                'tipo'        => $cuenta->tipo,
                'naturaleza'  => $cuenta->naturaleza,
                'ant_debito'  => $antDebito,
                'ant_credito' => $antCredito,
                'debito'      => $debitoPeriodo,
                'credito'     => $creditoPeriodo,
                'fin_debito'  => $finDebito,
                'fin_credito' => $finCredito,
            ];

            $totales['anterior_debito']  += $antDebito;
            $totales['anterior_credito'] += $antCredito;
            $totales['debito']           += $debitoPeriodo;
            $totales['credito']          += $creditoPeriodo;
            $totales['final_debito']     += $finDebito;
            $totales['final_credito']    += $finCredito;
        }

        return ['filas' => $filas, 'totales' => $totales];
    }

    // ── Libro Auxiliar por Cuenta ─────────────────────────────────

    public function auxiliarCuenta(int $empresaId, int $cuentaId, string $desde, string $hasta): array
    {
        $cuenta = PlanCuenta::deEmpresa($empresaId)->findOrFail($cuentaId);

        $diaAnterior = \Carbon\Carbon::parse($desde)->subDay()->toDateString();
        $saldoAnterior = $cuenta->saldo(null, $diaAnterior);

        $lineas = AsientoLinea::with('asiento')
            ->join('asientos_contables', 'asiento_lineas.asiento_id', '=', 'asientos_contables.id')
            ->where('asientos_contables.estado', 'confirmado')
            ->where('asientos_contables.empresa_id', $empresaId)
            ->where('asiento_lineas.cuenta_id', $cuentaId)
            ->whereBetween('asientos_contables.fecha', [$desde, $hasta])
            ->orderBy('asientos_contables.fecha')
            ->orderBy('asientos_contables.id')
            ->select('asiento_lineas.*')
            ->get();

        $movimientos = [];
        $saldoCorriente = $saldoAnterior;
        $totalDebito = 0;
        $totalCredito = 0;

        foreach ($lineas as $l) {
            $deb  = (float) $l->debito;
            $cred = (float) $l->credito;

            if ($cuenta->naturaleza === 'debito') {
                $saldoCorriente = $saldoCorriente + $deb - $cred;
            } else {
                $saldoCorriente = $saldoCorriente + $cred - $deb;
            }

            $totalDebito  += $deb;
            $totalCredito += $cred;

            $movimientos[] = [
                'asiento_id'     => $l->asiento->id,
                'asiento_numero' => $l->asiento->numero,
                'fecha'          => $l->asiento->fecha,
                'tipo'           => $l->asiento->tipo,
                'concepto'       => $l->descripcion ?: $l->asiento->descripcion,
                'debito'         => $deb,
                'credito'        => $cred,
                'saldo'          => $saldoCorriente,
            ];
        }

        return [
            'cuenta'         => $cuenta,
            'saldo_anterior' => $saldoAnterior,
            'movimientos'    => $movimientos,
            'total_debito'   => $totalDebito,
            'total_credito'  => $totalCredito,
            'saldo_final'    => $saldoCorriente,
        ];
    }

    // ── Resumen para Balance General ──────────────────────────────

    public function balance(int $empresaId, string $hasta): array
    {
        $cuentas = PlanCuenta::deEmpresa($empresaId)
            ->activas()
            ->conMovimientos()
            ->get();

        $activo = $pasivo = $patrimonio = [];

        foreach ($cuentas as $cuenta) {
            $saldo = $cuenta->saldo(null, $hasta);
            if (abs($saldo) < 0.01) continue;

            $entrada = ['codigo' => $cuenta->codigo, 'nombre' => $cuenta->nombre, 'saldo' => $saldo];

            match($cuenta->tipo) {
                'activo'     => $activo[]     = $entrada,
                'pasivo'     => $pasivo[]     = $entrada,
                'patrimonio' => $patrimonio[] = $entrada,
                default      => null,
            };
        }

        usort($activo,     fn($a, $b) => strcmp($a['codigo'], $b['codigo']));
        usort($pasivo,     fn($a, $b) => strcmp($a['codigo'], $b['codigo']));
        usort($patrimonio, fn($a, $b) => strcmp($a['codigo'], $b['codigo']));

        return compact('activo', 'pasivo', 'patrimonio');
    }

    // ── Resumen para Estado de Resultados (PyG) ───────────────────

    public function estadoResultados(int $empresaId, string $desde, string $hasta): array
    {
        $cuentas = PlanCuenta::deEmpresa($empresaId)
            ->activas()
            ->conMovimientos()
            ->whereIn('tipo', ['ingreso', 'gasto', 'costo'])
            ->get();

        $ingresos = $gastos = $costos = [];

        foreach ($cuentas as $cuenta) {
            $saldo = $cuenta->saldo($desde, $hasta);
            if (abs($saldo) < 0.01) continue;

            match($cuenta->tipo) {
                'ingreso' => $ingresos[] = ['codigo' => $cuenta->codigo, 'nombre' => $cuenta->nombre, 'saldo' => $saldo],
                'gasto'   => $gastos[]   = ['codigo' => $cuenta->codigo, 'nombre' => $cuenta->nombre, 'saldo' => $saldo],
                'costo'   => $costos[]   = ['codigo' => $cuenta->codigo, 'nombre' => $cuenta->nombre, 'saldo' => $saldo],
                default   => null,
            };
        }

        usort($ingresos, fn($a, $b) => strcmp($a['codigo'], $b['codigo']));
        usort($gastos,   fn($a, $b) => strcmp($a['codigo'], $b['codigo']));
        usort($costos,   fn($a, $b) => strcmp($a['codigo'], $b['codigo']));

        $totalIngresos = array_sum(array_column($ingresos, 'saldo'));
        $totalCostos   = array_sum(array_column($costos,   'saldo'));
        $totalGastos   = array_sum(array_column($gastos,   'saldo'));
        $utilidad      = $totalIngresos - $totalCostos - $totalGastos;

        return compact('ingresos', 'costos', 'gastos', 'totalIngresos', 'totalCostos', 'totalGastos', 'utilidad');
    }

    // ── Privados ──────────────────────────────────────────────────

    private function cuenta(int $empresaId, string $codigo): ?PlanCuenta
    {
        return PlanCuenta::deEmpresa($empresaId)
            ->activas()
            ->where('codigo', $codigo)
            ->first();
    }

    private function crearCabecera(array $data): AsientoContable
    {
        $numero = $this->siguienteNumero($data['empresa_id']);

        return AsientoContable::create(array_merge($data, [
            'numero'     => $numero,
            'estado'     => 'confirmado',
            'created_by' => auth()->id(),
        ]));
    }

    private function siguienteNumero(int $empresaId): string
    {
        $anio = now()->year;
        $ultimo = AsientoContable::where('empresa_id', $empresaId)
            ->whereYear('fecha', $anio)
            ->lockForUpdate()
            ->count();

        return 'AC-' . $anio . '-' . str_pad($ultimo + 1, 5, '0', STR_PAD_LEFT);
    }

    private function guardarLineas(AsientoContable $asiento, array $lineas): AsientoContable
    {
        $totalDebito = $totalCredito = 0;

        foreach ($lineas as $linea) {
            AsientoLinea::create(array_merge($linea, ['asiento_id' => $asiento->id]));
            $totalDebito  += (float) ($linea['debito']  ?? 0);
            $totalCredito += (float) ($linea['credito'] ?? 0);
        }

        $asiento->update([
            'total_debito'  => $totalDebito,
            'total_credito' => $totalCredito,
        ]);

        return $asiento->fresh();
    }
}
