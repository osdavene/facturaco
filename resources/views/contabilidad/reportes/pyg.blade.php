@extends('layouts.app')
@section('title', 'Estado de Resultados')
@section('page-title', 'Contabilidad')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-display font-bold text-2xl">Estado de Resultados (P&G)</h1>
        <p class="text-slate-500 text-sm mt-1">
            Del {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }}
            al {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}
        </p>
    </div>
    <form method="GET" action="{{ route('contabilidad.reportes.pyg') }}"
          class="flex flex-wrap items-center gap-2">
        <label class="text-sm text-slate-400">Desde</label>
        <input type="date" name="desde" value="{{ $desde }}" class="input sm:w-40">
        <label class="text-sm text-slate-400">Hasta</label>
        <input type="date" name="hasta" value="{{ $hasta }}" class="input sm:w-40">
        <button type="submit"
                class="inline-flex items-center gap-2 bg-slate-700 hover:bg-slate-600
                       text-white px-4 py-2.5 rounded-xl text-sm transition-colors">
            <i class="fas fa-sync-alt"></i>
        </button>
    </form>
</div>

{{-- KPIs --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="card p-5">
        <p class="text-xs text-slate-500 uppercase tracking-wider mb-2">Ingresos</p>
        <p class="font-display font-bold text-xl text-emerald-400">${{ number_format($totalIngresos, 0, ',', '.') }}</p>
    </div>
    <div class="card p-5">
        <p class="text-xs text-slate-500 uppercase tracking-wider mb-2">Costos</p>
        <p class="font-display font-bold text-xl text-orange-400">${{ number_format($totalCostos, 0, ',', '.') }}</p>
    </div>
    <div class="card p-5">
        <p class="text-xs text-slate-500 uppercase tracking-wider mb-2">Gastos</p>
        <p class="font-display font-bold text-xl text-red-400">${{ number_format($totalGastos, 0, ',', '.') }}</p>
    </div>
    <div class="card p-5">
        <p class="text-xs text-slate-500 uppercase tracking-wider mb-2">Utilidad Neta</p>
        <p class="font-display font-bold text-xl {{ $utilidad >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
            ${{ number_format(abs($utilidad), 0, ',', '.') }}
            @if($utilidad < 0)<span class="text-xs ml-1">(Pérdida)</span>@endif
        </p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Ingresos --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-3 border-b border-[#1e2d47] flex items-center justify-between">
            <h2 class="font-semibold text-emerald-400">Ingresos</h2>
            <span class="font-mono text-sm text-emerald-400">${{ number_format($totalIngresos, 0, ',', '.') }}</span>
        </div>
        @if(count($ingresos))
        <table class="w-full text-sm">
            <tbody class="divide-y divide-[#1e2d47]/50">
                @foreach($ingresos as $fila)
                <tr class="hover:bg-[#141c2e]/30">
                    <td class="px-5 py-2 font-mono text-xs text-slate-500">{{ $fila['codigo'] }}</td>
                    <td class="px-4 py-2 text-slate-300 text-xs">{{ $fila['nombre'] }}</td>
                    <td class="px-5 py-2 text-right font-mono text-xs text-emerald-300 whitespace-nowrap">
                        ${{ number_format($fila['saldo'], 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="px-5 py-6 text-slate-500 text-sm text-center">Sin ingresos en el período.</p>
        @endif
    </div>

    {{-- Costos --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-3 border-b border-[#1e2d47] flex items-center justify-between">
            <h2 class="font-semibold text-orange-400">Costos</h2>
            <span class="font-mono text-sm text-orange-400">${{ number_format($totalCostos, 0, ',', '.') }}</span>
        </div>
        @if(count($costos))
        <table class="w-full text-sm">
            <tbody class="divide-y divide-[#1e2d47]/50">
                @foreach($costos as $fila)
                <tr class="hover:bg-[#141c2e]/30">
                    <td class="px-5 py-2 font-mono text-xs text-slate-500">{{ $fila['codigo'] }}</td>
                    <td class="px-4 py-2 text-slate-300 text-xs">{{ $fila['nombre'] }}</td>
                    <td class="px-5 py-2 text-right font-mono text-xs text-orange-300 whitespace-nowrap">
                        ${{ number_format($fila['saldo'], 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="px-5 py-6 text-slate-500 text-sm text-center">Sin costos en el período.</p>
        @endif
    </div>

    {{-- Gastos --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-3 border-b border-[#1e2d47] flex items-center justify-between">
            <h2 class="font-semibold text-red-400">Gastos</h2>
            <span class="font-mono text-sm text-red-400">${{ number_format($totalGastos, 0, ',', '.') }}</span>
        </div>
        @if(count($gastos))
        <table class="w-full text-sm">
            <tbody class="divide-y divide-[#1e2d47]/50">
                @foreach($gastos as $fila)
                <tr class="hover:bg-[#141c2e]/30">
                    <td class="px-5 py-2 font-mono text-xs text-slate-500">{{ $fila['codigo'] }}</td>
                    <td class="px-4 py-2 text-slate-300 text-xs">{{ $fila['nombre'] }}</td>
                    <td class="px-5 py-2 text-right font-mono text-xs text-red-300 whitespace-nowrap">
                        ${{ number_format($fila['saldo'], 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="px-5 py-6 text-slate-500 text-sm text-center">Sin gastos en el período.</p>
        @endif
    </div>

</div>

{{-- Resumen --}}
<div class="card p-5 mt-5 max-w-sm ml-auto">
    <h3 class="font-semibold text-slate-300 mb-3 text-sm">Resumen</h3>
    <div class="space-y-2 text-sm">
        <div class="flex justify-between text-slate-400">
            <span>Ingresos</span>
            <span class="font-mono text-emerald-400">${{ number_format($totalIngresos, 0, ',', '.') }}</span>
        </div>
        <div class="flex justify-between text-slate-400">
            <span>— Costos</span>
            <span class="font-mono text-orange-400">(${{ number_format($totalCostos, 0, ',', '.') }})</span>
        </div>
        <div class="flex justify-between text-slate-400">
            <span>— Gastos</span>
            <span class="font-mono text-red-400">(${{ number_format($totalGastos, 0, ',', '.') }})</span>
        </div>
        <div class="border-t border-[#1e2d47] pt-2 flex justify-between font-bold">
            <span class="{{ $utilidad >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                {{ $utilidad >= 0 ? 'Utilidad Neta' : 'Pérdida Neta' }}
            </span>
            <span class="font-mono {{ $utilidad >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                ${{ number_format(abs($utilidad), 0, ',', '.') }}
            </span>
        </div>
    </div>
</div>

@endsection
