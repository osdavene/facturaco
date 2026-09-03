<?php

namespace App\Http\Controllers;

use App\Jobs\EnviarDianJob;
use App\Models\DianEvento;
use App\Models\Factura;
use App\Services\DianService;
use Illuminate\Http\Request;

class DianController extends Controller
{
    public function __construct(private DianService $dian) {}

    public function enviar(Factura $factura)
    {
        if (! $this->dian->estaConfigurado()) {
            return back()->with('error', 'La integración DIAN no está configurada en Backoffice.');
        }

        if ($factura->enviada_dian) {
            return back()->with('info', 'Esta factura ya fue enviada a la DIAN. CUFE: ' . $factura->cufe);
        }

        if (! in_array($factura->estado, ['emitida', 'pagada', 'borrador'])) {
            return back()->with('error', 'Solo se pueden enviar facturas emitidas o pagadas a la DIAN.');
        }

        try {
            $resultado = $this->dian->enviar($factura);

            $factura->update([
                'cufe'         => $resultado['cufe'],
                'enviada_dian' => true,
                'fecha_dian'   => now(),
                'estado'       => in_array($factura->estado, ['pagada', 'emitida']) ? $factura->estado : 'emitida',
            ]);

            DianEvento::registrar($factura, DianEvento::TIPO_ENVIO, DianEvento::ESTADO_EXITOSO, [
                'cufe'             => $resultado['cufe'],
                'codigo_respuesta' => $resultado['codigo'],
                'descripcion'      => $resultado['descripcion'],
                'payload'          => $resultado,
            ]);

            return back()->with('success', '¡Factura validada y aprobada por la DIAN exitosamente! CUFE: ' . substr((string)$resultado['cufe'], 0, 16) . '...');
        } catch (\Throwable $e) {
            DianEvento::registrar($factura, DianEvento::TIPO_ENVIO, DianEvento::ESTADO_FALLIDO, [
                'descripcion' => $e->getMessage(),
                'errores'     => [$e->getMessage()],
            ]);

            return back()->with('error', 'Error al enviar a la DIAN: ' . $e->getMessage());
        }
    }

    // ── Consulta de estado ────────────────────────────────────────────────────

    public function consultarEstado(Factura $factura)
    {
        if (! $factura->enviada_dian || ! $factura->cufe) {
            return back()->with('error', 'Esta factura no ha sido enviada a la DIAN aún.');
        }

        try {
            $estado = $this->dian->consultarEstado($factura);

            DianEvento::registrar($factura, DianEvento::TIPO_CONSULTA, DianEvento::ESTADO_EXITOSO, [
                'cufe'        => $factura->cufe,
                'codigo'      => $estado['codigo'],
                'descripcion' => $estado['descripcion'],
                'payload'     => $estado,
            ]);

            $msg = 'Estado DIAN: ' . $estado['descripcion'];
            if (! empty($estado['errores'])) {
                $msg .= ' — ' . implode(' | ', $estado['errores']);
            }

            return back()->with($estado['valido'] ? 'success' : 'error', $msg);

        } catch (\RuntimeException $e) {
            DianEvento::registrar($factura, DianEvento::TIPO_CONSULTA, DianEvento::ESTADO_FALLIDO, [
                'descripcion' => $e->getMessage(),
            ]);
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al consultar DIAN: ' . $e->getMessage());
        }
    }

    // ── Evento del comprador ──────────────────────────────────────────────────

    public function registrarEvento(Request $request, Factura $factura)
    {
        $request->validate([
            'tipo'            => 'required|in:acuse_recibo,recibo_bien,aceptacion,rechazo_comprador',
            'actor_nombre'    => 'nullable|string|max:255',
            'actor_documento' => 'nullable|string|max:20',
            'nota'            => 'nullable|string|max:500',
        ]);

        DianEvento::registrar($factura, $request->tipo, DianEvento::ESTADO_EXITOSO, [
            'descripcion'    => 'Evento registrado manualmente',
            'actor_nombre'   => $request->actor_nombre,
            'actor_documento'=> $request->actor_documento,
            'nota'           => $request->nota,
        ]);

        return back()->with('success', 'Evento registrado correctamente.');
    }

    // ── Descarga XML ──────────────────────────────────────────────────────────

    public function xml(Factura $factura)
    {
        try {
            $xml = $this->dian->generarXml($factura);
            return $this->xmlResponse($xml, $factura->numero . '.xml');
        } catch (\Throwable $e) {
            return back()->with('error', 'Error generando XML: ' . $e->getMessage());
        }
    }

    public function xmlFirmado(Factura $factura)
    {
        if (! $this->dian->estaConfigurado()) {
            return back()->with('error', 'Certificado DIAN no configurado.');
        }

        try {
            $xml = $this->dian->firmarXml($this->dian->generarXml($factura));
            return $this->xmlResponse($xml, $factura->numero . '-firmado.xml');
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
