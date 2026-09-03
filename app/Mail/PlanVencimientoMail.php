<?php

namespace App\Mail;

use App\Models\Empresa;
use App\Services\MailConfigService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PlanVencimientoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Empresa $empresa,
        public int $diasRestantes = 0,
        public ?string $notaAdicional = null
    ) {
        MailConfigService::aplicarConfiguracion();
    }

    public function envelope(): Envelope
    {
        $asunto = $this->diasRestantes <= 0
            ? "⚠️ Tu plan en FacCol ha vencido hoy — Renueva para mantener tu servicio activo"
            : ($this->diasRestantes === 1
                ? "⚡ Tu plan en FacCol vence mañana — Renueva tu suscripción"
                : "📅 Recordatorio: Tu plan en FacCol vence en {$this->diasRestantes} días");

        return new Envelope(
            subject: $asunto,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.plan_vencimiento',
        );
    }
}
