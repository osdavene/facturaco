<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desprendible de Pago — {{ $nomina->nombre }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f1f5f9; color: #1e293b; }
        .wrapper { max-width: 600px; margin: 0 auto; padding: 32px 16px; }
        .card { background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .header { background: #0f172a; padding: 32px; text-align: center; }
        .header-logo { font-size: 26px; font-weight: 800; color: #f59e0b; letter-spacing: -1px; }
        .header-sub { font-size: 13px; color: #94a3b8; margin-top: 4px; }
        .body { padding: 32px; }
        .greeting { font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 12px; }
        .text { font-size: 14px; color: #475569; line-height: 1.7; margin-bottom: 16px; }
        .nomina-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 24px; }
        .nomina-title { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 12px; }
        .nomina-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        .nomina-row:last-child { border-bottom: none; }
        .nomina-label { color: #64748b; }
        .nomina-value { font-weight: 600; color: #0f172a; }
        .total-row { background: #0f172a; border-radius: 8px; padding: 14px 16px; display: flex; justify-content: space-between; align-items: center; margin-top: 8px; }
        .total-label { color: #94a3b8; font-size: 13px; font-weight: 600; }
        .total-value { color: #f59e0b; font-size: 20px; font-weight: 800; }
        .pdf-note { background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 10px; padding: 14px 16px; font-size: 13px; color: #065f46; margin-bottom: 24px; }
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
            <div class="header-sub">NIT {{ $empresa->nit }}-{{ $empresa->digito_verificacion }} · Comprobante de Nómina</div>
        </div>

        {{-- Body --}}
        <div class="body">
            <div class="greeting">Hola, {{ $empleado->nombres }} {{ $empleado->apellidos }} 👋</div>

            <p class="text">
                Adjunto encontrarás tu comprobante y desprendible de pago correspondiente al período
                <strong>{{ $nomina->nombre }}</strong> ({{ $nomina->periodo_inicio->format('d/m/Y') }} al {{ $nomina->periodo_fin->format('d/m/Y') }}).
            </p>

            <div class="nomina-box">
                <div class="nomina-title">Resumen de Liquidación</div>
                <div class="nomina-row">
                    <span class="nomina-label">Empleado:</span>
                    <span class="nomina-value">{{ $empleado->nombre_completo }}</span>
                </div>
                <div class="nomina-row">
                    <span class="nomina-label">Documento:</span>
                    <span class="nomina-value">{{ $empleado->tipo_documento }} {{ $empleado->numero_documento }}</span>
                </div>
                <div class="nomina-row">
                    <span class="nomina-label">Cargo:</span>
                    <span class="nomina-value">{{ $empleado->cargo ?: 'General' }}</span>
                </div>
                <div class="nomina-row">
                    <span class="nomina-label">Días Liquidados:</span>
                    <span class="nomina-value">{{ $liquidacion->dias_trabajados }} días</span>
                </div>
                <div class="nomina-row">
                    <span class="nomina-label">Total Devengado:</span>
                    <span class="nomina-value" style="color:#059669;">+${{ number_format($liquidacion->total_devengado, 0, ',', '.') }}</span>
                </div>
                <div class="nomina-row">
                    <span class="nomina-label">Total Deducciones:</span>
                    <span class="nomina-value" style="color:#dc2626;">-${{ number_format($liquidacion->total_deducciones, 0, ',', '.') }}</span>
                </div>
                <div class="total-row">
                    <span class="total-label">NETO A PAGAR:</span>
                    <span class="total-value">${{ number_format($liquidacion->neto_pagar, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="pdf-note">
                📎 <strong>Colilla adjunta:</strong> Hemos adjuntado el documento en PDF con el detalle completo de tus horas, devengados y aportes a salud y pensión.
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <p class="footer-text">
                Enviado por <span class="footer-company">{{ $empresa->razon_social }}</span> a través de <strong>FacCol</strong>.
            </p>
        </div>
    </div>
</div>
</body>
</html>
