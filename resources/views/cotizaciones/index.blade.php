@extends('layouts.app')
@section('title', 'Cotizaciones')
@section('page-title', 'Cotizaciones')

@section('content')

@if(session('success'))
<div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400
            rounded-xl px-5 py-3 mb-5 flex items-center gap-3">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-display font-bold text-2xl">Cotizaciones</h1>
        <p class="text-slate-500 text-sm mt-1">Gestiona propuestas comerciales para tus clientes</p>
    </div>
    <a href="{{ route('cotizaciones.create') }}"
       class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600
              text-black font-semibold px-5 py-2.5 rounded-xl transition-colors">
        <i class="fas fa-plus"></i> Nueva Cotización
    </a>
</div>

{{-- KPIs --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-5">
    @foreach([
        ['Total',      $totales['total'],      'fa-file-alt',     'slate',   ''],
        ['Borrador',   $totales['borrador'],   'fa-pencil-alt',   'amber',   ''],
        ['Enviadas',   $totales['enviada'],    'fa-paper-plane',  'blue',    ''],
        ['Aceptadas',  $totales['aceptada'],   'fa-check-circle', 'emerald', ''],
        ['Convertidas',$totales['convertida'], 'fa-file-invoice', 'purple',  ''],
    ] as [$label, $val, $icon, $color, $prefix])
    <div class="card p-4">
        <div class="flex items-center justify-between mb-2">
            <div class="text-xs text-slate-500 uppercase tracking-wider">{{ $label }}</div>
            <div class="w-7 h-7 bg-{{ $color }}-500/10 rounded-lg flex items-center justify-center
                        text-{{ $color }}-{{ $color=='slate'?'400':'500' }}">
                <i class="fas {{ $icon }} text-xs"></i>
            </div>
        </div>
        <div class="font-display font-bold text-2xl">{{ $val }}</div>
    </div>
    @endforeach
</div>

{{-- Filtros --}}
<form method="GET" action="{{ route('cotizaciones.index') }}"
      class="card p-4 mb-5">
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1 relative">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
            <input type="text" name="buscar" value="{{ request('buscar') }}"
                   placeholder="Buscar por número o cliente..."
                   class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl
                          pl-9 pr-4 py-2.5 text-sm placeholder-slate-600
                          focus:outline-none focus:border-amber-500"
                   style="color:#e2e8f0">
        </div>
        <select name="estado"
                class="form-input"
                style="color:#e2e8f0">
            <option value="">Todos los estados</option>
            @foreach(['borrador'=>'Borrador','enviada'=>'Enviada','aceptada'=>'Aceptada',
                      'rechazada'=>'Rechazada','convertida'=>'Convertida'] as $val=>$label)
            <option value="{{ $val }}" {{ request('estado')==$val?'selected':'' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit"
                class="bg-amber-500 hover:bg-amber-600 text-black font-semibold
                       px-5 py-2.5 rounded-xl transition-colors whitespace-nowrap">
            <i class="fas fa-filter mr-1"></i> Filtrar
        </button>
        @if(request()->hasAny(['buscar','estado']))
        <a href="{{ route('cotizaciones.index') }}"
           class="bg-[#1a2235] border border-[#1e2d47] hover:border-red-500/50
                  text-slate-400 hover:text-red-400 px-4 py-2.5 rounded-xl
                  transition-colors text-sm flex items-center">
            <i class="fas fa-times"></i>
        </a>
        @endif
    </div>
</form>

{{-- Tabla --}}
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-[#1e2d47]">
                    <th class="table-th">Número</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Cliente</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden md:table-cell">Emisión</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden lg:table-cell">Vence</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Total</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Estado</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-5 py-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cotizaciones as $cotizacion)
                <tr class="table-row">
                    <td class="px-5 py-4">
                        <div class="font-mono text-sm font-semibold text-blue-400">
                            {{ $cotizacion->numero }}
                        </div>
                    </td>
                    <td class="px-3 py-4">
                        <div class="text-sm font-medium">{{ $cotizacion->cliente_nombre }}</div>
                        @if($cotizacion->cliente_documento)
                        <div class="text-xs text-slate-500">{{ $cotizacion->cliente_documento }}</div>
                        @endif
                    </td>
                    <td class="px-3 py-4 text-sm text-slate-400 hidden md:table-cell">
                        {{ $cotizacion->fecha_emision->format('d/m/Y') }}
                    </td>
                    <td class="px-3 py-4 hidden lg:table-cell">
                        <span class="text-sm {{ $cotizacion->vencida ? 'text-red-400 font-semibold' : 'text-slate-400' }}">
                            {{ $cotizacion->fecha_vencimiento->format('d/m/Y') }}
                        </span>
                    </td>
                    <td class="px-3 py-4 text-right">
                        <div class="text-sm font-bold">
                            ${{ number_format($cotizacion->total, 0, ',', '.') }}
                        </div>
                    </td>
                    <td class="px-3 py-4">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold
                                     px-2.5 py-1 rounded-full
                                     bg-{{ $cotizacion->estado_color }}-500/10
                                     text-{{ $cotizacion->estado_color }}-{{ $cotizacion->estado_color=='slate'?'400':'500' }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            {{ ucfirst($cotizacion->estado) }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('cotizaciones.show', $cotizacion) }}" title="Ver"
                               class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                      flex items-center justify-center text-slate-400
                                      hover:text-blue-400 hover:border-blue-500/50 transition-colors">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            <a href="{{ route('cotizaciones.pdf', $cotizacion) }}" target="_blank" title="PDF"
                               class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                      flex items-center justify-center text-slate-400
                                      hover:text-red-400 hover:border-red-500/50 transition-colors">
                                <i class="fas fa-file-pdf text-xs"></i>
                            </a>
                            @if(!in_array($cotizacion->estado, ['convertida','rechazada']))
                            <form method="POST"
                                  action="{{ route('cotizaciones.convertir', $cotizacion) }}"
                                  onsubmit="return confirm('¿Convertir esta cotización en factura?')">
                                @csrf
                                <button type="submit" title="Convertir a Factura"
                                        class="w-8 h-8 bg-emerald-500/10 border border-emerald-500/30
                                               rounded-lg flex items-center justify-center text-emerald-500
                                               hover:bg-emerald-500/20 transition-colors">
                                    <i class="fas fa-file-invoice text-xs"></i>
                                </button>
                            </form>
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
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="text-slate-500">No hay cotizaciones registradas</div>
                            <a href="{{ route('cotizaciones.create') }}"
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
    @if($cotizaciones->hasPages())
    <div class="px-5 py-4 border-t border-[#1e2d47]">
        {{ $cotizaciones->links() }}
    </div>
    @endif
</div>
@endsection