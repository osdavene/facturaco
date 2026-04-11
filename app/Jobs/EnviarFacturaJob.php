<?php

namespace App\Jobs;

use App\Mail\FacturaMail;
use App\Models\Empresa;
use App\Models\Factura;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class EnviarFacturaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60; // segundos entre reintentos

    public function __construct(
        public Factura $factura,
        public Empresa $empresa,
        public string  $email,
        public string  $mensaje = '',
    ) {}

    public function handle(): void
    {
        $this->factura->load(['items', 'cliente']);

        Mail::to($this->email)
            ->send(new FacturaMail($this->factura, $this->empresa, $this->mensaje));

        // Cambiar estado solo si aún está en borrador
        if ($this->factura->estado === 'borrador') {
            $this->factura->update(['estado' => 'emitida']);
        }
    }

    public function failed(\Throwable $exception): void
    {
        // El log de fallos queda en failed_jobs automáticamente.
        // Aquí puedes notificar al admin si lo necesitas en el futuro.
    }
}
