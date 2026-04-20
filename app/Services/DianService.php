<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\Factura;
use RuntimeException;

class DianService
{
    public function __construct(
        private DianXmlBuilder $builder,
        private DianXmlSigner  $signer,
        private DianSoapClient $soap,
    ) {}

    public function estaConfigurado(): bool
    {
        return filled(config('dian.certificado_path'))
            && filled(config('dian.certificado_password'));
    }

    public function generarXml(Factura $factura): string
    {
        $factura->loadMissing(['items', 'cliente']);
        $empresa = Empresa::findOrFail($factura->empresa_id);
        $cufe    = $this->calcularCufe($factura, $empresa);

        return $this->builder->build($factura, $empresa, $cufe);
    }

    public function firmarXml(string $xml): string
    {
        if (! $this->estaConfigurado()) {
            throw new RuntimeException('Certificado DIAN no configurado. Define DIAN_CERTIFICADO_PATH y DIAN_CERTIFICADO_PASSWORD.');
        }

        return $this->signer->sign($xml);
    }

    public function enviar(Factura $factura): array
    {
        if (! $this->estaConfigurado()) {
            throw new RuntimeException('Certificado DIAN no configurado.');
        }

        $factura->loadMissing(['items', 'cliente']);
        $empresa = Empresa::findOrFail($factura->empresa_id);

        $xml       = $this->generarXml($factura);
        $xmlFirmado = $this->firmarXml($xml);

        $resultado = $this->soap->sendBillSync($xmlFirmado, $empresa, $factura);

        if (! $resultado['valido']) {
            $detalle = implode(' | ', $resultado['errores'] ?: [$resultado['descripcion']]);
            throw new RuntimeException('DIAN rechazó la factura: ' . $detalle);
        }

        return $resultado;
    }

    public function consultarEstado(Factura $factura): array
    {
        $softwareId  = config('dian.software_id');
        $softwarePin = config('dian.software_pin');

        if (! $softwareId || ! $softwarePin) {
            throw new RuntimeException('DIAN_SOFTWARE_ID y DIAN_SOFTWARE_PIN son obligatorios para consultar estado.');
        }

        return $this->soap->getStatusZip($factura->cufe, $softwareId, $softwarePin);
    }

    // ── CUFE (SHA-384) ─────────────────────────────────────────────────────────
    // Spec DIAN: NumFac + FecFac + HoraFac + ValFac +
    //            CodImp1(01) + ValImp1(IVA) +
    //            CodImp2(04) + ValImp2(ICA) +
    //            CodImp3(03) + ValImp3(INC=0) +
    //            ValTot + NitOFE + NumAdq + ClTec + TipoAmbie

    public function calcularCufe(Factura $factura, Empresa $empresa): string
    {
        $ambiente = config('dian.ambiente', 'habilitacion');
        $claveTec = $empresa->clave_tecnica ?? '';

        $nitOfe = preg_replace('/\D/', '', $empresa->nit ?? '');
        $numAdq = preg_replace('/\D/', '', $factura->cliente?->numero_documento
                      ?? $factura->cliente_documento ?? '');

        $cadena = implode('', [
            $factura->numero,
            $factura->fecha_emision->format('Y-m-d'),
            now('America/Bogota')->format('H:i:s') . '-05:00',
            $this->fmt($factura->subtotal),
            '01', $this->fmt($factura->iva),
            '04', $this->fmt($factura->reteica),
            '03', '0.00',
            $this->fmt($factura->total),
            $nitOfe,
            $numAdq,
            $claveTec,
            $ambiente === 'produccion' ? '1' : '2',
        ]);

        return hash('sha384', $cadena);
    }

    private function fmt(float $v): string
    {
        return number_format($v, 2, '.', '');
    }
}
