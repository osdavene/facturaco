<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size:11px; color:#1a1a1a; }
    .page { padding:28px; }

    .header { display:table; width:100%; border-bottom:3px solid #3b82f6;
              padding-bottom:13px; margin-bottom:16px; }
    .header-left  { display:table-cell; width:55%; vertical-align:middle; }
    .header-right { display:table-cell; width:45%; text-align:right; vertical-align:top; }

    .logo-texto     { font-size:20px; font-weight:bold; color:#3b82f6; margin-bottom:4px; }
    .empresa-nombre { font-size:12px; font-weight:bold; }
    .empresa-sub    { font-size:9px; color:#666; margin-top:2px; line-height:1.5; }

    .cot-tipo   { font-size:10px; color:#888; text-transform:uppercase; letter-spacing:1px; }
    .cot-numero { font-size:22px; font-weight:bold; color:#3b82f6; }
    .cot-fecha  { font-size:10px; color:#666; margin-top:3px; }

    .badge-borrador  { background:#fef3c7; color:#92400e; padding:2px 8px; border-radius:8px; font-size:9px; font-weight:bold; }
    .badge-enviada   { background:#dbeafe; color:#1d4ed8; padding:2px 8px; border-radius:8px; font-size:9px; font-weight:bold; }
    .badge-aceptada  { background:#d1fae5; color:#065f46; padding:2px 8px; border-radius:8px; font-size:9px; font-weight:bold; }
    .badge-rechazada { background:#fee2e2; color:#991b1b; padding:2px 8px; border-radius:8px; font-size:9px; font-weight:bold; }
    .badge-convertida{ background:#ede9fe; color:#5b21b6; padding:2px 8px; border-radius:8px; font-size:9px; font-weight:bold; }

    .valida-box { background:#eff6ff; border:1px solid #bfdbfe; border-radius:6px;
                  padding:8px 12px; margin-bottom:14px; display:table; width:100%; }
    .valida-left  { display:table-cell; font-size:10px; color:#1d4ed8; font-weight:bold; }
    .valida-right { display:table-cell; text-align:right; font-size:10px; color:#1d4ed8; }

    .info-grid { display:table; width:100%; margin-bottom:16px; }
    .info-col  { display:table-cell; width:50%; vertical-align:top; padding-right:10px; }
    .info-col-r{ display:table-cell; width:50%; vertical-align:top; padding-left:10px; }
    .info-box  { background:#f8f9fa; border-radius:6px; padding:11px;
                 border-left:3px solid #3b82f6; }
    .info-title { font-size:9px; font-weight:bold; text-transform:uppercase;
                  color:#3b82f6; margin-bottom:6px; }
    .info-row  { display:table; width:100%; margin-bottom:3px; }
    .info-label{ display:table-cell; font-size:9px; color:#888; }
    .info-value{ display:table-cell; font-size:10px; text-align:right; font-weight:600; }

    table.items { width:100%; border-collapse:collapse; margin-bottom:14px; }
    table.items thead tr { background:#3b82f6; }
    table.items thead th { padding:7px 9px; text-align:left; font-size:9px;
                           text-transform:uppercase; color:#fff; }
    table.items tbody tr:nth-child(even) { background:#f8f9fa; }
    table.items tbody td { padding:7px 9px; border-bottom:1px solid #eee; font-size:10px; }

    .text-right  { text-align:right; }
    .text-center { text-align:center; }

    .bottom-grid  { display:table; width:100%; margin-top:10px; }
    .bottom-left  { display:table-cell; width:60%; vertical-align:top; padding-right:15px; }
    .bottom-right { display:table-cell; width:40%; vertical-align:top; text-align:center; }

    .totales { width:100%; }
    .totales table { width:100%; border-collapse:collapse; }
    .totales td { padding:5px 8px; font-size:11px; border-bottom:1px solid #f0f0f0; }
    .totales .label { color:#666; }
    .totales .valor { text-align:right; font-weight:600; }
    .totales .total-row td { background:#3b82f6; color:#fff; font-weight:bold;
                              font-size:13px; padding:8px; border:none; }

    .qr-box { border:1px solid #e5e7eb; border-radius:6px; padding:8px; display:inline-block; }
    .qr-box img { display:block; margin:0 auto; }
    .qr-label { font-size:8px; color:#999; margin-top:4px; text-align:center; }

    .obs-box { background:#f8f9fa; border-radius:5px; padding:9px 11px; margin-top:10px; }
    .obs-title { font-size:9px; font-weight:bold; text-transform:uppercase;
                 color:#888; margin-bottom:3px; }

    .footer { border-top:1px solid #eee; padding-top:8px; margin-top:14px;
              font-size:8px; color:#999; display:table; width:100%; }
    .footer-left  { display:table-cell; }
    .footer-right { display:table-cell; text-align:right; }
</style>
</head>
<body>
<div class="page">

    <div class="header">
        <div class="header-left">
            @if($empresa->logo)
            <img src="{{ public_path('storage/'.$empresa->logo) }}"
                 style="max-height:50px;max-width:140px;object-fit:contain;margin-bottom:4px;">
            @else
            <div class="logo-texto">{{ $empresa->nombre_comercial ?: $empresa->razon_social }}</div>
            @endif
            <div class="empresa-nombre">{{ $empresa->razon_social }}</div>
            <div class="empresa-sub">
                NIT: {{ $empresa->nit_formateado }}
                @if($empresa->direccion) · {{ $empresa->direccion }} @endif
                @if($empresa->telefono) · Tel: {{ $empresa->telefono }} @endif
                @if($empresa->email) · {{ $empresa->email }} @endif
            </div>
        </div>
        <div class="header-right">
            <div class="cot-tipo">Cotización</div>
            <div class="cot-numero">{{ $cotizacion->numero }}</div>
            <div class="cot-fecha">{{ $cotizacion->fecha_emision->format('d/m/Y') }}</div>
            <div style="margin-top:5px;">
                <span class="badge-{{ $cotizacion->estado }}">{{ strtoupper($cotizacion->estado) }}</span>
            </div>
        </div>
    </div>

    <div class="valida-box">
        <div class="valida-left">
            <i>Oferta válida hasta:</i>
            <strong> {{ $cotizacion->fecha_vencimiento->format('d/m/Y') }}</strong>
        </div>
        <div class="valida-right">
            Forma de pago: <strong>{{ ucfirst($cotizacion->forma_pago) }}</strong>
            @if($cotizacion->plazo_pago > 0)
            · Plazo: <strong>{{ $cotizacion->plazo_pago }} días</strong>
            @endif
        </div>
    </div>

    <div class="info-grid">
        <div class="info-col">
            <div class="info-box">
                <div class="info-title">Cotización para</div>
                <div style="font-size:12px;font-weight:bold;margin-bottom:3px;">
                    {{ $cotizacion->cliente_nombre }}
                </div>
                @if($cotizacion->cliente_documento)
                <div style="font-size:10px;color:#555;">{{ $cotizacion->cliente_documento }}</div>
                @endif
                @if($cotizacion->cliente_email)
                <div style="font-size:10px;color:#555;">{{ $cotizacion->cliente_email }}</div>
                @endif
                @if($cotizacion->cliente_telefono)
                <div style="font-size:10px;color:#555;">{{ $cotizacion->cliente_telefono }}</div>
                @endif
                @if($cotizacion->cliente_direccion)
                <div style="font-size:10px;color:#555;">{{ $cotizacion->cliente_direccion }}</div>
                @endif
            </div>
        </div>
        <div class="info-col-r">
            <div class="info-box">
                <div class="info-title">Datos</div>
                <div class="info-row">
                    <div class="info-label">Cotización N°</div>
                    <div class="info-value" style="color:#3b82f6;">{{ $cotizacion->numero }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Fecha</div>
                    <div class="info-value">{{ $cotizacion->fecha_emision->format('d/m/Y') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Válida hasta</div>
                    <div class="info-value">{{ $cotizacion->fecha_vencimiento->format('d/m/Y') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Elaboró</div>
                    <div class="info-value">{{ $cotizacion->usuario->name ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th style="width:40%;">Descripción</th>
                <th class="text-center" style="width:10%;">Cant.</th>
                <th class="text-right" style="width:20%;">Precio Unit.</th>
                <th class="text-center" style="width:10%;">IVA</th>
                <th class="text-right" style="width:20%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cotizacion->items as $item)
            <tr>
                <td>
                    <div style="font-weight:600;">{{ $item->descripcion }}</div>
                    @if($item->codigo)
                    <div style="font-size:9px;color:#888;font-family:monospace;">{{ $item->codigo }}</div>
                    @endif
                </td>
                <td class="text-center">{{ number_format($item->cantidad, 0) }}</td>
                <td class="text-right">${{ number_format($item->precio_unitario, 0, ',', '.') }}</td>
                <td class="text-center">{{ $item->iva_pct }}%</td>
                <td class="text-right" style="font-weight:bold;">
                    ${{ number_format($item->total, 0, ',', '.') }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="bottom-grid">
        <div class="bottom-left">
            @if($cotizacion->observaciones)
            <div class="obs-box">
                <div class="obs-title">Observaciones</div>
                <div style="font-size:10px;">{{ $cotizacion->observaciones }}</div>
            </div>
            @endif
            @if($cotizacion->terminos)
            <div class="obs-box" style="margin-top:8px;">
                <div class="obs-title">Términos y Condiciones</div>
                <div style="font-size:9px;color:#666;">{{ $cotizacion->terminos }}</div>
            </div>
            @endif
        </div>
        <div class="bottom-right">
            <div class="totales">
                <table>
                    <tr>
                        <td class="label">Subtotal</td>
                        <td class="valor">${{ number_format($cotizacion->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @if($cotizacion->descuento > 0)
                    <tr>
                        <td class="label">Descuento</td>
                        <td class="valor" style="color:#dc2626;">
                            -${{ number_format($cotizacion->descuento, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td class="label">IVA</td>
                        <td class="valor" style="color:#2563eb;">
                            +${{ number_format($cotizacion->iva, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr class="total-row">
                        <td>TOTAL</td>
                        <td style="text-align:right;">
                            ${{ number_format($cotizacion->total, 0, ',', '.') }}
                        </td>
                    </tr>
                </table>
            </div>
            @isset($qrBase64)
            <div class="qr-box" style="margin-top:12px;">
                <img src="data:image/png;base64,{{ $qrBase64 }}" width="90" height="90">
                <div class="qr-label">{{ $cotizacion->numero }}</div>
            </div>
            @endisset
        </div>
    </div>

    <div class="footer">
        <div class="footer-left">
            {{ $empresa->razon_social }} · NIT {{ $empresa->nit_formateado }}
            @if($empresa->pie_factura) · {{ $empresa->pie_factura }} @endif
        </div>
        <div class="footer-right">
            Generado el {{ now()->format('d/m/Y H:i') }} · FacturaCO
        </div>
    </div>

</div>
</body>
</html>