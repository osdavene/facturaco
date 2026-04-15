<?php

namespace App\Mail;

use App\Models\Empresa;
use App\Models\Factura;
use App\Services\PdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FacturaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Factura $factura,
        public Empresa $empresa,
        public string  $mensaje = '',
    ) {
        // NADA aquí — Config::set en el constructor no afecta al transport
        // ya instanciado en el queue worker. El mailer se configura en
        // EnviarFacturaJob usando MailService::paraEmpresa().
    }

    public function envelope(): Envelope
    {
        $fromAddress = $this->empresa->mail_from_address ?: $this->empresa->email;
        $fromName    = $this->empresa->mail_from_name    ?: $this->empresa->razon_social;

        return new Envelope(
            from:    new Address($fromAddress, $fromName),
            subject: 'Factura ' . $this->factura->numero . ' — ' . $this->empresa->razon_social,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.factura');
    }

    public function attachments(): array
    {
        $this->factura->loadMissing(['items', 'cliente']);

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
                'Factura-' . $factura->numero . '.pdf',
            )->withMime('application/pdf'),
        ];
    }
}
