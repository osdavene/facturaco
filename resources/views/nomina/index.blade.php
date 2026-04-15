@extends('layouts.app')
@section('title', 'Nómina')
@section('page-title', 'Nómina')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-display font-bold text-2xl">Nómina</h1>
        <p class="text-slate-500 text-sm mt-1">Liquidación de nómina del personal</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('nomina.empleados.index') }}"
           class="inline-flex items-center gap-2 bg-[#1a2235] border border-[#1e2d47]
                  hover:border-slate-500 text-slate-300 hover:text-white
                  px-4 py-2.5 rounded-xl transition-colors text-sm">
            <i class="fas fa-users"></i>
            <span class="hidden sm:inline">Empleados</span>
        </a>
        <a href="{{ route('nomina.create') }}"
           class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600
                  text-black font-semibold px-5 py-2.5 rounded-xl transition-colors">
            <i class="fas fa-plus"></i> Nueva Nómina
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert-success mb-4">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

{{-- Lista de nóminas --}}
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
    @if($nominas->isEmpty())
    <div class="flex flex-col items-center justify-center py-16 text-slate-500">
        <i class="fas fa-file-invoice-dollar text-5xl mb-4 opacity-20"></i>
        <p class="font-semibold text-base">No hay nóminas registradas</p>
        <p class="text-sm mt-1">Crea el primer período de nómina</p>
        <a href="{{ route('nomina.create') }}"
           class="mt-5 bg-amber-500 hover:bg-amber-600 text-black font-bold
                  text-sm px-5 py-2.5 rounded-xl transition-colors">
            <i class="fas fa-plus mr-1"></i> Nueva Nómina
        </a>
    </div>
    @else
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-[#1e2d47] text-xs text-slate-500 uppercase tracking-wider">
                <th class="px-5 py-3 text-left">Nombre / Período</th>
                <th class="px-4 py-3 text-right hidden md:table-cell">Empleados</th>
                <th class="px-4 py-3 text-right hidden sm:table-cell">Devengado</th>
                <th class="px-4 py-3 text-right hidden lg:table-cell">Deducciones</th>
                <th class="px-4 py-3 text-right">Neto a Pagar</th>
                <th class="px-4 py-3 text-center">Estado</th>
                <th class="px-5 py-3 text-right">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($nominas as $nomina)
            <tr class="table-row">
                <td class="px-5 py-4">
                    <div class="font-semibold text-slate-200">{{ $nomina->nombre }}</div>
                    <div class="text-xs text-slate-500">
                        {{ $nomina->periodo_inicio->format('d/m/Y') }} — {{ $nomina->periodo_fin->format('d/m/Y') }}
                    </div>
                    @if($nomina->fecha_pago)
                    <div class="text-[10px] text-emerald-500/70 mt-0.5">
                        <i class="fas fa-calendar-check mr-1"></i>Pago: {{ $nomina->fecha_pago->format('d/m/Y') }}
                    </div>
                    @endif
                </td>
                <td class="px-4 py-4 text-right hidden md:table-cell">
                    <span class="text-slate-300 font-semibold">{{ $nomina->liquidaciones->count() }}</span>
                </td>
                <td class="px-4 py-4 text-right hidden sm:table-cell">
                    <span class="text-slate-300">${{ number_format($nomina->total_devengado, 0, ',', '.') }}</span>
                </td>
                <td class="px-4 py-4 text-right hidden lg:table-cell">
                    <span class="text-red-400">-${{ number_format($nomina->total_deducciones, 0, ',', '.') }}</span>
                </td>
                <td class="px-4 py-4 text-right">
                    <span class="font-bold text-amber-400">${{ number_format($nomina->total_neto, 0, ',', '.') }}</span>
                </td>
                <td class="px-4 py-4 text-center">
                    <span class="badge bg-{{ $nomina->estado_color }}-500/10 text-{{ $nomina->estado_color }}-{{ in_array($nomina->estado_color,['slate']) ? '400':'500' }}">
                        {{ $nomina->estado_label }}
                    </span>
                </td>
                <td class="px-5 py-4 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('nomina.show', $nomina) }}" title="Ver detalle"
                           class="btn-icon hover:text-blue-400 hover:border-blue-500/50">
                            <i class="fas fa-eye text-xs"></i>
                        </a>
                        @if($nomina->estado !== 'pagada' && $nomina->estado !== 'anulada')
                        <form method="POST" action="{{ route('nomina.destroy', $nomina) }}"
                              onsubmit="return confirm('¿Eliminar esta nómina?')">
                            @csrf @method('DELETE')
                            <button type="submit" title="Eliminar"
                                    class="btn-icon hover:text-red-400 hover:border-red-500/50">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
    </div>

    @if($nominas->hasPages())
    <div class="px-5 py-4 border-t border-[#1e2d47]">
        {{ $nominas->links() }}
    </div>
    @endif
</div>

@endsection
