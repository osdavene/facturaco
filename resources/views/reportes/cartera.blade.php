@extends('layouts.app')
@section('title', 'Reporte de Cartera')
@section('page-title', 'Reportes · Cartera')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-display font-bold text-2xl">Reporte de Cartera</h1>
        <p class="text-slate-500 text-sm mt-1">Estado de cuentas por cobrar</p>
    </div>
    <div class="flex gap-2 flex-wrap">
        <a href="{{ route('reportes.cartera.pdf') }}"
           target="_blank"
           class="inline-flex items-center gap-2 bg-red-500/10 border border-red-500/30
                  text-red-400 hover:bg-red-500/20 px-4 py-2.5 rounded-xl transition-colors text-sm">
            <i class="fas fa-file-pdf"></i> PDF
        </a>
        <a href="{{ route('reportes.cartera.excel') }}"
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
<div class="card p-4 mb-5">
    <div class="flex gap-3 flex-wrap">
        @foreach(['pendiente'=>'Pendientes','vencida'=>'Vencidas','pagada'=>'Pagadas'] as $val=>$label)
        <a href="{{ route('reportes.cartera', ['estado'=>$val]) }}"
           class="px-4 py-2 rounded-xl text-sm font-medium transition-colors
                  {{ $estado==$val
                     ? 'bg-amber-500 text-black'
                     : 'bg-[#1a2235] border border-[#1e2d47] text-slate-400 hover:text-slate-200' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>
</div>

{{-- KPIs --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
    <div class="card p-5">
        <div class="text-xs text-slate-500 uppercase tracking-wider mb-2">Facturas</div>
        <div class="font-display font-bold text-xl">{{ $totales['count'] }}</div>
    </div>
    <div class="card p-5">
        <div class="text-xs text-slate-500 uppercase tracking-wider mb-2">Total Facturado</div>
        <div class="font-display font-bold text-xl" style="color:#e2e8f0">
            ${{ number_format($totales['total'], 0, ',', '.') }}
        </div>
    </div>
    <div class="card p-5">
        <div class="text-xs text-slate-500 uppercase tracking-wider mb-2">Total Pagado</div>
        <div class="font-display font-bold text-xl text-emerald-500">
            ${{ number_format($totales['pagado'], 0, ',', '.') }}
        </div>
    </div>
    <div class="card p-5">
        <div class="text-xs text-slate-500 uppercase tracking-wider mb-2">Saldo Pendiente</div>
        <div class="font-display font-bold text-xl text-amber-500">
            ${{ number_format($totales['pendiente'], 0, ',', '.') }}
        </div>
    </div>
</div>

{{-- Tabla --}}
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-[#1e2d47]">
                    <th class="table-th">Factura</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Cliente</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden md:table-cell">Emisión</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Vencimiento</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Total</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden sm:table-cell">Pagado</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-5 py-3">Saldo</th>
                </tr>
            </thead>
            <tbody>
                @forelse($facturas as $factura)
                @php $saldo = max(0, $factura->total - $factura->total_pagado); @endphp
                <tr class="table-row">
                    <td class="px-5 py-3">
                        <a href="{{ route('facturas.show', $factura) }}"
                           class="text-amber-500 hover:underline font-mono text-sm font-semibold">
                            {{ $factura->numero }}
                        </a>
                        <div class="text-xs mt-0.5">
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold
                                {{ $factura->estado=='vencida'
                                   ? 'bg-red-500/10 text-red-400'
                                   : 'bg-blue-500/10 text-blue-400' }}">
                                {{ ucfirst($factura->estado) }}
                            </span>
                        </div>
                    </td>
                    <td class="px-3 py-3 text-sm" style="color:#e2e8f0">{{ $factura->cliente_nombre }}</td>
                    <td class="px-3 py-3 text-sm text-slate-400 hidden md:table-cell">
                        {{ $factura->fecha_emision->format('d/m/Y') }}
                    </td>
                    <td class="px-3 py-3 text-sm
                        {{ $factura->fecha_vencimiento < now() ? 'text-red-400 font-semibold' : 'text-slate-400' }}">
                        {{ $factura->fecha_vencimiento->format('d/m/Y') }}
                        @if($factura->fecha_vencimiento < now() && $factura->estado != 'pagada')
                        <div class="text-[10px] text-red-400">
                            {{ now()->diffInDays($factura->fecha_vencimiento) }} días vencida
                        </div>
                        @endif
                    </td>
                    <td class="px-3 py-3 text-right text-sm font-semibold" style="color:#e2e8f0">
                        ${{ number_format($factura->total, 0, ',', '.') }}
                    </td>
                    <td class="px-3 py-3 text-right text-sm text-emerald-500 hidden sm:table-cell">
                        ${{ number_format($factura->total_pagado, 0, ',', '.') }}
                    </td>
                    <td class="px-5 py-3 text-right text-sm font-bold
                        {{ $saldo > 0 ? 'text-amber-500' : 'text-emerald-500' }}">
                        ${{ number_format($saldo, 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-12 text-center text-slate-500">
                        No hay facturas con el filtro seleccionado
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($facturas->count() > 0)
            <tfoot>
                <tr class="border-t-2 border-[#1e2d47] bg-[#1a2235]">
                    <td colspan="4" class="px-5 py-3 text-sm font-semibold text-slate-300">TOTALES</td>
                    <td class="px-3 py-3 text-right text-sm font-semibold" style="color:#e2e8f0">
                        ${{ number_format($totales['total'], 0, ',', '.') }}
                    </td>
                    <td class="px-3 py-3 text-right text-sm font-semibold text-emerald-500 hidden sm:table-cell">
                        ${{ number_format($totales['pagado'], 0, ',', '.') }}
                    </td>
                    <td class="px-5 py-3 text-right text-sm font-bold text-amber-500">
                        ${{ number_format($totales['pendiente'], 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@endsection