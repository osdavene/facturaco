@extends('layouts.app')
@section('title', 'Asiento '.$asiento->numero)
@section('page-title', 'Libro Diario')

@section('content')
<div class="max-w-3xl mx-auto">

    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('contabilidad.libro-diario.index') }}"
               class="w-9 h-9 bg-[#141c2e] border border-[#1e2d47] rounded-xl
                      flex items-center justify-center text-slate-400
                      hover:text-amber-500 hover:border-amber-500/50 transition-colors">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="font-display font-bold text-2xl font-mono text-amber-400">
                    {{ $asiento->numero }}
                </h1>
                <p class="text-slate-500 text-sm">{{ $asiento->fecha->format('d/m/Y') }}</p>
            </div>
        </div>
        <div>
            @if($asiento->estado === 'confirmado')
                <span class="px-3 py-1 rounded-full text-sm bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    Confirmado
                </span>
            @else
                <span class="px-3 py-1 rounded-full text-sm bg-red-500/10 text-red-400 border border-red-500/20">
                    Anulado
                </span>
            @endif
        </div>
    </div>

    {{-- Cabecera --}}
    <div class="card p-5 mb-5">
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Tipo</p>
                <p class="font-medium text-slate-300">{{ $asiento->tipo_label }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Fecha</p>
                <p class="font-medium text-slate-300">{{ $asiento->fecha->format('d/m/Y') }}</p>
            </div>
            @if($asiento->referencia_tipo && $asiento->referencia_id)
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Referencia</p>
                <p class="font-medium text-slate-300">{{ $asiento->referencia_tipo }} #{{ $asiento->referencia_id }}</p>
            </div>
            @endif
            <div class="col-span-2 sm:col-span-3">
                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Descripción</p>
                <p class="font-medium text-slate-300">{{ $asiento->descripcion }}</p>
            </div>
            @if($asiento->creadoPor)
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Creado por</p>
                <p class="font-medium text-slate-300">{{ $asiento->creadoPor->name ?? '—' }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Líneas del asiento --}}
    <div class="card overflow-hidden mb-5">
        <div class="px-5 py-3 border-b border-[#1e2d47]">
            <h2 class="font-semibold text-slate-200">Partidas</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-[#1e2d47] text-xs text-slate-400 uppercase tracking-wider">
                        <th class="px-4 py-3 text-left">Cuenta</th>
                        <th class="px-4 py-3 text-left">Descripción</th>
                        <th class="px-4 py-3 text-right">Débito</th>
                        <th class="px-4 py-3 text-right">Crédito</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1e2d47]">
                    @foreach($asiento->lineas as $linea)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-mono text-amber-400 text-xs font-semibold">{{ $linea->cuenta->codigo ?? '—' }}</div>
                            <div class="text-slate-400 text-xs mt-0.5">{{ $linea->cuenta->nombre ?? '' }}</div>
                        </td>
                        <td class="px-4 py-3 text-slate-400 text-xs">{{ $linea->descripcion }}</td>
                        <td class="px-4 py-3 text-right font-mono text-xs
                            {{ $linea->debito > 0 ? 'text-blue-400' : 'text-slate-600' }}">
                            {{ $linea->debito > 0 ? '$'.number_format($linea->debito, 0, ',', '.') : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-xs
                            {{ $linea->credito > 0 ? 'text-emerald-400' : 'text-slate-600' }}">
                            {{ $linea->credito > 0 ? '$'.number_format($linea->credito, 0, ',', '.') : '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t border-[#1e2d47] bg-[#141c2e]/50">
                        <td colspan="2" class="px-4 py-3 text-xs text-slate-400 font-semibold text-right">
                            TOTALES
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-sm font-bold text-blue-400">
                            ${{ number_format($asiento->total_debito, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-sm font-bold text-emerald-400">
                            ${{ number_format($asiento->total_credito, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Verificación de balance --}}
    @if($asiento->esta_balanceado)
    <div class="flex items-center gap-2 text-sm text-emerald-400">
        <i class="fas fa-check-circle"></i>
        <span>Asiento balanceado · Débito = Crédito</span>
    </div>
    @else
    <div class="flex items-center gap-2 text-sm text-red-400">
        <i class="fas fa-exclamation-triangle"></i>
        <span>Asiento desbalanceado · Diferencia: ${{ number_format(abs($asiento->total_debito - $asiento->total_credito), 0, ',', '.') }}</span>
    </div>
    @endif

</div>
@endsection
