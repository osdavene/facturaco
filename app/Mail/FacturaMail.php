<?php

namespace App\Mail;

use App\Models\Empresa;
use App\Models\Factura;
use App\Services\PdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;

class FacturaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Factura $factura,
        public Empresa $empresa,
        public string  $mensaje = '',
    ) {
        if ($empresa->mail_host && $empresa->mail_username && $empresa->mail_password) {
            Config::set('mail.mailers.smtp.host',       $empresa->mail_host);
            Config::set('mail.mailers.smtp.port',       $empresa->mail_port ?? 587);
            Config::set('mail.mailers.smtp.username',   $empresa->mail_username);
            Config::set('mail.mailers.smtp.password',   $empresa->mail_password);
            Config::set('mail.mailers.smtp.encryption', $empresa->mail_encryption ?? 'tls');
            Config::set('mail.from.address',            $empresa->mail_from_address ?? $empresa->email);
            Config::set('mail.from.name',               $empresa->mail_from_name    ?? $empresa->razon_social);
            Config::set('mail.default', 'smtp');
        }
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Factura '.$this->factura->numero.' — '.$this->empresa->razon_social,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.factura');
    }

    public function attachments(): array
    {
        $this->factura->load(['items', 'cliente']);

        $pdf      = app(PdfService::class);
        $factura  = $this->factura;
        $empresa  = $this->empresa;

        $qrBase64 = $pdf->qrBase64([
            'Factura: ' . $factura->numero,
            'NIT: '     . $empresa->nit_formateado,
            'Cliente: ' . $factura->cliente_nombre,
            'Total: $'  . number_format($factura->total, 0, ',', '.'),
        ]);

        return [
            Attachment::fromData(
                fn () => $pdf->output('facturas.pdf', compact('factura', 'empresa', 'qrBase64')),
                'Factura-'.$factura->numero.'.pdf',
            )->withMime('application/pdf'),
        ];
    }
}
