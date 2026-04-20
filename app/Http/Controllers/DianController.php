<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Services\DianService;

class DianController extends Controller
{
    public function __construct(private DianService $dian) {}

    public function enviar(Factura $factura)
    {
        if (! $this->dian->estaConfigurado()) {
            return back()->with('error', 'La integración DIAN no está configurada. Define DIAN_CERTIFICADO_PATH y DIAN_CERTIFICADO_PASSWORD en el servidor.');
        }

        if ($factura->enviada_dian) {
            return back()->with('info', 'Esta factura ya fue enviada a la DIAN. CUFE: ' . $factura->cufe);
        }

        if (! in_array($factura->estado, ['emitida', 'pagada'])) {
            return back()->with('error', 'Solo se pueden enviar facturas emitidas o pagadas a la DIAN.');
        }

        try {
            $resultado = $this->dian->enviar($factura);

            $factura->update([
                'cufe'         => $resultado['cufe'],
                'enviada_dian' => true,
                'fecha_dian'   => now(),
            ]);

            return back()->with('success', 'Factura enviada a la DIAN correctamente. CUFE: ' . $resultado['cufe']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al enviar a DIAN: ' . $e->getMessage());
        }
    }

    public function consultarEstado(Factura $factura)
    {
        if (! $factura->enviada_dian || ! $factura->cufe) {
            return back()->with('error', 'Esta factura no ha sido enviada a la DIAN aún.');
        }

        try {
            $estado = $this->dian->consultarEstado($factura);
            return back()->with('info', 'Estado DIAN: ' . $estado['descripcion']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al consultar DIAN: ' . $e->getMessage());
        }
    }

    /** Descarga el XML sin firma (útil para revisar estructura antes de tener certificado). */
    public function xml(Factura $factura)
    {
        try {
            $xml      = $this->dian->generarXml($factura);
            $filename = $factura->numero . '.xml';

            return $this->xmlResponse($xml, $filename);
        } catch (\Throwable $e) {
            return back()->with('error', 'Error generando XML: ' . $e->getMessage());
        }
    }

    /** Descarga el XML con firma XAdES-BES (requiere certificado configurado). */
    public function xmlFirmado(Factura $factura)
    {
        if (! $this->dian->estaConfigurado()) {
            return back()->with('error', 'Certificado DIAN no configurado. Define DIAN_CERTIFICADO_PATH y DIAN_CERTIFICADO_PASSWORD.');
        }

        try {
            $xml      = $this->dian->generarXml($factura);
            $xml      = $this->dian->firmarXml($xml);
            $filename = $factura->numero . '-firmado.xml';

            return $this->xmlResponse($xml, $filename);
        } catch (\Throwable $e) {
            return back()->with('error', 'Error firmando XML: ' . $e->getMessage());
        }
    }

    private function xmlResponse(string $xml, string $filename)
    {
        return response($xml, 200, [
            'Content-Type'        => 'application/xml; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
