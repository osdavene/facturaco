<?php

namespace App\Mail;

use App\Models\Factura;
use App\Models\Empresa;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class FacturaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Factura $factura,
        public Empresa $empresa,
        public string  $mensaje = '',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Factura ' . $this->factura->numero . ' — ' . $this->empresa->razon_social,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.factura',
        );
    }

    public function attachments(): array
    {
        // Generar PDF en memoria y adjuntarlo
        $this->factura->load(['items', 'cliente']);

        $qrData = implode("\n", [
            'Factura: ' . $this->factura->numero,
            'NIT: '     . $this->empresa->nit_formateado,
            'Cliente: ' . $this->factura->cliente_nombre,
            'Total: $'  . number_format($this->factura->total, 0, ',', '.'),
        ]);

        $qr       = \Endroid\QrCode\QrCode::create($qrData)->setSize(120)->setMargin(4);
        $writer   = new \Endroid\QrCode\Writer\PngWriter();
        $qrBase64 = base64_encode($writer->write($qr)->getString());

        $factura  = $this->factura;
        $empresa  = $this->empresa;

        $pdf = Pdf::loadView('facturas.pdf', compact('factura', 'empresa', 'qrBase64'))
                  ->setPaper('a4', 'portrait');

        return [
            Attachment::fromData(
                fn () => $pdf->output(),
                'Factura-' . $this->factura->numero . '.pdf'
            )->withMime('application/pdf'),
        ];
    }
}