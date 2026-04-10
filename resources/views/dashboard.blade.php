@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- Bienvenida --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display font-bold text-2xl">
            Bienvenido, {{ explode(' ', auth()->user()->name)[0] }} 👋
        </h1>
        <p class="text-slate-500 text-sm mt-1">
            {{ now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
        </p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('cotizaciones.create') }}"
           class="inline-flex items-center gap-2 bg-[#1a2235] border border-[#1e2d47]
                  hover:border-blue-500/50 text-slate-300 hover:text-blue-400
                  px-4 py-2 rounded-xl transition-colors text-sm">
            <i class="fas fa-file-alt"></i> Cotización
        </a>
        <a href="{{ route('facturas.create') }}"
           class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600
                  text-black font-semibold px-4 py-2 rounded-xl transition-colors text-sm">
            <i class="fas fa-plus"></i> Nueva Factura
        </a>
    </div>
</div>

{{-- ALERTAS --}}
@if($facturasVencidas > 0 || $productosStockBajo > 0 || $cotizacionesPend > 0 || $ordenesPend > 0)
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
    @if($facturasVencidas > 0)
    <a href="{{ route('facturas.index', ['estado'=>'vencida']) }}"
       class="flex items-center gap-3 bg-red-500/10 border border-red-500/30
              rounded-xl px-4 py-3 hover:bg-red-500/15 transition-colors">
        <div class="w-8 h-8 bg-red-500/20 rounded-lg flex items-center justify-center text-red-400 flex-shrink-0">
            <i class="fas fa-exclamation-triangle text-sm"></i>
        </div>
        <div>
            <div class="text-sm font-semibold text-red-400">{{ $facturasVencidas }} facturas vencidas</div>
            <div class="text-xs text-red-400/70">Requieren atención</div>
        </div>
    </a>
    @endif
    @if($productosStockBajo > 0)
    <a href="{{ route('reportes.inventario', ['filtro'=>'bajo_stock']) }}"
       class="flex items-center gap-3 bg-amber-500/10 border border-amber-500/30
              rounded-xl px-4 py-3 hover:bg-amber-500/15 transition-colors">
        <div class="w-8 h-8 bg-amber-500/20 rounded-lg flex items-center justify-center text-amber-500 flex-shrink-0">
            <i class="fas fa-box-open text-sm"></i>
        </div>
        <div>
            <div class="text-sm font-semibold text-amber-500">{{ $productosStockBajo }} con stock bajo</div>
            <div class="text-xs text-amber-500/70">Reponer inventario</div>
        </div>
    </a>
    @endif
    @if($cotizacionesPend > 0)
    <a href="{{ route('cotizaciones.index', ['estado'=>'enviada']) }}"
       class="flex items-center gap-3 bg-blue-500/10 border border-blue-500/30
              rounded-xl px-4 py-3 hover:bg-blue-500/15 transition-colors">
        <div class="w-8 h-8 bg-blue-500/20 rounded-lg flex items-center justify-center text-blue-400 flex-shrink-0">
            <i class="fas fa-file-alt text-sm"></i>
        </div>
        <div>
            <div class="text-sm font-semibold text-blue-400">{{ $cotizacionesPend }} cotizaciones</div>
            <div class="text-xs text-blue-400/70">Pendientes de respuesta</div>
        </div>
    </a>
    @endif
    @if($ordenesPend > 0)
    <a href="{{ route('ordenes.index', ['estado'=>'aprobada']) }}"
       class="flex items-center gap-3 bg-purple-500/10 border border-purple-500/30
              rounded-xl px-4 py-3 hover:bg-purple-500/15 transition-colors">
        <div class="w-8 h-8 bg-purple-500/20 rounded-lg flex items-center justify-center text-purple-400 flex-shrink-0">
            <i class="fas fa-truck text-sm"></i>
        </div>
        <div>
            <div class="text-sm font-semibold text-purple-400">{{ $ordenesPend }} órdenes por recibir</div>
            <div class="text-xs text-purple-400/70">Mercancía en camino</div>
        </div>
    </a>
    @endif
</div>
@endif

{{-- KPIs PRINCIPALES --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5">
        <div class="flex items-center justify-between mb-3">
            <div class="text-xs text-slate-500 uppercase tracking-wider">Ventas Hoy</div>
            <div class="w-9 h-9 bg-emerald-500/10 rounded-xl flex items-center justify-center text-emerald-500">
                <i class="fas fa-sun text-sm"></i>
            </div>
        </div>
        <div class="font-display font-bold text-2xl text-emerald-500">
            ${{ number_format($ventasHoy, 0, ',', '.') }}
        </div>
        <div class="text-xs text-slate-500 mt-1">{{ now()->format('d/m/Y') }}</div>
    </div>
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5">
        <div class="flex items-center justify-between mb-3">
            <div class="text-xs text-slate-500 uppercase tracking-wider">Ventas del Mes</div>
            <div class="w-9 h-9 bg-blue-500/10 rounded-xl flex items-center justify-center text-blue-400">
                <i class="fas fa-chart-line text-sm"></i>
            </div>
        </div>
        <div class="font-display font-bold text-2xl text-blue-400">
            ${{ number_format($ventasMes, 0, ',', '.') }}
        </div>
        <div class="text-xs text-slate-500 mt-1">
            {{ $facturasMes }} facturas · {{ now()->locale('es')->monthName }}
        </div>
    </div>
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5">
        <div class="flex items-center justify-between mb-3">
            <div class="text-xs text-slate-500 uppercase tracking-wider">Ventas del Año</div>
            <div class="w-9 h-9 bg-amber-500/10 rounded-xl flex items-center justify-center text-amber-500">
                <i class="fas fa-chart-bar text-sm"></i>
            </div>
        </div>
        <div class="font-display font-bold text-2xl text-amber-500">
            ${{ number_format($ventasAno, 0, ',', '.') }}
        </div>
        <div class="text-xs text-slate-500 mt-1">Acumulado {{ now()->year }}</div>
    </div>
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5">
        <div class="flex items-center justify-between mb-3">
            <div class="text-xs text-slate-500 uppercase tracking-wider">Cartera</div>
            <div class="w-9 h-9 bg-red-500/10 rounded-xl flex items-center justify-center text-red-400">
                <i class="fas fa-hand-holding-usd text-sm"></i>
            </div>
        </div>
        <div class="font-display font-bold text-2xl text-red-400">
            ${{ number_format($cartera, 0, ',', '.') }}
        </div>
        <div class="text-xs text-slate-500 mt-1">Por cobrar</div>
    </div>
</div>

{{-- FILA 1: Ventas 12 meses + Donut estados --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

    {{-- Gráfica línea — ventas 12 meses --}}
    <div class="lg:col-span-2 bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="font-display font-bold text-base">Ventas Últimos 12 Meses</h3>
                <p class="text-xs text-slate-500 mt-0.5">Tendencia mensual</p>
            </div>
            <a href="{{ route('reportes.ventas') }}"
               class="text-xs text-amber-500 hover:underline">Ver reporte →</a>
        </div>
        <div style="position:relative; height:200px;">
            <canvas id="chartVentasMeses"></canvas>
        </div>
    </div>

    {{-- Donut — estados del mes --}}
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="font-display font-bold text-base">Facturas del Mes</h3>
                <p class="text-xs text-slate-500 mt-0.5">Por estado</p>
            </div>
        </div>
        <div style="position:relative; height:160px;">
            <canvas id="chartEstados"></canvas>
        </div>
        {{-- Leyenda --}}
        <div class="mt-4 space-y-1.5">
            @foreach([
                ['emitida', '#3b82f6', 'Emitidas'],
                ['pagada',  '#10b981', 'Pagadas'],
                ['vencida', '#ef4444', 'Vencidas'],
                ['borrador','#64748b', 'Borradores'],
            ] as [$estado, $color, $label])
            @if(isset($ventasPorEstado[$estado]))
            <div class="flex items-center justify-between text-xs">
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full" style="background:{{ $color }}"></div>
                    <span class="text-slate-400">{{ $label }}</span>
                </div>
                <span class="font-semibold text-slate-300">{{ $ventasPorEstado[$estado]->cantidad }}</span>
            </div>
            @endif
            @endforeach
        </div>
    </div>

</div>

{{-- FILA 2: Barras 7 días + Top clientes --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

    {{-- Barras — ventas 7 días --}}
    <div class="lg:col-span-2 bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="font-display font-bold text-base">Ventas Últimos 7 Días</h3>
                <p class="text-xs text-slate-500 mt-0.5">Tendencia diaria</p>
            </div>
        </div>
        <div style="position:relative; height:180px;">
            <canvas id="chartVentasDias"></canvas>
        </div>
    </div>

    {{-- Top clientes --}}
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-display font-bold text-base">Top Clientes</h3>
            <span class="text-xs text-slate-500">Este mes</span>
        </div>
        @php $maxCli = $topClientes->max('total_mes') ?: 1; @endphp
        <div class="space-y-3">
            @forelse($topClientes as $i => $cli)
            <div class="flex items-center gap-2">
                <div class="w-5 h-5 rounded flex items-center justify-center text-[10px] font-black
                            {{ $i===0 ? 'bg-amber-500 text-black' : 'bg-[#1a2235] text-slate-400' }}">
                    {{ $i+1 }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-medium truncate text-slate-200">
                        {{ $cli->cliente_nombre }}
                    </div>
                    <div class="w-full bg-[#1e2d47] rounded-full h-1 mt-1">
                        <div class="h-1 rounded-full bg-amber-500 transition-all"
                             style="width:{{ ($cli->total_mes/$maxCli)*100 }}%"></div>
                    </div>
                </div>
                <div class="text-xs font-semibold text-emerald-400 flex-shrink-0">
                    ${{ number_format($cli->total_mes/1000, 1) }}k
                </div>
            </div>
            @empty
            <div class="text-center text-slate-500 text-sm py-4">Sin ventas este mes</div>
            @endforelse
        </div>
    </div>

</div>

{{-- FILA 3: Top productos + Accesos rápidos --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

    {{-- Top productos --}}
    <div class="lg:col-span-2 bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="font-display font-bold text-base">Top Productos del Mes</h3>
                <p class="text-xs text-slate-500 mt-0.5">Por valor vendido</p>
            </div>
        </div>
        @php $maxProd = $topProductos->max('total_valor') ?: 1; @endphp
        <div class="space-y-3">
            @forelse($topProductos as $i => $prod)
            <div class="flex items-center gap-3">
                <div class="w-5 h-5 rounded flex items-center justify-center text-[10px] font-black flex-shrink-0
                            {{ $i===0 ? 'bg-amber-500 text-black' : 'bg-[#1a2235] text-slate-400' }}">
                    {{ $i+1 }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-medium text-slate-200 truncate">{{ $prod->descripcion }}</span>
                        <span class="text-xs text-slate-500 ml-2 flex-shrink-0">
                            {{ number_format($prod->total_qty, 0) }} uds
                        </span>
                    </div>
                    <div class="w-full bg-[#1e2d47] rounded-full h-1.5">
                        <div class="h-1.5 rounded-full bg-blue-500"
                             style="width:{{ ($prod->total_valor/$maxProd)*100 }}%"></div>
                    </div>
                </div>
                <div class="text-xs font-semibold text-blue-400 flex-shrink-0 w-14 text-right">
                    ${{ number_format($prod->total_valor/1000, 1) }}k
                </div>
            </div>
            @empty
            <div class="text-center text-slate-500 text-sm py-4">Sin ventas este mes</div>
            @endforelse
        </div>
    </div>

    {{-- Accesos rápidos --}}
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-6">
        <h3 class="font-display font-bold text-base mb-4">Accesos Rápidos</h3>
        <div class="grid grid-cols-2 gap-2">
            @foreach([
                [route('facturas.create'),     'fa-file-invoice',      'amber',  'Nueva Factura'],
                [route('cotizaciones.create'), 'fa-file-alt',          'blue',   'Cotización'],
                [route('recibos.create'),      'fa-hand-holding-usd',  'emerald','Recibo Caja'],
                [route('ordenes.create'),      'fa-shopping-cart',     'purple', 'Orden Compra'],
                [route('clientes.create'),     'fa-user-plus',         'cyan',   'Nuevo Cliente'],
                [route('inventario.create'),   'fa-box',               'orange', 'Producto'],
                [route('reportes.index'),      'fa-chart-bar',         'slate',  'Reportes'],
                [route('empresa.index'),       'fa-cog',               'slate',  'Config'],
            ] as [$url, $icon, $color, $label])
            <a href="{{ $url }}"
               class="flex flex-col items-center gap-2 bg-[#1a2235] border border-[#1e2d47]
                      hover:border-{{ $color }}-500/50 hover:bg-[#1e2d47]
                      rounded-xl p-3 transition-colors group text-center">
                <div class="w-8 h-8 bg-{{ $color }}-500/10 rounded-lg flex items-center justify-center
                            text-{{ $color }}-{{ $color=='slate'?'400':'500' }}">
                    <i class="fas {{ $icon }} text-sm"></i>
                </div>
                <span class="text-[11px] text-slate-500 group-hover:text-slate-300 transition-colors leading-tight">
                    {{ $label }}
                </span>
            </a>
            @endforeach
        </div>
    </div>

</div>

{{-- Últimas facturas --}}
<div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl overflow-hidden">
    <div class="px-5 py-4 border-b border-[#1e2d47] flex items-center justify-between">
        <h3 class="font-display font-bold text-base">Facturas Recientes</h3>
        <a href="{{ route('facturas.index') }}"
           class="text-xs text-amber-500 hover:underline">Ver todas →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-[#1e2d47]">
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-5 py-3">Número</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Cliente</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden md:table-cell">Fecha</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Total</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-5 py-3">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ultimasFacturas as $factura)
                <tr class="border-b border-[#1e2d47]/50 hover:bg-[#1a2235]/50 transition-colors">
                    <td class="px-5 py-3">
                        <a href="{{ route('facturas.show', $factura) }}"
                           class="font-mono text-sm font-semibold text-amber-500 hover:underline">
                            {{ $factura->numero }}
                        </a>
                    </td>
                    <td class="px-3 py-3 text-sm text-slate-200">{{ $factura->cliente_nombre }}</td>
                    <td class="px-3 py-3 text-sm text-slate-400 hidden md:table-cell">
                        {{ $factura->fecha_emision->format('d/m/Y') }}
                    </td>
                    <td class="px-3 py-3 text-right text-sm font-semibold text-slate-200">
                        ${{ number_format($factura->total, 0, ',', '.') }}
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold
                                     px-2.5 py-1 rounded-full
                                     bg-{{ $factura->estado_color }}-500/10
                                     text-{{ $factura->estado_color }}-{{ $factura->estado_color=='slate'?'400':'500' }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            {{ ucfirst($factura->estado) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-10 text-center text-slate-500 text-sm">
                        No hay facturas aún —
                        <a href="{{ route('facturas.create') }}" class="text-amber-500 hover:underline">
                            crear la primera
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
Chart.defaults.color = '#64748b';
Chart.defaults.borderColor = '#1e2d47';
Chart.defaults.font.family = 'Inter, sans-serif';

// ── Datos desde PHP ───────────────────────────────────────
const meses = @json($ventasPorMes->pluck('mes'));
const totalesMeses = @json($ventasPorMes->pluck('total'));

const dias = @json($ventasSemana->pluck('dia'));
const totalesDias = @json($ventasSemana->pluck('total'));

const estadosData = {
    emitida: {{ $ventasPorEstado['emitida']->cantidad  ?? 0 }},
    pagada:  {{ $ventasPorEstado['pagada']->cantidad   ?? 0 }},
    vencida: {{ $ventasPorEstado['vencida']->cantidad  ?? 0 }},
    borrador:{{ $ventasPorEstado['borrador']->cantidad ?? 0 }},
};

// ── Formateador de pesos colombianos ─────────────────────
function formatCOP(value) {
    if (value >= 1000000) return '$' + (value/1000000).toFixed(1) + 'M';
    if (value >= 1000)    return '$' + (value/1000).toFixed(0) + 'k';
    return '$' + value.toFixed(0);
}

// ── 1. Gráfica de línea — 12 meses ───────────────────────
new Chart(document.getElementById('chartVentasMeses'), {
    type: 'line',
    data: {
        labels: meses,
        datasets: [{
            label: 'Ventas',
            data: totalesMeses,
            borderColor: '#f59e0b',
            backgroundColor: 'rgba(245,158,11,0.08)',
            borderWidth: 2.5,
            pointBackgroundColor: '#f59e0b',
            pointRadius: 4,
            pointHoverRadius: 6,
            tension: 0.4,
            fill: true,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ' ' + formatCOP(ctx.parsed.y)
                }
            }
        },
        scales: {
            x: { grid: { color: '#1e2d47' } },
            y: {
                grid: { color: '#1e2d47' },
                ticks: { callback: v => formatCOP(v) }
            }
        }
    }
});

// ── 2. Gráfica de barras — 7 días ────────────────────────
new Chart(document.getElementById('chartVentasDias'), {
    type: 'bar',
    data: {
        labels: dias,
        datasets: [{
            label: 'Ventas',
            data: totalesDias,
            backgroundColor: totalesDias.map((v, i) =>
                i === totalesDias.length - 1 ? '#f59e0b' : 'rgba(245,158,11,0.3)'
            ),
            borderColor: '#f59e0b',
            borderWidth: 1,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ' ' + formatCOP(ctx.parsed.y)
                }
            }
        },
        scales: {
            x: { grid: { display: false } },
            y: {
                grid: { color: '#1e2d47' },
                ticks: { callback: v => formatCOP(v) }
            }
        }
    }
});

// ── 3. Donut — estados ────────────────────────────────────
const totalEstados = Object.values(estadosData).reduce((a,b) => a+b, 0);

if (totalEstados > 0) {
    new Chart(document.getElementById('chartEstados'), {
        type: 'doughnut',
        data: {
            labels: ['Emitidas', 'Pagadas', 'Vencidas', 'Borradores'],
            datasets: [{
                data: [estadosData.emitida, estadosData.pagada, estadosData.vencida, estadosData.borrador],
                backgroundColor: ['#3b82f6', '#10b981', '#ef4444', '#475569'],
                borderColor: '#141c2e',
                borderWidth: 3,
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ' + ctx.label + ': ' + ctx.parsed
                    }
                }
            }
        }
    });
} else {
    document.getElementById('chartEstados').parentElement.innerHTML =
        '<div class="flex items-center justify-center h-full text-slate-600 text-sm">Sin facturas este mes</div>';
}
</script>
@endpush