<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: Arial, sans-serif; font-size: 11px; color: #1e293b; }
  .header { background: #0f172a; color: white; padding: 20px 24px; margin-bottom: 16px; }
  .header-top { display: flex; justify-content: space-between; align-items: flex-start; }
  .empresa-nombre { font-size: 20px; font-weight: 800; color: #8b5cf6; }
  .nota-numero { font-size: 18px; font-weight: 800; color: #8b5cf6; text-align: right; }
  .nota-label { font-size: 10px; color: #94a3b8; text-align: right; }
  .empresa-info { font-size: 10px; color: #94a3b8; margin-top: 4px; }
  .info-grid { display: flex; gap: 16px; margin-bottom: 16px; }
  .info-box { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px; }
  .info-label { font-size: 9px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; }
  .info-value { font-size: 11px; color: #1e293b; font-weight: 600; }
  .info-sub { font-size: 10px; color: #64748b; margin-top: 2px; }
  .alerta { background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; padding: 8px 12px; margin-bottom: 12px; color: #991b1b; font-size: 10px; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
  th { background: #0f172a; color: white; padding: 7px 10px; text-align: left; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
  td { padding: 7px 10px; border-bottom: 1px solid #f1f5f9; font-size: 11px; }
  tr:nth-child(even) td { background: #f8fafc; }
  .text-right { text-align: right; }
  .text-center { text-align: center; }
  .totales { float: right; width: 220px; }
  .totales table { margin-bottom: 0; }
  .totales td { padding: 4px 8px; border: none; }
  .total-final { background: #0f172a; }
  .total-final td { color: white; font-weight: 800; font-size: 13px; padding: 8px; }
  .total-label-final { color: #94a3b8; }
  .total-valor-final { color: #8b5cf6; text-align: right; }
  .footer { margin-top: 24px; padding-top: 12px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 9px; color: #94a3b8; }
</style>
</head>
<body>

<div class="header">
    <div class="header-top">
        <div>
            <div class="empresa-nombre">{{ $empresa->nombre_comercial ?? $empresa->razon_social }}</div>
            <div class="empresa-info">NIT {{ $empresa->nit_formateado }} · {{ $empresa->email }}</div>
            <div class="empresa-info">{{ $empresa->direccion }} · {{ $empresa->municipio }}, {{ $empresa->departamento }}</div>
        </div>
        <div>
            <div class="nota-label">NOTA DE CRÉDITO</div>
            <div class="nota-numero">{{ $nota->numero }}</div>
            <div class="nota-label">Fecha: {{ $nota->fecha->format('d/m/Y') }}</div>
        </div>
    </div>
</div>

<div class="info-grid">
    <div class="info-box">
        <div class="info-label">Cliente</div>
        <div class="info-value">{{ $nota->cliente_nombre }}</div>
        <div class="info-sub">{{ $nota->cliente_documento }}</div>
    </div>
    <div class="info-box">
        <div class="info-label">Factura Origen</div>
        <div class="info-value">{{ $nota->factura_numero }}</div>
        <div class="info-sub">{{ $nota->motivo_texto }}</div>
    </div>
    <div class="info-box">
        <div class="info-label">Tipo</div>
        <div class="info-value">{{ ucfirst($nota->tipo) }}</div>
        <div class="info-sub">Nota de crédito {{ $nota->tipo === 'total' ? '— Anula factura' : 'parcial' }}</div>
    </div>
</div>

@if($nota->tipo === 'total')
<div class="alerta">
    ⚠ Esta nota de crédito ANULA TOTALMENTE la factura {{ $nota->factura_numero }}.
</div>
@endif

<table>
    <thead>
        <tr>
            <th>Descripción</th>
            <th class="text-center">Cantidad</th>
            <th class="text-right">Precio Unit.</th>
            <th class="text-center">IVA %</th>
            <th class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($nota->items as $item)
        <tr>
            <td>
                <strong>{{ $item->descripcion }}</strong><br>
                <small style="color:#94a3b8">{{ $item->codigo }}</small>
            </td>
            <td class="text-center">{{ format_cantidad($item->cantidad) }} {{ $item->unidad }}</td>
            <td class="text-right">${{ number_format($item->precio_unitario, 0, ',', '.') }}</td>
            <td class="text-center">{{ $item->iva_pct }}%</td>
            <td class="text-right">${{ number_format($item->total, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="totales">
    <table>
        <tr><td>Subtotal</td><td class="text-right">${{ number_format($nota->subtotal, 0, ',', '.') }}</td></tr>
        <tr><td>IVA</td><td class="text-right">${{ number_format($nota->iva, 0, ',', '.') }}</td></tr>
        <tr class="total-final">
            <td class="total-label-final">TOTAL NC</td>
            <td class="total-valor-final">${{ number_format($nota->total, 0, ',', '.') }}</td>
        </tr>
    </table>
</div>

@if($nota->observaciones)
<div style="clear:both; margin-top:16px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:10px 12px;">
    <div style="font-size:9px; font-weight:700; color:#94a3b8; text-transform:uppercase; margin-bottom:4px;">Observaciones</div>
    <div style="font-size:11px; color:#334155;">{{ $nota->observaciones }}</div>
</div>
@endif

<div class="footer">
    Nota de Crédito generada por FacturaCO · {{ $empresa->razon_social }} · {{ now()->format('d/m/Y H:i') }}
</div>

</body>
</html>