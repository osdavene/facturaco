<?php

namespace App\Services\Dian;

use App\Contracts\DianProviderInterface;
use App\Models\Empresa;
use App\Models\Factura;
use App\Services\DianSoapClient;
use App\Services\DianXmlBuilder;
use App\Services\DianXmlSigner;
use RuntimeException;

class DirectoSoapProvider implements DianProviderInterface
{
    public function __construct(
        private DianXmlBuilder $builder,
        private DianXmlSigner  $signer,
        private DianSoapClient $soap,
    ) {}

    public function estaConfigurado(?Empresa $empresa = null): bool
    {
        $path = config('dian.certificado_path');
        return filled($path)
            && file_exists($path)
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

        $xml        = $this->generarXml($factura);
        $xmlFirmado = $this->firmarXml($xml);

        $resultado  = $this->soap->sendBillSync($xmlFirmado, $empresa, $factura);

        if (! ($resultado['valido'] ?? false)) {
            $detalle = implode(' | ', $resultado['errores'] ?: [$resultado['descripcion'] ?? 'Error desconocido']);
            throw new RuntimeException('DIAN rechazó la factura: ' . $detalle);
        }

        return $resultado;
    }

    public function consultarEstado(Factura $factura): array
    {
        if (! $this->estaConfigurado()) {
            throw new RuntimeException('Certificado DIAN no configurado.');
        }

        $empresa = Empresa::findOrFail($factura->empresa_id);

        return $this->soap->getStatus($factura->cufe, $empresa);
    }

    public function calcularCufe(Factura $factura, Empresa $empresa): string
    {
        $numFac = $factura->numero;
        $fecFac = $factura->fecha_emision?->format('Y-m-d') ?? now()->format('Y-m-d');
        $horFac = $factura->hora_emision ?? now()->format('H:i:s') . '-05:00';
        $valFac = number_format((float)$factura->subtotal, 2, '.', '');
        $codImp1 = '01';
        $valImp1 = number_format((float)$factura->iva, 2, '.', '');
        $codImp2 = '04'; $valImp2 = '0.00';
        $codImp3 = '03'; $valImp3 = '0.00';
        $valTot  = number_format((float)$factura->total, 2, '.', '');
        $nitEmi  = preg_replace('/\D/', '', $empresa->nit ?? '');
        $docAdq  = preg_replace('/\D/', '', $factura->cliente?->numero_documento ?? '222222222222');
        $clvTec  = $empresa->clave_tecnica ?? config('dian.clave_tecnica', '');
        $tipoAmb = config('dian.ambiente') === 'produccion' ? '1' : '2';

        $cadena = $numFac . $fecFac . $horFac . $valFac . $codImp1 . $valImp1 . $codImp2 . $valImp2 . $codImp3 . $valImp3 . $valTot . $nitEmi . $docAdq . $clvTec . $tipoAmb;

        return hash('sha384', $cadena);
    }
}
