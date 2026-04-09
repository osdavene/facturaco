<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size:11px; color:#1a1a1a; }
    .page { padding:25px; }

    .header { display:table; width:100%; border-bottom:3px solid #10b981;
              padding-bottom:12px; margin-bottom:16px; }
    .header-left  { display:table-cell; width:55%; vertical-align:middle; }
    .header-right { display:table-cell; width:45%; text-align:right; vertical-align:top; }

    .logo-texto { font-size:20px; font-weight:bold; color:#10b981; }
    .empresa-nombre { font-size:12px; font-weight:bold; margin-top:3px; }
    .empresa-sub    { font-size:9px; color:#666; margin-top:2px; }

    .titulo { font-size:10px; color:#888; text-transform:uppercase; letter-spacing:1px; }
    .numero { font-size:22px; font-weight:bold; }
    .fecha  { font-size:10px; color:#666; margin-top:3px; }

    .valor-box { background:#10b981; color:#fff; border-radius:10px;
                 padding:16px; text-align:center; margin-bottom:16px; }
    .valor-label { font-size:10px; text-transform:uppercase; letter-spacing:1px; opacity:.8; }
    .valor-num   { font-size:28px; font-weight:bold; margin-top:4px; }

    .info-grid { display:table; width:100%; margin-bottom:16px; }
    .info-col  { display:table-cell; width:50%; vertical-align:top; padding-right:10px; }
    .info-col-r{ display:table-cell; width:50%; vertical-align:top; padding-left:10px; }
    .info-box  { background:#f8f9fa; border-radius:6px; padding:12px;
                 border-left:3px solid #10b981; }
    .info-title { font-size:9px; font-weight:bold; text-transform:uppercase;
                  color:#10b981; margin-bottom:7px; }
    .info-row  { display:table; width:100%; margin-bottom:4px; }
    .info-label{ display:table-cell; font-size:9px; color:#888; text-transform:uppercase; }
    .info-value{ display:table-cell; font-size:11px; text-align:right; font-weight:600; }

    .concepto-box { background:#f0fdf4; border:1px solid #bbf7d0; border-radius:6px;
                    padding:12px; margin-bottom:16px; }
    .concepto-label { font-size:9px; font-weight:bold; text-transform:uppercase;
                      color:#065f46; margin-bottom:4px; }

    .firma-grid { display:table; width:100%; margin-top:30px; }
    .firma-col  { display:table-cell; width:45%; text-align:center; }
    .firma-linea{ border-top:1px solid #333; padding-top:6px; font-size:9px;
                  color:#666; text-transform:uppercase; }

    .footer { border-top:1px solid #eee; padding-top:8px; margin-top:16px;
              font-size:8px; color:#999; text-align:center; }

    .badge-activo  { background:#d1fae5; color:#065f46; padding:2px 8px;
                     border-radius:8px; font-size:9px; font-weight:bold; }
    .badge-anulado { background:#fee2e2; color:#991b1b; padding:2px 8px;
                     border-radius:8px; font-size:9px; font-weight:bold; }
</style>
</head>
<body>
<div class="page">

    {{-- HEADER --}}
    <div class="header">
        <div class="header-left">
            @if($empresa->logo)
            <img src="{{ public_path('storage/'.$empresa->logo) }}"
                 style="max-height:50px;max-width:150px;object-fit:contain;margin-bottom:4px;">
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
            <div class="titulo">Recibo de Caja</div>
            <div class="numero">{{ $recibo->numero }}</div>
            <div class="fecha">{{ $recibo->fecha->format('d/m/Y') }}</div>
            <div style="margin-top:6px;">
                <span class="badge-{{ $recibo->estado }}">{{ strtoupper($recibo->estado) }}</span>
            </div>
        </div>
    </div>

    {{-- VALOR --}}
    <div class="valor-box">
        <div class="valor-label">Valor Recibido</div>
        <div class="valor-num">${{ number_format($recibo->valor, 0, ',', '.') }}</div>
    </div>

    {{-- INFO --}}
    <div class="info-grid">
        <div class="info-col">
            <div class="info-box">
                <div class="info-title">Recibido de</div>
                <div style="font-size:12px;font-weight:bold;margin-bottom:3px;">
                    {{ $recibo->cliente_nombre }}
                </div>
                <div style="font-size:10px;color:#555;">{{ $recibo->cliente_documento }}</div>
            </div>
        </div>
        <div class="info-col-r">
            <div class="info-box">
                <div class="info-title">Detalles del Pago</div>
                <div class="info-row">
                    <div class="info-label">Forma de pago</div>
                    <div class="info-value">{{ ucfirst($recibo->forma_pago) }}</div>
                </div>
                @if($recibo->banco)
                <div class="info-row">
                    <div class="info-label">Banco</div>
                    <div class="info-value">{{ $recibo->banco }}</div>
                </div>
                @endif
                @if($recibo->num_referencia)
                <div class="info-row">
                    <div class="info-label">Referencia</div>
                    <div class="info-value" style="font-family:monospace;font-size:10px;">
                        {{ $recibo->num_referencia }}
                    </div>
                </div>
                @endif
                @if($recibo->factura)
                <div class="info-row">
                    <div class="info-label">Factura</div>
                    <div class="info-value" style="color:#f59e0b;">{{ $recibo->factura->numero }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- CONCEPTO --}}
    <div class="concepto-box">
        <div class="concepto-label">Concepto</div>
        <div style="font-size:11px;">{{ $recibo->concepto }}</div>
        @if($recibo->observaciones)
        <div style="font-size:10px;color:#666;margin-top:6px;font-style:italic;">
            {{ $recibo->observaciones }}
        </div>
        @endif
    </div>

    {{-- FIRMAS --}}
    <div class="firma-grid">
        <div class="firma-col">
            <div style="height:35px;"></div>
            <div class="firma-linea">Firma quien recibe</div>
            <div style="font-size:9px;color:#999;margin-top:3px;">{{ $empresa->razon_social }}</div>
        </div>
        <div class="firma-col" style="width:10%;"></div>
        <div class="firma-col">
            <div style="height:35px;"></div>
            <div class="firma-linea">Firma quien paga</div>
            <div style="font-size:9px;color:#999;margin-top:3px;">{{ $recibo->cliente_nombre }}</div>
        </div>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        {{ $empresa->razon_social }} · NIT {{ $empresa->nit_formateado }}
        @if($empresa->pie_factura) · {{ $empresa->pie_factura }} @endif
        · Generado el {{ now()->format('d/m/Y H:i') }} · FacturaCO
    </div>

</div>
</body>
</html>