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
    <title>Cierre de Caja #{{ $turno->id }}</title>
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

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: {{ $es58 ? '9.5px' : '10.5px' }};
            padding: 1.5px 0;
        }
        .total-row.grand {
            font-size: {{ $es58 ? '11px' : '13px' }};
            font-weight: bold;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 3px 0;
            margin: 3px 0;
        }

        .pie {
            font-size: {{ $es58 ? '8px' : '8.5px' }};
            text-align: center;
            color: #444;
            margin-top: 6px;
            line-height: 1.3;
        }

        .corte-papel {
            height: 15mm;
            text-align: center;
            color: #999;
            font-size: 8px;
            padding-top: 5mm;
        }

        /* ── Barra preview ── */
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
            <a href="?ancho=80" class="preview-btn-sub {{ !$es58 ? 'active' : '' }}">80 mm</a>
            <a href="?ancho=58" class="preview-btn-sub {{ $es58 ? 'active' : '' }}">58 mm</a>
        </div>
        <div style="display:flex; gap:8px;">
            <button onclick="window.print()" class="preview-btn">
                🖨️ Imprimir Cierre (Z)
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
        @if($empresa->telefono)
            <div class="empresa-sub">Tel: {{ $empresa->telefono }}</div>
        @endif
    </div>

    <div class="separator-solid"></div>

    {{-- TÍTULO REPORTE Z --}}
    <div class="titulo-doc">
        COMPROBANTE DE CIERRE DE CAJA<br>
        REPORTE Z — TURNO #{{ $turno->id }}
    </div>

    {{-- DATOS DEL TURNO --}}
    <div class="info-row">
        <span>Caja:</span>
        <span class="bold">{{ $turno->caja->nombre ?? 'Caja Principal' }}</span>
    </div>
    <div class="info-row">
        <span>Cajero / Usuario:</span>
        <span class="bold">{{ $turno->usuario->name ?? 'Usuario' }}</span>
    </div>
    <div class="info-row">
        <span>Apertura:</span>
        <span>{{ $turno->fecha_apertura ? $turno->fecha_apertura->format('d/m/Y H:i') : '-' }}</span>
    </div>
    <div class="info-row">
        <span>Cierre:</span>
        <span>{{ $turno->fecha_cierre ? $turno->fecha_cierre->format('d/m/Y H:i') : now('America/Bogota')->format('d/m/Y H:i') }}</span>
    </div>
    <div class="info-row">
        <span>Estado:</span>
        <span class="bold" style="text-transform:uppercase;">{{ $turno->estado }}</span>
    </div>

    <div class="separator-solid"></div>

    {{-- RESUMEN DE VENTAS POR MEDIO DE PAGO --}}
    <div class="bold" style="margin-bottom:3px; text-transform:uppercase; font-size:{{ $es58 ? '9px':'10px' }}">
        VENTAS POR MEDIO DE PAGO
    </div>

    <div class="total-row">
        <span>Efectivo (Contado):</span>
        <span>${{ number_format($turno->total_ventas_efectivo, 0, ',', '.') }}</span>
    </div>
    <div class="total-row">
        <span>Tarjeta (Débito/Crédito):</span>
        <span>${{ number_format($turno->total_ventas_tarjeta, 0, ',', '.') }}</span>
    </div>
    <div class="total-row">
        <span>Transferencia Bancaria:</span>
        <span>${{ number_format($turno->total_ventas_transferencia, 0, ',', '.') }}</span>
    </div>
    <div class="total-row">
        <span>Nequi / Daviplata:</span>
        <span>${{ number_format($turno->total_ventas_nequi, 0, ',', '.') }}</span>
    </div>

    <div class="separator"></div>

    <div class="total-row bold">
        <span>TOTAL VENTAS DEL TURNO:</span>
        <span>${{ number_format($turno->total_ventas, 0, ',', '.') }}</span>
    </div>

    <div class="separator-solid"></div>

    {{-- MOVIMIENTOS DE EFECTIVO EN CAJA --}}
    <div class="bold" style="margin-bottom:3px; text-transform:uppercase; font-size:{{ $es58 ? '9px':'10px' }}">
        ARQUEO Y FLUJO DE EFECTIVO
    </div>

    <div class="total-row">
        <span>(+) Base Inicial Apertura:</span>
        <span>${{ number_format($turno->monto_apertura, 0, ',', '.') }}</span>
    </div>
    <div class="total-row">
        <span>(+) Ventas en Efectivo:</span>
        <span>${{ number_format($turno->total_ventas_efectivo, 0, ',', '.') }}</span>
    </div>
    <div class="total-row">
        <span>(+) Entradas Menores:</span>
        <span>${{ number_format($turno->total_entradas, 0, ',', '.') }}</span>
    </div>
    <div class="total-row">
        <span>(-) Salidas / Retiros Menores:</span>
        <span>-${{ number_format($turno->total_salidas, 0, ',', '.') }}</span>
    </div>

    <div class="separator"></div>

    <div class="total-row bold">
        <span>(=) Efectivo Esperado en Caja:</span>
        <span>${{ number_format($turno->monto_cierre_esperado, 0, ',', '.') }}</span>
    </div>

    @if($turno->monto_cierre_real !== null)
    <div class="total-row">
        <span>Efectivo Real Contado:</span>
        <span>${{ number_format($turno->monto_cierre_real, 0, ',', '.') }}</span>
    </div>

    <div class="total-row grand">
        <span>
            @if($turno->diferencia > 0)
                SOBRANTE DE CAJA:
            @elseif($turno->diferencia < 0)
                FALTANTE DE CAJA:
            @else
                DIFERENCIA (CUADRE):
            @endif
        </span>
        <span>
            @if($turno->diferencia > 0)
                +${{ number_format($turno->diferencia, 0, ',', '.') }}
            @elseif($turno->diferencia < 0)
                -${{ number_format(abs($turno->diferencia), 0, ',', '.') }}
            @else
                $0 (Exacto)
            @endif
        </span>
    </div>
    @endif

    @if($turno->observaciones)
    <div class="separator"></div>
    <div class="info-row" style="flex-direction:column;">
        <span class="bold">Observaciones:</span>
        <span>{{ $turno->observaciones }}</span>
    </div>
    @endif

    <div class="separator-solid"></div>

    {{-- FIRMAS --}}
    <div style="margin-top: 15px; display:flex; justify-content:space-between; text-align:center; font-size:8px;">
        <div style="width:45%;">
            <div style="border-top:1px solid #000; padding-top:2px;">Firma Cajero</div>
        </div>
        <div style="width:45%;">
            <div style="border-top:1px solid #000; padding-top:2px;">Firma Supervisor</div>
        </div>
    </div>

    <div class="pie">
        FacCol — Reporte Oficial de Arqueo y Cierre
    </div>

    <div class="corte-papel no-print">
        -- Corte de papel --
    </div>

</body>
</html>
