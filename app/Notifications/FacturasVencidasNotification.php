<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Collection;

class FacturasVencidasNotification extends Notification
{
    public function __construct(
        private Collection $facturas,
        private string $razonSocial
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $total = $this->facturas->sum('saldo_pendiente');

        $mail = (new MailMessage)
            ->subject("⚠️ {$this->facturas->count()} factura(s) vencidas — {$this->razonSocial}")
            ->greeting("Hola,")
            ->line("Tienes **{$this->facturas->count()} factura(s) vencidas** con un saldo pendiente de **$" . number_format($total, 2, ',', '.') . "**.")
            ->line('');

        foreach ($this->facturas->take(10) as $f) {
            $mail->line("• **{$f->numero}** — {$f->cliente_nombre} — $" . number_format($f->saldo_pendiente, 2, ',', '.') . " — Venció: {$f->fecha_vencimiento->format('d/m/Y')}");
        }

        if ($this->facturas->count() > 10) {
            $mail->line('... y ' . ($this->facturas->count() - 10) . ' más.');
        }

        return $mail
            ->action('Ver facturas vencidas', url('/facturas?estado=vencida'))
            ->line('Gestiona tu cartera para mantener el flujo de caja al día.');
    }
}
