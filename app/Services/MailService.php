<?php

namespace App\Services;

use App\Models\Empresa;
use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

/**
 * Crea un mailer SMTP dinámico por empresa.
 *
 * Problema que resuelve: Config::set() en el constructor del Mailable
 * no afecta al transport ya instanciado en el IoC container cuando
 * el job corre en un worker. Este servicio crea un mailer nombrado
 * nuevo cada vez, forzando que Laravel lo instancie con la config correcta.
 */
class MailService
{
    /**
     * Retorna un mailer configurado con los datos SMTP de la empresa.
     * Úsalo como: $this->mail->paraEmpresa($empresa)->to(...)->send(...)
     *
     * @throws \RuntimeException si la empresa no tiene correo configurado
     */
    public function paraEmpresa(Empresa $empresa): Mailer
    {
        if (! $this->estaConfigurado($empresa)) {
            throw new \RuntimeException(
                "La empresa [{$empresa->razon_social}] no tiene configuración de correo SMTP."
            );
        }

        // Nombre único por empresa para que Laravel lo registre como nuevo mailer
        $key = 'empresa_smtp_' . $empresa->id;

        Config::set("mail.mailers.{$key}", [
            'transport'  => 'smtp',
            'host'       => $empresa->mail_host,
            'port'       => (int) ($empresa->mail_port ?? 587),
            'encryption' => $empresa->mail_encryption ?: 'tls',
            'username'   => $empresa->mail_username,
            'password'   => $empresa->mail_password,
            'timeout'    => 30,
        ]);

        return Mail::mailer($key);
    }

    public function estaConfigurado(Empresa $empresa): bool
    {
        return ! empty($empresa->mail_host)
            && ! empty($empresa->mail_username)
            && ! empty($empresa->mail_password);
    }
}
