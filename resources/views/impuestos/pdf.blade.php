<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size:10px; color:#1a1a1a; }
    .page { padding:25px; }

    .header { display:table; width:100%; border-bottom:3px solid #f59e0b;
              padding-bottom:12px; margin-bottom:16px; }
    .header-left  { display:table-cell; width:55%; vertical-align:middle; }
    .header-right { display:table-cell; width:45%; text-align:right; vertical-align:top; }

    .logo-texto     { font-size:18px; font-weight:bold; color:#f59e0b; margin-bottom:3px; }
    .empresa-nombre { font-size:11px; font-weight:bold; }
    .empresa-sub    { font-size:8px; color:#666; margin-top:2px; line-height:1.5; }
    .titulo         { font-size:16px; font-weight:bold; color:#1a1a1a; }
    .subtitulo      { font-size:10px; color:#666; margin-top:3px; }

    .kpis { display:table; width:100%; margin-bottom:14px; }
    .kpi  { display:table-cell; text-align:center; padding:10px 8px;
            background:#f8f9fa; border-radius:6px; border-top:3px solid #f59e0b; }
    .kpi + .kpi { margin-left:8px; }
    .kpi-val { font-size:13px; font-weight:bold; color:#f59e0b; }
    .kpi-lab { font-size:8px; color:#888; text-transform:uppercase; margin-top:2px; }

    .seccion-titulo { font-size:10px; font-weight:bold; text-transform:uppercase;
                      color:#f59e0b; margin:14px 0 8px 0; padding-bottom:4px;
                      border-bottom:1px solid #fde68a; }

    .retenciones { display:table; width:100%; margin-bottom:14px; }
    .ret-item    { display:table-cell; width:33%; }
    .ret-item + .ret-item { padding-left:8px; }
    .ret-box     { background:#f8f9fa; border-radius:6px; padding:10px;
                   border-left:3px solid #d97706; }
    .ret-label   { font-size:8px; text-transform:uppercase; color:#888; }
    .ret-val     { font-size:14px; font-weight:bold; color:#1a1a1a; margin-top:2px; }

    .iva-grid { display:table; width:100%; margin-bottom:14px; }
    .iva-col  { display:table-cell; vertical-align:top; }

    table { width:100%; border-collapse:collapse; margin-bottom:12px; }
    thead tr { background:#f59e0b; }
    thead th { padding:6px 8px; text-align:left; font-size:8px;
               text-transform:uppercase; color:#000; }
    tbody tr:nth-child(even) { background:#f8f9fa; }
    tbody td { padding:5px 8px; border-bottom:1px solid #eee; font-size:9px; }
    tfoot td { padding:6px 8px; font-weight:bold; background:#1a1a1a; color:#fff; font-size:10px; }

    .text-right  { text-align:right; }
    .text-center { text-align:center; }

    .aviso { background:#fffbeb; border:1px solid #fde68a; border-radius:5px;
             padding:8px 12px; margin-bottom:14px; font-size:9px; color:#92400e; }

    .resumen-box { background:#f0fdf4; border:1px solid #bbf7d0; border-radius:6px;
                   padding:12px; margin-bottom:14px; }
    .res-row     { display:table; width:100%; margin-bottom:5px; }
    .res-label   { display:table-cell; font-size:9px; color:#555; }
    .res-val     { display:table-cell; text-align:right; font-weight:bold; font-size:10px; }
    .res-total   { border-top:1px solid #86efac; padding-top:6px; margin-top:6px; }
    .res-total .res-val { font-size:14px; color:#065f46; }

    .footer { border-top:1px solid #eee; padding-top:8px; margin-top:14px;
              font-size:8px; color:#999; display:table; width:100%; }
    .footer-left  { display:table-cell; }
    .footer-right { display:table-cell; text-align:right; }
</style>
</head>
<body>
<div class="page">

    {{-- HEADER --}}
    <div class="header">
        <div class="header-left">
            @if($empresa->logo)
            <img src="{{ public_path('storage/'.$empresa->logo) }}"
                 style="max-height:45px;max-width:130px;object-fit:contain;margin-bottom:3px;">
            @else
            <div class="logo-texto">{{ $empresa->nombre_comercial ?: $empresa->razon_social }}</div>
            @endif
            <div class="empresa-nombre">{{ $empresa->razon_social }}</div>
            <div class="empresa-sub">
                NIT: {{ $empresa->nit_formateado }} ·
                {{ $empresa->regimen === 'responsable_iva' ? 'Responsable de IVA' : 'Régimen Simple' }}
                @if($empresa->municipio) · {{ $empresa->municipio }} @endif
            </div>
        </div>
        <div class="header-right">
            <div class="titulo">Resumen Tributario</div>
            <div class="subtitulo">
                @if($periodoTipo === 'bimestral')
                    {{ $bimestre }}° Bimestre {{ $anio }}
                @elseif($periodoTipo === 'mensual')
                    Período Mensual {{ $anio }}
                @else
                    Año {{ $anio }}
                @endif
            </div>
            <div class="subtitulo" style="margin-top:4px;">
                Del {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }}
                al {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}
            </div>
            <div class="subtitulo" style="color:#999;margin-top:3px;">
                Generado: {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>

    {{-- AVISO --}}
    <div class="aviso">
        <strong>Nota:</strong> Este documento es un resumen informativo para facilitar la preparación
        de la declaración. Consulte con su contador para la liquidación oficial ante la DIAN.
    </div>

    {{-- KPIs --}}
    <div class="kpis">
        <div class="kpi">
            <div class="kpi-val">{{ $resumen['num_facturas'] }}</div>
            <div class="kpi-lab">Facturas</div>
        </div>
        <div class="kpi">
            <div class="kpi-val">${{ number_format($resumen['base_gravable'],0,',','.') }}</div>
            <div class="kpi-lab">Base Gravable</div>
        </div>
        <div class="kpi">
            <div class="kpi-val">${{ number_format($resumen['total_iva'],0,',','.') }}</div>
            <div class="kpi-lab">IVA Generado</div>
        </div>
        <div class="kpi">
            <div class="kpi-val">${{ number_format($resumen['total_ventas'],0,',','.') }}</div>
            <div class="kpi-lab">Total Ventas</div>
        </div>
    </div>

    {{-- RETENCIONES --}}
    <div class="seccion-titulo">Retenciones Practicadas</div>
    <div class="retenciones">
        <div class="ret-item">
            <div class="ret-box">
                <div class="ret-label">ReteFuente</div>
                <div class="ret-val">${{ number_format($resumen['total_rete'],0,',','.') }}</div>
            </div>
        </div>
        <div class="ret-item">
            <div class="ret-box" style="border-color:#7c3aed;">
                <div class="ret-label">ReteICA</div>
                <div class="ret-val">${{ number_format($resumen['total_reteica'],0,',','.') }}</div>
            </div>
        </div>
        <div class="ret-item">
            <div class="ret-box" style="border-color:#dc2626;">
                <div class="ret-label">Total Retenciones</div>
                <div class="ret-val" style="color:#dc2626;">
                    ${{ number_format($resumen['total_rete']+$resumen['total_reteica'],0,',','.') }}
                </div>
            </div>
        </div>
    </div>

    {{-- IVA POR TASA --}}
    @if($ivaPorTasa->count())
    <div class="seccion-titulo">IVA por Tarifa</div>
    <table>
        <thead>
            <tr>
                <th>Tarifa IVA</th>
                <th class="text-right">Base Gravable</th>
                <th class="text-right">IVA Generado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ivaPorTasa as $tasa)
            <tr>
                <td style="font-weight:bold;">{{ $tasa->iva_pct }}%
                    @if($tasa->iva_pct == 0) (Excluido/Exento) @endif
                </td>
                <td class="text-right">${{ number_format($tasa->base,0,',','.') }}</td>
                <td class="text-right" style="font-weight:bold;color:#d97706;">
                    ${{ number_format($tasa->iva,0,',','.') }}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>TOTAL</td>
                <td class="text-right">${{ number_format($resumen['base_gravable'],0,',','.') }}</td>
                <td class="text-right">${{ number_format($resumen['total_iva'],0,',','.') }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

    {{-- DETALLE FACTURAS --}}
    <div class="seccion-titulo">Detalle de Facturas del Período</div>
    <table>
        <thead>
            <tr>
                <th>Factura</th>
                <th>Cliente</th>
                <th>Fecha</th>
                <th class="text-right">Base</th>
                <th class="text-right">IVA</th>
                <th class="text-right">ReteFte</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($facturas as $f)
            <tr>
                <td style="font-family:monospace;color:#d97706;">{{ $f->numero }}</td>
                <td>{{ Str::limit($f->cliente_nombre, 25) }}</td>
                <td>{{ $f->fecha_emision->format('d/m/Y') }}</td>
                <td class="text-right">${{ number_format($f->subtotal,0,',','.') }}</td>
                <td class="text-right" style="color:#d97706;">
                    ${{ number_format($f->iva,0,',','.') }}
                </td>
                <td class="text-right">
                    @if($f->retefuente > 0)
                    -${{ number_format($f->retefuente,0,',','.') }}
                    @else —
                    @endif
                </td>
                <td class="text-right" style="font-weight:bold;">
                    ${{ number_format($f->total,0,',','.') }}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">TOTALES ({{ $resumen['num_facturas'] }} facturas)</td>
                <td class="text-right">${{ number_format($resumen['base_gravable'],0,',','.') }}</td>
                <td class="text-right">${{ number_format($resumen['total_iva'],0,',','.') }}</td>
                <td class="text-right">-${{ number_format($resumen['total_rete'],0,',','.') }}</td>
                <td class="text-right">${{ number_format($resumen['total_ventas'],0,',','.') }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- RESUMEN FINAL --}}
    <div style="display:table;width:100%;margin-top:4px;">
        <div style="display:table-cell;width:60%;"></div>
        <div style="display:table-cell;width:40%;">
            <div class="resumen-box">
                <div class="seccion-titulo" style="margin-top:0;">Liquidación Estimada IVA</div>
                <div class="res-row">
                    <div class="res-label">IVA Generado (ventas)</div>
                    <div class="res-val">${{ number_format($resumen['total_iva'],0,',','.') }}</div>
                </div>
                <div class="res-row">
                    <div class="res-label">IVA Descontable (compras)*</div>
                    <div class="res-val" style="color:#dc2626;">-$0</div>
                </div>
                <div class="res-row res-total">
                    <div class="res-label" style="font-weight:bold;">IVA a Pagar Estimado</div>
                    <div class="res-val">${{ number_format($resumen['total_iva'],0,',','.') }}</div>
                </div>
                <div style="font-size:8px;color:#999;margin-top:6px;">
                    * El IVA descontable se calcula con base en las compras registradas con IVA.
                    Consulte con su contador.
                </div>
            </div>
        </div>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        <div class="footer-left">
            {{ $empresa->razon_social }} · NIT {{ $empresa->nit_formateado }}
        </div>
        <div class="footer-right">
            FacturaCO · Documento informativo · {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

</div>
</body>
</html>