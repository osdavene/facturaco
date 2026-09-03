@extends('layouts.app')
@section('title', 'Libro Auxiliar')
@section('page-title', 'Contabilidad · Libro Auxiliar')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-display font-bold text-2xl">Libro Auxiliar por Cuenta</h1>
            <p class="text-slate-500 text-sm mt-0.5">Historial cronológico detallado de movimientos y saldos por cuenta contable</p>
        </div>
        @if($cuenta)
        <div class="flex items-center gap-2.5">
            <a href="{{ route('contabilidad.reportes.auxiliar.exportar', request()->query()) }}"
               class="inline-flex items-center gap-2 bg-[#1a2235] hover:bg-[#222f48] border border-[#1e2d47]
                      hover:border-emerald-500/50 text-emerald-400 font-semibold px-4 py-2.5 rounded-xl text-sm transition-colors">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </a>
        </div>
        @endif
    </div>

    {{-- Filtros: Cuenta y Fechas --}}
    <form method="GET" action="{{ route('contabilidad.reportes.auxiliar') }}"
          class="card p-4">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
            <div class="sm:col-span-2">
                <label class="block text-xs text-slate-500 mb-1 uppercase tracking-wider">Cuenta PUC a Consultar</label>
                <select name="cuenta_id" class="form-input text-sm" onchange="this.form.submit()">
                    @foreach($cuentas as $c)
                    <option value="{{ $c->id }}" @selected($c->id == $cuentaId)>
                        {{ $c->codigo }} - {{ $c->nombre }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1 uppercase tracking-wider">Fecha Desde</label>
                <input type="date" name="desde" value="{{ $desde }}" class="form-input text-sm">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1 uppercase tracking-wider">Fecha Hasta</label>
                <input type="date" name="hasta" value="{{ $hasta }}" class="form-input text-sm">
            </div>
        </div>
        <div class="flex gap-2 mt-3">
            <button type="submit"
                    class="bg-amber-500 hover:bg-amber-600 text-black font-bold text-sm px-4 py-2 rounded-xl transition-colors">
                <i class="fas fa-search mr-1"></i> Consultar
            </button>
            <a href="{{ route('contabilidad.reportes.auxiliar', ['cuenta_id' => $cuentaId]) }}"
               class="bg-[#1a2235] border border-[#1e2d47] text-slate-400 font-semibold text-sm px-4 py-2 rounded-xl hover:border-slate-500 transition-colors">
                Mes Actual
            </a>
        </div>
    </form>

    @if($cuenta)
    {{-- Resumen de la Cuenta --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="card p-4">
            <span class="text-xs text-slate-500 uppercase tracking-wider block">Saldo Anterior</span>
            <span class="font-mono font-bold text-lg text-slate-300">${{ number_format($saldo_anterior, 0, ',', '.') }}</span>
        </div>
        <div class="card p-4">
            <span class="text-xs text-slate-500 uppercase tracking-wider block">Débitos Periodo</span>
            <span class="font-mono font-bold text-lg text-amber-400">+${{ number_format($total_debito, 0, ',', '.') }}</span>
        </div>
        <div class="card p-4">
            <span class="text-xs text-slate-500 uppercase tracking-wider block">Créditos Periodo</span>
            <span class="font-mono font-bold text-lg text-amber-400">-${{ number_format($total_credito, 0, ',', '.') }}</span>
        </div>
        <div class="card p-4 bg-emerald-500/5 border-emerald-500/20">
            <span class="text-xs text-emerald-400/80 uppercase tracking-wider block font-semibold">Saldo Final</span>
            <span class="font-mono font-bold text-lg text-emerald-400">${{ number_format($saldo_final, 0, ',', '.') }}</span>
        </div>
    </div>

    {{-- Tabla de Movimientos --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-[#1e2d47] flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-200 text-sm font-mono">
                    <span class="text-amber-400">{{ $cuenta->codigo }}</span> · {{ $cuenta->nombre }}
                </h3>
                <p class="text-xs text-slate-500">Naturaleza: {{ strtoupper($cuenta->naturaleza) }} · Tipo: {{ ucfirst($cuenta->tipo) }}</p>
            </div>
            <span class="text-xs text-slate-400 font-mono">{{ count($movimientos) }} movimiento(s)</span>
        </div>

        @if(empty($movimientos))
        <div class="py-12 flex flex-col items-center text-slate-500">
            <i class="fas fa-folder-open text-3xl mb-2 opacity-20"></i>
            <p class="text-sm font-semibold">Sin movimientos registrados en este rango de fechas</p>
            <p class="text-xs text-slate-600 mt-1">El saldo se mantiene en ${{ number_format($saldo_anterior, 0, ',', '.') }}</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b border-[#1e2d47] bg-[#141c2e] text-slate-400 uppercase tracking-wider text-[11px]">
                        <th class="px-4 py-3 text-left">Fecha</th>
                        <th class="px-4 py-3 text-left">Comprobante</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Tipo</th>
                        <th class="px-4 py-3 text-left">Concepto / Detalle</th>
                        <th class="px-4 py-3 text-right">Débito</th>
                        <th class="px-4 py-3 text-right">Crédito</th>
                        <th class="px-4 py-3 text-right">Saldo Acumulado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1e2d47] font-mono">
                    {{-- Fila de Saldo Anterior --}}
                    <tr class="bg-[#141c2e]/40 text-slate-500 italic">
                        <td class="px-4 py-2 text-slate-500">{{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }}</td>
                        <td class="px-4 py-2 font-sans font-medium" colspan="3">SALDO ANTERIOR</td>
                        <td class="px-4 py-2 text-right">—</td>
                        <td class="px-4 py-2 text-right">—</td>
                        <td class="px-4 py-2 text-right font-bold text-slate-300">${{ number_format($saldo_anterior, 0, ',', '.') }}</td>
                    </tr>
                    @foreach($movimientos as $m)
                    <tr class="hover:bg-[#1a2235]/60 transition-colors">
                        <td class="px-4 py-2.5 text-slate-400">{{ $m['fecha']->format('d/m/Y') }}</td>
                        <td class="px-4 py-2.5">
                            <a href="{{ route('contabilidad.libro-diario.show', $m['asiento_id']) }}"
                               class="font-bold text-amber-400 hover:underline">
                                {{ $m['asiento_numero'] }}
                            </a>
                        </td>
                        <td class="px-4 py-2.5 hidden sm:table-cell font-sans">
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-[#141c2e] border border-[#1e2d47] text-slate-400">
                                {{ ucfirst($m['tipo']) }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 font-sans text-slate-200">{{ $m['concepto'] }}</td>
                        <td class="px-4 py-2.5 text-right text-slate-200">
                            {{ $m['debito'] > 0 ? '$'.number_format($m['debito'], 0, ',', '.') : '—' }}
                        </td>
                        <td class="px-4 py-2.5 text-right text-slate-200">
                            {{ $m['credito'] > 0 ? '$'.number_format($m['credito'], 0, ',', '.') : '—' }}
                        </td>
                        <td class="px-4 py-2.5 text-right font-bold text-emerald-400">
                            ${{ number_format($m['saldo'], 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t-2 border-[#1e2d47] bg-[#141c2e] font-mono font-bold text-xs">
                    <tr>
                        <td colspan="4" class="px-4 py-3 font-sans uppercase tracking-wider text-slate-300">
                            TOTALES MOVIMIENTOS
                        </td>
                        <td class="px-4 py-3 text-right text-amber-400">
                            ${{ number_format($total_debito, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right text-amber-400">
                            ${{ number_format($total_credito, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right text-emerald-400">
                            ${{ number_format($saldo_final, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
    </div>
    @endif

</div>
@endsection
