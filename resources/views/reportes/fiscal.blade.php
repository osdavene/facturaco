@extends('layouts.app')
@section('title', 'Informe Fiscal y Tributario')
@section('page-title', 'Reportes · Informe Fiscal DIAN')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-display font-bold text-2xl">Informe Fiscal y Tributario (DIAN)</h1>
            <p class="text-slate-500 text-sm mt-0.5">Bases gravables, discriminación de IVA (19%, 5%, 0%) y Retenciones practicadas para declaraciones</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('reportes.fiscal.excel', request()->query()) }}"
               class="inline-flex items-center gap-2 bg-[#1a2235] hover:bg-[#222f48] border border-[#1e2d47]
                      hover:border-emerald-500/50 text-emerald-400 font-semibold px-4 py-2.5 rounded-xl text-sm transition-colors">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </a>
        </div>
    </div>

    {{-- Filtros de Fecha --}}
    <form method="GET" action="{{ route('reportes.fiscal') }}" class="card p-4">
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
                <a href="{{ route('reportes.fiscal') }}"
                   class="bg-[#1a2235] border border-[#1e2d47] text-slate-400 font-semibold text-sm px-4 py-2.5 rounded-xl hover:border-slate-500 transition-colors">
                    Mes Actual
                </a>
            </div>
        </div>
    </form>

    {{-- Tarjetas Resumen Impuestos --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="card p-4">
            <span class="text-xs text-slate-500 uppercase tracking-wider block">Ventas Brutas</span>
            <span class="font-mono font-bold text-lg text-slate-200">${{ number_format($totales['total_ventas_brutas'], 0, ',', '.') }}</span>
        </div>
        <div class="card p-4">
            <span class="text-xs text-slate-500 uppercase tracking-wider block">IVA Generado (Ventas)</span>
            <span class="font-mono font-bold text-lg text-emerald-400">${{ number_format($totales['total_iva_generado'], 0, ',', '.') }}</span>
        </div>
        <div class="card p-4">
            <span class="text-xs text-slate-500 uppercase tracking-wider block">ReteFuente Practicada</span>
            <span class="font-mono font-bold text-lg text-amber-400">-${{ number_format($totales['total_retefuente'], 0, ',', '.') }}</span>
        </div>
        <div class="card p-4">
            <span class="text-xs text-slate-500 uppercase tracking-wider block">ReteICA + ReteIVA</span>
            <span class="font-mono font-bold text-lg text-purple-400">-${{ number_format($totales['total_reteica'] + $totales['total_reteiva'], 0, ',', '.') }}</span>
        </div>
    </div>

    {{-- Cuadro de Bases Gravables DIAN (Formulario 300 y 350) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Formulario 300 (IVA) --}}
        <div class="card p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-[#1e2d47] pb-3">
                <h3 class="font-bold text-slate-200 text-sm">Discriminación de IVA por Tarifa (Formulario 300)</h3>
                <span class="badge bg-emerald-500/10 text-emerald-400 text-xs">DIAN IVA</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="text-slate-400 uppercase tracking-wider border-b border-[#1e2d47] text-[11px]">
                            <th class="pb-2 text-left">Tarifa</th>
                            <th class="pb-2 text-right">Base Gravable</th>
                            <th class="pb-2 text-right">Impuesto Generado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#1e2d47] font-mono">
                        <tr>
                            <td class="py-2.5 font-sans font-medium text-slate-200">Tarifa General (19%)</td>
                            <td class="py-2.5 text-right text-slate-300">${{ number_format($totales['base_19'], 0, ',', '.') }}</td>
                            <td class="py-2.5 text-right font-bold text-emerald-400">${{ number_format($totales['iva_19'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="py-2.5 font-sans font-medium text-slate-200">Tarifa Especial (5%)</td>
                            <td class="py-2.5 text-right text-slate-300">${{ number_format($totales['base_5'], 0, ',', '.') }}</td>
                            <td class="py-2.5 text-right font-bold text-emerald-400">${{ number_format($totales['iva_5'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="py-2.5 font-sans font-medium text-slate-200">Exentas / Excluidas (0%)</td>
                            <td class="py-2.5 text-right text-slate-300">${{ number_format($totales['base_0'], 0, ',', '.') }}</td>
                            <td class="py-2.5 text-right text-slate-500">$0</td>
                        </tr>
                    </tbody>
                    <tfoot class="border-t-2 border-[#1e2d47] font-mono font-bold">
                        <tr>
                            <td class="pt-2 font-sans text-slate-200 uppercase">TOTALES IVA:</td>
                            <td class="pt-2 text-right text-slate-200">${{ number_format($totales['base_19'] + $totales['base_5'] + $totales['base_0'], 0, ',', '.') }}</td>
                            <td class="pt-2 text-right text-emerald-400">${{ number_format($totales['total_iva_generado'], 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Formulario 350 (Retenciones) --}}
        <div class="card p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-[#1e2d47] pb-3">
                <h3 class="font-bold text-slate-200 text-sm">Retenciones en la Fuente Practicadas (Formulario 350)</h3>
                <span class="badge bg-amber-500/10 text-amber-400 text-xs">Retenciones</span>
            </div>
            <div class="space-y-3 text-xs">
                <div class="flex justify-between py-2 border-b border-[#1e2d47]/50 font-sans">
                    <span class="text-slate-400">ReteFuente en Ventas (a favor):</span>
                    <span class="font-mono font-bold text-amber-400">${{ number_format($totales['total_retefuente'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-[#1e2d47]/50 font-sans">
                    <span class="text-slate-400">ReteICA en Ventas (Municipal):</span>
                    <span class="font-mono font-bold text-amber-400">${{ number_format($totales['total_reteica'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-[#1e2d47]/50 font-sans">
                    <span class="text-slate-400">ReteIVA en Ventas (15% del IVA):</span>
                    <span class="font-mono font-bold text-amber-400">${{ number_format($totales['total_reteiva'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-2 font-sans border-t border-[#1e2d47] pt-3">
                    <span class="text-slate-200 font-bold uppercase">Total Retenciones Acumuladas:</span>
                    <span class="font-mono font-bold text-amber-400 text-sm">${{ number_format($totales['total_retefuente'] + $totales['total_reteica'] + $totales['total_reteiva'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

    </div>

    {{-- Tabla de Facturas --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-[#1e2d47] flex items-center justify-between">
            <h3 class="font-bold text-slate-200 text-sm">Detalle de Facturas del Periodo Fiscal</h3>
            <span class="text-xs text-slate-500 font-mono">{{ count($facturas) }} factura(s)</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs font-mono">
                <thead>
                    <tr class="border-b border-[#1e2d47] bg-[#141c2e] text-slate-400 uppercase tracking-wider text-[11px]">
                        <th class="px-4 py-3 text-left">Factura</th>
                        <th class="px-4 py-3 text-left">Fecha</th>
                        <th class="px-4 py-3 text-left">Cliente</th>
                        <th class="px-4 py-3 text-right">Subtotal</th>
                        <th class="px-4 py-3 text-right">IVA</th>
                        <th class="px-4 py-3 text-right">ReteFuente</th>
                        <th class="px-4 py-3 text-right">Total Factura</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1e2d47]">
                    @forelse($facturas as $f)
                    <tr class="hover:bg-[#1a2235]/60 transition-colors">
                        <td class="px-4 py-2.5 font-bold text-amber-400">
                            <a href="{{ route('facturas.show', $f) }}" class="hover:underline">
                                {{ $f->numero }}
                            </a>
                        </td>
                        <td class="px-4 py-2.5 text-slate-400">{{ $f->fecha_emision ? $f->fecha_emision->format('d/m/Y') : '' }}</td>
                        <td class="px-4 py-2.5 font-sans font-medium text-slate-200">{{ $f->cliente_nombre }}</td>
                        <td class="px-4 py-2.5 text-right text-slate-300">${{ number_format($f->subtotal, 0, ',', '.') }}</td>
                        <td class="px-4 py-2.5 text-right text-emerald-400 font-semibold">${{ number_format($f->iva, 0, ',', '.') }}</td>
                        <td class="px-4 py-2.5 text-right text-amber-400">-${{ number_format($f->retefuente, 0, ',', '.') }}</td>
                        <td class="px-4 py-2.5 text-right font-bold text-slate-100">${{ number_format($f->total, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500 font-sans text-xs">
                            No hay facturas emitidas en el rango de fechas seleccionado
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
