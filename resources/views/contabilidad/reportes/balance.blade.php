@extends('layouts.app')
@section('title', 'Balance General')
@section('page-title', 'Contabilidad')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-display font-bold text-2xl">Balance General</h1>
        <p class="text-slate-500 text-sm mt-1">Posición financiera al {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}</p>
    </div>
    <form method="GET" action="{{ route('contabilidad.reportes.balance') }}"
          class="flex items-center gap-2">
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
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="card p-5">
        <p class="text-xs text-slate-500 uppercase tracking-wider mb-2">Total Activos</p>
        <p class="font-display font-bold text-2xl text-blue-400">${{ number_format($totalActivo, 0, ',', '.') }}</p>
    </div>
    <div class="card p-5">
        <p class="text-xs text-slate-500 uppercase tracking-wider mb-2">Total Pasivos</p>
        <p class="font-display font-bold text-2xl text-red-400">${{ number_format($totalPasivo, 0, ',', '.') }}</p>
    </div>
    <div class="card p-5">
        <p class="text-xs text-slate-500 uppercase tracking-wider mb-2">Patrimonio</p>
        <p class="font-display font-bold text-2xl text-purple-400">${{ number_format($totalPatrimonio, 0, ',', '.') }}</p>
    </div>
</div>

@if(abs($totalActivo - ($totalPasivo + $totalPatrimonio)) > 1)
<div class="bg-amber-500/10 border border-amber-500/30 text-amber-400 rounded-xl p-4 mb-5 text-sm flex items-center gap-2">
    <i class="fas fa-exclamation-triangle"></i>
    Balance no cuadra. Diferencia: ${{ number_format(abs($totalActivo - ($totalPasivo + $totalPatrimonio)), 0, ',', '.') }}
    — Verifica que todos los asientos estén registrados.
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    {{-- ACTIVOS --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-3 border-b border-[#1e2d47] flex items-center justify-between">
            <h2 class="font-semibold text-blue-400">Activos</h2>
            <span class="font-mono text-sm text-blue-400">${{ number_format($totalActivo, 0, ',', '.') }}</span>
        </div>
        @if(count($activo))
        <table class="w-full text-sm">
            <tbody class="divide-y divide-[#1e2d47]/50">
                @foreach($activo as $fila)
                <tr class="hover:bg-[#141c2e]/30">
                    <td class="px-5 py-2 font-mono text-xs text-slate-500">{{ $fila['codigo'] }}</td>
                    <td class="px-4 py-2 text-slate-300">{{ $fila['nombre'] }}</td>
                    <td class="px-5 py-2 text-right font-mono text-xs text-blue-300">
                        ${{ number_format($fila['saldo'], 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="px-5 py-6 text-slate-500 text-sm text-center">Sin movimientos en el período.</p>
        @endif
    </div>

    {{-- PASIVOS + PATRIMONIO --}}
    <div class="space-y-5">

        {{-- Pasivos --}}
        <div class="card overflow-hidden">
            <div class="px-5 py-3 border-b border-[#1e2d47] flex items-center justify-between">
                <h2 class="font-semibold text-red-400">Pasivos</h2>
                <span class="font-mono text-sm text-red-400">${{ number_format($totalPasivo, 0, ',', '.') }}</span>
            </div>
            @if(count($pasivo))
            <table class="w-full text-sm">
                <tbody class="divide-y divide-[#1e2d47]/50">
                    @foreach($pasivo as $fila)
                    <tr class="hover:bg-[#141c2e]/30">
                        <td class="px-5 py-2 font-mono text-xs text-slate-500">{{ $fila['codigo'] }}</td>
                        <td class="px-4 py-2 text-slate-300">{{ $fila['nombre'] }}</td>
                        <td class="px-5 py-2 text-right font-mono text-xs text-red-300">
                            ${{ number_format($fila['saldo'], 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="px-5 py-6 text-slate-500 text-sm text-center">Sin movimientos.</p>
            @endif
        </div>

        {{-- Patrimonio --}}
        <div class="card overflow-hidden">
            <div class="px-5 py-3 border-b border-[#1e2d47] flex items-center justify-between">
                <h2 class="font-semibold text-purple-400">Patrimonio</h2>
                <span class="font-mono text-sm text-purple-400">${{ number_format($totalPatrimonio, 0, ',', '.') }}</span>
            </div>
            @if(count($patrimonio))
            <table class="w-full text-sm">
                <tbody class="divide-y divide-[#1e2d47]/50">
                    @foreach($patrimonio as $fila)
                    <tr class="hover:bg-[#141c2e]/30">
                        <td class="px-5 py-2 font-mono text-xs text-slate-500">{{ $fila['codigo'] }}</td>
                        <td class="px-4 py-2 text-slate-300">{{ $fila['nombre'] }}</td>
                        <td class="px-5 py-2 text-right font-mono text-xs text-purple-300">
                            ${{ number_format($fila['saldo'], 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="px-5 py-6 text-slate-500 text-sm text-center">Sin movimientos.</p>
            @endif
        </div>

    </div>
</div>

@endsection
