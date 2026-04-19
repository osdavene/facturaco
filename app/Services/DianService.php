<?php

namespace App\Services;

use App\Models\Factura;
use RuntimeException;

class DianService
{
    public function estaConfigurado(): bool
    {
        return filled(config('dian.certificado_path'))
            && filled(config('dian.certificado_password'));
    }

    public function generarXml(Factura $factura): string
    {
        throw new RuntimeException('DIAN XML UBL 2.1 no implementado aún (Parte 2).');
    }

    public function firmarXml(string $xml): string
    {
        throw new RuntimeException('Firma digital XAdES no implementada aún (Parte 3).');
    }

    public function enviar(Factura $factura): array
    {
        throw new RuntimeException('Envío a DIAN no implementado aún (Parte 4).');
    }

    public function consultarEstado(Factura $factura): array
    {
        throw new RuntimeException('Consulta de estado DIAN no implementada aún (Parte 4).');
    }
}
