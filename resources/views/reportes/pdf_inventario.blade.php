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
    table { width:100%; border-collapse:collapse; }
    thead tr { background:#f59e0b; }
    thead th { padding:7px 8px; text-align:left; font-size:9px; text-transform:uppercase; color:#000; }
    tbody tr:nth-child(even) { background:#f8f9fa; }
    tbody td { padding:6px 8px; border-bottom:1px solid #eee; font-size:10px; }
    .text-right { text-align:right; }
    .badge-ok   { color:#065f46; background:#d1fae5; padding:1px 6px; border-radius:8px; font-size:8px; }
    .badge-low  { color:#92400e; background:#fef3c7; padding:1px 6px; border-radius:8px; font-size:8px; }
    .badge-none { color:#991b1b; background:#fee2e2; padding:1px 6px; border-radius:8px; font-size:8px; }
    .footer { border-top:1px solid #eee; padding-top:8px; margin-top:12px; font-size:8px; color:#999; text-align:center; }
    .resumen { background:#f8f9fa; padding:10px; border-radius:6px; margin-bottom:14px;
               display:table; width:100%; }
    .res-item { display:table-cell; text-align:center; }
    .res-val { font-size:14px; font-weight:bold; color:#f59e0b; }
    .res-lab { font-size:8px; color:#888; }
</style>
</head>
<body>
<div class="page">
    <div class="header">
        <div class="header-left">
            <div class="titulo">Reporte de Inventario</div>
            <div class="empresa">{{ $empresa->razon_social }}</div>
            <div class="sub">NIT: {{ $empresa->nit_formateado }}</div>
        </div>
        <div class="header-right">
            <div class="sub">Generado: {{ now()->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    <div class="resumen">
        <div class="res-item">
            <div class="res-val">{{ $productos->count() }}</div>
            <div class="res-lab">Total Productos</div>
        </div>
        <div class="res-item">
            <div class="res-val">{{ $productos->where('es_servicio',false)->filter(fn($p)=>$p->bajo_stock)->count() }}</div>
            <div class="res-lab">Bajo Stock</div>
        </div>
        <div class="res-item">
            <div class="res-val">{{ $productos->where('es_servicio',false)->where('stock_actual',0)->count() }}</div>
            <div class="res-lab">Sin Stock</div>
        </div>
        <div class="res-item">
            <div class="res-val">${{ number_format($valorInventario,0,',','.') }}</div>
            <div class="res-lab">Valor Total</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Producto</th>
                <th>Categoría</th>
                <th class="text-right">Stock</th>
                <th class="text-right">Mínimo</th>
                <th class="text-right">P. Venta</th>
                <th class="text-right">Valor Stock</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productos as $p)
            <tr>
                <td style="font-family:monospace;font-size:9px;">{{ $p->codigo }}</td>
                <td>{{ $p->nombre }}</td>
                <td>{{ $p->categoria->nombre ?? '—' }}</td>
                <td class="text-right" style="font-weight:bold;">
                    {{ $p->es_servicio ? '—' : number_format($p->stock_actual,0) }}
                </td>
                <td class="text-right">
                    {{ $p->es_servicio ? '—' : number_format($p->stock_minimo,0) }}
                </td>
                <td class="text-right">${{ number_format($p->precio_venta,0,',','.') }}</td>
                <td class="text-right">
                    @if(!$p->es_servicio)
                    ${{ number_format($p->stock_actual * $p->precio_compra,0,',','.') }}
                    @else —
                    @endif
                </td>
                <td>
                    @if($p->es_servicio) <span class="badge-ok">Servicio</span>
                    @elseif($p->stock_actual==0) <span class="badge-none">Sin stock</span>
                    @elseif($p->bajo_stock) <span class="badge-low">Bajo stock</span>
                    @else <span class="badge-ok">OK</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">{{ $empresa->razon_social }} · FacturaCO · {{ now()->format('d/m/Y H:i') }}</div>
</div>
</body>
</html>