<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size:10px; color:#1a1a1a; }
    .page { padding:25px; }
    .header { border-bottom:3px solid #f59e0b; padding-bottom:12px; margin-bottom:16px; display:table; width:100%; }
    .header-left { display:table-cell; vertical-align:top; }
    .header-right { display:table-cell; text-align:right; vertical-align:top; }
    .titulo { font-size:18px; font-weight:bold; color:#f59e0b; }
    .empresa { font-size:11px; font-weight:bold; margin-top:4px; }
    .sub { font-size:9px; color:#666; }
    .kpis { display:table; width:100%; margin-bottom:16px; }
    .kpi  { display:table-cell; text-align:center; padding:10px;
            background:#f8f9fa; border-radius:6px; margin-right:8px; }
    .kpi-val { font-size:14px; font-weight:bold; color:#f59e0b; }
    .kpi-lab { font-size:8px; color:#888; text-transform:uppercase; margin-top:2px; }
    table { width:100%; border-collapse:collapse; }
    thead tr { background:#f59e0b; }
    thead th { padding:7px 8px; text-align:left; font-size:9px; text-transform:uppercase; color:#000; }
    tbody tr:nth-child(even) { background:#f8f9fa; }
    tbody td { padding:6px 8px; border-bottom:1px solid #eee; font-size:10px; }
    tfoot td { padding:7px 8px; font-weight:bold; background:#1a1a1a; color:#fff; }
    .text-right { text-align:right; }
    .vencida { color:#dc2626; font-weight:bold; }
    .footer { border-top:1px solid #eee; padding-top:8px; margin-top:12px; font-size:8px; color:#999; text-align:center; }
</style>
</head>
<body>
<div class="page">
    <div class="header">
        <div class="header-left">
            <div class="titulo">Reporte de Cartera</div>
            <div class="empresa">{{ $empresa->razon_social }}</div>
            <div class="sub">NIT: {{ $empresa->nit_formateado }}</div>
        </div>
        <div class="header-right">
            <div class="sub">Generado: {{ now()->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    <div class="kpis">
        <div class="kpi" style="margin-right:8px;">
            <div class="kpi-val">{{ $facturas->count() }}</div>
            <div class="kpi-lab">Facturas</div>
        </div>
        <div class="kpi" style="margin-right:8px;">
            <div class="kpi-val">${{ number_format($totales['total'],0,',','.') }}</div>
            <div class="kpi-lab">Total</div>
        </div>
        <div class="kpi">
            <div class="kpi-val">${{ number_format($totales['pendiente'],0,',','.') }}</div>
            <div class="kpi-lab">Por Cobrar</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Factura</th>
                <th>Cliente</th>
                <th>Emisión</th>
                <th>Vencimiento</th>
                <th class="text-right">Total</th>
                <th class="text-right">Pagado</th>
                <th class="text-right">Saldo</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($facturas as $f)
            @php $saldo = max(0, $f->total - $f->total_pagado); @endphp
            <tr>
                <td style="font-family:monospace;color:#f59e0b;">{{ $f->numero }}</td>
                <td>{{ $f->cliente_nombre }}</td>
                <td>{{ $f->fecha_emision->format('d/m/Y') }}</td>
                <td class="{{ $f->fecha_vencimiento < now() ? 'vencida' : '' }}">
                    {{ $f->fecha_vencimiento->format('d/m/Y') }}
                </td>
                <td class="text-right">${{ number_format($f->total,0,',','.') }}</td>
                <td class="text-right">${{ number_format($f->total_pagado,0,',','.') }}</td>
                <td class="text-right" style="font-weight:bold;color:{{ $saldo>0 ? '#d97706':'#065f46' }};">
                    ${{ number_format($saldo,0,',','.') }}
                </td>
                <td>{{ ucfirst($f->estado) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4">TOTALES</td>
                <td class="text-right">${{ number_format($totales['total'],0,',','.') }}</td>
                <td class="text-right">—</td>
                <td class="text-right">${{ number_format($totales['pendiente'],0,',','.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">{{ $empresa->razon_social }} · FacturaCO · {{ now()->format('d/m/Y H:i') }}</div>
</div>
</body>
</html>