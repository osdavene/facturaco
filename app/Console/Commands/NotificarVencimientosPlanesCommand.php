<?php

namespace App\Console\Commands;

use App\Mail\PlanVencimientoMail;
use App\Models\Empresa;
use App\Services\MailConfigService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificarVencimientosPlanesCommand extends Command
{
    protected $signature = 'suscripciones:notificar-vencimientos {--forzar : Enviar sin importar la fecha}';
    protected $description = 'Envía correos automáticos de aviso a las empresas cuyos planes estén próximos a vencer (5, 3, 1 día o hoy).';

    public function handle(): int
    {
        MailConfigService::aplicarConfiguracion();

        $hoy = Carbon::today();
        $this->info("🔍 Buscando empresas con planes por vencer...");

        $empresas = Empresa::whereNotNull('plan_vencimiento')
            ->whereNotNull('plan_id')
            ->with(['plan', 'usuarios'])
            ->get();

        $enviados = 0;

        foreach ($empresas as $empresa) {
            $vencimiento = Carbon::parse($empresa->plan_vencimiento)->startOfDay();
            $diasRestantes = (int) $hoy->diffInDays($vencimiento, false);

            // Notificar cuando falten exactamente 5, 3, 1 día o si vence hoy (0)
            $debeNotificar = in_array($diasRestantes, [5, 3, 1, 0]) || $this->option('forzar');

            if ($debeNotificar) {
                // Obtener destinatarios (email de la empresa + emails de los administradores)
                $destinatarios = collect([$empresa->email])
                    ->merge($empresa->usuarios->pluck('email'))
                    ->filter(fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL))
                    ->unique()
                    ->values()
                    ->toArray();

                if (empty($destinatarios)) {
                    $this->warn("⚠️ La empresa {$empresa->razon_social} no tiene correos válidos registrados.");
                    continue;
                }

                try {
                    Mail::to($destinatarios)->send(new PlanVencimientoMail($empresa, $diasRestantes));
                    $this->info("✅ Notificación enviada a {$empresa->razon_social} ({$diasRestantes} días restantes) -> " . implode(', ', $destinatarios));
                    $enviados++;
                } catch (\Throwable $e) {
                    $this->error("❌ Error enviando correo a {$empresa->razon_social}: " . $e->getMessage());
                    Log::error("Error enviando aviso de vencimiento", [
                        'empresa' => $empresa->razon_social,
                        'error'   => $e->getMessage()
                    ]);
                }
            }
        }

        $this->info("🎉 Proceso finalizado. Total correos enviados: {$enviados}");
        return self::SUCCESS;
    }
}
