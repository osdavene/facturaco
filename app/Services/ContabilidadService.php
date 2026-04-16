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

            $grupo = match($cuenta->tipo) {
                'activo'     => &$activo,
                'pasivo'     => &$pasivo,
                'patrimonio' => &$patrimonio,
                default      => null,
            };

            if ($grupo !== null) {
                $grupo[] = ['codigo' => $cuenta->codigo, 'nombre' => $cuenta->nombre, 'saldo' => $saldo];
            }
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
