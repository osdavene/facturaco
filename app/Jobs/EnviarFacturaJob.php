<?php

namespace App\Jobs;

use App\Mail\FacturaMail;
use App\Models\Empresa;
use App\Models\Factura;
use App\Services\MailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EnviarFacturaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60; // segundos entre reintentos

    public function __construct(
        public Factura $factura,
        public Empresa $empresa,
        public string  $email,
        public string  $mensaje = '',
    ) {}

    public function handle(MailService $mail): void
    {
        if (! $mail->estaConfigurado($this->empresa)) {
            $this->fail(new \RuntimeException(
                "Correo no configurado en la empresa [{$this->empresa->razon_social}]."
            ));
            return;
        }

        $this->factura->loadMissing(['items', 'cliente']);

        $mail->paraEmpresa($this->empresa)
             ->to($this->email)
             ->send(new FacturaMail($this->factura, $this->empresa, $this->mensaje));

        if ($this->factura->estado === 'borrador') {
            $this->factura->update(['estado' => 'emitida']);
        }
    }

    public function failed(\Throwable $exception): void
    {
        // El registro queda en failed_jobs automáticamente.
    }
}
