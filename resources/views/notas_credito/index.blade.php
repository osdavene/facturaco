@extends('layouts.app')
@section('title', 'Notas de Crédito')
@section('page-title', 'Facturación · Notas de Crédito')

@section('content')
<div class="max-w-5xl mx-auto">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="font-display font-bold text-2xl">Notas de Crédito</h1>
            <p class="text-slate-500 text-sm mt-0.5">
                Devoluciones y ajustes sobre facturas emitidas.
            </p>
        </div>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('notas_credito.index') }}"
          class="card p-4 mb-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <input type="text" name="buscar" value="{{ request('buscar') }}"
                       placeholder="Número, factura o cliente..."
                       class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-3 py-2
                              text-sm text-slate-200 placeholder-slate-600
                              focus:outline-none focus:border-amber-500 transition-colors">
            </div>
            <div>
                <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"
                       class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-3 py-2
                              text-sm text-slate-200 focus:outline-none focus:border-amber-500 transition-colors">
            </div>
            <div>
                <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}"
                       class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-3 py-2
                              text-sm text-slate-200 focus:outline-none focus:border-amber-500 transition-colors">
            </div>
        </div>
        <div class="flex gap-2 mt-3">
            <button type="submit"
                    class="bg-amber-500 hover:bg-amber-600 text-black font-bold text-sm
                           px-4 py-2 rounded-xl transition-colors">
                <i class="fas fa-search mr-1"></i> Filtrar
            </button>
            <a href="{{ route('notas_credito.index') }}"
               class="bg-[#1a2235] border border-[#1e2d47] text-slate-400 font-semibold text-sm
                      px-4 py-2 rounded-xl hover:border-slate-500 transition-colors">
                Limpiar
            </a>
        </div>
    </form>

    {{-- Tabla --}}
    <div class="card overflow-hidden">
        @if($notas->isEmpty())
        <x-empty-state
            :table="false"
            icon="fa-file-invoice"
            title="No hay notas de crédito"
            subtitle="Las notas de crédito se generan desde el detalle de una factura emitida." />
        @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-[#1e2d47] text-xs text-slate-500 uppercase tracking-wider">
                    <th class="px-5 py-3 text-left">Número</th>
                    <th class="px-5 py-3 text-left hidden sm:table-cell">Factura</th>
                    <th class="px-5 py-3 text-left">Cliente</th>
                    <th class="px-5 py-3 text-left hidden md:table-cell">Motivo</th>
                    <th class="px-5 py-3 text-center hidden md:table-cell">Tipo</th>
                    <th class="px-5 py-3 text-right">Total</th>
                    <th class="px-5 py-3 text-right">Fecha</th>
                    <th class="px-5 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#1e2d47]">
                @foreach($notas as $nota)
                <tr class="hover:bg-[#1a2235] transition-colors">
                    <td class="px-5 py-3.5">
                        <span class="font-mono font-bold text-violet-400">{{ $nota->numero }}</span>
                    </td>
                    <td class="px-5 py-3.5 hidden sm:table-cell">
                        <a href="{{ route('facturas.show', $nota->factura_id) }}"
                           class="font-mono text-amber-400 hover:underline text-xs">
                            {{ $nota->factura_numero }}
                        </a>
                    </td>
                    <td class="px-5 py-3.5 text-slate-300 max-w-xs truncate">
                        {{ $nota->cliente_nombre }}
                    </td>
                    <td class="px-5 py-3.5 text-slate-500 text-xs hidden md:table-cell">
                        {{ $nota->motivo_texto }}
                    </td>
                    <td class="px-5 py-3.5 text-center hidden md:table-cell">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold
                            {{ $nota->tipo === 'total' ? 'bg-red-500/10 text-red-400' : 'bg-amber-500/10 text-amber-400' }}">
                            {{ ucfirst($nota->tipo) }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-right font-semibold text-slate-200">
                        ${{ number_format($nota->total, 0, ',', '.') }}
                    </td>
                    <td class="px-5 py-3.5 text-right text-xs text-slate-500">
                        {{ $nota->fecha->format('d/m/Y') }}
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('notas_credito.show', $nota) }}"
                               class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                      flex items-center justify-center text-slate-400
                                      hover:text-violet-400 hover:border-violet-500/50 transition-colors">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            <a href="{{ route('notas_credito.pdf', $nota) }}" target="_blank"
                               class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                      flex items-center justify-center text-slate-400
                                      hover:text-red-400 hover:border-red-500/50 transition-colors">
                                <i class="fas fa-file-pdf text-xs"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if($notas->hasPages())
        <div class="px-5 py-4 border-t border-[#1e2d47]">
            {{ $notas->links() }}
        </div>
        @endif
        @endif
    </div>
</div>
@endsection