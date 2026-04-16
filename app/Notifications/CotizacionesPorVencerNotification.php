<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Collection;

class CotizacionesPorVencerNotification extends Notification
{
    public function __construct(
        private Collection $cotizaciones,
        private string $razonSocial
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("📋 {$this->cotizaciones->count()} cotización(es) por vencer — {$this->razonSocial}")
            ->greeting("Hola,")
            ->line("Las siguientes cotizaciones vencen en los próximos **7 días**:")
            ->line('');

        foreach ($this->cotizaciones as $c) {
            $dias = now()->diffInDays($c->fecha_vencimiento, false);
            $label = $dias === 0 ? 'hoy' : "en {$dias} día(s)";
            $mail->line("• **{$c->numero}** — {$c->cliente_nombre} — $" . number_format($c->total, 2, ',', '.') . " — Vence {$label} ({$c->fecha_vencimiento->format('d/m/Y')})");
        }

        return $mail
            ->action('Ver cotizaciones', url('/cotizaciones'))
            ->line('Da seguimiento a tus cotizaciones para no perder oportunidades de venta.');
    }
}
