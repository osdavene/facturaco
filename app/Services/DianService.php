<?php

namespace App\Services;

use App\Contracts\DianProviderInterface;
use App\Models\Empresa;
use App\Models\Factura;
use App\Services\Dian\DirectoSoapProvider;
use App\Services\Dian\FactusProvider;
use InvalidArgumentException;
use RuntimeException;

class DianService
{
    private array $providers = [];

    public function __construct(
        private DirectoSoapProvider $soapProvider,
        private FactusProvider      $factusProvider,
    ) {
        $this->providers['directo'] = $this->soapProvider;
        $this->providers['factus']  = $this->factusProvider;
    }

    /**
     * Obtiene el proveedor DIAN activo según la configuración o la empresa.
     */
    public function getProvider(?string $nombre = null): DianProviderInterface
    {
        $driver = $nombre
            ?: \App\Models\ConfiguracionPlataforma::get('dian_proveedor', config('dian.proveedor', 'factus'));

        if (! isset($this->providers[$driver])) {
            throw new InvalidArgumentException("El proveedor DIAN [{$driver}] no está soportado.");
        }

        return $this->providers[$driver];
    }

    public function estaConfigurado(?Empresa $empresa = null): bool
    {
        return $this->getProvider()->estaConfigurado($empresa);
    }

    public function tieneCertificadoFirma(): bool
    {
        return $this->soapProvider->estaConfigurado();
    }

    public function enviar(Factura $factura): array
    {
        $provider = $this->getProvider();

        if (! $provider->estaConfigurado()) {
            $nombreProv = config('dian.proveedor', 'factus');
            throw new RuntimeException("La integración DIAN con [{$nombreProv}] no está configurada.");
        }

        $resultado = $provider->enviar($factura);

        if (! ($resultado['valido'] ?? false)) {
            $detalle = implode(' | ', $resultado['errores'] ?: [$resultado['descripcion'] ?? 'Error de validación DIAN']);
            throw new RuntimeException('DIAN rechazó la factura: ' . $detalle);
        }

        return $resultado;
    }

    public function consultarEstado(Factura $factura): array
    {
        return $this->getProvider()->consultarEstado($factura);
    }

    public function generarXml(Factura $factura): string
    {
        return $this->soapProvider->generarXml($factura);
    }

    public function firmarXml(string $xml): string
    {
        return $this->soapProvider->firmarXml($xml);
    }

    public function calcularCufe(Factura $factura, Empresa $empresa): string
    {
        return $this->soapProvider->calcularCufe($factura, $empresa);
    }
}
