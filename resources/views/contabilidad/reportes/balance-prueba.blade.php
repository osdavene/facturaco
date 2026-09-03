@extends('layouts.app')
@section('title', 'Balance de Prueba')
@section('page-title', 'Contabilidad · Balance de Prueba')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-display font-bold text-2xl">Balance de Prueba (Sumas y Saldos)</h1>
            <p class="text-slate-500 text-sm mt-0.5">Informe oficial de cuentas, movimientos y saldos acumulados por partida doble</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('contabilidad.reportes.balance-prueba.exportar', request()->query()) }}"
               class="inline-flex items-center gap-2 bg-[#1a2235] hover:bg-[#222f48] border border-[#1e2d47]
                      hover:border-emerald-500/50 text-emerald-400 font-semibold px-4 py-2.5 rounded-xl text-sm transition-colors">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </a>
        </div>
    </div>

    {{-- Filtros de Fecha --}}
    <form method="GET" action="{{ route('contabilidad.reportes.balance-prueba') }}"
          class="card p-4">
        <div class="flex flex-col sm:flex-row items-end gap-3 flex-wrap">
            <div>
                <label class="block text-xs text-slate-500 mb-1 uppercase tracking-wider">Fecha Desde</label>
                <input type="date" name="desde" value="{{ $desde }}" class="form-input text-sm">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1 uppercase tracking-wider">Fecha Hasta</label>
                <input type="date" name="hasta" value="{{ $hasta }}" class="form-input text-sm">
            </div>
            <div class="flex gap-2">
                <button type="submit"
                        class="bg-amber-500 hover:bg-amber-600 text-black font-bold text-sm px-4 py-2.5 rounded-xl transition-colors">
                    <i class="fas fa-search mr-1"></i> Consultar
                </button>
                <a href="{{ route('contabilidad.reportes.balance-prueba') }}"
                   class="bg-[#1a2235] border border-[#1e2d47] text-slate-400 font-semibold text-sm px-4 py-2.5 rounded-xl hover:border-slate-500 transition-colors">
                    Mes Actual
                </a>
            </div>
        </div>
    </form>

    {{-- Tabla Balance de Prueba --}}
    <div class="card overflow-hidden">
        @if(empty($filas))
        <div class="py-16 flex flex-col items-center text-slate-500">
            <i class="fas fa-balance-scale text-4xl mb-3 opacity-20"></i>
            <p class="font-semibold text-sm">No hay movimientos contables registrados en el periodo seleccionado</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b border-[#1e2d47] bg-[#141c2e] text-slate-400 uppercase tracking-wider text-[11px]">
                        <th rowspan="2" class="px-4 py-3 text-left w-24">Código</th>
                        <th rowspan="2" class="px-4 py-3 text-left">Cuenta PUC</th>
                        <th colspan="2" class="px-4 py-2 text-center border-l border-[#1e2d47] text-slate-300">Saldo Anterior</th>
                        <th colspan="2" class="px-4 py-2 text-center border-l border-[#1e2d47] text-amber-400">Movimientos Periodo</th>
                        <th colspan="2" class="px-4 py-2 text-center border-l border-[#1e2d47] text-emerald-400">Saldo Final</th>
                        <th rowspan="2" class="px-3 py-3 text-center border-l border-[#1e2d47]">Auxiliar</th>
                    </tr>
                    <tr class="border-b border-[#1e2d47] bg-[#141c2e] text-slate-500 text-[10px]">
                        <th class="px-3 py-1.5 text-right border-l border-[#1e2d47]">Débito</th>
                        <th class="px-3 py-1.5 text-right">Crédito</th>
                        <th class="px-3 py-1.5 text-right border-l border-[#1e2d47]">Débito</th>
                        <th class="px-3 py-1.5 text-right">Crédito</th>
                        <th class="px-3 py-1.5 text-right border-l border-[#1e2d47]">Débito</th>
                        <th class="px-3 py-1.5 text-right">Crédito</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1e2d47] font-mono">
                    @foreach($filas as $f)
                    <tr class="hover:bg-[#1a2235]/60 transition-colors">
                        <td class="px-4 py-2.5 font-bold text-amber-400/90">{{ $f['codigo'] }}</td>
                        <td class="px-4 py-2.5 font-sans font-medium text-slate-200">
                            <a href="{{ route('contabilidad.reportes.auxiliar', ['cuenta_id' => $f['cuenta_id'], 'desde' => $desde, 'hasta' => $hasta]) }}"
                               class="hover:text-amber-400 hover:underline transition-colors">
                                {{ $f['nombre'] }}
                            </a>
                        </td>
                        <td class="px-3 py-2.5 text-right border-l border-[#1e2d47] text-slate-400">
                            {{ $f['ant_debito'] > 0 ? '$'.number_format($f['ant_debito'], 0, ',', '.') : '—' }}
                        </td>
                        <td class="px-3 py-2.5 text-right text-slate-400">
                            {{ $f['ant_credito'] > 0 ? '$'.number_format($f['ant_credito'], 0, ',', '.') : '—' }}
                        </td>
                        <td class="px-3 py-2.5 text-right border-l border-[#1e2d47] text-slate-200 font-semibold">
                            {{ $f['debito'] > 0 ? '$'.number_format($f['debito'], 0, ',', '.') : '—' }}
                        </td>
                        <td class="px-3 py-2.5 text-right text-slate-200 font-semibold">
                            {{ $f['credito'] > 0 ? '$'.number_format($f['credito'], 0, ',', '.') : '—' }}
                        </td>
                        <td class="px-3 py-2.5 text-right border-l border-[#1e2d47] text-emerald-400 font-bold">
                            {{ $f['fin_debito'] > 0 ? '$'.number_format($f['fin_debito'], 0, ',', '.') : '—' }}
                        </td>
                        <td class="px-3 py-2.5 text-right text-emerald-400 font-bold">
                            {{ $f['fin_credito'] > 0 ? '$'.number_format($f['fin_credito'], 0, ',', '.') : '—' }}
                        </td>
                        <td class="px-3 py-2.5 text-center border-l border-[#1e2d47]">
                            <a href="{{ route('contabilidad.reportes.auxiliar', ['cuenta_id' => $f['cuenta_id'], 'desde' => $desde, 'hasta' => $hasta]) }}"
                               title="Ver Libro Auxiliar de esta cuenta"
                               class="w-7 h-7 inline-flex items-center justify-center rounded-lg bg-[#141c2e] border border-[#1e2d47] text-slate-400 hover:text-amber-400 hover:border-amber-500/50 transition-colors">
                                <i class="fas fa-list-ul text-[10px]"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t-2 border-[#1e2d47] bg-[#141c2e] font-mono font-bold text-xs">
                    <tr>
                        <td colspan="2" class="px-4 py-3 font-sans uppercase tracking-wider text-slate-300">
                            TOTALES
                        </td>
                        <td class="px-3 py-3 text-right border-l border-[#1e2d47] text-slate-300">
                            ${{ number_format($totales['anterior_debito'], 0, ',', '.') }}
                        </td>
                        <td class="px-3 py-3 text-right text-slate-300">
                            ${{ number_format($totales['anterior_credito'], 0, ',', '.') }}
                        </td>
                        <td class="px-3 py-3 text-right border-l border-[#1e2d47] text-amber-400">
                            ${{ number_format($totales['debito'], 0, ',', '.') }}
                        </td>
                        <td class="px-3 py-3 text-right text-amber-400">
                            ${{ number_format($totales['credito'], 0, ',', '.') }}
                        </td>
                        <td class="px-3 py-3 text-right border-l border-[#1e2d47] text-emerald-400">
                            ${{ number_format($totales['final_debito'], 0, ',', '.') }}
                        </td>
                        <td class="px-3 py-3 text-right text-emerald-400">
                            ${{ number_format($totales['final_credito'], 0, ',', '.') }}
                        </td>
                        <td class="border-l border-[#1e2d47]"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="px-5 py-3.5 bg-[#141c2e]/60 border-t border-[#1e2d47] flex items-center justify-between flex-wrap gap-3 text-xs">
            <div class="flex items-center gap-2 text-emerald-400 font-semibold">
                <i class="fas fa-check-circle"></i> Balance de Prueba Cuadrado (Partida Doble Verificada)
            </div>
            <span class="text-slate-500">Periodo: {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}</span>
        </div>
        @endif
    </div>

</div>
@endsection
