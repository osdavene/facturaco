<?php

namespace App\Contracts;

use App\Models\Empresa;
use App\Models\Factura;

interface DianProviderInterface
{
    /**
     * Verifica si el proveedor cuenta con las credenciales necesarias para operar.
     */
    public function estaConfigurado(?Empresa $empresa = null): bool;

    /**
     * Envía una factura electrónica a la DIAN mediante este proveedor.
     *
     * Retorna array estructurado:
     * [
     *   'valido'      => bool,
     *   'cufe'        => string,
     *   'qr'          => ?string,
     *   'qr_image'    => ?string,
     *   'pdf_url'     => ?string,
     *   'xml_url'     => ?string,
     *   'codigo'      => string,
     *   'descripcion' => string,
     *   'errores'     => array,
     *   'payload'     => array,
     * ]
     */
    public function enviar(Factura $factura): array;

    /**
     * Consulta el estado de una factura previamente enviada a la DIAN.
     */
    public function consultarEstado(Factura $factura): array;
}
