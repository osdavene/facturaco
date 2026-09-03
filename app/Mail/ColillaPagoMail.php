<?php

namespace App\Mail;

use App\Models\Empresa;
use App\Models\Nomina;
use App\Models\NominaEmpleado;
use App\Services\PdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ColillaPagoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Nomina $nomina,
        public NominaEmpleado $liquidacion,
        public Empresa $empresa,
    ) {}

    public function envelope(): Envelope
    {
        $fromAddress = $this->empresa->mail_from_address ?: $this->empresa->email;
        $fromName    = $this->empresa->mail_from_name    ?: $this->empresa->razon_social;
        $empleado    = $this->liquidacion->empleado;

        return new Envelope(
            from:    new Address($fromAddress, $fromName),
            subject: "Desprendible de Pago {$this->nomina->nombre} — {$empleado->nombre_completo}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.colilla',
            with: [
                'empleado' => $this->liquidacion->empleado,
            ]
        );
    }

    public function attachments(): array
    {
        $pdf = app(PdfService::class);
        $nomina      = $this->nomina;
        $liquidacion = $this->liquidacion;
        $empresa     = $this->empresa;
        $empleado    = $liquidacion->empleado;

        return [
            Attachment::fromData(
                fn () => $pdf->output('nomina.colilla', compact('nomina', 'liquidacion', 'empresa')),
                "Colilla-{$empleado->numero_documento}-{$nomina->id}.pdf",
            )->withMime('application/pdf'),
        ];
    }
}
