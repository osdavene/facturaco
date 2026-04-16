<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Collection;

class StockBajoNotification extends Notification
{
    public function __construct(
        private Collection $productos,
        private string $razonSocial
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("📦 {$this->productos->count()} producto(s) con stock bajo — {$this->razonSocial}")
            ->greeting("Hola,")
            ->line("Los siguientes **{$this->productos->count()} producto(s)** están por debajo del stock mínimo configurado:")
            ->line('');

        foreach ($this->productos->take(15) as $p) {
            $mail->line("• **{$p->nombre}** [{$p->codigo}] — Stock actual: {$p->stock_actual} | Mínimo: {$p->stock_minimo}");
        }

        if ($this->productos->count() > 15) {
            $mail->line('... y ' . ($this->productos->count() - 15) . ' más.');
        }

        return $mail
            ->action('Ver inventario', url('/inventario?filtro=stock_bajo'))
            ->line('Considera realizar una orden de compra para reponer el inventario.');
    }
}
