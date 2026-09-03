<?php

namespace App\Services\Dian;

use App\Contracts\DianProviderInterface;
use App\Models\Empresa;
use App\Models\Factura;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class FactusProvider implements DianProviderInterface
{
    private function getAmbiente(): string
    {
        return \App\Models\ConfiguracionPlataforma::get('dian_factus_ambiente', config('dian.factus.ambiente', 'sandbox'));
    }

    private function getBaseUrl(): string
    {
        return $this->getAmbiente() === 'produccion'
            ? \App\Models\ConfiguracionPlataforma::get('dian_factus_url_prod', config('dian.factus.url_produccion', 'https://api.factus.com.co'))
            : \App\Models\ConfiguracionPlataforma::get('dian_factus_url_sand', config('dian.factus.url_sandbox', 'https://api-sandbox.factus.com.co'));
    }

    private function getApiToken(): ?string
    {
        return \App\Models\ConfiguracionPlataforma::get('dian_factus_token', config('dian.factus.api_token') ?: config('dian.proveedor_api_key'));
    }

    public function estaConfigurado(?Empresa $empresa = null): bool
    {
        if (filled($this->getApiToken())) {
            return true;
        }

        $clientId     = \App\Models\ConfiguracionPlataforma::get('dian_factus_client_id', config('dian.factus.client_id'));
        $clientSecret = \App\Models\ConfiguracionPlataforma::get('dian_factus_client_secret', config('dian.factus.client_secret'));
        $username     = \App\Models\ConfiguracionPlataforma::get('dian_factus_username', config('dian.factus.username'));
        $password     = \App\Models\ConfiguracionPlataforma::get('dian_factus_password', config('dian.factus.password'));

        return filled($clientId) && filled($clientSecret) && filled($username) && filled($password);
    }

    /**
     * Obtiene o renueva el token Bearer para Factus.
     */
    public function obtenerToken(): string
    {
        $token = $this->getApiToken();
        if (filled($token)) {
            return $token;
        }

        return Cache::remember('factus_access_token', 3600, function () {
            $clientId     = \App\Models\ConfiguracionPlataforma::get('dian_factus_client_id', config('dian.factus.client_id'));
            $clientSecret = \App\Models\ConfiguracionPlataforma::get('dian_factus_client_secret', config('dian.factus.client_secret'));
            $username     = \App\Models\ConfiguracionPlataforma::get('dian_factus_username', config('dian.factus.username'));
            $password     = \App\Models\ConfiguracionPlataforma::get('dian_factus_password', config('dian.factus.password'));

            $response = Http::asForm()->post("{$this->getBaseUrl()}/oauth/token", [
                'grant_type'    => 'password',
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'username'      => $username,
                'password'      => $password,
            ]);

            if (! $response->successful()) {
                Log::error('Error autenticando con Factus API', ['response' => $response->json()]);
                throw new RuntimeException('No se pudo autenticar con Factus API: ' . ($response->json('message') ?? $response->body()));
            }

            return $response->json('access_token');
        });
    }

    public function enviar(Factura $factura): array
    {
        $factura->loadMissing(['items.producto', 'cliente']);
        $empresa = Empresa::findOrFail($factura->empresa_id);

        $payload = $this->construirPayloadFactura($factura, $empresa);
        $token   = $this->obtenerToken();

        Log::info('Enviando factura a Factus API', ['numero' => $factura->numero, 'payload' => $payload]);

        $response = Http::withToken($token)
            ->acceptJson()
            ->post("{$this->baseUrl}/v1/bills/validate", $payload);

        $body = $response->json() ?? [];
        Log::info('Respuesta de Factus API', ['status' => $response->status(), 'response' => $body]);

        if (! $response->successful()) {
            $errores = [];
            if (isset($body['errors'])) {
                foreach ((array)$body['errors'] as $k => $errList) {
                    $errores[] = is_array($errList) ? implode(', ', $errList) : $errList;
                }
            }

            $mensaje = $body['message'] ?? 'Error desconocido al validar con Factus/DIAN';
            return [
                'valido'      => false,
                'cufe'        => null,
                'qr'          => null,
                'qr_image'    => null,
                'pdf_url'     => null,
                'xml_url'     => null,
                'codigo'      => (string)$response->status(),
                'descripcion' => $mensaje,
                'errores'     => $errores ?: [$mensaje],
                'payload'     => $body,
            ];
        }

        $billData = $body['data']['bill'] ?? $body['data'] ?? [];

        return [
            'valido'      => true,
            'cufe'        => $billData['cufe'] ?? null,
            'qr'          => $billData['qr'] ?? null,
            'qr_image'    => $billData['qr_image'] ?? null,
            'pdf_url'     => $billData['public_url'] ?? $billData['pdf'] ?? null,
            'xml_url'     => $billData['xml'] ?? null,
            'codigo'      => (string)($billData['number'] ?? $factura->numero),
            'descripcion' => $body['message'] ?? 'Factura validada y aceptada por la DIAN exitosamente.',
            'errores'     => [],
            'payload'     => $body,
        ];
    }

    public function consultarEstado(Factura $factura): array
    {
        $token = $this->obtenerToken();
        $numero = $factura->numero;

        $response = Http::withToken($token)
            ->acceptJson()
            ->get("{$this->baseUrl}/v1/bills/show/{$numero}");

        $body = $response->json() ?? [];

        if (! $response->successful()) {
            return [
                'valido'      => false,
                'codigo'      => (string)$response->status(),
                'descripcion' => $body['message'] ?? 'Factura no encontrada en Factus',
                'errores'     => [$body['message'] ?? 'Error al consultar estado'],
                'payload'     => $body,
            ];
        }

        $billData = $body['data']['bill'] ?? $body['data'] ?? [];

        return [
            'valido'      => ($billData['status'] ?? '') === 'valid' || ($billData['status'] ?? '') === 'validated',
            'cufe'        => $billData['cufe'] ?? $factura->cufe,
            'qr'          => $billData['qr'] ?? null,
            'pdf_url'     => $billData['public_url'] ?? null,
            'xml_url'     => $billData['xml'] ?? null,
            'codigo'      => (string)($billData['number'] ?? $numero),
            'descripcion' => 'Estado en DIAN: ' . ($billData['status'] ?? 'Consultado'),
            'errores'     => [],
            'payload'     => $body,
        ];
    }

    /**
     * Mapea la factura de FacturaCO al estándar JSON de Factus v1.
     */
    public function construirPayloadFactura(Factura $factura, Empresa $empresa): array
    {
        $cliente = $factura->cliente;

        // 1. Forma y medio de pago
        $formaPago = $factura->forma_pago === 'credito' ? '2' : '1'; // 1: Contado, 2: Crédito
        $metodoPagoCode = match($factura->forma_pago) {
            'efectivo'          => '10',
            'transferencia'     => '47',
            'tarjeta_debito'    => '49',
            'tarjeta_credito'   => '48',
            'nequi', 'daviplata'=> '47',
            default             => '10',
        };

        // 2. Cliente
        $tipoDocDian = match(strtoupper((string)$cliente?->tipo_documento)) {
            'CC'     => '3',  // Cédula de Ciudadanía
            'NIT'    => '6',  // NIT
            'CE'     => '2',  // Cédula de Extranjería
            'PASAPORTE' => '7',
            default  => '3',
        };

        $tipoPersonaId = ($cliente?->tipo_persona === 'juridica' || strtoupper((string)$cliente?->tipo_documento) === 'NIT') ? '1' : '2';

        $customer = [
            'identification'            => preg_replace('/\D/', '', $cliente?->numero_documento ?? '222222222222'),
            'dv'                        => $cliente?->digito_verificacion ?: null,
            'names'                     => $factura->cliente_nombre ?: ($cliente?->nombre_completo ?? 'CONSUMIDOR FINAL'),
            'address'                   => $factura->cliente_direccion ?: ($cliente?->direccion ?? 'Calle Principal'),
            'email'                     => $factura->cliente_email ?: ($cliente?->email ?? 'facturacion@empresa.com'),
            'phone'                     => $cliente?->telefono ?: ($cliente?->celular ?? '3000000000'),
            'legal_organization_id'     => $tipoPersonaId,
            'tribute_id'                => '21', // No responsable de IVA / simplificado o '01'
            'identification_document_id'=> $tipoDocDian,
            'municipality_id'           => '980', // Default Montería/Bogotá
        ];

        // 3. Ítems
        $items = [];
        foreach ($factura->items as $idx => $it) {
            $items[] = [
                'code_reference'   => $it->codigo ?: 'ITEM-' . ($idx + 1),
                'name'             => $it->descripcion ?: 'Producto / Servicio',
                'quantity'         => (float)$it->cantidad,
                'discount_rate'    => (float)($it->descuento_pct ?? 0),
                'price'            => (float)$it->precio_unitario, // Base antes de impuestos
                'tax_rate'         => number_format((float)($it->iva_pct ?? 19), 2, '.', ''),
                'unit_measure_id'  => 70, // 70 = Unidad (UN) estándar DIAN
                'standard_code_id' => 1,
                'is_excluded'      => 0,
                'tribute_id'       => 1, // 1 = IVA
            ];
        }

        $numberingRangeId = (int) (config('dian.factus.numbering_range_id') ?: 1);

        $payload = [
            'numbering_range_id' => $numberingRangeId,
            'reference_code'     => $factura->numero,
            'observation'        => $factura->observaciones ?: 'Factura de venta electrónica',
            'payment_form'       => $formaPago,
            'payment_method_code'=> $metodoPagoCode,
            'customer'           => $customer,
            'items'              => $items,
        ];

        if ($formaPago === '2' && $factura->fecha_vencimiento) {
            $payload['payment_due_date'] = $factura->fecha_vencimiento->format('Y-m-d');
        }

        return $payload;
    }
}
