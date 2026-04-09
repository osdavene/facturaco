@extends('layouts.app')
@section('title', 'Órdenes de Compra')
@section('page-title', 'Órdenes de Compra')

@section('content')

@if(session('success'))
<div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400
            rounded-xl px-5 py-3 mb-5 flex items-center gap-3">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-display font-bold text-2xl">Órdenes de Compra</h1>
        <p class="text-slate-500 text-sm mt-1">Gestiona las compras a proveedores</p>
    </div>
    <a href="{{ route('ordenes.create') }}"
       class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600
              text-black font-semibold px-5 py-2.5 rounded-xl transition-colors">
        <i class="fas fa-plus"></i> Nueva Orden
    </a>
</div>

{{-- KPIs --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    @foreach([
        ['Total',     $totales['total'],    'fa-file-alt',      'slate'],
        ['Borrador',  $totales['borrador'], 'fa-pencil-alt',    'amber'],
        ['Aprobadas', $totales['aprobada'], 'fa-check-circle',  'blue'],
        ['Recibidas', $totales['recibida'], 'fa-box-open',      'emerald'],
    ] as [$label, $val, $icon, $color])
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5">
        <div class="flex items-center justify-between mb-2">
            <div class="text-xs text-slate-500 uppercase tracking-wider">{{ $label }}</div>
            <div class="w-8 h-8 bg-{{ $color }}-500/10 rounded-lg flex items-center justify-center
                        text-{{ $color }}-{{ $color=='slate'?'400':'500' }}">
                <i class="fas {{ $icon }} text-sm"></i>
            </div>
        </div>
        <div class="font-display font-bold text-2xl
                    {{ $color=='slate'?'':'text-'.$color.'-'.($color=='slate'?'400':'500') }}">
            {{ $val }}
        </div>
    </div>
    @endforeach
</div>

{{-- Filtros --}}
<form method="GET" action="{{ route('ordenes.index') }}"
      class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-4 mb-5">
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1 relative">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
            <input type="text" name="buscar" value="{{ request('buscar') }}"
                   placeholder="Buscar por número o proveedor..."
                   class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl
                          pl-9 pr-4 py-2.5 text-sm placeholder-slate-600
                          focus:outline-none focus:border-amber-500"
                   style="color:#e2e8f0">
        </div>
        <select name="estado"
                class="bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                       text-sm focus:outline-none focus:border-amber-500"
                style="color:#e2e8f0">
            <option value="">Todos los estados</option>
            @foreach(['borrador'=>'Borrador','enviada'=>'Enviada','aprobada'=>'Aprobada',
                      'recibida'=>'Recibida','anulada'=>'Anulada'] as $val=>$label)
            <option value="{{ $val }}" {{ request('estado')==$val?'selected':'' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit"
                class="bg-amber-500 hover:bg-amber-600 text-black font-semibold
                       px-5 py-2.5 rounded-xl transition-colors whitespace-nowrap">
            <i class="fas fa-filter mr-1"></i> Filtrar
        </button>
        @if(request()->hasAny(['buscar','estado']))
        <a href="{{ route('ordenes.index') }}"
           class="bg-[#1a2235] border border-[#1e2d47] hover:border-red-500/50
                  text-slate-400 hover:text-red-400 px-4 py-2.5 rounded-xl
                  transition-colors text-sm flex items-center">
            <i class="fas fa-times"></i>
        </a>
        @endif
    </div>
</form>

{{-- Tabla --}}
<div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-[#1e2d47]">
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-5 py-3">Número</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Proveedor</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden md:table-cell">Emisión</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden lg:table-cell">Esperada</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Total</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Estado</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-5 py-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ordenes as $orden)
                <tr class="border-b border-[#1e2d47]/50 hover:bg-[#1a2235]/50 transition-colors">
                    <td class="px-5 py-4">
                        <div class="font-mono text-sm font-semibold text-amber-500">
                            {{ $orden->numero }}
                        </div>
                    </td>
                    <td class="px-3 py-4">
                        <div class="text-sm font-medium">{{ $orden->proveedor_nombre }}</div>
                        <div class="text-xs text-slate-500">{{ $orden->proveedor_documento }}</div>
                    </td>
                    <td class="px-3 py-4 text-sm text-slate-400 hidden md:table-cell">
                        {{ $orden->fecha_emision->format('d/m/Y') }}
                    </td>
                    <td class="px-3 py-4 hidden lg:table-cell">
                        <span class="text-sm {{ $orden->fecha_esperada && $orden->fecha_esperada < now() && $orden->estado !== 'recibida' ? 'text-red-400' : 'text-slate-400' }}">
                            {{ $orden->fecha_esperada?->format('d/m/Y') ?? '—' }}
                        </span>
                    </td>
                    <td class="px-3 py-4 text-right">
                        <div class="text-sm font-bold">
                            ${{ number_format($orden->total, 0, ',', '.') }}
                        </div>
                    </td>
                    <td class="px-3 py-4">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold
                                     px-2.5 py-1 rounded-full
                                     bg-{{ $orden->estado_color }}-500/10
                                     text-{{ $orden->estado_color }}-{{ $orden->estado_color=='slate'?'400':'500' }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            {{ ucfirst($orden->estado) }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('ordenes.show', $orden) }}" title="Ver"
                               class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                      flex items-center justify-center text-slate-400
                                      hover:text-blue-400 hover:border-blue-500/50 transition-colors">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            <a href="{{ route('ordenes.pdf', $orden) }}" target="_blank" title="PDF"
                               class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                      flex items-center justify-center text-slate-400
                                      hover:text-red-400 hover:border-red-500/50 transition-colors">
                                <i class="fas fa-file-pdf text-xs"></i>
                            </a>
                            @if($orden->estado === 'borrador')
                            <a href="{{ route('ordenes.edit', $orden) }}" title="Editar"
                               class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                      flex items-center justify-center text-slate-400
                                      hover:text-amber-500 hover:border-amber-500/50 transition-colors">
                                <i class="fas fa-pen text-xs"></i>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-16 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-14 h-14 bg-[#1a2235] rounded-2xl flex items-center
                                        justify-center text-slate-600 text-2xl">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="text-slate-500">No hay órdenes de compra</div>
                            <a href="{{ route('ordenes.create') }}"
                               class="text-amber-500 hover:underline text-sm">
                                + Crear la primera
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($ordenes->hasPages())
    <div class="px-5 py-4 border-t border-[#1e2d47]">
        {{ $ordenes->links() }}
    </div>
    @endif
</div>
@endsection