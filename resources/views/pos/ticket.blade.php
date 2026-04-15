<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket {{ $factura->numero }}</title>
    <style>
        /* ── Reset y tamaño 80mm ── */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page {
            size: 80mm auto;
            margin: 4mm 4mm;
        }

        @media print {
            body { width: 80mm; }
            .no-print { display: none !important; }
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            color: #000;
            background: #fff;
            width: 80mm;
            padding: 4mm;
        }

        /* ── Utilidades ── */
        .center  { text-align: center; }
        .right   { text-align: right; }
        .bold    { font-weight: bold; }
        .separator {
            border: none;
            border-top: 1px dashed #555;
            margin: 4px 0;
        }
        .separator-solid {
            border: none;
            border-top: 1px solid #000;
            margin: 4px 0;
        }

        /* ── Header empresa ── */
        .header { text-align: center; margin-bottom: 4px; }
        .header .empresa-nombre { font-size: 14px; font-weight: bold; text-transform: uppercase; }
        .header .empresa-sub    { font-size: 10px; color: #333; }

        /* ── Título factura ── */
        .titulo-doc {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            border: 1px solid #000;
            padding: 3px 0;
            margin: 4px 0;
        }

        /* ── Info cliente/fecha ── */
        .info-row {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            margin: 1px 0;
        }
        .info-label { color: #555; min-width: 45px; }

        /* ── Tabla de ítems ── */
        .items-header {
            display: flex;
            font-size: 10px;
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding: 2px 0;
            margin-top: 4px;
        }
        .items-header .col-desc  { flex: 1; }
        .items-header .col-cant  { width: 22px; text-align: center; }
        .items-header .col-precio{ width: 52px; text-align: right; }
        .items-header .col-total { width: 52px; text-align: right; }

        .item-row {
            font-size: 10px;
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
            color: #333;
        }
        .item-detalle .col-desc  { flex: 1; font-size: 9px; color: #666; }
        .item-detalle .col-cant  { width: 22px; text-align: center; }
        .item-detalle .col-precio{ width: 52px; text-align: right; }
        .item-detalle .col-total { width: 52px; text-align: right; font-weight: bold; }

        /* ── Totales ── */
        .totales { margin-top: 4px; }
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            padding: 1px 0;
        }
        .total-row.grand {
            font-size: 14px;
            font-weight: bold;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 3px 0;
            margin: 2px 0;
        }
        .total-row.pago { font-size: 11px; color: #333; }

        /* ── Footer ── */
        .footer {
            text-align: center;
            font-size: 9px;
            color: #555;
            margin-top: 6px;
            line-height: 1.5;
        }
        .footer .gracias {
            font-size: 12px;
            font-weight: bold;
            color: #000;
            margin-bottom: 2px;
        }

        /* ── Botones solo para pantalla ── */
        .no-print {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin: 12px 0 6px;
        }
        .btn-print {
            padding: 8px 20px;
            background: #000;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-close {
            padding: 8px 20px;
            background: #eee;
            color: #333;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
        }
    </style>
</head>
<body>

    {{-- ── Botones pantalla ── --}}
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">
            🖨️ Imprimir
        </button>
        <button class="btn-close" onclick="window.close()">
            Cerrar
        </button>
    </div>

    {{-- ── Empresa ── --}}
    <div class="header">
        @if($empresa->logo)
            <img src="{{ url(Storage::url($empresa->logo)) }}" style="max-height:30mm; max-width:100%; margin-bottom:3px;">
        @endif
        <div class="empresa-nombre">{{ $empresa->razon_social }}</div>
        @if($empresa->nombre_comercial && $empresa->nombre_comercial !== $empresa->razon_social)
        <div class="empresa-sub">{{ $empresa->nombre_comercial }}</div>
        @endif
        @if($empresa->nit)
        <div class="empresa-sub">NIT: {{ $empresa->nit }}{{ $empresa->digito_verificacion ? '-'.$empresa->digito_verificacion : '' }}</div>
        @endif
        @if($empresa->direccion)
        <div class="empresa-sub">{{ $empresa->direccion }}{{ $empresa->municipio ? ', '.$empresa->municipio : '' }}</div>
        @endif
        @if($empresa->telefono)
        <div class="empresa-sub">Tel: {{ $empresa->telefono }}</div>
        @endif
        @if($empresa->email)
        <div class="empresa-sub">{{ $empresa->email }}</div>
        @endif
    </div>

    <hr class="separator-solid">

    {{-- ── Título ── --}}
    <div class="titulo-doc">FACTURA DE VENTA</div>
    <div class="titulo-doc" style="font-size:11px; font-weight:normal; border-top:none; margin-top:-3px;">
        No. {{ $factura->numero }}
    </div>

    {{-- ── Info ── --}}
    <div class="info-row">
        <span class="info-label">Fecha:</span>
        <span>{{ $factura->fecha_emision->format('d/m/Y') }} {{ now()->format('H:i') }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Cliente:</span>
        <span style="text-align:right; max-width: 110px; overflow:hidden; white-space:nowrap; text-overflow:ellipsis;">
            {{ $factura->cliente_nombre }}
        </span>
    </div>
    @if($factura->cliente_documento && !str_contains($factura->cliente_documento, '222222222'))
    <div class="info-row">
        <span class="info-label">Doc:</span>
        <span>{{ $factura->cliente_documento }}</span>
    </div>
    @endif
    <div class="info-row">
        <span class="info-label">Cajero:</span>
        <span>{{ $factura->usuario?->name ?? auth()->user()->name }}</span>
    </div>

    <hr class="separator">

    {{-- ── Cabecera tabla ── --}}
    <div class="items-header">
        <span class="col-desc">PRODUCTO</span>
        <span class="col-cant">Ct</span>
        <span class="col-precio">Precio</span>
        <span class="col-total">Total</span>
    </div>

    {{-- ── Ítems ── --}}
    @foreach($factura->items as $item)
    <div class="item-row">
        <div class="item-nombre">{{ Str::limit($item->descripcion, 28) }}</div>
        <div class="item-detalle">
            <span class="col-desc">
                @if($item->iva_pct > 0) IVA {{ $item->iva_pct }}% @else Exento @endif
                @if($item->descuento_pct > 0) · Dto {{ $item->descuento_pct }}% @endif
            </span>
            <span class="col-cant">{{ $item->cantidad % 1 == 0 ? (int)$item->cantidad : number_format($item->cantidad, 2) }}</span>
            <span class="col-precio">{{ number_format($item->precio_unitario * (1 + $item->iva_pct/100), 0, ',', '.') }}</span>
            <span class="col-total">{{ number_format($item->total, 0, ',', '.') }}</span>
        </div>
    </div>
    @endforeach

    <hr class="separator-solid">

    {{-- ── Totales ── --}}
    <div class="totales">
        <div class="total-row">
            <span>Subtotal:</span>
            <span>$ {{ number_format($factura->subtotal, 0, ',', '.') }}</span>
        </div>
        @if($factura->descuento > 0)
        <div class="total-row">
            <span>Descuento:</span>
            <span>-$ {{ number_format($factura->descuento, 0, ',', '.') }}</span>
        </div>
        @endif
        <div class="total-row">
            <span>IVA:</span>
            <span>$ {{ number_format($factura->iva, 0, ',', '.') }}</span>
        </div>
        @if($factura->retefuente > 0)
        <div class="total-row">
            <span>ReteFuente:</span>
            <span>-$ {{ number_format($factura->retefuente, 0, ',', '.') }}</span>
        </div>
        @endif
        <div class="total-row grand">
            <span>TOTAL:</span>
            <span>$ {{ number_format($factura->total, 0, ',', '.') }}</span>
        </div>

        <div class="total-row pago">
            <span>Forma pago:</span>
            <span class="bold">{{ strtoupper($factura->forma_pago) }}</span>
        </div>
        @if($efectivo > 0)
        <div class="total-row pago">
            <span>Efectivo:</span>
            <span>$ {{ number_format($efectivo, 0, ',', '.') }}</span>
        </div>
        <div class="total-row pago">
            <span class="bold">Vuelto:</span>
            <span class="bold">$ {{ number_format($vuelto, 0, ',', '.') }}</span>
        </div>
        @endif
    </div>

    <hr class="separator">

    {{-- ── Estado ── --}}
    <div class="center" style="font-size:10px; margin: 3px 0;">
        Estado: <strong>{{ strtoupper($factura->estado) }}</strong>
    </div>

    <hr class="separator">

    {{-- ── Footer ── --}}
    <div class="footer">
        <div class="gracias">¡Gracias por su compra!</div>
        @if($empresa->website)
        <div>{{ $empresa->website }}</div>
        @endif
        <div>Conserve este comprobante</div>
        <div style="margin-top:4px; font-size:9px; color:#999;">
            Generado por FacturaCO · {{ now()->format('d/m/Y H:i:s') }}
        </div>
    </div>

    {{-- Auto-print al cargar ── --}}
    <script>
        window.addEventListener('load', function () {
            // Solo imprimir automáticamente si se abrió como popup (no tab directo)
            if (window.opener) {
                setTimeout(() => window.print(), 400);
            }
        });
    </script>
</body>
</html>
