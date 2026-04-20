<?php

namespace App\Services;

use DOMDocument;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Firma un XML UBL 2.1 con XAdES-BES usando el certificado PKCS#12 de la DIAN.
 * Usa Exclusive C14N (exc-c14n) para que los digests sean independientes del contexto
 * de namespace, permitiendo computar cada pieza en forma standalone antes de ensamblar.
 */
class DianXmlSigner
{
    private const NS_DS    = 'http://www.w3.org/2000/09/xmldsig#';
    private const NS_XADES = 'http://uri.etsi.org/01903/v1.3.2#';
    private const NS_EXT   = 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2';

    private const ALG_C14N_EXC   = 'http://www.w3.org/2001/10/xml-exc-c14n#';
    private const ALG_RSA_SHA256 = 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256';
    private const ALG_SHA256     = 'http://www.w3.org/2001/04/xmlenc#sha256';
    private const ALG_ENVELOPED  = 'http://www.w3.org/2000/09/xmldsig#enveloped-signature';
    private const TYPE_SIGNED_PROPS = 'http://uri.etsi.org/01903#SignedProperties';

    public function sign(string $xml): string
    {
        $p12Path     = config('dian.certificado_path');
        $p12Password = config('dian.certificado_password');

        if (! $p12Path || ! file_exists($p12Path)) {
            throw new RuntimeException('Certificado DIAN no encontrado: ' . ($p12Path ?? '(no configurado)'));
        }

        $p12 = file_get_contents($p12Path);
        if (! openssl_pkcs12_read($p12, $certs, $p12Password)) {
            throw new RuntimeException('No se pudo leer el certificado P12. Verifique la contraseña.');
        }

        openssl_x509_export($certs['cert'], $certPem);
        $certBase64  = $this->pemToBase64($certPem);
        $certDer     = base64_decode($certBase64);
        $certDigest  = base64_encode(hash('sha256', $certDer, true));
        $certInfo    = openssl_x509_parse($certs['cert']);
        $issuerDn    = $this->buildIssuerDn($certInfo['issuer'] ?? []);
        $serialNum   = (string) ($certInfo['serialNumber'] ?? '0');
        $signingTime = now('America/Bogota')->format('Y-m-d\TH:i:sP');

        $uuid  = Str::uuid()->toString();
        $sigId = "Sig-{$uuid}";
        $refId = "Ref-{$uuid}";
        $kvId  = "KI-{$uuid}";
        $spId  = "SP-{$uuid}";
        $svId  = "SV-{$uuid}";

        // ── 1. Digest del documento (antes de inyectar la firma) ──────────────
        $docDom = new DOMDocument();
        $docDom->loadXML($xml);
        $docDigest = $this->sha256b64($docDom->documentElement->C14N(true, false));

        // ── 2. KeyInfo standalone → digest ───────────────────────────────────
        $kiXml    = $this->kiXml($kvId, $certBase64);
        $kiDom    = new DOMDocument(); $kiDom->loadXML($kiXml);
        $kiDigest = $this->sha256b64($kiDom->documentElement->C14N(true, false));

        // ── 3. SignedProperties standalone → digest ───────────────────────────
        $spXml    = $this->spXml($spId, $sigId, $signingTime, $certDigest, $issuerDn, $serialNum);
        $spDom    = new DOMDocument(); $spDom->loadXML($spXml);
        $spDigest = $this->sha256b64($spDom->documentElement->C14N(true, false));

        // ── 4. SignedInfo standalone → C14N → firma RSA-SHA256 ───────────────
        $siXml  = $this->siXml($refId, $docDigest, $kvId, $kiDigest, $spId, $spDigest);
        $siDom  = new DOMDocument(); $siDom->loadXML($siXml);
        $siC14N = $siDom->documentElement->C14N(true, false);

        if (! openssl_sign($siC14N, $rawSig, $certs['pkey'], OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Error firmando SignedInfo: ' . openssl_error_string());
        }
        $sigValue = base64_encode($rawSig);

        // ── 5. Ensamblar Signature completo ───────────────────────────────────
        $sigXml = $this->assembleSig(
            $sigId, $svId, $refId,
            $docDigest, $kvId, $kiDigest, $spId, $spDigest,
            $sigValue, $certBase64,
            $signingTime, $certDigest, $issuerDn, $serialNum
        );

        // ── 6. Inyectar en ext:ExtensionContent ───────────────────────────────
        $docDom->loadXML($xml);
        $extNodes = $docDom->getElementsByTagNameNS(self::NS_EXT, 'ExtensionContent');
        if ($extNodes->length === 0) {
            throw new RuntimeException('El XML no contiene ext:ExtensionContent.');
        }

        $fragDom = new DOMDocument();
        $fragDom->loadXML($sigXml);
        $extNodes->item(0)->appendChild($docDom->importNode($fragDom->documentElement, true));

        $docDom->formatOutput = true;
        return $docDom->saveXML();
    }

    // ── Builders XML standalone ───────────────────────────────────────────────

    private function kiXml(string $id, string $cert): string
    {
        $ns = self::NS_DS;
        return <<<XML
<ds:KeyInfo xmlns:ds="{$ns}" Id="{$id}">
  <ds:X509Data>
    <ds:X509Certificate>{$cert}</ds:X509Certificate>
  </ds:X509Data>
</ds:KeyInfo>
XML;
    }

    private function spXml(
        string $spId, string $sigId, string $time,
        string $certDigest, string $issuerDn, string $serial
    ): string {
        $ns  = self::NS_XADES;
        $ds  = self::NS_DS;
        $sha = self::ALG_SHA256;
        $iss = htmlspecialchars($issuerDn, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        return <<<XML
<xades:SignedProperties xmlns:xades="{$ns}" xmlns:ds="{$ds}" Id="{$spId}">
  <xades:SignedSignatureProperties>
    <xades:SigningTime>{$time}</xades:SigningTime>
    <xades:SigningCertificate>
      <xades:Cert>
        <xades:CertDigest>
          <ds:DigestMethod Algorithm="{$sha}"/>
          <ds:DigestValue>{$certDigest}</ds:DigestValue>
        </xades:CertDigest>
        <xades:IssuerSerial>
          <ds:X509IssuerName>{$iss}</ds:X509IssuerName>
          <ds:X509SerialNumber>{$serial}</ds:X509SerialNumber>
        </xades:IssuerSerial>
      </xades:Cert>
    </xades:SigningCertificate>
  </xades:SignedSignatureProperties>
</xades:SignedProperties>
XML;
    }

    private function siXml(
        string $refId, string $docDigest,
        string $kvId,  string $kiDigest,
        string $spId,  string $spDigest
    ): string {
        $ns  = self::NS_DS;
        $c14 = self::ALG_C14N_EXC;
        $rsa = self::ALG_RSA_SHA256;
        $sha = self::ALG_SHA256;
        $env = self::ALG_ENVELOPED;
        $spt = self::TYPE_SIGNED_PROPS;

        return <<<XML
<ds:SignedInfo xmlns:ds="{$ns}">
  <ds:CanonicalizationMethod Algorithm="{$c14}"/>
  <ds:SignatureMethod Algorithm="{$rsa}"/>
  <ds:Reference Id="{$refId}" URI="">
    <ds:Transforms>
      <ds:Transform Algorithm="{$env}"/>
    </ds:Transforms>
    <ds:DigestMethod Algorithm="{$sha}"/>
    <ds:DigestValue>{$docDigest}</ds:DigestValue>
  </ds:Reference>
  <ds:Reference URI="#{$kvId}">
    <ds:DigestMethod Algorithm="{$sha}"/>
    <ds:DigestValue>{$kiDigest}</ds:DigestValue>
  </ds:Reference>
  <ds:Reference Type="{$spt}" URI="#{$spId}">
    <ds:DigestMethod Algorithm="{$sha}"/>
    <ds:DigestValue>{$spDigest}</ds:DigestValue>
  </ds:Reference>
</ds:SignedInfo>
XML;
    }

    private function assembleSig(
        string $sigId,    string $svId,     string $refId,
        string $docDigest,string $kvId,     string $kiDigest,
        string $spId,     string $spDigest, string $sigValue,
        string $cert,     string $time,     string $certDigest,
        string $issuerDn, string $serial
    ): string {
        $ns  = self::NS_DS;
        $nsX = self::NS_XADES;
        $c14 = self::ALG_C14N_EXC;
        $rsa = self::ALG_RSA_SHA256;
        $sha = self::ALG_SHA256;
        $env = self::ALG_ENVELOPED;
        $spt = self::TYPE_SIGNED_PROPS;
        $iss = htmlspecialchars($issuerDn, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        return <<<XML
<ds:Signature xmlns:ds="{$ns}" Id="{$sigId}">
  <ds:SignedInfo>
    <ds:CanonicalizationMethod Algorithm="{$c14}"/>
    <ds:SignatureMethod Algorithm="{$rsa}"/>
    <ds:Reference Id="{$refId}" URI="">
      <ds:Transforms>
        <ds:Transform Algorithm="{$env}"/>
      </ds:Transforms>
      <ds:DigestMethod Algorithm="{$sha}"/>
      <ds:DigestValue>{$docDigest}</ds:DigestValue>
    </ds:Reference>
    <ds:Reference URI="#{$kvId}">
      <ds:DigestMethod Algorithm="{$sha}"/>
      <ds:DigestValue>{$kiDigest}</ds:DigestValue>
    </ds:Reference>
    <ds:Reference Type="{$spt}" URI="#{$spId}">
      <ds:DigestMethod Algorithm="{$sha}"/>
      <ds:DigestValue>{$spDigest}</ds:DigestValue>
    </ds:Reference>
  </ds:SignedInfo>
  <ds:SignatureValue Id="{$svId}">{$sigValue}</ds:SignatureValue>
  <ds:KeyInfo Id="{$kvId}">
    <ds:X509Data>
      <ds:X509Certificate>{$cert}</ds:X509Certificate>
    </ds:X509Data>
  </ds:KeyInfo>
  <ds:Object>
    <xades:QualifyingProperties xmlns:xades="{$nsX}" Target="#{$sigId}">
      <xades:SignedProperties Id="{$spId}">
        <xades:SignedSignatureProperties>
          <xades:SigningTime>{$time}</xades:SigningTime>
          <xades:SigningCertificate>
            <xades:Cert>
              <xades:CertDigest>
                <ds:DigestMethod Algorithm="{$sha}"/>
                <ds:DigestValue>{$certDigest}</ds:DigestValue>
              </xades:CertDigest>
              <xades:IssuerSerial>
                <ds:X509IssuerName>{$iss}</ds:X509IssuerName>
                <ds:X509SerialNumber>{$serial}</ds:X509SerialNumber>
              </xades:IssuerSerial>
            </xades:Cert>
          </xades:SigningCertificate>
        </xades:SignedSignatureProperties>
      </xades:SignedProperties>
    </xades:QualifyingProperties>
  </ds:Object>
</ds:Signature>
XML;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function sha256b64(string $data): string
    {
        return base64_encode(hash('sha256', $data, true));
    }

    private function pemToBase64(string $pem): string
    {
        return preg_replace('/-----[^-]+-----|[\r\n\s]/', '', $pem);
    }

    private function buildIssuerDn(array $issuer): string
    {
        $parts = [];
        foreach (['CN', 'OU', 'O', 'L', 'ST', 'C'] as $key) {
            if (! isset($issuer[$key])) continue;
            $vals = is_array($issuer[$key]) ? $issuer[$key] : [$issuer[$key]];
            foreach ($vals as $v) {
                $parts[] = $key . '=' . $v;
            }
        }
        return implode(', ', $parts);
    }
}
