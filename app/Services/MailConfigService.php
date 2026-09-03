<?php

namespace App\Services;

use App\Models\ConfiguracionPlataforma;
use Illuminate\Support\Facades\Config;

class MailConfigService
{
    /**
     * Aplica la configuración SMTP almacenada en la base de datos de Backoffice.
     */
    public static function aplicarConfiguracion(): void
    {
        $mailer = ConfiguracionPlataforma::get('mail_mailer', config('mail.default', 'smtp'));
        $host   = ConfiguracionPlataforma::get('mail_host', config('mail.mailers.smtp.host'));
        $port   = ConfiguracionPlataforma::get('mail_port', config('mail.mailers.smtp.port', 587));
        $enc    = ConfiguracionPlataforma::get('mail_encryption', config('mail.mailers.smtp.encryption', 'tls'));
        $user   = ConfiguracionPlataforma::get('mail_username', config('mail.mailers.smtp.username'));
        $pass   = ConfiguracionPlataforma::get('mail_password', config('mail.mailers.smtp.password'));
        $from   = ConfiguracionPlataforma::get('mail_from_address', config('mail.from.address'));
        $name   = ConfiguracionPlataforma::get('mail_from_name', config('mail.from.name', 'FacCol'));

        Config::set('mail.default', $mailer);
        Config::set('mail.mailers.smtp.host', $host);
        Config::set('mail.mailers.smtp.port', (int) $port);
        Config::set('mail.mailers.smtp.encryption', $enc === 'null' || empty($enc) ? null : $enc);
        Config::set('mail.mailers.smtp.username', $user);
        Config::set('mail.mailers.smtp.password', $pass);
        Config::set('mail.from.address', $from ?: ($user ?: 'notificaciones@faccol.co'));
        Config::set('mail.from.name', $name ?: 'FacCol Notificaciones');
    }
}
