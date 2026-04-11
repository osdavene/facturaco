<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Genera PDFs con código QR opcional.
 * Elimina la lógica duplicada de QR en cada controlador.
 */
class PdfService
{
    /**
     * Genera un código QR en base64 a partir de un arreglo de líneas.
     *
     * @param  array<string> $lineas
     */
    public function qrBase64(array $lineas, int $size = 120, int $margin = 4): string
    {
        $qr     = QrCode::create(implode("\n", $lineas))->setSize($size)->setMargin($margin);
        $result = (new PngWriter())->write($qr);

        return base64_encode($result->getString());
    }

    /**
     * Devuelve un stream PDF para el navegador.
     *
     * @param  array<string, mixed> $data
     */
    public function stream(string $view, array $data, string $filename): StreamedResponse
    {
        $pdf = Pdf::loadView($view, $data)->setPaper('a4', 'portrait');

        return $pdf->stream($filename);
    }

    /**
     * Devuelve el contenido binario del PDF (para adjuntar en emails).
     *
     * @param  array<string, mixed> $data
     */
    public function output(string $view, array $data): string
    {
        return Pdf::loadView($view, $data)->setPaper('a4', 'portrait')->output();
    }
}
