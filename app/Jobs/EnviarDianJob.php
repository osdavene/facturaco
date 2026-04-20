<?php

namespace App\Jobs;

use App\Models\DianEvento;
use App\Models\Factura;
use App\Services\DianService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EnviarDianJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 120; // segundos entre reintentos

    private ?int $eventoId = null;

    public function __construct(public Factura $factura) {}

    public function handle(DianService $dian): void
    {
        // Marcar evento como "procesando"
        $evento = DianEvento::registrar($this->factura, DianEvento::TIPO_ENVIO, DianEvento::ESTADO_PROCESANDO);
        $this->eventoId = $evento->id;

        try {
            $resultado = $dian->enviar($this->factura);

            // Actualizar factura con datos DIAN
            $this->factura->update([
                'cufe'         => $resultado['cufe'],
                'enviada_dian' => true,
                'fecha_dian'   => now(),
            ]);

            // Registrar éxito
            $evento->update([
                'estado'          => DianEvento::ESTADO_EXITOSO,
                'cufe'            => $resultado['cufe'],
                'codigo_respuesta'=> $resultado['codigo'],
                'descripcion'     => $resultado['descripcion'],
                'payload'         => $resultado,
            ]);

        } catch (\Throwable $e) {
            $evento->update([
                'estado'     => DianEvento::ESTADO_FALLIDO,
                'descripcion'=> $e->getMessage(),
                'errores'    => [$e->getMessage()],
            ]);

            throw $e; // re-lanza para que la cola gestione reintentos
        }
    }

    public function failed(\Throwable $exception): void
    {
        // Después de agotar todos los intentos, marcar el último evento como fallido definitivo
        if ($this->eventoId) {
            DianEvento::find($this->eventoId)?->update([
                'estado'     => DianEvento::ESTADO_FALLIDO,
                'descripcion'=> 'Agotados todos los intentos: ' . $exception->getMessage(),
            ]);
        }
    }
}
