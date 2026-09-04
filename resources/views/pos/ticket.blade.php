<!DOCTYPE html>
@php
    $ancho = request('ancho', $ancho ?? '80');
    $es58 = ($ancho == '58');
    $anchoCss = $es58 ? '58mm' : '80mm';
    $fontSize = $es58 ? '10px' : '11px';
    $nombreEmpresa = $empresa->nombre_comercial ?: $empresa->razon_social;
@endphp
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket {{ $factura->numero }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page {
            size: {{ $anchoCss }} auto;
            margin: 2mm 3mm;
        }

        @media print {
            body { width: {{ $anchoCss }}; margin: 0; padding: 2mm; }
            .no-print { display: none !important; }
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: {{ $fontSize }};
            color: #000;
            background: #fff;
            width: {{ $anchoCss }};
            margin: 0 auto;
            padding: 3mm;
        }

        .center { text-align: center; }
        .right  { text-align: right; }
        .bold   { font-weight: bold; }

        .separator {
            border: none;
            border-top: 1px dashed #444;
            margin: 4px 0;
        }

        .separator-solid {
            border: none;
            border-top: 1px solid #000;
            margin: 4px 0;
        }

        .header { text-align: center; margin-bottom: 4px; }
        .header .empresa-nombre { font-size: {{ $es58 ? '12px' : '14px' }}; font-weight: bold; text-transform: uppercase; }
        .header .empresa-sub    { font-size: {{ $es58 ? '9px' : '10px' }}; color: #222; }

        .titulo-doc {
            text-align: center;
            font-size: {{ $es58 ? '11px' : '12px' }};
            font-weight: bold;
            border: 1px solid #000;
            padding: 2px 0;
            margin: 4px 0;
            text-transform: uppercase;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            font-size: {{ $es58 ? '9px' : '10px' }};
            margin: 1px 0;
        }

        .items-header {
            display: flex;
            font-size: {{ $es58 ? '9px' : '10px' }};
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding: 2px 0;
            margin-top: 4px;
        }
        .items-header .col-desc  { flex: 1; }
        .items-header .col-cant  { width: {{ $es58 ? '18px' : '24px' }}; text-align: center; }
        .items-header .col-precio{ width: {{ $es58 ? '45px' : '52px' }}; text-align: right; }
        .items-header .col-total { width: {{ $es58 ? '45px' : '52px' }}; text-align: right; }

        .item-row {
            font-size: {{ $es58 ? '9px' : '10px' }};
            padding: 2px 0;
            border-bottom: 1px dotted #ccc;
        }
        .item-nombre {
            font-weight: bold;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            max-width: 100%;
        }
        .item-detalle {
            display: flex;
            color: #111;
        }
        .item-detalle .col-desc  { flex: 1; font-size: {{ $es58 ? '8.5px' : '9px' }}; color: #444; }
        .item-detalle .col-cant  { width: {{ $es58 ? '18px' : '24px' }}; text-align: center; }
        .item-detalle .col-precio{ width: {{ $es58 ? '45px' : '52px' }}; text-align: right; }
        .item-detalle .col-total { width: {{ $es58 ? '45px' : '52px' }}; text-align: right; font-weight: bold; }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: {{ $es58 ? '9.5px' : '10.5px' }};
            padding: 1px 0;
        }
        .total-row.grand {
            font-size: {{ $es58 ? '12px' : '14px' }};
            font-weight: bold;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 3px 0;
            margin: 3px 0;
        }

        .qr-section { text-align: center; margin: 6px 0; }
        .qr-section img { width: {{ $es58 ? '80px' : '100px' }}; height: auto; display: inline-block; }

        .pie {
            font-size: {{ $es58 ? '8px' : '8.5px' }};
            text-align: center;
            color: #444;
            margin-top: 4px;
            line-height: 1.3;
        }

        .corte-papel {
            height: 15mm;
            text-align: center;
            color: #999;
            font-size: 8px;
            padding-top: 5mm;
        }

        /* ── Barra de acciones preview ── */
        .preview-bar {
            background: #1e293b;
            color: #fff;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-family: sans-serif;
            font-size: 13px;
        }
        .preview-btn {
            background: #f59e0b;
            color: #000;
            font-weight: bold;
            border: none;
            padding: 6px 14px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .preview-btn-sub {
            background: #334155;
            color: #fff;
            border: none;
            padding: 6px 10px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 12px;
        }
        .preview-btn-sub.active {
            background: #3b82f6;
            color: #fff;
            font-weight: bold;
        }
    </style>
</head>
<body>

    {{-- BARRA PREVIEW (NO IMPRIMIBLE) --}}
    <div class="no-print preview-bar">
        <div style="display:flex; align-items:center; gap:8px;">
            <span>Formato:</span>
            <a href="?ancho=80&efectivo={{ $efectivo }}" class="preview-btn-sub {{ !$es58 ? 'active' : '' }}">80 mm</a>
            <a href="?ancho=58&efectivo={{ $efectivo }}" class="preview-btn-sub {{ $es58 ? 'active' : '' }}">58 mm</a>
        </div>
        <div style="display:flex; gap:8px;">
            <button onclick="window.print()" class="preview-btn">
                🖨️ Imprimir Ticket
            </button>
            <button onclick="window.close()" class="preview-btn-sub">
                ✕ Cerrar
            </button>
        </div>
    </div>

    {{-- HEADER EMPRESA --}}
    <div class="header">
        <div class="empresa-nombre">{{ $nombreEmpresa }}</div>
        <div class="empresa-sub">NIT: {{ $empresa->nit_formateado ?? $empresa->nit }}</div>
        @if($empresa->direccion)
            <div class="empresa-sub">{{ $empresa->direccion }}</div>
        @endif
        @if($empresa->municipio)
            <div class="empresa-sub">{{ $empresa->municipio }}, {{ $empresa->departamento }}</div>
        @endif
        @if($empresa->telefono)
            <div class="empresa-sub">Tel: {{ $empresa->telefono }}</div>
        @endif
        @if($empresa->regimen)
            <div class="empresa-sub">{{ $empresa->regimen === 'responsable_iva' ? 'Responsable de IVA' : 'No Responsable de IVA' }}</div>
        @endif
    </div>

    <div class="separator-solid"></div>

    {{-- TÍTULO Y CONSECUTIVO --}}
    <div class="titulo-doc">
        Factura de Venta POS<br>
        # {{ $factura->numero }}
    </div>

    {{-- INFORMACIÓN DIAN / RESOLUCIÓN --}}
    @if($empresa->resolucion_numero)
    <div class="pie" style="text-align:left; margin-bottom:3px;">
        Res. DIAN: {{ $empresa->resolucion_numero }}<br>
        Del: {{ $empresa->resolucion_fecha ? \Carbon\Carbon::parse($empresa->resolucion_fecha)->format('d/m/Y') : '-' }} al {{ $empresa->resolucion_vigencia_hasta ? \Carbon\Carbon::parse($empresa->resolucion_vigencia_hasta)->format('d/m/Y') : '-' }}<br>
        Prefijo: {{ $empresa->prefijo_factura ?? 'FE' }} Rango: {{ $empresa->consecutivo_desde }} a {{ $empresa->consecutivo_hasta }}
    </div>
    <div class="separator"></div>
    @endif

    {{-- DATOS CLIENTE Y FECHA --}}
    <div class="info-row">
        <span>Fecha:</span>
        <span class="bold">{{ $factura->fecha_emision ? $factura->fecha_emision->format('d/m/Y') : date('d/m/Y') }} {{ $factura->hora_emision ?? now('America/Bogota')->format('H:i') }}</span>
    </div>
    <div class="info-row">
        <span>Cliente:</span>
        <span class="bold">{{ $factura->cliente_nombre }}</span>
    </div>
    @if($factura->cliente_documento)
    <div class="info-row">
        <span>Doc:</span>
        <span>{{ $factura->cliente_documento }}</span>
    </div>
    @endif
    @if($factura->usuario)
    <div class="info-row">
        <span>Cajero:</span>
        <span>{{ $factura->usuario->name }}</span>
    </div>
    @endif

    <div class="separator-solid"></div>

    {{-- TABLA DE ÍTEMS --}}
    <div class="items-header">
        <span class="col-desc">DESCRIPCIÓN</span>
        <span class="col-cant">CANT</span>
        <span class="col-precio">PRECIO</span>
        <span class="col-total">TOTAL</span>
    </div>

    @foreach($factura->items as $it)
    <div class="item-row">
        <div class="item-nombre">{{ $it->descripcion }}</div>
        <div class="item-detalle">
            <span class="col-desc">Ref: {{ $it->codigo ?: 'GEN' }}</span>
            <span class="col-cant">{{ rtrim(rtrim(number_format($it->cantidad, 2), '0'), '.') }}</span>
            <span class="col-precio">${{ number_format($it->precio_unitario, 0, ',', '.') }}</span>
            <span class="col-total">${{ number_format($it->total, 0, ',', '.') }}</span>
        </div>
    </div>
    @endforeach

    <div class="separator-solid"></div>

    {{-- TOTALES --}}
    <div class="total-row">
        <span>Subtotal:</span>
        <span>${{ number_format($factura->subtotal, 0, ',', '.') }}</span>
    </div>

    @if($factura->descuento > 0)
    <div class="total-row">
        <span>Descuento:</span>
        <span>-${{ number_format($factura->descuento, 0, ',', '.') }}</span>
    </div>
    @endif

    @if($factura->iva > 0)
    <div class="total-row">
        <span>IVA:</span>
        <span>${{ number_format($factura->iva, 0, ',', '.') }}</span>
    </div>
    @endif

    <div class="total-row grand">
        <span>TOTAL A PAGAR:</span>
        <span>${{ number_format($factura->total, 0, ',', '.') }}</span>
    </div>

    <div class="separator"></div>

    {{-- FORMA DE PAGO Y CAMBIO --}}
    <div class="total-row">
        <span>Forma de Pago:</span>
        <span class="bold" style="text-transform:uppercase;">{{ $factura->forma_pago }}</span>
    </div>

    @if($factura->forma_pago === 'contado' && $efectivo > 0)
    <div class="total-row">
        <span>Efectivo Recibido:</span>
        <span>${{ number_format($efectivo, 0, ',', '.') }}</span>
    </div>
    <div class="total-row bold">
        <span>Cambio / Vuelto:</span>
        <span>${{ number_format($vuelto, 0, ',', '.') }}</span>
    </div>
    @endif

    {{-- CÓDIGO QR / CUFE SI APLICA --}}
    @if(!empty($qrBase64))
    <div class="separator"></div>
    <div class="qr-section">
        <img src="data:image/png;base64,{{ $qrBase64 }}" alt="QR Factura">
        <div style="font-size:7.5px; color:#555; margin-top:2px;">Verificación Electrónica</div>
    </div>
    @endif

    {{-- MENSAJE DE PIE --}}
    <div class="separator"></div>
    <div class="pie">
        ¡Gracias por su compra!<br>
        {{ $empresa->pie_factura ?: 'Sistema de Facturación y POS — FacturaCO' }}
    </div>

    <div class="corte-papel no-print">
        -- Corte de papel --
    </div>

</body>
</html>
