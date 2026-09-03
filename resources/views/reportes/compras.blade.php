@extends('layouts.app')
@section('title', 'Informe de Compras y Gastos')
@section('page-title', 'Reportes · Compras y Gastos')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-display font-bold text-2xl">Informe de Compras y Proveedores</h1>
            <p class="text-slate-500 text-sm mt-0.5">Control de órdenes de compra, gastos a proveedores e IVA descontable</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('reportes.compras.excel', request()->query()) }}"
               class="inline-flex items-center gap-2 bg-[#1a2235] hover:bg-[#222f48] border border-[#1e2d47]
                      hover:border-emerald-500/50 text-emerald-400 font-semibold px-4 py-2.5 rounded-xl text-sm transition-colors">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </a>
        </div>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('reportes.compras') }}" class="card p-4">
        <div class="flex flex-col sm:flex-row items-end gap-3 flex-wrap">
            <div>
                <label class="block text-xs text-slate-500 mb-1 uppercase tracking-wider">Fecha Desde</label>
                <input type="date" name="fecha_desde" value="{{ $fecha_desde }}" class="form-input text-sm">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1 uppercase tracking-wider">Fecha Hasta</label>
                <input type="date" name="fecha_hasta" value="{{ $fecha_hasta }}" class="form-input text-sm">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1 uppercase tracking-wider">Estado</label>
                <select name="estado" class="form-input text-sm">
                    <option value="">Todos los estados</option>
                    <option value="recibida" @selected($estado === 'recibida')>Recibida / Facturada</option>
                    <option value="aprobada" @selected($estado === 'aprobada')>Aprobada</option>
                    <option value="pendiente" @selected($estado === 'pendiente')>Pendiente</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit"
                        class="bg-amber-500 hover:bg-amber-600 text-black font-bold text-sm px-4 py-2.5 rounded-xl transition-colors">
                    <i class="fas fa-search mr-1"></i> Consultar
                </button>
                <a href="{{ route('reportes.compras') }}"
                   class="bg-[#1a2235] border border-[#1e2d47] text-slate-400 font-semibold text-sm px-4 py-2.5 rounded-xl hover:border-slate-500 transition-colors">
                    Mes Actual
                </a>
            </div>
        </div>
    </form>

    {{-- Tarjetas Resumen --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="card p-4">
            <span class="text-xs text-slate-500 uppercase tracking-wider block">Total Compras (Subtotal)</span>
            <span class="font-mono font-bold text-lg text-slate-200">${{ number_format($totales['subtotal'], 0, ',', '.') }}</span>
        </div>
        <div class="card p-4">
            <span class="text-xs text-slate-500 uppercase tracking-wider block">IVA Descontable (Compras)</span>
            <span class="font-mono font-bold text-lg text-purple-400">${{ number_format($totales['iva'], 0, ',', '.') }}</span>
        </div>
        <div class="card p-4">
            <span class="text-xs text-slate-500 uppercase tracking-wider block">Total Egresos Compras</span>
            <span class="font-mono font-bold text-lg text-amber-400">${{ number_format($totales['total'], 0, ',', '.') }}</span>
        </div>
        <div class="card p-4">
            <span class="text-xs text-slate-500 uppercase tracking-wider block">Órdenes Registradas</span>
            <span class="font-mono font-bold text-lg text-blue-400">{{ $totales['count'] }}</span>
        </div>
    </div>

    {{-- Tabla de Órdenes de Compra --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs font-mono">
                <thead>
                    <tr class="border-b border-[#1e2d47] bg-[#141c2e] text-slate-400 uppercase tracking-wider text-[11px]">
                        <th class="px-4 py-3 text-left">N° Orden</th>
                        <th class="px-4 py-3 text-left">Fecha</th>
                        <th class="px-4 py-3 text-left">Proveedor</th>
                        <th class="px-4 py-3 text-left">Estado</th>
                        <th class="px-4 py-3 text-right">Subtotal</th>
                        <th class="px-4 py-3 text-right">IVA</th>
                        <th class="px-4 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1e2d47]">
                    @forelse($ordenes as $o)
                    <tr class="hover:bg-[#1a2235]/60 transition-colors">
                        <td class="px-4 py-2.5 font-bold text-amber-400">
                            <a href="{{ route('ordenes.show', $o) }}" class="hover:underline">
                                {{ $o->numero }}
                            </a>
                        </td>
                        <td class="px-4 py-2.5 text-slate-400">{{ $o->fecha_emision ? $o->fecha_emision->format('d/m/Y') : '' }}</td>
                        <td class="px-4 py-2.5 font-sans font-medium text-slate-200">{{ $o->proveedor_nombre }}</td>
                        <td class="px-4 py-2.5 font-sans">
                            <span class="badge bg-slate-500/10 text-slate-300 text-[10px] px-2 py-0.5">
                                {{ ucfirst($o->estado) }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-right text-slate-300">${{ number_format($o->subtotal, 0, ',', '.') }}</td>
                        <td class="px-4 py-2.5 text-right text-purple-400">${{ number_format($o->iva, 0, ',', '.') }}</td>
                        <td class="px-4 py-2.5 text-right font-bold text-slate-100">${{ number_format($o->total, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500 font-sans text-xs">
                            No hay compras registradas en el rango de fechas seleccionado
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
