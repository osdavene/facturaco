<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\Factura;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use ZipArchive;

/**
 * Envía documentos electrónicos al web service SOAP de la DIAN (WcfDianCustomerServices).
 * Autenticación: WS-Security UsernameToken con PasswordDigest (SHA-1).
 */
class DianSoapClient
{
    private const NS_WCF  = 'http://wcf.dian.colombia';
    private const NS_WSSE = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
    private const NS_WSU  = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd';
    private const NS_SOAP = 'http://www.w3.org/2003/05/soap-envelope';

    // ── Envío de factura ──────────────────────────────────────────────────────

    public function sendBillSync(string $xmlFirmado, Empresa $empresa, Factura $factura): array
    {
        $softwareId  = config('dian.software_id');
        $softwarePin = config('dian.software_pin');

        if (! $softwareId || ! $softwarePin) {
            throw new RuntimeException('DIAN_SOFTWARE_ID y DIAN_SOFTWARE_PIN son obligatorios para enviar a DIAN.');
        }

        $xmlFilename = $this->buildXmlFilename($empresa->nit, $factura->numero);
        $zipContent  = $this->zipXml($xmlFilename, $xmlFirmado);
        $zipBase64   = base64_encode($zipContent);
        $zipFilename = substr($xmlFilename, 0, -4) . '.zip';

        [$nonce, $created, $passDigest] = $this->wsSecurityTokens($softwarePin);

        $envelope = $this->envelopeSendBillSync(
            $softwareId, $passDigest, $nonce, $created,
            $zipFilename, $zipBase64
        );

        $body = $this->postSoap($envelope, 'SendBillSync');

        return $this->parseSendBillSyncResponse($body, $factura->cufe ?? '');
    }

    // ── Consulta de estado ─────────────────────────────────────────────────────

    public function getStatusZip(string $cufe, string $softwareId, string $softwarePin): array
    {
        [$nonce, $created, $passDigest] = $this->wsSecurityTokens($softwarePin);

        $envelope = $this->envelopeGetStatusZip($softwareId, $passDigest, $nonce, $created, $cufe);
        $body     = $this->postSoap($envelope, 'GetStatusZip');

        return $this->parseGetStatusZipResponse($body);
    }

    // ── Envelopes SOAP ────────────────────────────────────────────────────────

    private function envelopeSendBillSync(
        string $user, string $pass, string $nonce, string $created,
        string $fileName, string $contentBase64
    ): string {
        $sec = $this->securityHeader($user, $pass, $nonce, $created);

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope"
               xmlns:wcf="http://wcf.dian.colombia">
  <soap:Header>
    {$sec}
  </soap:Header>
  <soap:Body>
    <wcf:SendBillSync>
      <wcf:fileName>{$fileName}</wcf:fileName>
      <wcf:contentFile>{$contentBase64}</wcf:contentFile>
    </wcf:SendBillSync>
  </soap:Body>
</soap:Envelope>
XML;
    }

    private function envelopeGetStatusZip(
        string $user, string $pass, string $nonce, string $created,
        string $trackId
    ): string {
        $sec = $this->securityHeader($user, $pass, $nonce, $created);

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope"
               xmlns:wcf="http://wcf.dian.colombia">
  <soap:Header>
    {$sec}
  </soap:Header>
  <soap:Body>
    <wcf:GetStatusZip>
      <wcf:trackId>{$trackId}</wcf:trackId>
    </wcf:GetStatusZip>
  </soap:Body>
</soap:Envelope>
XML;
    }

    private function securityHeader(
        string $user, string $pass, string $nonce, string $created
    ): string {
        $wsse = self::NS_WSSE;
        $wsu  = self::NS_WSU;
        $type = $wsse . '#PasswordDigest';
        $enc  = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary';

        return <<<XML
<wsse:Security xmlns:wsse="{$wsse}" xmlns:wsu="{$wsu}" soap:mustUnderstand="1">
  <wsse:UsernameToken>
    <wsse:Username>{$user}</wsse:Username>
    <wsse:Password Type="{$type}">{$pass}</wsse:Password>
    <wsse:Nonce EncodingType="{$enc}">{$nonce}</wsse:Nonce>
    <wsu:Created>{$created}</wsu:Created>
  </wsse:UsernameToken>
</wsse:Security>
XML;
    }

    // ── Parsers de respuesta ──────────────────────────────────────────────────

    private function parseSendBillSyncResponse(string $soapXml, string $cufeLocal): array
    {
        $dom = new DOMDocument();
        if (! @$dom->loadXML($soapXml)) {
            throw new RuntimeException('La DIAN devolvió una respuesta no XML: ' . substr($soapXml, 0, 300));
        }

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('wcf', self::NS_WCF);

        // El resultado puede venir como XML embebido o como base64
        $resultNode = $xpath->query('//*[local-name()="SendBillSyncResult"]')->item(0);

        if (! $resultNode) {
            // Puede haber un Fault SOAP
            $faultNode = $xpath->query('//*[local-name()="Fault"]')->item(0);
            $reason    = $xpath->query('//*[local-name()="Text"]', $faultNode ?? $dom)->item(0)?->textContent
                       ?? 'Error SOAP desconocido';
            throw new RuntimeException('DIAN devolvió error SOAP: ' . $reason);
        }

        // El resultado es XML embebido (no base64 en SendBillSync)
        $innerXml = $resultNode->textContent;

        // A veces viene base64
        $decoded = base64_decode($innerXml, true);
        if ($decoded && str_contains($decoded, '<')) {
            $innerXml = $decoded;
        }

        return $this->parseApplicationResponse($innerXml, $cufeLocal);
    }

    private function parseGetStatusZipResponse(string $soapXml): array
    {
        $dom = new DOMDocument();
        if (! @$dom->loadXML($soapXml)) {
            throw new RuntimeException('Respuesta de estado DIAN no es XML válido.');
        }

        $xpath = new DOMXPath($dom);

        $resultNode = $xpath->query('//*[local-name()="GetStatusZipResult"]')->item(0);

        if (! $resultNode) {
            throw new RuntimeException('Respuesta DIAN inesperada: no se encontró GetStatusZipResult.');
        }

        $innerXml = $resultNode->textContent;
        $decoded  = base64_decode($innerXml, true);
        if ($decoded && str_contains($decoded, '<')) {
            $innerXml = $decoded;
        }

        return $this->parseApplicationResponse($innerXml, '');
    }

    private function parseApplicationResponse(string $xml, string $cufeLocal): array
    {
        $dom = new DOMDocument();
        if (! @$dom->loadXML($xml)) {
            return [
                'cufe'        => $cufeLocal,
                'valido'      => false,
                'codigo'      => '99',
                'descripcion' => 'No se pudo parsear ApplicationResponse',
                'errores'     => [$xml],
            ];
        }

        $xpath = new DOMXPath($dom);

        // ResponseCode y Description están en cac:Response o directamente
        $responseCode = $xpath->query('//*[local-name()="ResponseCode"]')->item(0)?->textContent ?? '';
        $description  = $xpath->query('//*[local-name()="Description"]')->item(0)?->textContent  ?? '';

        // Mensajes de error adicionales
        $errores = [];
        foreach ($xpath->query('//*[local-name()="ErrorMessage"]') as $errNode) {
            $errores[] = $errNode->textContent;
        }
        foreach ($xpath->query('//*[local-name()="StatusMessage"]') as $msgNode) {
            $errores[] = $msgNode->textContent;
        }

        // CUFE confirmado por DIAN (si viene en la respuesta)
        $cufeConfirmado = $xpath->query('//*[local-name()="UUID"]')->item(0)?->textContent
                       ?? $cufeLocal;

        $valido = in_array($responseCode, ['00', '0', '1']) || str_contains(strtolower($description), 'procesado');

        return [
            'cufe'        => $cufeConfirmado ?: $cufeLocal,
            'valido'      => $valido,
            'codigo'      => $responseCode,
            'descripcion' => $description,
            'errores'     => array_filter(array_unique($errores)),
        ];
    }

    // ── HTTP ──────────────────────────────────────────────────────────────────

    private function postSoap(string $envelope, string $action): string
    {
        $url = config('dian.ambiente') === 'produccion'
            ? config('dian.url_produccion')
            : config('dian.url_habilitacion');

        $response = Http::withHeaders([
            'Content-Type' => 'application/soap+xml; charset=utf-8',
            'SOAPAction'   => 'http://wcf.dian.colombia/IWcfDianCustomerServices/' . $action,
        ])->timeout(30)
          ->withBody($envelope, 'application/soap+xml')
          ->post($url);

        if ($response->serverError()) {
            throw new RuntimeException('Error del servidor DIAN (HTTP ' . $response->status() . '): ' . substr($response->body(), 0, 500));
        }

        return $response->body();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * PasswordDigest = Base64( SHA1( decode(Nonce) + Created + SoftwarePin ) )
     * Retorna [nonce_b64, created_iso, password_digest_b64]
     */
    private function wsSecurityTokens(string $softwarePin): array
    {
        $nonceRaw = random_bytes(16);
        $nonce    = base64_encode($nonceRaw);
        $created  = now()->utc()->format('Y-m-d\TH:i:s.v\Z');
        $pass     = base64_encode(sha1($nonceRaw . $created . $softwarePin, true));

        return [$nonce, $created, $pass];
    }

    /**
     * Nombre de archivo XML según nomenclatura DIAN:
     * {NIT sin DV}{numero sanitizado}.xml
     */
    private function buildXmlFilename(string $nit, string $numero): string
    {
        $nitLimpio    = preg_replace('/\D/', '', $nit);
        $numeroLimpio = preg_replace('/[^A-Za-z0-9]/', '', $numero);
        return $nitLimpio . $numeroLimpio . '.xml';
    }

    /** Crea un ZIP en memoria y devuelve el contenido binario. */
    private function zipXml(string $xmlFilename, string $xmlContent): string
    {
        $tmpPath = sys_get_temp_dir() . '/' . uniqid('dian_', true) . '.zip';

        $zip = new ZipArchive();
        if ($zip->open($tmpPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('No se pudo crear el archivo ZIP para DIAN.');
        }

        $zip->addFromString($xmlFilename, $xmlContent);
        $zip->close();

        $content = file_get_contents($tmpPath);
        @unlink($tmpPath);

        return $content;
    }
}
