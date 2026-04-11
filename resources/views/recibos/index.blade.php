@extends('layouts.app')
@section('title', 'Recibos de Caja')
@section('page-title', 'Recibos de Caja')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-display font-bold text-2xl">Recibos de Caja</h1>
        <p class="text-slate-500 text-sm mt-1">Registro de pagos recibidos</p>
    </div>
    <a href="{{ route('recibos.create') }}"
       class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600
              text-black font-semibold px-5 py-2.5 rounded-xl transition-colors">
        <i class="fas fa-plus"></i> Nuevo Recibo
    </a>
</div>

{{-- KPIs --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
    <div class="card p-5">
        <div class="flex items-center justify-between mb-2">
            <div class="text-xs text-slate-500 uppercase tracking-wider">Recaudado Hoy</div>
            <div class="w-8 h-8 bg-emerald-500/10 rounded-lg flex items-center justify-center text-emerald-500">
                <i class="fas fa-calendar-day text-sm"></i>
            </div>
        </div>
        <div class="font-display font-bold text-2xl text-emerald-500">
            ${{ number_format($totalHoy, 0, ',', '.') }}
        </div>
        <div class="text-xs text-slate-500 mt-1">{{ now()->format('d/m/Y') }}</div>
    </div>
    <div class="card p-5">
        <div class="flex items-center justify-between mb-2">
            <div class="text-xs text-slate-500 uppercase tracking-wider">Recaudado del Mes</div>
            <div class="w-8 h-8 bg-blue-500/10 rounded-lg flex items-center justify-center text-blue-400">
                <i class="fas fa-calendar-alt text-sm"></i>
            </div>
        </div>
        <div class="font-display font-bold text-2xl text-blue-400">
            ${{ number_format($totalMes, 0, ',', '.') }}
        </div>
        <div class="text-xs text-slate-500 mt-1">{{ now()->locale('es')->monthName }} {{ now()->year }}</div>
    </div>
</div>

{{-- Filtros --}}
<form method="GET" action="{{ route('recibos.index') }}"
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
        <div>
            <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"
                   class="form-input"
                   style="color:#e2e8f0">
        </div>
        <div>
            <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}"
                   class="form-input"
                   style="color:#e2e8f0">
        </div>
        <button type="submit"
                class="bg-amber-500 hover:bg-amber-600 text-black font-semibold
                       px-5 py-2.5 rounded-xl transition-colors whitespace-nowrap">
            <i class="fas fa-filter mr-1"></i> Filtrar
        </button>
        @if(request()->hasAny(['buscar','fecha_desde','fecha_hasta']))
        <a href="{{ route('recibos.index') }}"
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
        <table class="w-full">
            <thead>
                <tr class="border-b border-[#1e2d47]">
                    <th class="table-th">Número</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Cliente</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden md:table-cell">Fecha</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden lg:table-cell">Forma Pago</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden lg:table-cell">Factura</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Valor</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Estado</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-5 py-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recibos as $recibo)
                <tr class="table-row">
                    <td class="px-5 py-4">
                        <div class="font-mono text-sm font-semibold text-emerald-500">
                            {{ $recibo->numero }}
                        </div>
                    </td>
                    <td class="px-3 py-4">
                        <div class="text-sm font-medium">{{ $recibo->cliente_nombre }}</div>
                        <div class="text-xs text-slate-500">{{ $recibo->cliente_documento }}</div>
                    </td>
                    <td class="px-3 py-4 text-sm text-slate-400 hidden md:table-cell">
                        {{ $recibo->fecha->format('d/m/Y') }}
                    </td>
                    <td class="px-3 py-4 hidden lg:table-cell">
                        @php
                        $iconos = [
                            'efectivo'     => ['fa-money-bill-wave', 'emerald'],
                            'transferencia'=> ['fa-exchange-alt',    'blue'],
                            'cheque'       => ['fa-file-alt',        'purple'],
                            'tarjeta'      => ['fa-credit-card',     'cyan'],
                            'consignacion' => ['fa-university',      'amber'],
                        ];
                        $ic = $iconos[$recibo->forma_pago] ?? ['fa-money-bill', 'slate'];
                        @endphp
                        <span class="inline-flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-full
                                     bg-{{ $ic[1] }}-500/10 text-{{ $ic[1] }}-{{ $ic[1]=='slate'?'400':'500' }}">
                            <i class="fas {{ $ic[0] }}"></i>
                            {{ ucfirst($recibo->forma_pago) }}
                        </span>
                    </td>
                    <td class="px-3 py-4 hidden lg:table-cell">
                        @if($recibo->factura)
                        <a href="{{ route('facturas.show', $recibo->factura) }}"
                           class="text-xs text-amber-500 hover:underline font-mono">
                            {{ $recibo->factura->numero }}
                        </a>
                        @else
                        <span class="text-xs text-slate-600">—</span>
                        @endif
                    </td>
                    <td class="px-3 py-4 text-right">
                        <div class="text-sm font-bold text-emerald-500">
                            ${{ number_format($recibo->valor, 0, ',', '.') }}
                        </div>
                    </td>
                    <td class="px-3 py-4">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full
                            {{ $recibo->estado === 'activo'
                               ? 'bg-emerald-500/10 text-emerald-500'
                               : 'bg-red-500/10 text-red-400' }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            {{ ucfirst($recibo->estado) }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('recibos.show', $recibo) }}" title="Ver"
                               class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                      flex items-center justify-center text-slate-400
                                      hover:text-blue-400 hover:border-blue-500/50 transition-colors">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            <a href="{{ route('recibos.pdf', $recibo) }}" target="_blank" title="PDF"
                               class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                      flex items-center justify-center text-slate-400
                                      hover:text-red-400 hover:border-red-500/50 transition-colors">
                                <i class="fas fa-file-pdf text-xs"></i>
                            </a>
                            @if($recibo->estado === 'activo')
                            <form method="POST" action="{{ route('recibos.destroy', $recibo) }}"
                                  onsubmit="return confirm('¿Anular el recibo {{ $recibo->numero }}? Esto revertirá el pago.')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Anular"
                                        class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                               flex items-center justify-center text-slate-400
                                               hover:text-red-400 hover:border-red-500/50 transition-colors">
                                    <i class="fas fa-ban text-xs"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <x-empty-state
                    icon="fa-hand-holding-usd"
                    title="No hay recibos de caja"
                    subtitle="Registra los pagos recibidos de tus clientes con un recibo de caja."
                    href="{{ route('recibos.create') }}"
                    label="Nuevo Recibo"
                    :colspan="8" />
                @endforelse
            </tbody>
        </table>
    </div>
    @if($recibos->hasPages())
    <div class="px-5 py-4 border-t border-[#1e2d47]">
        {{ $recibos->links() }}
    </div>
    @endif
</div>

@endsection