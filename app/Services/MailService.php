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
                "No hay un servidor de correo SMTP configurado ni en la empresa ni en la plataforma."
            );
        }

        $key = 'empresa_smtp_' . $empresa->id;

        // Purgar instancia cacheada para que el worker no reutilice un transport stale
        app('mail.manager')->purge($key);

        if ($this->tieneSmtpPropio($empresa)) {
            Config::set("mail.mailers.{$key}", [
                'transport'  => 'smtp',
                'host'       => $empresa->mail_host,
                'port'       => (int) ($empresa->mail_port ?? 587),
                'encryption' => $empresa->mail_encryption ?: 'tls',
                'username'   => $empresa->mail_username,
                'password'   => $empresa->mail_password,
                'timeout'    => 30,
            ]);
        } else {
            // Fallback al SMTP maestro configurado en Backoffice
            $host = \App\Models\ConfiguracionPlataforma::get('mail_host', config('mail.mailers.smtp.host'));
            $port = \App\Models\ConfiguracionPlataforma::get('mail_port', config('mail.mailers.smtp.port', 587));
            $enc  = \App\Models\ConfiguracionPlataforma::get('mail_encryption', config('mail.mailers.smtp.encryption', 'tls'));
            $user = \App\Models\ConfiguracionPlataforma::get('mail_username', config('mail.mailers.smtp.username'));
            $pass = \App\Models\ConfiguracionPlataforma::get('mail_password', config('mail.mailers.smtp.password'));

            Config::set("mail.mailers.{$key}", [
                'transport'  => 'smtp',
                'host'       => $host,
                'port'       => (int) $port,
                'encryption' => $enc === 'null' || empty($enc) ? null : $enc,
                'username'   => $user,
                'password'   => $pass,
                'timeout'    => 30,
            ]);
        }

        return Mail::mailer($key);
    }

    public function tieneSmtpPropio(Empresa $empresa): bool
    {
        $fromAddress = $empresa->mail_from_address ?: $empresa->email;

        return ! empty($empresa->mail_host)
            && ! empty($empresa->mail_username)
            && ! empty($empresa->mail_password)
            && ! empty($fromAddress);
    }

    public function estaConfigurado(Empresa $empresa): bool
    {
        if ($this->tieneSmtpPropio($empresa)) {
            return true;
        }

        // Verificar si Backoffice tiene SMTP configurado
        $masterHost = \App\Models\ConfiguracionPlataforma::get('mail_host', config('mail.mailers.smtp.host'));
        $masterUser = \App\Models\ConfiguracionPlataforma::get('mail_username', config('mail.mailers.smtp.username'));

        return ! empty($masterHost) && ! empty($masterUser);
    }
}
