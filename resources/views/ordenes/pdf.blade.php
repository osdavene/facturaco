<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size:11px; color:#1a1a1a; }
    .page { padding:28px; }

    .header { display:table; width:100%; border-bottom:3px solid #f59e0b;
              padding-bottom:13px; margin-bottom:16px; }
    .header-left  { display:table-cell; width:55%; vertical-align:middle; }
    .header-right { display:table-cell; width:45%; text-align:right; vertical-align:top; }

    .logo-texto   { font-size:20px; font-weight:bold; color:#f59e0b; margin-bottom:4px; }
    .empresa-nombre { font-size:12px; font-weight:bold; }
    .empresa-sub    { font-size:9px; color:#666; margin-top:2px; line-height:1.5; }

    .oc-tipo   { font-size:10px; color:#888; text-transform:uppercase; letter-spacing:1px; }
    .oc-numero { font-size:22px; font-weight:bold; }
    .oc-fecha  { font-size:10px; color:#666; margin-top:3px; }

    .badge { display:inline-block; padding:2px 8px; border-radius:8px;
             font-size:9px; font-weight:bold; margin-top:5px; }
    .badge-borrador { background:#fef3c7; color:#92400e; }
    .badge-enviada  { background:#cffafe; color:#164e63; }
    .badge-aprobada { background:#dbeafe; color:#1d4ed8; }
    .badge-recibida { background:#d1fae5; color:#065f46; }
    .badge-anulada  { background:#f1f5f9; color:#64748b; }

    .info-grid { display:table; width:100%; margin-bottom:16px; }
    .info-col  { display:table-cell; width:50%; vertical-align:top; padding-right:10px; }
    .info-col-r{ display:table-cell; width:50%; vertical-align:top; padding-left:10px; }
    .info-box  { background:#f8f9fa; border-radius:6px; padding:11px;
                 border-left:3px solid #f59e0b; }
    .info-title { font-size:9px; font-weight:bold; text-transform:uppercase;
                  color:#f59e0b; margin-bottom:6px; }
    .info-row  { display:table; width:100%; margin-bottom:3px; }
    .info-label{ display:table-cell; font-size:9px; color:#888; }
    .info-value{ display:table-cell; font-size:10px; text-align:right; font-weight:600; }

    table.items { width:100%; border-collapse:collapse; margin-bottom:14px; }
    table.items thead tr { background:#f59e0b; }
    table.items thead th { padding:7px 9px; text-align:left; font-size:9px;
                           text-transform:uppercase; color:#000; }
    table.items tbody tr:nth-child(even) { background:#f8f9fa; }
    table.items tbody td { padding:7px 9px; border-bottom:1px solid #eee; font-size:10px; }

    .text-right  { text-align:right; }
    .text-center { text-align:center; }

    .totales { width:220px; float:right; margin-bottom:16px; }
    .totales table { width:100%; border-collapse:collapse; }
    .totales td { padding:4px 8px; font-size:11px; border-bottom:1px solid #f0f0f0; }
    .totales .label { color:#666; }
    .totales .valor { text-align:right; font-weight:600; }
    .totales .total-row td { background:#f59e0b; font-weight:bold; font-size:13px;
                              padding:7px 8px; border:none; }

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
            </div>
        </div>
        <div class="header-right">
            <div class="oc-tipo">Orden de Compra</div>
            <div class="oc-numero">{{ $orden->numero }}</div>
            <div class="oc-fecha">{{ $orden->fecha_emision->format('d/m/Y') }}</div>
            <div><span class="badge badge-{{ $orden->estado }}">{{ strtoupper($orden->estado) }}</span></div>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-col">
            <div class="info-box">
                <div class="info-title">Proveedor</div>
                <div style="font-size:12px;font-weight:bold;margin-bottom:3px;">
                    {{ $orden->proveedor_nombre }}
                </div>
                <div style="font-size:10px;color:#555;">{{ $orden->proveedor_documento }}</div>
            </div>
        </div>
        <div class="info-col-r">
            <div class="info-box">
                <div class="info-title">Datos de la Orden</div>
                <div class="info-row">
                    <div class="info-label">Fecha emisión</div>
                    <div class="info-value">{{ $orden->fecha_emision->format('d/m/Y') }}</div>
                </div>
                @if($orden->fecha_esperada)
                <div class="info-row">
                    <div class="info-label">Entrega esperada</div>
                    <div class="info-value">{{ $orden->fecha_esperada->format('d/m/Y') }}</div>
                </div>
                @endif
                <div class="info-row">
                    <div class="info-label">Forma de pago</div>
                    <div class="info-value">{{ ucfirst($orden->forma_pago) }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Plazo</div>
                    <div class="info-value">{{ $orden->plazo_pago }} días</div>
                </div>
            </div>
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th style="width:12%;">Código</th>
                <th style="width:40%;">Descripción</th>
                <th class="text-center" style="width:10%;">Cant.</th>
                <th class="text-right" style="width:17%;">Precio Unit.</th>
                <th class="text-center" style="width:8%;">IVA</th>
                <th class="text-right" style="width:13%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orden->items as $item)
            <tr>
                <td style="font-family:monospace;font-size:9px;color:#888;">{{ $item->codigo }}</td>
                <td>{{ $item->descripcion }}</td>
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

    <div class="totales">
        <table>
            <tr>
                <td class="label">Subtotal</td>
                <td class="valor">${{ number_format($orden->subtotal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">IVA</td>
                <td class="valor" style="color:#2563eb;">
                    +${{ number_format($orden->iva, 0, ',', '.') }}
                </td>
            </tr>
            <tr class="total-row">
                <td>TOTAL</td>
                <td style="text-align:right;">${{ number_format($orden->total, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div style="clear:both;"></div>

    @if($orden->observaciones)
    <div class="obs-box">
        <div class="obs-title">Observaciones</div>
        <div style="font-size:10px;">{{ $orden->observaciones }}</div>
    </div>
    @endif

    <div class="footer">
        <div class="footer-left">
            {{ $empresa->razon_social }} · NIT {{ $empresa->nit_formateado }}
        </div>
        <div class="footer-right">
            Generado el {{ now()->format('d/m/Y H:i') }} · FacturaCO
        </div>
    </div>

</div>
</body>
</html>