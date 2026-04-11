@extends('layouts.app')
@section('title', 'Reporte de Ventas')
@section('page-title', 'Reportes · Ventas')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-display font-bold text-2xl">Reporte de Ventas</h1>
        <p class="text-slate-500 text-sm mt-1">
            Del {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }}
            al {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}
        </p>
    </div>
    <div class="flex gap-2 flex-wrap">
        <a href="{{ route('reportes.ventas.pdf', request()->query()) }}"
           target="_blank"
           class="inline-flex items-center gap-2 bg-red-500/10 border border-red-500/30
                  text-red-400 hover:bg-red-500/20 px-4 py-2.5 rounded-xl transition-colors text-sm">
            <i class="fas fa-file-pdf"></i> PDF
        </a>
        <a href="{{ route('reportes.ventas.excel', request()->query()) }}"
           class="inline-flex items-center gap-2 bg-emerald-500/10 border border-emerald-500/30
                  text-emerald-400 hover:bg-emerald-500/20 px-4 py-2.5 rounded-xl transition-colors text-sm">
            <i class="fas fa-file-excel"></i> Excel
        </a>
        <a href="{{ route('reportes.index') }}"
           class="inline-flex items-center gap-2 bg-[#1a2235] border border-[#1e2d47]
                  text-slate-400 hover:text-slate-200 px-4 py-2.5 rounded-xl transition-colors text-sm">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>

{{-- Filtros --}}
<form method="GET" action="{{ route('reportes.ventas') }}"
      class="card p-4 mb-5">
    <div class="flex flex-col sm:flex-row gap-3">
        <div>
            <label class="block text-xs text-slate-500 mb-1">Desde</label>
            <input type="date" name="fecha_desde" value="{{ $fechaDesde }}"
                   class="form-input"
                   style="color:#e2e8f0">
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">Hasta</label>
            <input type="date" name="fecha_hasta" value="{{ $fechaHasta }}"
                   class="form-input"
                   style="color:#e2e8f0">
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">Estado</label>
            <select name="estado"
                    class="form-input"
                    style="color:#e2e8f0">
                <option value="">Todos</option>
                @foreach(['emitida','pagada','vencida'] as $e)
                <option value="{{ $e }}" {{ $estado==$e ? 'selected':'' }}>{{ ucfirst($e) }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit"
                    class="bg-amber-500 hover:bg-amber-600 text-black font-semibold
                           px-5 py-2.5 rounded-xl transition-colors">
                <i class="fas fa-filter mr-1"></i> Filtrar
            </button>
        </div>
    </div>
</form>

{{-- KPIs --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
    @foreach([
        ['Total Facturas', $totales['count'],    'fa-file-invoice', 'blue',    ''],
        ['Subtotal',       $totales['subtotal'], 'fa-dollar-sign',  'slate',   '$'],
        ['IVA Generado',   $totales['iva'],      'fa-percent',      'purple',  '$'],
        ['Total Ventas',   $totales['total'],    'fa-chart-line',   'emerald', '$'],
    ] as [$label, $valor, $icon, $color, $prefix])
    <div class="card p-5">
        <div class="flex items-center justify-between mb-2">
            <div class="text-xs text-slate-500 uppercase tracking-wider">{{ $label }}</div>
            <div class="w-8 h-8 bg-{{ $color }}-500/10 rounded-lg flex items-center
                        justify-center text-{{ $color }}-{{ $color=='slate'?'400':'500' }}">
                <i class="fas {{ $icon }} text-sm"></i>
            </div>
        </div>
        <div class="font-display font-bold text-xl">
            {{ $prefix }}{{ number_format($valor, 0, ',', '.') }}
        </div>
    </div>
    @endforeach
</div>

{{-- Tabla --}}
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-[#1e2d47]">
                    <th class="table-th">Factura</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Cliente</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden md:table-cell">Fecha</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden sm:table-cell">Subtotal</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden sm:table-cell">IVA</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Total</th>
                    <th class="table-th">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($facturas as $factura)
                <tr class="table-row">
                    <td class="px-5 py-3">
                        <a href="{{ route('facturas.show', $factura) }}"
                           class="text-amber-500 hover:underline font-mono text-sm font-semibold">
                            {{ $factura->numero }}
                        </a>
                    </td>
                    <td class="px-3 py-3 text-sm" style="color:#e2e8f0">{{ $factura->cliente_nombre }}</td>
                    <td class="px-3 py-3 text-sm text-slate-400 hidden md:table-cell">
                        {{ $factura->fecha_emision->format('d/m/Y') }}
                    </td>
                    <td class="px-3 py-3 text-right text-sm text-slate-400 hidden sm:table-cell">
                        ${{ number_format($factura->subtotal, 0, ',', '.') }}
                    </td>
                    <td class="px-3 py-3 text-right text-sm text-blue-400 hidden sm:table-cell">
                        ${{ number_format($factura->iva, 0, ',', '.') }}
                    </td>
                    <td class="px-3 py-3 text-right text-sm font-semibold" style="color:#e2e8f0">
                        ${{ number_format($factura->total, 0, ',', '.') }}
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full
                            bg-{{ $factura->estado_color }}-500/10
                            text-{{ $factura->estado_color }}-{{ $factura->estado_color=='slate'?'400':'500' }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            {{ ucfirst($factura->estado) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-12 text-center text-slate-500">
                        No hay facturas en el período seleccionado
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($facturas->count() > 0)
            <tfoot>
                <tr class="border-t-2 border-[#1e2d47] bg-[#1a2235]">
                    <td colspan="3" class="px-5 py-3 text-sm font-semibold text-slate-300">
                        TOTALES ({{ $totales['count'] }} facturas)
                    </td>
                    <td class="px-3 py-3 text-right text-sm font-semibold text-slate-300 hidden sm:table-cell">
                        ${{ number_format($totales['subtotal'], 0, ',', '.') }}
                    </td>
                    <td class="px-3 py-3 text-right text-sm font-semibold text-blue-400 hidden sm:table-cell">
                        ${{ number_format($totales['iva'], 0, ',', '.') }}
                    </td>
                    <td class="px-3 py-3 text-right text-sm font-bold text-amber-500">
                        ${{ number_format($totales['total'], 0, ',', '.') }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@endsection