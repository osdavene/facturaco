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

    /**
     * Envía una factura por correo con su PDF adjunto, usando Resend API o SMTP según corresponda.
     */
    public function enviarFactura(\App\Models\Factura $factura, Empresa $empresa, string $email, string $mensaje = ''): bool
    {
        if (! $this->estaConfigurado($empresa)) {
            throw new \RuntimeException(
                "No hay un servidor de correo configurado ni en la empresa ni en la plataforma."
            );
        }

        $factura->loadMissing(['items', 'cliente']);

        // Determinar credenciales
        if ($this->tieneSmtpPropio($empresa)) {
            $host = $empresa->mail_host;
            $user = $empresa->mail_username;
            $pass = $empresa->mail_password;
            $from = $empresa->mail_from_address ?: $empresa->email;
            $name = $empresa->mail_from_name ?: $empresa->razon_social;
        } else {
            $host = \App\Models\ConfiguracionPlataforma::get('mail_host', config('mail.mailers.smtp.host'));
            $user = \App\Models\ConfiguracionPlataforma::get('mail_username', config('mail.mailers.smtp.username'));
            $pass = \App\Models\ConfiguracionPlataforma::get('mail_password', config('mail.mailers.smtp.password'));
            $from = \App\Models\ConfiguracionPlataforma::get('mail_from_address', config('mail.from.address', 'onboarding@resend.dev'));
            $name = \App\Models\ConfiguracionPlataforma::get('mail_from_name', config('mail.from.name', 'FacCol'));
        }

        // Si es Resend (API Key o usuario resend), enviar vía Resend HTTPS REST API (Puerto 443)
        if (str_contains(strtolower((string)$host), 'resend') || strtolower((string)$user) === 'resend' || str_starts_with((string)$pass, 're_')) {
            $pdf = app(PdfService::class);
            $qrContent = $factura->cufe
                ? "NumFac: {$factura->numero}\nFecFac: {$factura->fecha_emision->format('Y-m-d')}\nNitFac: {$empresa->nit}\nDocAdq: {$factura->cliente_documento}\nValFac: " . number_format($factura->subtotal, 2, '.', '') . "\nValIva: " . number_format($factura->iva, 2, '.', '') . "\nValOtroIm: 0.00\nValTotal: " . number_format($factura->total, 2, '.', '') . "\nCUFE: {$factura->cufe}\nQRCode: https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey={$factura->cufe}"
                : "Factura: {$factura->numero}\nNIT: {$empresa->nit_formateado}\nCliente: {$factura->cliente_nombre}\nTotal: $" . number_format($factura->total, 0, ',', '.');
            $qrBase64 = $pdf->qrBase64([$qrContent]);
            $pdfContent = $pdf->output('facturas.pdf', compact('factura', 'empresa', 'qrBase64'));
            $pdfBase64 = base64_encode($pdfContent);

            $attachments = [
                [
                    'filename' => "Factura-{$factura->numero}.pdf",
                    'content'  => $pdfBase64,
                ],
            ];

            try {
                $xml = app(\App\Services\DianService::class)->generarXml($factura);
                $attachments[] = [
                    'filename' => "Factura-{$factura->numero}.xml",
                    'content'  => base64_encode($xml),
                ];
            } catch (\Throwable) {}

            $htmlBody = view('emails.factura', compact('factura', 'empresa', 'mensaje'))->render();

            $response = \Illuminate\Support\Facades\Http::withToken($pass)
                ->acceptJson()
                ->post('https://api.resend.com/emails', [
                    'from'        => "{$name} <{$from}>",
                    'to'          => [$email],
                    'subject'     => "Factura {$factura->numero} — {$empresa->razon_social}",
                    'html'        => $htmlBody,
                    'attachments' => $attachments,
                ]);

            if (! $response->successful()) {
                $err = $response->json('message') ?? ($response->json('error') ?? $response->body());
                throw new \RuntimeException($err);
            }

            return true;
        }

        // Envío SMTP estándar
        $this->paraEmpresa($empresa)
             ->to($email)
             ->send(new \App\Mail\FacturaMail($factura, $empresa, $mensaje));

        return true;
    }

    /**
     * Envía la colilla de pago por correo al empleado.
     */
    public function enviarColillaPago(\App\Models\Nomina $nomina, \App\Models\NominaEmpleado $liquidacion, Empresa $empresa): bool
    {
        $empleado = $liquidacion->empleado;
        if (! $empleado || empty($empleado->email)) {
            throw new \RuntimeException("El empleado no tiene correo electrónico registrado.");
        }

        $email = $empleado->email;

        // Determinar credenciales
        if ($this->tieneSmtpPropio($empresa)) {
            $host = $empresa->mail_host;
            $user = $empresa->mail_username;
            $pass = $empresa->mail_password;
            $from = $empresa->mail_from_address ?: $empresa->email;
            $name = $empresa->mail_from_name ?: $empresa->razon_social;
        } else {
            $host = \App\Models\ConfiguracionPlataforma::get('mail_host', config('mail.mailers.smtp.host'));
            $user = \App\Models\ConfiguracionPlataforma::get('mail_username', config('mail.mailers.smtp.username'));
            $pass = \App\Models\ConfiguracionPlataforma::get('mail_password', config('mail.mailers.smtp.password'));
            $from = \App\Models\ConfiguracionPlataforma::get('mail_from_address', config('mail.from.address', 'onboarding@resend.dev'));
            $name = \App\Models\ConfiguracionPlataforma::get('mail_from_name', config('mail.from.name', 'FacCol'));
        }

        $pdf = app(PdfService::class);
        $pdfContent = $pdf->output('nomina.colilla', compact('nomina', 'liquidacion', 'empresa'));
        $pdfBase64  = base64_encode($pdfContent);

        $htmlBody = view('emails.colilla', compact('nomina', 'liquidacion', 'empresa', 'empleado'))->render();

        if (str_contains(strtolower((string)$host), 'resend') || strtolower((string)$user) === 'resend' || str_starts_with((string)$pass, 're_')) {
            $response = \Illuminate\Support\Facades\Http::withToken($pass)
                ->acceptJson()
                ->post('https://api.resend.com/emails', [
                    'from'        => "{$name} <{$from}>",
                    'to'          => [$email],
                    'subject'     => "Desprendible de Pago {$nomina->nombre} — {$empleado->nombre_completo}",
                    'html'        => $htmlBody,
                    'attachments' => [
                        [
                            'filename' => "Colilla-{$empleado->numero_documento}-{$nomina->id}.pdf",
                            'content'  => $pdfBase64,
                        ],
                    ],
                ]);

            if (! $response->successful()) {
                $err = $response->json('message') ?? ($response->json('error') ?? $response->body());
                throw new \RuntimeException($err);
            }

            return true;
        }

        // Envío SMTP estándar
        $this->paraEmpresa($empresa)
             ->to($email)
             ->send(new \App\Mail\ColillaPagoMail($nomina, $liquidacion, $empresa));

        return true;
    }
}
