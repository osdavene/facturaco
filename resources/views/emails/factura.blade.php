<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura {{ $factura->numero }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f1f5f9; color: #1e293b; }
        .wrapper { max-width: 600px; margin: 0 auto; padding: 32px 16px; }
        .card { background: #ffffff; border-radius: 16px; overflow: hidden; }
        .header { background: #0f172a; padding: 32px; text-align: center; }
        .header-logo { font-size: 28px; font-weight: 800; color: #f59e0b; letter-spacing: -1px; }
        .header-sub { font-size: 13px; color: #94a3b8; margin-top: 4px; }
        .body { padding: 32px; }
        .greeting { font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 12px; }
        .text { font-size: 14px; color: #475569; line-height: 1.7; margin-bottom: 16px; }
        .mensaje-box { background: #f8fafc; border-left: 4px solid #f59e0b; border-radius: 0 8px 8px 0; padding: 16px; margin-bottom: 24px; font-size: 14px; color: #334155; font-style: italic; }
        .factura-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 24px; }
        .factura-title { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 12px; }
        .factura-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        .factura-row:last-child { border-bottom: none; }
        .factura-label { color: #64748b; }
        .factura-value { font-weight: 600; color: #0f172a; }
        .total-row { background: #0f172a; border-radius: 8px; padding: 14px 16px; display: flex; justify-content: space-between; align-items: center; margin-top: 8px; }
        .total-label { color: #94a3b8; font-size: 13px; }
        .total-value { color: #f59e0b; font-size: 20px; font-weight: 800; }
        .estado { display: inline-block; padding: 4px 12px; border-radius: 99px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
        .estado-emitida { background: #dbeafe; color: #1e40af; }
        .estado-pagada  { background: #dcfce7; color: #166534; }
        .estado-vencida { background: #fee2e2; color: #991b1b; }
        .estado-borrador{ background: #f1f5f9; color: #475569; }
        .pdf-note { background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 14px 16px; font-size: 13px; color: #92400e; margin-bottom: 24px; }
        .footer { background: #f8fafc; padding: 24px 32px; text-align: center; border-top: 1px solid #e2e8f0; }
        .footer-text { font-size: 12px; color: #94a3b8; line-height: 1.6; }
        .footer-company { font-weight: 700; color: #64748b; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">

        {{-- Header --}}
        <div class="header">
            <div class="header-logo">{{ $empresa->nombre_comercial ?? $empresa->razon_social }}</div>
            <div class="header-sub">NIT {{ $empresa->nit_formateado }} · {{ $empresa->email }}</div>
        </div>

        {{-- Cuerpo --}}
        <div class="body">

            <div class="greeting">Hola, {{ $factura->cliente_nombre }}</div>

            <p class="text">
                Adjunto encontrará la factura <strong>{{ $factura->numero }}</strong> emitida por
                <strong>{{ $empresa->razon_social }}</strong>.
            </p>

            {{-- Mensaje personalizado --}}
            @if($mensaje)
            <div class="mensaje-box">{{ $mensaje }}</div>
            @endif

            {{-- Detalle de la factura --}}
            <div class="factura-box">
                <div class="factura-title">Detalle de la Factura</div>

                <div class="factura-row">
                    <span class="factura-label">Número</span>
                    <span class="factura-value">{{ $factura->numero }}</span>
                </div>
                <div class="factura-row">
                    <span class="factura-label">Fecha de emisión</span>
                    <span class="factura-value">{{ $factura->fecha_emision->format('d/m/Y') }}</span>
                </div>
                <div class="factura-row">
                    <span class="factura-label">Fecha de vencimiento</span>
                    <span class="factura-value">{{ $factura->fecha_vencimiento->format('d/m/Y') }}</span>
                </div>
                <div class="factura-row">
                    <span class="factura-label">Forma de pago</span>
                    <span class="factura-value">{{ ucfirst($factura->forma_pago) }}</span>
                </div>
                <div class="factura-row">
                    <span class="factura-label">Estado</span>
                    <span class="estado estado-{{ $factura->estado }}">{{ ucfirst($factura->estado) }}</span>
                </div>

                <div class="total-row">
                    <span class="total-label">TOTAL A PAGAR</span>
                    <span class="total-value">${{ number_format($factura->total, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Nota PDF --}}
            <div class="pdf-note">
                <strong>📎 Archivo adjunto:</strong> El PDF de la factura está adjunto a este correo.
                Puede descargarlo, imprimirlo o guardarlo como soporte.
            </div>

            <p class="text">
                Si tiene alguna pregunta sobre esta factura, no dude en contactarnos respondiendo
                este correo o comunicándose con nosotros directamente.
            </p>

            <p class="text">Gracias por su preferencia.</p>

        </div>

        {{-- Footer --}}
        <div class="footer">
            <p class="footer-text">
                <span class="footer-company">{{ $empresa->razon_social }}</span><br>
                {{ $empresa->direccion }} · {{ $empresa->municipio }}, {{ $empresa->departamento }}<br>
                Tel: {{ $empresa->telefono }} · {{ $empresa->email }}<br><br>
                Este correo fue generado automáticamente por <strong>FacturaCO</strong>.
                Por favor no responda si no reconoce esta factura.
            </p>
        </div>

    </div>
</div>
</body>
</html>