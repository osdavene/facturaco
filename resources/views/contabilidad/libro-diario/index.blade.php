@extends('layouts.app')
@section('title', 'Libro Diario')
@section('page-title', 'Libro Diario')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-display font-bold text-2xl">Libro Diario</h1>
        <p class="text-slate-500 text-sm mt-1">Asientos contables generados automáticamente</p>
    </div>
</div>

{{-- Filtros --}}
<form method="GET" action="{{ route('contabilidad.libro-diario.index') }}"
      class="card p-4 mb-5">
    <div class="flex flex-col sm:flex-row gap-3 flex-wrap">
        <input type="text" name="buscar" value="{{ request('buscar') }}"
               placeholder="Número o descripción..."
               class="input flex-1 min-w-[200px]">
        <select name="tipo" class="input sm:w-40">
            <option value="">Todos los tipos</option>
            <option value="factura"  @selected(request('tipo') === 'factura')>Factura</option>
            <option value="recibo"   @selected(request('tipo') === 'recibo')>Recibo de Caja</option>
            <option value="manual"   @selected(request('tipo') === 'manual')>Manual</option>
            <option value="ajuste"   @selected(request('tipo') === 'ajuste')>Ajuste</option>
        </select>
        <select name="estado" class="input sm:w-36">
            <option value="">Todos</option>
            <option value="confirmado" @selected(request('estado') === 'confirmado')>Confirmado</option>
            <option value="anulado"    @selected(request('estado') === 'anulado')>Anulado</option>
        </select>
        <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="input sm:w-40">
        <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="input sm:w-40">
        <button type="submit"
                class="inline-flex items-center gap-2 bg-slate-700 hover:bg-slate-600
                       text-white px-4 py-2.5 rounded-xl text-sm transition-colors">
            <i class="fas fa-search"></i> Filtrar
        </button>
        @if(request()->hasAny(['buscar','tipo','estado','fecha_desde','fecha_hasta']))
        <a href="{{ route('contabilidad.libro-diario.index') }}"
           class="inline-flex items-center gap-2 text-slate-400 hover:text-slate-200 px-3 py-2.5 text-sm">
            <i class="fas fa-times"></i>
        </a>
        @endif
    </div>
</form>

{{-- Tabla --}}
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-[#1e2d47] text-xs text-slate-400 uppercase tracking-wider">
                    <th class="px-4 py-3 text-left">Número</th>
                    <th class="px-4 py-3 text-left">Fecha</th>
                    <th class="px-4 py-3 text-left">Descripción</th>
                    <th class="px-4 py-3 text-left hidden md:table-cell">Tipo</th>
                    <th class="px-4 py-3 text-right hidden sm:table-cell">Débito</th>
                    <th class="px-4 py-3 text-right hidden sm:table-cell">Crédito</th>
                    <th class="px-4 py-3 text-center">Estado</th>
                    <th class="px-4 py-3 text-right"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#1e2d47]">
                @forelse($asientos as $asiento)
                <tr class="hover:bg-[#141c2e]/50 transition-colors
                    {{ $asiento->estado === 'anulado' ? 'opacity-50' : '' }}">
                    <td class="px-4 py-3 font-mono text-amber-400 font-semibold text-xs whitespace-nowrap">
                        {{ $asiento->numero }}
                    </td>
                    <td class="px-4 py-3 text-slate-300 text-xs whitespace-nowrap">
                        {{ $asiento->fecha->format('d/m/Y') }}
                    </td>
                    <td class="px-4 py-3 text-slate-300 max-w-xs truncate">
                        {{ $asiento->descripcion }}
                    </td>
                    <td class="px-4 py-3 hidden md:table-cell">
                        <span class="px-2 py-0.5 rounded text-xs bg-slate-500/10 text-slate-400">
                            {{ $asiento->tipo_label }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right hidden sm:table-cell font-mono text-xs text-slate-300">
                        ${{ number_format($asiento->total_debito, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-right hidden sm:table-cell font-mono text-xs text-slate-300">
                        ${{ number_format($asiento->total_credito, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($asiento->estado === 'confirmado')
                            <span class="px-2 py-0.5 rounded-full text-xs bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                Confirmado
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs bg-red-500/10 text-red-400 border border-red-500/20">
                                Anulado
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('contabilidad.libro-diario.show', $asiento) }}"
                           class="text-slate-400 hover:text-amber-400 transition-colors text-xs">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-slate-500">
                        <i class="fas fa-book-open text-3xl mb-3 block opacity-30"></i>
                        No hay asientos contables registrados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $asientos->links() }}
</div>

@endsection
