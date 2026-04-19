<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\Factura;
use DOMDocument;
use DOMElement;

/**
 * Genera el XML UBL 2.1 requerido por la DIAN para factura electrónica.
 * Spec: Anexo Técnico Factura Electrónica de Venta v1.9 (DIAN 2023)
 */
class DianXmlBuilder
{
    private const NS_INVOICE = 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2';
    private const NS_CAC     = 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2';
    private const NS_CBC     = 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2';
    private const NS_EXT     = 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2';
    private const NS_XSI     = 'http://www.w3.org/2001/XMLSchema-instance';

    private DOMDocument $dom;
    private string $moneda;

    public function build(Factura $factura, Empresa $empresa, string $cufe): string
    {
        $this->dom    = new DOMDocument('1.0', 'UTF-8');
        $this->dom->formatOutput = true;
        $this->moneda = $empresa->moneda ?? 'COP';

        $ambiente    = config('dian.ambiente', 'habilitacion');
        $profileExec = $ambiente === 'produccion' ? '1' : '2';

        $invoice = $this->dom->createElementNS(self::NS_INVOICE, 'Invoice');
        $invoice->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:cac', self::NS_CAC);
        $invoice->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:cbc', self::NS_CBC);
        $invoice->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ext', self::NS_EXT);
        $invoice->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', self::NS_XSI);
        $this->dom->appendChild($invoice);

        // Extensión UBL — la firma XAdES irá aquí en Parte 3
        $ublExts   = $this->dom->createElementNS(self::NS_EXT, 'ext:UBLExtensions');
        $ublExt    = $this->dom->createElementNS(self::NS_EXT, 'ext:UBLExtension');
        $extCont   = $this->dom->createElementNS(self::NS_EXT, 'ext:ExtensionContent');
        $ublExt->appendChild($extCont);
        $ublExts->appendChild($ublExt);
        $invoice->appendChild($ublExts);

        // ── Cabecera ──────────────────────────────────────────────────────────
        $this->cbc($invoice, 'UBLVersionID', 'UBL 2.1');
        $this->cbc($invoice, 'CustomizationID', '10');
        $this->cbc($invoice, 'ProfileID', 'DIAN 2.1');
        $this->cbc($invoice, 'ProfileExecutionID', $profileExec);
        $this->cbc($invoice, 'ID', $factura->numero);

        $uuid = $this->cbc($invoice, 'UUID', $cufe);
        $uuid->setAttribute('schemeID', $profileExec);
        $uuid->setAttribute('schemeName', 'CUFE-SHA384');

        $this->cbc($invoice, 'IssueDate', $factura->fecha_emision->format('Y-m-d'));
        $this->cbc($invoice, 'IssueTime', now('America/Bogota')->format('H:i:s') . '-05:00');

        $typeCode = $this->cbc($invoice, 'InvoiceTypeCode', '01');
        $typeCode->setAttribute('listAgencyID', '195');
        $typeCode->setAttribute('listAgencyName', 'CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)');
        $typeCode->setAttribute('listSchemeURI', 'https://www.dian.gov.co/F2PEV2/Variables/TiposDocumentosV2.xsd');

        if ($factura->observaciones) {
            $this->cbc($invoice, 'Note', $factura->observaciones);
        }

        $this->cbc($invoice, 'DocumentCurrencyCode', $this->moneda);
        $this->cbc($invoice, 'LineCountNumeric', (string) $factura->items->count());

        // Referencia de autorización (resolución DIAN)
        if ($empresa->resolucion_numero) {
            $addDoc = $this->el($invoice, 'cac:AdditionalDocumentReference');
            $this->cbc($addDoc, 'ID', $empresa->resolucion_numero);
            if ($empresa->resolucion_fecha) {
                $this->cbc($addDoc, 'IssueDate', $empresa->resolucion_fecha->format('Y-m-d'));
            }
            $this->cbc($addDoc, 'DocumentTypeCode', 'ResolucionFacturacion');
            $invoice->appendChild($addDoc);
        }

        // ── Partes ────────────────────────────────────────────────────────────
        $invoice->appendChild($this->buildSupplier($empresa));
        $invoice->appendChild($this->buildCustomer($factura));

        // ── Medio de pago ─────────────────────────────────────────────────────
        $payMeans = $this->el($invoice, 'cac:PaymentMeans');
        $this->cbc($payMeans, 'ID', '1');
        $this->cbc($payMeans, 'PaymentMeansCode', $factura->forma_pago === 'contado' ? '10' : '2');
        $this->cbc($payMeans, 'PaymentDueDate', $factura->fecha_vencimiento->format('Y-m-d'));
        $invoice->appendChild($payMeans);

        // ── Impuestos totales ─────────────────────────────────────────────────
        if ($factura->iva > 0) {
            $invoice->appendChild($this->buildTaxTotal($factura));
        }
        if ($factura->retefuente > 0) {
            $invoice->appendChild($this->buildWithholding('06', 'ReteFuente', $factura->subtotal, $factura->retefuente));
        }
        if ($factura->reteiva > 0) {
            $invoice->appendChild($this->buildWithholding('05', 'ReteIVA', $factura->iva, $factura->reteiva));
        }
        if ($factura->reteica > 0) {
            $invoice->appendChild($this->buildWithholding('07', 'ReteICA', $factura->subtotal, $factura->reteica));
        }

        // ── Totales monetarios ────────────────────────────────────────────────
        $invoice->appendChild($this->buildMonetaryTotal($factura));

        // ── Líneas ────────────────────────────────────────────────────────────
        foreach ($factura->items as $i => $item) {
            $invoice->appendChild($this->buildLine($item, $i + 1));
        }

        return $this->dom->saveXML();
    }

    // ── Partes ─────────────────────────────────────────────────────────────────

    private function buildSupplier(Empresa $empresa): DOMElement
    {
        $regimen = $this->mapRegimen($empresa->regimen ?? 'comun');

        $supplier = $this->el(null, 'cac:AccountingSupplierParty');
        $this->cbc($supplier, 'AdditionalAccountID', '1');

        $party = $this->el(null, 'cac:Party');

        $partyName = $this->el(null, 'cac:PartyName');
        $this->cbc($partyName, 'Name', $empresa->nombre_comercial ?? $empresa->razon_social);
        $party->appendChild($partyName);

        $party->appendChild($this->buildAddress(
            $empresa->departamento ?? '',
            $empresa->municipio    ?? '',
            $empresa->direccion    ?? '',
            $empresa->pais         ?? 'CO',
        ));

        // PartyTaxScheme
        $partyTax = $this->el(null, 'cac:PartyTaxScheme');
        $this->cbc($partyTax, 'RegistrationName', $empresa->razon_social);
        $compId = $this->cbc($partyTax, 'CompanyID', $empresa->nit);
        $this->nitAtributos($compId, '4', 'NIT');
        $this->cbc($partyTax, 'TaxLevelCode', $regimen)->setAttribute('listName', '48');
        $regAddr = $this->el(null, 'cac:RegistrationAddress');
        $this->cbc($regAddr, 'Department', strtoupper($empresa->departamento ?? ''));
        $this->cbc($regAddr, 'CityName', strtoupper($empresa->municipio ?? ''));
        $addrCountry = $this->el(null, 'cac:Country');
        $this->cbc($addrCountry, 'IdentificationCode', $empresa->pais ?? 'CO');
        $regAddr->appendChild($addrCountry);
        $partyTax->appendChild($regAddr);
        $taxScheme = $this->el(null, 'cac:TaxScheme');
        $this->cbc($taxScheme, 'ID', 'ZZ');
        $this->cbc($taxScheme, 'Name', 'No aplica');
        $partyTax->appendChild($taxScheme);
        $party->appendChild($partyTax);

        // PartyLegalEntity
        $partyLegal = $this->el(null, 'cac:PartyLegalEntity');
        $this->cbc($partyLegal, 'RegistrationName', $empresa->razon_social);
        $legalId = $this->cbc($partyLegal, 'CompanyID', $empresa->nit);
        $this->nitAtributos($legalId, '4', 'NIT');
        $corpScheme = $this->el(null, 'cac:CorporateRegistrationScheme');
        $this->cbc($corpScheme, 'ID', $empresa->prefijo_factura ?? '');
        $partyLegal->appendChild($corpScheme);
        $party->appendChild($partyLegal);

        if ($empresa->email) {
            $contact = $this->el(null, 'cac:Contact');
            $this->cbc($contact, 'ElectronicMail', $empresa->email);
            $party->appendChild($contact);
        }

        $supplier->appendChild($party);
        return $supplier;
    }

    private function buildCustomer(Factura $factura): DOMElement
    {
        $cliente    = $factura->cliente;
        $tipoDoc    = $this->mapTipoDocumento($cliente?->tipo_documento ?? 'CC');
        $docNum     = $this->soloDigitos($cliente?->numero_documento ?? $factura->cliente_documento ?? '');
        $regimen    = $this->mapRegimen($cliente?->regimen ?? 'simplificado');
        $tipoPerso  = ($cliente?->tipo_persona === 'juridica') ? '1' : '2';

        $customer = $this->el(null, 'cac:AccountingCustomerParty');
        $this->cbc($customer, 'AdditionalAccountID', $tipoPerso);

        $party = $this->el(null, 'cac:Party');

        $partyName = $this->el(null, 'cac:PartyName');
        $this->cbc($partyName, 'Name', $factura->cliente_nombre);
        $party->appendChild($partyName);

        if ($factura->cliente_direccion || $cliente?->municipio) {
            $party->appendChild($this->buildAddress(
                $cliente?->departamento ?? '',
                $cliente?->municipio    ?? '',
                $factura->cliente_direccion ?? $cliente?->direccion ?? '',
                $cliente?->pais         ?? 'CO',
            ));
        }

        $partyTax = $this->el(null, 'cac:PartyTaxScheme');
        $this->cbc($partyTax, 'RegistrationName', $factura->cliente_nombre);
        $custId = $this->cbc($partyTax, 'CompanyID', $docNum);
        $this->nitAtributos($custId, $tipoDoc, $cliente?->tipo_documento ?? 'CC');
        $this->cbc($partyTax, 'TaxLevelCode', $regimen)->setAttribute('listName', '48');
        $taxScheme = $this->el(null, 'cac:TaxScheme');
        $this->cbc($taxScheme, 'ID', 'ZZ');
        $this->cbc($taxScheme, 'Name', 'No aplica');
        $partyTax->appendChild($taxScheme);
        $party->appendChild($partyTax);

        $partyLegal = $this->el(null, 'cac:PartyLegalEntity');
        $this->cbc($partyLegal, 'RegistrationName', $factura->cliente_nombre);
        $legalId = $this->cbc($partyLegal, 'CompanyID', $docNum);
        $this->nitAtributos($legalId, $tipoDoc, $cliente?->tipo_documento ?? 'CC');
        $party->appendChild($partyLegal);

        if ($factura->cliente_email) {
            $contact = $this->el(null, 'cac:Contact');
            $this->cbc($contact, 'ElectronicMail', $factura->cliente_email);
            $party->appendChild($contact);
        }

        $customer->appendChild($party);
        return $customer;
    }

    // ── Impuestos ──────────────────────────────────────────────────────────────

    private function buildTaxTotal(Factura $factura): DOMElement
    {
        $taxTotal = $this->el(null, 'cac:TaxTotal');
        $taxAmt = $this->cbc($taxTotal, 'TaxAmount', $this->fmt($factura->iva));
        $taxAmt->setAttribute('currencyID', $this->moneda);

        // Subtotales por tasa
        foreach ($factura->items->where('iva_pct', '>', 0)->groupBy('iva_pct') as $pct => $items) {
            $base    = $items->sum('subtotal');
            $ivaSum  = $items->sum('iva');

            $taxSub = $this->el(null, 'cac:TaxSubtotal');
            $ta = $this->cbc($taxSub, 'TaxableAmount', $this->fmt($base));
            $ta->setAttribute('currencyID', $this->moneda);
            $tv = $this->cbc($taxSub, 'TaxAmount', $this->fmt($ivaSum));
            $tv->setAttribute('currencyID', $this->moneda);

            $taxCat = $this->el(null, 'cac:TaxCategory');
            $this->cbc($taxCat, 'Percent', $this->fmt((float) $pct));
            $ts = $this->el(null, 'cac:TaxScheme');
            $this->cbc($ts, 'ID', '01');
            $this->cbc($ts, 'Name', 'IVA');
            $taxCat->appendChild($ts);
            $taxSub->appendChild($taxCat);
            $taxTotal->appendChild($taxSub);
        }

        return $taxTotal;
    }

    private function buildWithholding(string $codigo, string $nombre, float $base, float $monto): DOMElement
    {
        $wht = $this->el(null, 'cac:WithholdingTaxTotal');
        $ta = $this->cbc($wht, 'TaxAmount', $this->fmt($monto));
        $ta->setAttribute('currencyID', $this->moneda);

        $taxSub = $this->el(null, 'cac:TaxSubtotal');
        $tb = $this->cbc($taxSub, 'TaxableAmount', $this->fmt($base));
        $tb->setAttribute('currencyID', $this->moneda);
        $tv = $this->cbc($taxSub, 'TaxAmount', $this->fmt($monto));
        $tv->setAttribute('currencyID', $this->moneda);

        $taxCat = $this->el(null, 'cac:TaxCategory');
        $pct = $base > 0 ? round($monto / $base * 100, 2) : 0;
        $this->cbc($taxCat, 'Percent', $this->fmt($pct));
        $ts = $this->el(null, 'cac:TaxScheme');
        $this->cbc($ts, 'ID', $codigo);
        $this->cbc($ts, 'Name', $nombre);
        $taxCat->appendChild($ts);
        $taxSub->appendChild($taxCat);
        $wht->appendChild($taxSub);

        return $wht;
    }

    // ── Totales ────────────────────────────────────────────────────────────────

    private function buildMonetaryTotal(Factura $factura): DOMElement
    {
        $total = $this->el(null, 'cac:LegalMonetaryTotal');

        $le = $this->cbc($total, 'LineExtensionAmount', $this->fmt($factura->subtotal));
        $le->setAttribute('currencyID', $this->moneda);

        $te = $this->cbc($total, 'TaxExclusiveAmount', $this->fmt($factura->subtotal));
        $te->setAttribute('currencyID', $this->moneda);

        $ti = $this->cbc($total, 'TaxInclusiveAmount', $this->fmt($factura->subtotal + $factura->iva));
        $ti->setAttribute('currencyID', $this->moneda);

        $al = $this->cbc($total, 'AllowanceTotalAmount', $this->fmt($factura->descuento));
        $al->setAttribute('currencyID', $this->moneda);

        $ch = $this->cbc($total, 'ChargeTotalAmount', '0.00');
        $ch->setAttribute('currencyID', $this->moneda);

        $pa = $this->cbc($total, 'PayableAmount', $this->fmt($factura->total));
        $pa->setAttribute('currencyID', $this->moneda);

        return $total;
    }

    // ── Líneas ─────────────────────────────────────────────────────────────────

    private function buildLine($item, int $num): DOMElement
    {
        $line = $this->el(null, 'cac:InvoiceLine');

        $this->cbc($line, 'ID', (string) $num);

        $qty = $this->cbc($line, 'InvoicedQuantity', $this->fmt($item->cantidad));
        $qty->setAttribute('unitCode', $item->unidad ?? 'EA');

        $lineExt = $this->cbc($line, 'LineExtensionAmount', $this->fmt($item->subtotal));
        $lineExt->setAttribute('currencyID', $this->moneda);

        if ($item->descuento > 0) {
            $ac = $this->el(null, 'cac:AllowanceCharge');
            $this->cbc($ac, 'ChargeIndicator', 'false');
            $this->cbc($ac, 'AllowanceChargeReason', 'Descuento');
            $am = $this->cbc($ac, 'Amount', $this->fmt($item->descuento));
            $am->setAttribute('currencyID', $this->moneda);
            $line->appendChild($ac);
        }

        if ($item->iva > 0) {
            $tt = $this->el(null, 'cac:TaxTotal');
            $ta = $this->cbc($tt, 'TaxAmount', $this->fmt($item->iva));
            $ta->setAttribute('currencyID', $this->moneda);
            $ts = $this->el(null, 'cac:TaxSubtotal');
            $tb = $this->cbc($ts, 'TaxableAmount', $this->fmt($item->subtotal));
            $tb->setAttribute('currencyID', $this->moneda);
            $tv = $this->cbc($ts, 'TaxAmount', $this->fmt($item->iva));
            $tv->setAttribute('currencyID', $this->moneda);
            $tc = $this->el(null, 'cac:TaxCategory');
            $this->cbc($tc, 'Percent', $this->fmt($item->iva_pct ?? 0));
            $tsch = $this->el(null, 'cac:TaxScheme');
            $this->cbc($tsch, 'ID', '01');
            $this->cbc($tsch, 'Name', 'IVA');
            $tc->appendChild($tsch);
            $ts->appendChild($tc);
            $tt->appendChild($ts);
            $line->appendChild($tt);
        }

        $itemEl = $this->el(null, 'cac:Item');
        $this->cbc($itemEl, 'Description', $item->descripcion);
        $stdId = $this->el(null, 'cac:StandardItemIdentification');
        $idEl = $this->cbc($stdId, 'ID', $item->codigo ?? '');
        $idEl->setAttribute('schemeAgencyID', '10');
        $itemEl->appendChild($stdId);
        $line->appendChild($itemEl);

        $price = $this->el(null, 'cac:Price');
        $pa = $this->cbc($price, 'PriceAmount', $this->fmt($item->precio_unitario));
        $pa->setAttribute('currencyID', $this->moneda);
        $bq = $this->cbc($price, 'BaseQuantity', '1');
        $bq->setAttribute('unitCode', $item->unidad ?? 'EA');
        $line->appendChild($price);

        return $line;
    }

    // ── Helpers de estructura ──────────────────────────────────────────────────

    private function buildAddress(string $dpto, string $ciudad, string $linea, string $pais): DOMElement
    {
        $physLoc = $this->el(null, 'cac:PhysicalLocation');
        $address = $this->el(null, 'cac:Address');
        $this->cbc($address, 'Department', strtoupper($dpto));
        $this->cbc($address, 'CitySubdivisionName', '');
        $this->cbc($address, 'CityName', strtoupper($ciudad));
        $this->cbc($address, 'PostalZone', '000000');
        $this->cbc($address, 'CountrySubentity', strtoupper($dpto));
        $addrLine = $this->el(null, 'cac:AddressLine');
        $this->cbc($addrLine, 'Line', $linea);
        $address->appendChild($addrLine);
        $country = $this->el(null, 'cac:Country');
        $this->cbc($country, 'IdentificationCode', $pais ?: 'CO');
        $this->cbc($country, 'Name', 'Colombia');
        $address->appendChild($country);
        $physLoc->appendChild($address);
        return $physLoc;
    }

    private function nitAtributos(DOMElement $el, string $schemeId, string $schemeName): void
    {
        $el->setAttribute('schemeAgencyID', '195');
        $el->setAttribute('schemeAgencyName', 'CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)');
        $el->setAttribute('schemeID', $schemeId);
        $el->setAttribute('schemeName', $schemeName);
    }

    // ── DOM helpers ────────────────────────────────────────────────────────────

    /** Crea elemento cbc: y lo adjunta al padre. */
    private function cbc(DOMElement $parent, string $name, string $value): DOMElement
    {
        $el = $this->dom->createElementNS(self::NS_CBC, 'cbc:' . $name);
        $el->appendChild($this->dom->createTextNode($value));
        $parent->appendChild($el);
        return $el;
    }

    /** Crea elemento cac: sin adjuntar (el llamador lo adjunta). */
    private function el(?DOMElement $parent, string $tag): DOMElement
    {
        $ns = str_starts_with($tag, 'cac:') ? self::NS_CAC : self::NS_EXT;
        $el = $this->dom->createElementNS($ns, $tag);
        if ($parent) {
            $parent->appendChild($el);
        }
        return $el;
    }

    private function fmt(float $v): string
    {
        return number_format($v, 2, '.', '');
    }

    private function soloDigitos(string $v): string
    {
        return preg_replace('/\D/', '', $v);
    }

    private function mapTipoDocumento(string $tipo): string
    {
        return match (strtoupper($tipo)) {
            'NIT'       => '31',
            'CC'        => '13',
            'CE'        => '22',
            'TI'        => '12',
            'PASAPORTE' => '41',
            'NUIP'      => '48',
            default     => '13',
        };
    }

    private function mapRegimen(string $regimen): string
    {
        return match (strtolower($regimen)) {
            'comun', 'responsable_iva', 'responsable' => 'O-13',
            'gran_contribuyente'                       => 'O-15',
            'autoretenedor'                            => 'O-23',
            default                                    => 'O-49',
        };
    }
}
