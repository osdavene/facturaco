@extends('layouts.app')
@section('title', 'Facturas')
@section('page-title', 'Facturación')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-display font-bold text-2xl">Facturas</h1>
        <p class="text-slate-500 text-sm mt-1">Gestiona tu facturación electrónica</p>
    </div>
    <a href="{{ route('facturas.create') }}"
       class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600
              text-black font-semibold px-5 py-2.5 rounded-xl transition-colors">
        <i class="fas fa-plus"></i> Nueva Factura
    </a>
</div>

{{-- KPIs --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="card p-5">
        <div class="text-xs text-slate-500 uppercase tracking-wider mb-2">Total Facturas</div>
        <div class="font-display font-bold text-2xl">{{ $totales->total ?? 0 }}</div>
    </div>
    <div class="card p-5">
        <div class="text-xs text-slate-500 uppercase tracking-wider mb-2">Emitidas</div>
        <div class="font-display font-bold text-2xl text-blue-400">{{ $totales->emitidas ?? 0 }}</div>
    </div>
    <div class="card p-5">
        <div class="text-xs text-slate-500 uppercase tracking-wider mb-2">Vencidas</div>
        <div class="font-display font-bold text-2xl text-red-400">{{ $totales->vencidas ?? 0 }}</div>
    </div>
    <div class="card p-5">
        <div class="text-xs text-slate-500 uppercase tracking-wider mb-2">Pagadas</div>
        <div class="font-display font-bold text-2xl text-emerald-500">{{ $totales->pagadas ?? 0 }}</div>
    </div>
</div>

{{-- Filtros --}}
<form method="GET" action="{{ route('facturas.index') }}"
      class="card p-4 mb-5">
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1 relative">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
            <input type="text" name="buscar" value="{{ request('buscar') }}"
                   placeholder="Buscar por número o cliente..."
                   class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl
                          pl-9 pr-4 py-2.5 text-sm text-slate-200 placeholder-slate-600
                          focus:outline-none focus:border-amber-500">
        </div>
        <select name="estado"
                class="form-input text-slate-300 focus:outline-none focus:border-amber-500">
            <option value="">Todos los estados</option>
            @foreach(['borrador'=>'Borrador','emitida'=>'Emitida','pagada'=>'Pagada','vencida'=>'Vencida','anulada'=>'Anulada'] as $val=>$label)
            <option value="{{ $val }}" {{ request('estado')==$val ? 'selected':'' }}>{{ $label }}</option>
            @endforeach
        </select>
        <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"
               class="form-input text-slate-300 focus:outline-none focus:border-amber-500">
        <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}"
               class="form-input text-slate-300 focus:outline-none focus:border-amber-500">
        <button type="submit"
                class="bg-amber-500 hover:bg-amber-600 text-black font-semibold
                       px-5 py-2.5 rounded-xl transition-colors whitespace-nowrap">
            <i class="fas fa-filter mr-1"></i> Filtrar
        </button>
        @if(request()->hasAny(['buscar','estado','fecha_desde','fecha_hasta']))
        <a href="{{ route('facturas.index') }}"
           class="bg-[#1a2235] border border-[#1e2d47] hover:border-red-500/50
                  text-slate-400 hover:text-red-400 px-4 py-2.5 rounded-xl
                  transition-colors text-sm flex items-center gap-2">
            <i class="fas fa-times"></i>
        </a>
        @endif
    </div>
</form>

{{-- Tabla --}}
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        {{-- Formulario bulk --}}
        <form id="bulk-form" method="POST" action="{{ route('facturas.bulk-delete') }}">
            @csrf @method('DELETE')
        </form>

        <table class="w-full">
            <thead>
                <tr class="border-b border-[#1e2d47]">
                    <th class="w-10 px-4 py-3">
                        <input type="checkbox" class="bulk-select-all w-4 h-4 rounded border-[#2d3f5c]
                               bg-[#1a2235] accent-amber-500 cursor-pointer">
                    </th>
                    <th class="table-th">Número</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Cliente</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden md:table-cell">Fecha</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden md:table-cell">Vence</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Total</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Estado</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-5 py-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($facturas as $factura)
                <tr class="table-row">
                    <td class="px-4 py-4">
                        <input type="checkbox" class="bulk-item w-4 h-4 rounded border-[#2d3f5c]
                               bg-[#1a2235] accent-amber-500 cursor-pointer"
                               value="{{ $factura->id }}"
                               {{ $factura->estado !== 'borrador' ? 'disabled title=Solo facturas en borrador' : '' }}>
                    </td>
                    <td class="px-5 py-4">
                        <div class="font-mono text-sm font-semibold text-amber-500">
                            {{ $factura->numero }}
                        </div>
                        <div class="text-xs text-slate-500">{{ ucfirst($factura->tipo) }}</div>
                    </td>
                    <td class="px-3 py-4">
                        <div class="text-sm font-medium">{{ $factura->cliente_nombre }}</div>
                        <div class="text-xs text-slate-500">{{ $factura->cliente_documento }}</div>
                    </td>
                    <td class="px-3 py-4 text-sm text-slate-400 hidden md:table-cell">
                        {{ $factura->fecha_emision->format('d/m/Y') }}
                    </td>
                    <td class="px-3 py-4 hidden md:table-cell">
                        <span class="text-sm {{ $factura->fecha_vencimiento < now() && $factura->estado=='emitida' ? 'text-red-400' : 'text-slate-400' }}">
                            {{ $factura->fecha_vencimiento->format('d/m/Y') }}
                        </span>
                    </td>
                    <td class="px-3 py-4 text-right">
                        <div class="text-sm font-bold">
                            ${{ number_format($factura->total, 0, ',', '.') }}
                        </div>
                    </td>
                    <td class="px-3 py-4">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full
                            bg-{{ $factura->estado_color }}-500/10 text-{{ $factura->estado_color }}-{{ $factura->estado_color=='slate' ? '400' : '500' }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            {{ ucfirst($factura->estado) }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('facturas.show', $factura) }}" title="Ver"
                               class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                      flex items-center justify-center text-slate-400
                                      hover:text-blue-400 hover:border-blue-500/50 transition-colors">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            <a href="{{ route('facturas.pdf', $factura) }}" title="PDF" target="_blank"
                               class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                      flex items-center justify-center text-slate-400
                                      hover:text-red-400 hover:border-red-500/50 transition-colors">
                                <i class="fas fa-file-pdf text-xs"></i>
                            </a>
                            @if($factura->estado == 'borrador')
                            <a href="{{ route('facturas.edit', $factura) }}" title="Editar"
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
                <x-empty-state
                    icon="fa-file-invoice"
                    title="No hay facturas aún"
                    subtitle="Crea tu primera factura y empieza a registrar tus ventas."
                    href="{{ route('facturas.create') }}"
                    label="Nueva Factura"
                    :colspan="8" />
                @endforelse
            </tbody>
        </table>
    </div>
    @if($facturas->hasPages())
    <div class="px-5 py-4 border-t border-[#1e2d47]">
        {{ $facturas->links() }}
    </div>
    @endif
</div>

{{-- Barra de acciones en masa --}}
<div id="bulk-bar"
     class="hidden fixed bottom-16 left-0 right-0 z-[95]
            bg-[#0d1526]/97 backdrop-blur border-t border-amber-500/20
            px-4 py-3 flex items-center gap-3 lg:left-64">
    <div class="w-8 h-8 bg-amber-500/10 border border-amber-500/20 rounded-lg
                flex items-center justify-center flex-shrink-0">
        <i class="fas fa-check-square text-amber-400 text-sm"></i>
    </div>
    <span id="bulk-count" class="text-sm font-medium text-slate-200 flex-1">0 seleccionados</span>
    <button onclick="submitBulkAction('delete')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-red-500/10 border border-red-500/30
                   hover:bg-red-500/20 text-red-400 rounded-xl text-sm transition-colors">
        <i class="fas fa-trash text-xs"></i> Eliminar
    </button>
    <button onclick="clearBulkSelection()"
            class="inline-flex items-center gap-2 px-4 py-2 bg-[#1a2235] border border-[#1e2d47]
                   hover:border-slate-500 text-slate-400 rounded-xl text-sm transition-colors">
        <i class="fas fa-times text-xs"></i> Cancelar
    </button>
</div>
@endsection