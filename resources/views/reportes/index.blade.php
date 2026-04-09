@extends('layouts.app')
@section('title', 'Reportes')
@section('page-title', 'Reportes y Estadísticas')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-display font-bold text-2xl">Reportes</h1>
        <p class="text-slate-500 text-sm mt-1">Análisis y estadísticas del negocio</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('reportes.ventas') }}"
           class="inline-flex items-center gap-2 bg-[#1a2235] border border-[#1e2d47]
                  hover:border-amber-500 text-slate-300 hover:text-amber-500
                  px-4 py-2.5 rounded-xl transition-colors text-sm">
            <i class="fas fa-chart-line"></i> Ventas
        </a>
        <a href="{{ route('reportes.inventario') }}"
           class="inline-flex items-center gap-2 bg-[#1a2235] border border-[#1e2d47]
                  hover:border-amber-500 text-slate-300 hover:text-amber-500
                  px-4 py-2.5 rounded-xl transition-colors text-sm">
            <i class="fas fa-boxes"></i> Inventario
        </a>
        <a href="{{ route('reportes.cartera') }}"
           class="inline-flex items-center gap-2 bg-[#1a2235] border border-[#1e2d47]
                  hover:border-amber-500 text-slate-300 hover:text-amber-500
                  px-4 py-2.5 rounded-xl transition-colors text-sm">
            <i class="fas fa-hand-holding-usd"></i> Cartera
        </a>
    </div>
</div>

{{-- KPIs --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5">
        <div class="flex items-center justify-between mb-3">
            <div class="text-xs text-slate-500 uppercase tracking-wider">Ventas del Mes</div>
            <div class="w-8 h-8 bg-emerald-500/10 rounded-lg flex items-center justify-center text-emerald-500">
                <i class="fas fa-dollar-sign text-sm"></i>
            </div>
        </div>
        <div class="font-display font-bold text-xl text-emerald-500">
            ${{ number_format($ventasMes, 0, ',', '.') }}
        </div>
        <div class="text-xs text-slate-500 mt-1">{{ now()->locale('es')->monthName }}</div>
    </div>

    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5">
        <div class="flex items-center justify-between mb-3">
            <div class="text-xs text-slate-500 uppercase tracking-wider">Ventas del Año</div>
            <div class="w-8 h-8 bg-blue-500/10 rounded-lg flex items-center justify-center text-blue-400">
                <i class="fas fa-chart-line text-sm"></i>
            </div>
        </div>
        <div class="font-display font-bold text-xl text-blue-400">
            ${{ number_format($ventasAnio, 0, ',', '.') }}
        </div>
        <div class="text-xs text-slate-500 mt-1">{{ now()->year }}</div>
    </div>

    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5">
        <div class="flex items-center justify-between mb-3">
            <div class="text-xs text-slate-500 uppercase tracking-wider">Cartera</div>
            <div class="w-8 h-8 bg-amber-500/10 rounded-lg flex items-center justify-center text-amber-500">
                <i class="fas fa-clock text-sm"></i>
            </div>
        </div>
        <div class="font-display font-bold text-xl text-amber-500">
            ${{ number_format($carteraPendiente, 0, ',', '.') }}
        </div>
        <div class="text-xs text-slate-500 mt-1">Por cobrar</div>
    </div>

    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5">
        <div class="flex items-center justify-between mb-3">
            <div class="text-xs text-slate-500 uppercase tracking-wider">Facturas Mes</div>
            <div class="w-8 h-8 bg-purple-500/10 rounded-lg flex items-center justify-center text-purple-400">
                <i class="fas fa-file-invoice text-sm"></i>
            </div>
        </div>
        <div class="font-display font-bold text-xl text-purple-400">{{ $facturasMes }}</div>
        <div class="text-xs text-slate-500 mt-1">Este mes</div>
    </div>

    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5">
        <div class="flex items-center justify-between mb-3">
            <div class="text-xs text-slate-500 uppercase tracking-wider">Stock Bajo</div>
            <div class="w-8 h-8 bg-red-500/10 rounded-lg flex items-center justify-center text-red-400">
                <i class="fas fa-exclamation-triangle text-sm"></i>
            </div>
        </div>
        <div class="font-display font-bold text-xl text-red-400">{{ $productosStock }}</div>
        <div class="text-xs text-slate-500 mt-1">Productos</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">

    {{-- Ventas por mes --}}
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-display font-bold text-base">Ventas Últimos 6 Meses</h3>
            <a href="{{ route('reportes.ventas') }}"
               class="text-xs text-amber-500 hover:underline">Ver detalle →</a>
        </div>
        @php $maxVenta = $ventasPorMes->max('total') ?: 1; @endphp
        <div class="space-y-3">
            @foreach($ventasPorMes as $v)
            <div class="flex items-center gap-3">
                <div class="text-xs text-slate-500 w-20 capitalize">{{ $v['mes'] }}</div>
                <div class="flex-1 bg-[#1e2d47] rounded-full h-2 overflow-hidden">
                    <div class="h-2 rounded-full bg-amber-500 transition-all"
                         style="width: {{ $maxVenta > 0 ? ($v['total']/$maxVenta)*100 : 0 }}%"></div>
                </div>
                <div class="text-xs font-semibold text-slate-300 w-24 text-right">
                    ${{ number_format($v['total'], 0, ',', '.') }}
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Top clientes --}}
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-display font-bold text-base">Top 5 Clientes</h3>
        </div>
        @php $maxCliente = $topClientes->max('total_compras') ?: 1; @endphp
        <div class="space-y-3">
            @forelse($topClientes as $i => $cliente)
            <div class="flex items-center gap-3">
                <div class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-bold
                            {{ $i === 0 ? 'bg-amber-500 text-black' : 'bg-[#1a2235] text-slate-400' }}">
                    {{ $i + 1 }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium truncate">{{ $cliente->cliente_nombre }}</div>
                    <div class="text-xs text-slate-500">{{ $cliente->num_facturas }} facturas</div>
                </div>
                <div class="text-xs font-semibold text-emerald-500 text-right">
                    ${{ number_format($cliente->total_compras, 0, ',', '.') }}
                </div>
            </div>
            @empty
            <div class="text-center text-slate-500 text-sm py-4">Sin datos</div>
            @endforelse
        </div>
    </div>

</div>

{{-- Top productos --}}
<div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl overflow-hidden">
    <div class="px-5 py-4 border-b border-[#1e2d47] flex items-center justify-between">
        <h3 class="font-display font-bold text-base">Top 5 Productos Más Vendidos</h3>
        <a href="{{ route('reportes.inventario') }}"
           class="text-xs text-amber-500 hover:underline">Ver inventario →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-[#1e2d47]">
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-5 py-3">#</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Producto</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Cantidad</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-5 py-3">Total Ventas</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topProductos as $i => $prod)
                <tr class="border-b border-[#1e2d47]/50 hover:bg-[#1a2235]/50 transition-colors">
                    <td class="px-5 py-3">
                        <span class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-bold
                                     {{ $i === 0 ? 'bg-amber-500 text-black' : 'bg-[#1a2235] text-slate-400' }}
                                     inline-flex">
                            {{ $i + 1 }}
                        </span>
                    </td>
                    <td class="px-3 py-3 text-sm font-medium">{{ $prod->descripcion }}</td>
                    <td class="px-3 py-3 text-right text-sm">
                        {{ number_format($prod->total_cantidad, 0) }}
                    </td>
                    <td class="px-5 py-3 text-right text-sm font-semibold text-emerald-500">
                        ${{ number_format($prod->total_ventas, 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-5 py-10 text-center text-slate-500">Sin datos</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection