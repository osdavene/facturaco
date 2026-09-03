@extends('layouts.app')
@section('title', 'Informe de Rentabilidad por Producto')
@section('page-title', 'Reportes · Rentabilidad y Márgenes')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-display font-bold text-2xl">Rentabilidad y Margen por Producto</h1>
            <p class="text-slate-500 text-sm mt-0.5">Análisis de ingresos por ventas vs costo de adquisición y margen bruto obtenido</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('reportes.rentabilidad.excel', request()->query()) }}"
               class="inline-flex items-center gap-2 bg-[#1a2235] hover:bg-[#222f48] border border-[#1e2d47]
                      hover:border-emerald-500/50 text-emerald-400 font-semibold px-4 py-2.5 rounded-xl text-sm transition-colors">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </a>
        </div>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('reportes.rentabilidad') }}" class="card p-4">
        <div class="flex flex-col sm:flex-row items-end gap-3 flex-wrap">
            <div>
                <label class="block text-xs text-slate-500 mb-1 uppercase tracking-wider">Fecha Desde</label>
                <input type="date" name="fecha_desde" value="{{ $fecha_desde }}" class="form-input text-sm">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1 uppercase tracking-wider">Fecha Hasta</label>
                <input type="date" name="fecha_hasta" value="{{ $fecha_hasta }}" class="form-input text-sm">
            </div>
            <div class="flex gap-2">
                <button type="submit"
                        class="bg-amber-500 hover:bg-amber-600 text-black font-bold text-sm px-4 py-2.5 rounded-xl transition-colors">
                    <i class="fas fa-search mr-1"></i> Consultar
                </button>
                <a href="{{ route('reportes.rentabilidad') }}"
                   class="bg-[#1a2235] border border-[#1e2d47] text-slate-400 font-semibold text-sm px-4 py-2.5 rounded-xl hover:border-slate-500 transition-colors">
                    Mes Actual
                </a>
            </div>
        </div>
    </form>

    {{-- Tarjetas Resumen --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="card p-4">
            <span class="text-xs text-slate-500 uppercase tracking-wider block">Ingresos por Ventas</span>
            <span class="font-mono font-bold text-lg text-slate-200">${{ number_format($totales['total_ingreso'], 0, ',', '.') }}</span>
        </div>
        <div class="card p-4">
            <span class="text-xs text-slate-500 uppercase tracking-wider block">Costo de Mercancía Vendida</span>
            <span class="font-mono font-bold text-lg text-slate-400">${{ number_format($totales['total_costo'], 0, ',', '.') }}</span>
        </div>
        <div class="card p-4 bg-emerald-500/5 border-emerald-500/20">
            <span class="text-xs text-emerald-400 uppercase tracking-wider block font-semibold">Utilidad Bruta (Ganancia)</span>
            <span class="font-mono font-bold text-lg text-emerald-400">${{ number_format($totales['total_utilidad'], 0, ',', '.') }}</span>
        </div>
        <div class="card p-4 bg-amber-500/5 border-amber-500/20">
            <span class="text-xs text-amber-400 uppercase tracking-wider block font-semibold">Margen Bruto Global</span>
            <span class="font-mono font-bold text-lg text-amber-400">{{ $totales['margen_global'] }}%</span>
        </div>
    </div>

    {{-- Tabla de Rentabilidad --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-[#1e2d47] flex items-center justify-between">
            <h3 class="font-bold text-slate-200 text-sm">Margen Obtenido por Producto y Servicio</h3>
            <span class="text-xs text-slate-500 font-mono">{{ count($filas) }} producto(s)</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs font-mono">
                <thead>
                    <tr class="border-b border-[#1e2d47] bg-[#141c2e] text-slate-400 uppercase tracking-wider text-[11px]">
                        <th class="px-4 py-3 text-left">Producto / Servicio</th>
                        <th class="px-4 py-3 text-left">Categoría</th>
                        <th class="px-4 py-3 text-center">Cant.</th>
                        <th class="px-4 py-3 text-right">Ingreso Venta</th>
                        <th class="px-4 py-3 text-right">Costo Compra</th>
                        <th class="px-4 py-3 text-right">Utilidad Bruta</th>
                        <th class="px-4 py-3 text-right">Margen (%)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1e2d47]">
                    @forelse($filas as $r)
                    <tr class="hover:bg-[#1a2235]/60 transition-colors">
                        <td class="px-4 py-2.5 font-sans font-medium text-slate-200">
                            {{ $r['producto'] }}
                            @if($r['codigo'] !== '—')
                            <span class="block text-[10px] text-slate-500 font-mono">Cód: {{ $r['codigo'] }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 font-sans text-slate-400">{{ $r['categoria'] }}</td>
                        <td class="px-4 py-2.5 text-center font-bold text-slate-300">{{ number_format($r['cantidad'], 0) }}</td>
                        <td class="px-4 py-2.5 text-right text-slate-200">${{ number_format($r['ingreso'], 0, ',', '.') }}</td>
                        <td class="px-4 py-2.5 text-right text-slate-400">${{ number_format($r['costo'], 0, ',', '.') }}</td>
                        <td class="px-4 py-2.5 text-right font-bold text-emerald-400">${{ number_format($r['utilidad'], 0, ',', '.') }}</td>
                        <td class="px-4 py-2.5 text-right font-bold {{ $r['margen_pct'] >= 30 ? 'text-emerald-400' : ($r['margen_pct'] >= 15 ? 'text-amber-400' : 'text-red-400') }}">
                            {{ $r['margen_pct'] }}%
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500 font-sans text-xs">
                            No hay ventas registradas en el rango de fechas seleccionado
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
