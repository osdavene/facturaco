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

    .logo-texto     { font-size:20px; font-weight:bold; color:#f59e0b; margin-bottom:4px; }
    .empresa-nombre { font-size:12px; font-weight:bold; }
    .empresa-sub    { font-size:9px; color:#666; margin-top:2px; line-height:1.5; }

    .rem-tipo   { font-size:10px; color:#888; text-transform:uppercase; letter-spacing:1px; }
    .rem-numero { font-size:22px; font-weight:bold; }
    .rem-fecha  { font-size:10px; color:#666; margin-top:3px; }

    .badge-borrador  { background:#fef3c7; color:#92400e; padding:2px 8px; border-radius:8px; font-size:9px; font-weight:bold; }
    .badge-enviada   { background:#dbeafe; color:#1d4ed8; padding:2px 8px; border-radius:8px; font-size:9px; font-weight:bold; }
    .badge-entregada { background:#d1fae5; color:#065f46; padding:2px 8px; border-radius:8px; font-size:9px; font-weight:bold; }
    .badge-facturada { background:#ede9fe; color:#5b21b6; padding:2px 8px; border-radius:8px; font-size:9px; font-weight:bold; }
    .badge-anulada   { background:#f1f5f9; color:#64748b; padding:2px 8px; border-radius:8px; font-size:9px; font-weight:bold; }

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
    table.items thead th { padding:8px 10px; text-align:left; font-size:9px;
                           text-transform:uppercase; color:#000; }
    table.items tbody tr:nth-child(even) { background:#f8f9fa; }
    table.items tbody td { padding:7px 10px; border-bottom:1px solid #eee; font-size:10px; }
    table.items tfoot td { padding:7px 10px; font-weight:bold;
                           background:#f59e0b; font-size:11px; }

    .text-right  { text-align:right; }
    .text-center { text-align:center; }

    .firma-grid { display:table; width:100%; margin-top:40px; }
    .firma-col  { display:table-cell; width:40%; text-align:center; }
    .firma-linea{ border-top:1px solid #333; padding-top:6px; font-size:9px;
                  color:#666; text-transform:uppercase; }

    .obs-box { background:#f8f9fa; border-radius:5px; padding:9px 11px; margin-top:10px; }
    .obs-title { font-size:9px; font-weight:bold; text-transform:uppercase;
                 color:#888; margin-bottom:3px; }

    .footer { border-top:1px solid #eee; padding-top:8px; margin-top:14px;
              font-size:8px; color:#999; display:table; width:100%; }
    .footer-left  { display:table-cell; }
    .footer-right { display:table-cell; text-align:right; }

    .aviso { background:#fffbeb; border:1px solid #fde68a; border-radius:5px;
             padding:7px 10px; margin-bottom:12px; font-size:9px; color:#92400e;
             text-align:center; }
</style>
</head>
<body>
<div class="page">

    {{-- HEADER --}}
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
            <div class="rem-tipo">Remisión de Despacho</div>
            <div class="rem-numero">{{ $remision->numero }}</div>
            <div class="rem-fecha">{{ $remision->fecha_emision->format('d/m/Y') }}</div>
            <div style="margin-top:5px;">
                <span class="badge-{{ $remision->estado }}">{{ strtoupper($remision->estado) }}</span>
            </div>
        </div>
    </div>

    {{-- AVISO --}}
    <div class="aviso">
        Este documento es una remisión de despacho y NO constituye factura de venta.
        @if($remision->factura)
        Facturado como <strong>{{ $remision->factura->numero }}</strong>.
        @else
        Los impuestos se aplicarán al momento de facturar.
        @endif
    </div>

    {{-- INFO --}}
    <div class="info-grid">
        <div class="info-col">
            <div class="info-box">
                <div class="info-title">Destinatario</div>
                <div style="font-size:12px;font-weight:bold;margin-bottom:3px;">
                    {{ $remision->cliente_nombre }}
                </div>
                @if($remision->cliente_documento)
                <div style="font-size:10px;color:#555;">{{ $remision->cliente_documento }}</div>
                @endif
                @if($remision->cliente_telefono)
                <div style="font-size:10px;color:#555;">Tel: {{ $remision->cliente_telefono }}</div>
                @endif
                @if($remision->cliente_direccion)
                <div style="font-size:10px;color:#555;">{{ $remision->cliente_direccion }}</div>
                @endif
            </div>
        </div>
        <div class="info-col-r">
            <div class="info-box">
                <div class="info-title">Datos del Envío</div>
                @if($remision->fecha_entrega)
                <div class="info-row">
                    <div class="info-label">Fecha entrega</div>
                    <div class="info-value">{{ $remision->fecha_entrega->format('d/m/Y') }}</div>
                </div>
                @endif
                @if($remision->lugar_entrega)
                <div class="info-row">
                    <div class="info-label">Lugar</div>
                    <div class="info-value">{{ $remision->lugar_entrega }}</div>
                </div>
                @endif
                @if($remision->transportador)
                <div class="info-row">
                    <div class="info-label">Transportador</div>
                    <div class="info-value">{{ $remision->transportador }}</div>
                </div>
                @endif
                @if($remision->guia)
                <div class="info-row">
                    <div class="info-label">Guía</div>
                    <div class="info-value" style="font-family:monospace;">{{ $remision->guia }}</div>
                </div>
                @endif
                <div class="info-row">
                    <div class="info-label">Elaboró</div>
                    <div class="info-value">{{ $remision->usuario->name ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ITEMS --}}
    <table class="items">
        <thead>
            <tr>
                <th style="width:10%;">Código</th>
                <th style="width:45%;">Descripción</th>
                <th class="text-center" style="width:10%;">Unidad</th>
                <th class="text-center" style="width:15%;">Cantidad</th>
                <th class="text-right" style="width:20%;">Precio Ref.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($remision->items as $item)
            <tr>
                <td style="font-family:monospace;font-size:9px;color:#888;">
                    {{ $item->codigo }}
                </td>
                <td style="font-weight:600;">{{ $item->descripcion }}</td>
                <td class="text-center">{{ $item->unidad }}</td>
                <td class="text-center" style="font-weight:bold;font-size:12px;">
                    {{ number_format($item->cantidad, 0) }}
                </td>
                <td class="text-right">${{ number_format($item->precio_unitario, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">TOTALES</td>
                <td class="text-center">{{ $remision->items->sum('cantidad') }} uds</td>
                <td class="text-right">${{ number_format($remision->total, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- OBSERVACIONES --}}
    @if($remision->observaciones)
    <div class="obs-box">
        <div class="obs-title">Observaciones</div>
        <div style="font-size:10px;">{{ $remision->observaciones }}</div>
    </div>
    @endif

    {{-- FIRMAS --}}
    <div class="firma-grid">
        <div class="firma-col">
            <div style="height:40px;"></div>
            <div class="firma-linea">Entregado por</div>
            <div style="font-size:9px;color:#999;margin-top:3px;">{{ $empresa->razon_social }}</div>
        </div>
        <div class="firma-col" style="width:20%;"></div>
        <div class="firma-col">
            <div style="height:40px;"></div>
            <div class="firma-linea">Recibido por</div>
            <div style="font-size:9px;color:#999;margin-top:3px;">{{ $remision->cliente_nombre }}</div>
        </div>
    </div>

    {{-- FOOTER --}}
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