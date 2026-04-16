<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Empresa;
use App\Models\Factura;
use App\Models\Producto;
use App\Models\Cotizacion;
use App\Notifications\FacturasVencidasNotification;
use App\Notifications\StockBajoNotification;
use App\Notifications\CotizacionesPorVencerNotification;

class EnviarAlertas extends Command
{
    protected $signature   = 'alertas:enviar
                                {--empresa= : ID de empresa específica (omitir = todas)}
                                {--dry-run  : Mostrar alertas sin enviar emails}';

    protected $description = 'Envía alertas por email: facturas vencidas, stock bajo, cotizaciones por vencer';

    public function handle(): int
    {
        $empresas = Empresa::query();

        if ($id = $this->option('empresa')) {
            $empresas->where('id', $id);
        }

        foreach ($empresas->get() as $empresa) {
            $this->procesarEmpresa($empresa);
        }

        $this->info('Alertas procesadas.');
        return self::SUCCESS;
    }

    private function procesarEmpresa(Empresa $empresa): void
    {
        $this->line("  → {$empresa->razon_social}");

        // Usuarios de la empresa que tienen email
        $usuarios = $empresa->users()
            ->wherePivot('activo', true)
            ->whereNotNull('email')
            ->get();

        if ($usuarios->isEmpty()) {
            $this->warn("     Sin usuarios activos con email.");
            return;
        }

        // ── Facturas vencidas ────────────────────────────────────────
        $factVencidas = Factura::withoutGlobalScope('empresa')
            ->where('empresa_id', $empresa->id)
            ->where('estado', 'vencida')
            ->get();

        if ($factVencidas->isNotEmpty()) {
            $this->line("     Facturas vencidas: {$factVencidas->count()}");
            if (!$this->option('dry-run')) {
                foreach ($usuarios as $usuario) {
                    $usuario->notify(new FacturasVencidasNotification($factVencidas, $empresa->razon_social));
                }
            }
        }

        // ── Stock bajo ───────────────────────────────────────────────
        $stockBajo = Producto::withoutGlobalScope('empresa')
            ->where('empresa_id', $empresa->id)
            ->where('activo', true)
            ->where('es_servicio', false)
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->get();

        if ($stockBajo->isNotEmpty()) {
            $this->line("     Stock bajo: {$stockBajo->count()} productos");
            if (!$this->option('dry-run')) {
                foreach ($usuarios as $usuario) {
                    $usuario->notify(new StockBajoNotification($stockBajo, $empresa->razon_social));
                }
            }
        }

        // ── Cotizaciones por vencer (próximos 7 días) ────────────────
        $cotPorVencer = Cotizacion::withoutGlobalScope('empresa')
            ->where('empresa_id', $empresa->id)
            ->whereIn('estado', ['borrador', 'enviada'])
            ->whereDate('fecha_vencimiento', '>=', now())
            ->whereDate('fecha_vencimiento', '<=', now()->addDays(7))
            ->orderBy('fecha_vencimiento')
            ->get();

        if ($cotPorVencer->isNotEmpty()) {
            $this->line("     Cotizaciones por vencer: {$cotPorVencer->count()}");
            if (!$this->option('dry-run')) {
                foreach ($usuarios as $usuario) {
                    $usuario->notify(new CotizacionesPorVencerNotification($cotPorVencer, $empresa->razon_social));
                }
            }
        }

        if ($factVencidas->isEmpty() && $stockBajo->isEmpty() && $cotPorVencer->isEmpty()) {
            $this->line("     Sin alertas.");
        }
    }
}
