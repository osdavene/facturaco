<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; }
    .page { padding: 30px; }

    .header { display:table; width:100%; margin-bottom:20px;
              border-bottom:3px solid #f59e0b; padding-bottom:15px; }
    .header-left  { display:table-cell; width:55%; vertical-align:middle; }
    .header-right { display:table-cell; width:45%; text-align:right; vertical-align:top; }

    .logo-box img { max-height:85px; max-width:240px; object-fit:contain; margin-bottom:8px; }
    .logo-texto   { font-size:22px; font-weight:bold; color:#f59e0b; margin-bottom:4px; }
    .empresa-nombre { font-size:13px; font-weight:bold; color:#1a1a1a; }
    .empresa-sub    { font-size:9px; color:#666; margin-top:1px; }
    .empresa-datos  { font-size:9px; color:#555; margin-top:5px; line-height:1.7; }

    .factura-tipo { font-size:10px; color:#888; text-transform:uppercase;
                    letter-spacing:1.5px; margin-bottom:3px; }
    .factura-num  { font-size:24px; font-weight:bold; color:#1a1a1a; }
    .factura-res  { font-size:8px; color:#999; margin-top:3px; line-height:1.5; }

    .badge { display:inline-block; padding:3px 10px; border-radius:10px;
             font-size:9px; font-weight:bold; margin-top:5px; }
    .badge-emitida  { background:#dbeafe; color:#1d4ed8; }
    .badge-pagada   { background:#d1fae5; color:#065f46; }
    .badge-borrador { background:#fef3c7; color:#92400e; }
    .badge-vencida  { background:#fee2e2; color:#991b1b; }
    .badge-anulada  { background:#f1f5f9; color:#64748b; }

    .info-grid { display:table; width:100%; margin-bottom:18px; }
    .info-col  { display:table-cell; width:50%; vertical-align:top; }
    .info-col-right { padding-left:12px; }
    .info-box  { background:#f8f9fa; border-radius:6px; padding:11px;
                 border-left:3px solid #f59e0b; }
    .info-title { font-size:9px; font-weight:bold; text-transform:uppercase;
                  color:#f59e0b; margin-bottom:7px; letter-spacing:0.5px; }
    .info-label { font-size:9px; text-transform:uppercase; color:#999; }
    .info-value { font-size:11px; color:#1a1a1a; margin-bottom:3px; }
    .info-value-sm { font-size:10px; color:#555; margin-bottom:2px; }

    .resolucion-box { background:#fffbeb; border:1px solid #fde68a; border-radius:5px;
                      padding:7px 11px; margin-bottom:14px; font-size:9px; color:#92400e; }

    table.items { width:100%; border-collapse:collapse; margin-bottom:15px; }
    table.items thead tr { background:#f59e0b; }
    table.items thead th { padding:8px 10px; text-align:left; font-size:9px;
                           text-transform:uppercase; letter-spacing:0.5px; color:#000; }
    table.items tbody tr:nth-child(even) { background:#f8f9fa; }
    table.items tbody tr:nth-child(odd)  { background:#ffffff; }
    table.items tbody td { padding:7px 10px; border-bottom:1px solid #eee; font-size:10px; }

    .text-right  { text-align:right; }
    .text-center { text-align:center; }

    .bottom-grid { display:table; width:100%; margin-top:10px; }
    .bottom-left  { display:table-cell; width:65%; vertical-align:top; padding-right:15px; }
    .bottom-right { display:table-cell; width:35%; vertical-align:top; text-align:center; }

    .totales { width:100%; }
    .totales table { width:100%; border-collapse:collapse; }
    .totales td { padding:5px 8px; font-size:11px; border-bottom:1px solid #f0f0f0; }
    .totales .label { color:#666; }
    .totales .valor { text-align:right; font-weight:600; }
    .totales .total-row td { background:#f59e0b; font-weight:bold;
                              font-size:13px; padding:8px; border:none; }

    .qr-box { border:1px solid #e2e8f0; border-radius:8px; padding:10px;
              display:inline-block; text-align:center; background:#ffffff; }
    .qr-box img { display:block; margin:0 auto; }
    .qr-label { font-size:8px; color:#475569; margin-top:5px; font-weight:600; }

    .cufe-box { background:#f8fafc; border:1px solid #cbd5e1; border-radius:6px;
                padding:8px 12px; margin-top:12px; margin-bottom:12px; }
    .cufe-title { font-size:8px; font-weight:bold; color:#1e293b; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:3px; }
    .cufe-text { font-family:DejaVu Sans Mono, monospace; font-size:7.5px; color:#334155; word-wrap:break-word; word-break:break-all; line-height:1.3; }
    .cufe-meta { font-size:7.5px; color:#059669; font-weight:600; margin-top:4px; }

    .obs-box { background:#f8f9fa; border-radius:5px; padding:9px 11px; margin-top:12px; }
    .obs-title { font-size:9px; font-weight:bold; text-transform:uppercase;
                 color:#888; margin-bottom:3px; }

    .footer { border-top:1px solid #e5e7eb; padding-top:9px; margin-top:14px;
              font-size:8px; color:#999; }
    .footer-grid { display:table; width:100%; }
    .footer-left  { display:table-cell; width:60%; }
    .footer-right { display:table-cell; width:40%; text-align:right; }
    .pie-pagina   { font-size:9px; color:#666; margin-bottom:5px; font-style:italic; }
</style>
</head>
<body>
<div class="page">

    {{-- ═══ HEADER ═══ --}}
    <div class="header">
        <div class="header-left">
            <div class="logo-box">
                @if($empresa->logo_base64)
                    <img src="{{ $empresa->logo_base64 }}" style="max-height:85px; max-width:240px; object-fit:contain; margin-bottom:8px;">
                    <div class="empresa-nombre">{{ $empresa->razon_social }}</div>
                    @if($empresa->nombre_comercial && $empresa->nombre_comercial !== $empresa->razon_social)
                    <div class="empresa-sub">{{ $empresa->nombre_comercial }}</div>
                    @endif
                @else
                    <div class="logo-texto">
                        {{ $empresa->nombre_comercial ?: $empresa->razon_social }}
                    </div>
                    @if($empresa->nombre_comercial && $empresa->nombre_comercial !== $empresa->razon_social)
                    <div class="empresa-sub">{{ $empresa->razon_social }}</div>
                    @endif
                @endif
            </div>
            <div class="empresa-datos">
                NIT: {{ $empresa->nit_formateado }} —
                {{ $empresa->regimen === 'responsable_iva' ? 'Responsable de IVA' : 'No Responsable de IVA' }}
                @if($empresa->direccion)
                <br>{{ $empresa->direccion }}{{ $empresa->municipio ? ', '.$empresa->municipio : '' }}
                {{ $empresa->departamento ? '- '.$empresa->departamento : '' }}
                @endif
                @if($empresa->telefono || $empresa->celular)
                <br>Tel: {{ $empresa->telefono ?: $empresa->celular }}
                @if($empresa->email) — {{ $empresa->email }} @endif
                @endif
                @if($empresa->sitio_web)
                <br>{{ $empresa->sitio_web }}
                @endif
            </div>
        </div>
        <div class="header-right">
            <div class="factura-tipo">{{ $factura->enviada_dian ? 'FACTURA ELECTRÓNICA DE VENTA' : strtoupper($factura->tipo) }}</div>
            <div class="factura-num">{{ $factura->numero }}</div>
            @if($empresa->resolucion_numero)
            <div class="factura-res">
                Res. DIAN N° {{ is_numeric($empresa->resolucion_numero) ? number_format((float)$empresa->resolucion_numero, 0, ',', '.') : $empresa->resolucion_numero }}
                @if($empresa->resolucion_fecha)
                <br>del {{ $empresa->resolucion_fecha->format('d/m/Y') }}
                @endif
                @if($empresa->resolucion_vencimiento)
                <br>Vigente hasta {{ $empresa->resolucion_vencimiento->format('d/m/Y') }}
                @endif
            </div>
            @endif
            <div>
                <span class="badge badge-{{ $factura->estado }}">
                    {{ strtoupper($factura->estado) }}
                </span>
                @if($factura->enviada_dian)
                <span class="badge badge-pagada" style="background:#d1fae5;color:#065f46;margin-left:4px;">
                    DIAN ✓
                </span>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══ RESOLUCIÓN ═══ --}}
    @if($empresa->resolucion_numero)
    <div class="resolucion-box">
        Autorizado mediante Resolución N° <strong>{{ $empresa->resolucion_numero }}</strong>
        @if($empresa->resolucion_fecha) del {{ $empresa->resolucion_fecha->format('d/m/Y') }} @endif
        — Consecutivos del
        <strong>{{ number_format($empresa->consecutivo_desde) }}</strong> al
        <strong>{{ number_format($empresa->consecutivo_hasta) }}</strong>
        @if($empresa->resolucion_vencimiento)
        · Vigente hasta {{ $empresa->resolucion_vencimiento->format('d/m/Y') }}
        @endif
    </div>
    @endif

    {{-- ═══ INFO CLIENTE / FACTURA ═══ --}}
    <div class="info-grid">
        <div class="info-col">
            <div class="info-box">
                <div class="info-title">Datos del Cliente</div>
                <div class="info-value" style="font-weight:bold;font-size:12px;">
                    {{ $factura->cliente_nombre }}
                </div>
                <div class="info-value-sm">{{ $factura->cliente_documento }}</div>
                @if($factura->cliente_direccion)
                <div class="info-value-sm">{{ $factura->cliente_direccion }}</div>
                @endif
                @if($factura->cliente_email)
                <div class="info-value-sm">{{ $factura->cliente_email }}</div>
                @endif
            </div>
        </div>
        <div class="info-col info-col-right">
            <div class="info-box">
                <div class="info-title">Datos de la Factura</div>
                <table style="width:100%;border-collapse:collapse;">
                    <tr>
                        <td class="info-label" style="padding:2px 0;">Fecha emisión</td>
                        <td class="info-value" style="text-align:right;padding:2px 0;">
                            {{ $factura->fecha_emision->format('d/m/Y') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="info-label" style="padding:2px 0;">Vencimiento</td>
                        <td class="info-value" style="text-align:right;padding:2px 0;">
                            {{ $factura->fecha_vencimiento->format('d/m/Y') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="info-label" style="padding:2px 0;">Forma de pago</td>
                        <td class="info-value" style="text-align:right;padding:2px 0;">
                            {{ ucfirst($factura->forma_pago) }}
                        </td>
                    </tr>
                    @if($factura->plazo_pago > 0)
                    <tr>
                        <td class="info-label" style="padding:2px 0;">Plazo</td>
                        <td class="info-value" style="text-align:right;padding:2px 0;">
                            {{ $factura->plazo_pago }} días
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- ═══ ITEMS ═══ --}}
    <table class="items">
        <thead>
            <tr>
                <th style="width:12%;">Código</th>
                <th style="width:38%;">Descripción</th>
                <th class="text-center" style="width:10%;">Cant.</th>
                <th class="text-right" style="width:15%;">Precio Unit.</th>
                <th class="text-center" style="width:8%;">IVA</th>
                <th class="text-right" style="width:17%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($factura->items as $item)
            <tr>
                <td style="color:#888;font-size:9px;font-family:monospace;">
                    {{ $item->codigo }}
                </td>
                <td>{{ $item->descripcion }}</td>
                <td class="text-center">{{ format_cantidad($item->cantidad) }}</td>
                <td class="text-right">${{ number_format($item->precio_unitario, 0, ',', '.') }}</td>
                <td class="text-center">{{ $item->iva_pct }}%</td>
                <td class="text-right" style="font-weight:bold;">
                    ${{ number_format($item->total, 0, ',', '.') }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ═══ TOTALES + QR ═══ --}}
    <div class="bottom-grid">

        {{-- Totales izquierda --}}
        <div class="bottom-left">
            <div class="totales">
                <table>
                    <tr>
                        <td class="label">Subtotal</td>
                        <td class="valor">${{ number_format($factura->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @if($factura->descuento > 0)
                    <tr>
                        <td class="label">Descuento</td>
                        <td class="valor" style="color:#dc2626;">
                            -${{ number_format($factura->descuento, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td class="label">IVA (19%)</td>
                        <td class="valor" style="color:#2563eb;">
                            +${{ number_format($factura->iva, 0, ',', '.') }}
                        </td>
                    </tr>
                    @if($factura->retefuente > 0)
                    <tr>
                        <td class="label">ReteFuente</td>
                        <td class="valor" style="color:#d97706;">
                            -${{ number_format($factura->retefuente, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endif
                    @if($factura->reteica > 0)
                    <tr>
                        <td class="label">ReteICA</td>
                        <td class="valor" style="color:#d97706;">
                            -${{ number_format($factura->reteica, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endif
                    <tr class="total-row">
                        <td>TOTAL A PAGAR</td>
                        <td style="text-align:right;">
                            ${{ number_format($factura->total, 0, ',', '.') }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- QR derecha --}}
        <div class="bottom-right">
            @isset($qrBase64)
            <div class="qr-box">
                <img src="data:image/png;base64,{{ $qrBase64 }}"
                    width="105" height="105" alt="QR Verificación DIAN">
                <div class="qr-label">
                    {{ $factura->cufe ? 'Validación Oficial DIAN' : 'Verificación Documento' }}<br>
                    {{ $factura->numero }}
                </div>
            </div>
            @endisset
        </div>
    </div>

    {{-- ═══ CUFE OFICIAL DIAN ═══ --}}
    @if($factura->cufe)
    <div class="cufe-box">
        <div class="cufe-title">CUFE (Código Único de Facturación Electrónica):</div>
        <div class="cufe-text">{{ $factura->cufe }}</div>
        @if($factura->fecha_dian)
        <div class="cufe-meta">✓ Documento validado y firmado electrónicamente por la DIAN el {{ $factura->fecha_dian->format('d/m/Y \a \l\a\s H:i:s') }}</div>
        @endif
    </div>
    @endif

    {{-- ═══ BLOQUE PAGO EN LÍNEA ═══ --}}
    @if(!in_array($factura->estado, ['pagada', 'anulada']))
    <div style="background:#fffbeb; border:1px dashed #f59e0b; border-radius:6px; padding:8px 12px; margin-top:10px; display:table; width:100%;">
        <div style="display:table-cell; width:75%; vertical-align:middle;">
            <div style="font-size:9.5px; font-weight:bold; color:#b45309; text-transform:uppercase;">
                Pague esta factura en línea (PSE, Nequi, Tarjetas)
            </div>
            <div style="font-size:8px; color:#78350f; margin-top:2px;">
                Escanee el código QR o ingrese a: <span style="color:#2563eb; font-weight:bold;">{{ $factura->url_pago }}</span>
            </div>
        </div>
        @if(isset($qrPagoBase64))
        <div style="display:table-cell; width:25%; text-align:right; vertical-align:middle;">
            <img src="data:image/png;base64,{{ $qrPagoBase64 }}" width="60" height="60" alt="QR Pago Online">
        </div>
        @endif
    </div>
    @endif

    {{-- ═══ OBSERVACIONES ═══ --}}
    @if($factura->observaciones)
    <div class="obs-box">
        <div class="obs-title">Observaciones</div>
        <div style="font-size:10px;color:#444;">{{ $factura->observaciones }}</div>
    </div>
    @endif

    {{-- ═══ TÉRMINOS ═══ --}}
    @if($empresa->terminos_condiciones)
    <div class="obs-box" style="margin-top:8px;">
        <div class="obs-title">Términos y Condiciones</div>
        <div style="font-size:9px;color:#666;">{{ $empresa->terminos_condiciones }}</div>
    </div>
    @endif

    {{-- ═══ FOOTER ═══ --}}
    <div class="footer">
        @if($empresa->pie_factura)
        <div class="pie-pagina">{{ $empresa->pie_factura }}</div>
        @endif
        <div class="footer-grid">
            <div class="footer-left">
                {{ $empresa->razon_social }} · NIT {{ $empresa->nit_formateado }}
                @if($empresa->municipio) · {{ $empresa->municipio }} @endif
            </div>
            <div class="footer-right">
                Generado el {{ now()->format('d/m/Y H:i') }} · FacCol
            </div>
        </div>
    </div>

</div>
</body>
</html>