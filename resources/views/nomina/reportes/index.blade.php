@extends('layouts.app')
@section('title', 'Informe de Nómina y PILA')
@section('page-title', 'Nómina · Informe y PILA')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-display font-bold text-2xl">Informe Consolidado de Nómina y PILA</h1>
            <p class="text-slate-500 text-sm mt-0.5">Resumen de costos laborales, aportes a seguridad social y provisiones de prestaciones</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('nomina.reportes.exportar', ['anio' => $anio]) }}"
               class="inline-flex items-center gap-2 bg-[#1a2235] hover:bg-[#222f48] border border-[#1e2d47]
                      hover:border-emerald-500/50 text-emerald-400 font-semibold px-4 py-2.5 rounded-xl text-sm transition-colors">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </a>
        </div>
    </div>

    {{-- Filtro de Año --}}
    <form method="GET" action="{{ route('nomina.reportes.index') }}" class="card p-4">
        <div class="flex items-center gap-3">
            <label class="text-xs text-slate-400 uppercase font-semibold">Año a Consultar:</label>
            <select name="anio" onchange="this.form.submit()" class="form-input text-sm w-36">
                @for($y = now()->year; $y >= now()->year - 4; $y--)
                <option value="{{ $y }}" @selected($anio == $y)>{{ $y }}</option>
                @endfor
            </select>
        </div>
    </form>

    {{-- Tarjetas Resumen Anual --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="card p-4">
            <span class="text-xs text-slate-500 uppercase tracking-wider block">Devengado Bruto</span>
            <span class="font-mono font-bold text-xl text-blue-400">${{ number_format($totales['devengado'], 0, ',', '.') }}</span>
        </div>
        <div class="card p-4">
            <span class="text-xs text-slate-500 uppercase tracking-wider block">Seguridad Social (PILA)</span>
            <span class="font-mono font-bold text-xl text-purple-400">${{ number_format($pila['total_pila'], 0, ',', '.') }}</span>
        </div>
        <div class="card p-4">
            <span class="text-xs text-slate-500 uppercase tracking-wider block">Provisiones Prestaciones</span>
            <span class="font-mono font-bold text-xl text-amber-400">${{ number_format($provisiones['total_provisiones'], 0, ',', '.') }}</span>
        </div>
        <div class="card p-4 bg-emerald-500/5 border-emerald-500/20">
            <span class="text-xs text-emerald-400 uppercase tracking-wider block font-semibold">Costo Total Empresa</span>
            <span class="font-mono font-bold text-xl text-emerald-400">${{ number_format($costoTotalEmpresa, 0, ',', '.') }}</span>
        </div>
    </div>

    {{-- Desglose PILA y Provisiones --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- PILA --}}
        <div class="card p-5 space-y-3">
            <div class="flex items-center justify-between border-b border-[#1e2d47] pb-3">
                <h3 class="font-bold text-slate-200 text-sm">Aportes a Seguridad Social y Parafiscales (PILA)</h3>
                <span class="badge bg-purple-500/10 text-purple-400 text-xs">Planilla Única</span>
            </div>
            <div class="space-y-2 text-sm font-mono">
                <div class="flex justify-between py-1.5 border-b border-[#1e2d47]/50 font-sans">
                    <span class="text-slate-400">Salud Total (Empresa + Empleado 12.5%):</span>
                    <span class="font-mono font-bold text-slate-200">${{ number_format($pila['salud_empresa'] + $pila['salud_empleado'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#1e2d47]/50 font-sans">
                    <span class="text-slate-400">Pensión Total (Empresa + Empleado 16%):</span>
                    <span class="font-mono font-bold text-slate-200">${{ number_format($pila['pension_empresa'] + $pila['pension_empleado'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#1e2d47]/50 font-sans">
                    <span class="text-slate-400">ARL (Riesgos Laborales):</span>
                    <span class="font-mono font-bold text-slate-200">${{ number_format($pila['arl'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#1e2d47]/50 font-sans">
                    <span class="text-slate-400">Caja de Compensación Familiar (4%):</span>
                    <span class="font-mono font-bold text-slate-200">${{ number_format($pila['caja'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-1.5 font-sans">
                    <span class="text-slate-400">SENA (2%) e ICBF (3%):</span>
                    <span class="font-mono font-bold text-slate-200">${{ number_format($pila['sena'] + $pila['icbf'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- Prestaciones --}}
        <div class="card p-5 space-y-3">
            <div class="flex items-center justify-between border-b border-[#1e2d47] pb-3">
                <h3 class="font-bold text-slate-200 text-sm">Provisiones Acumuladas de Prestaciones Sociales</h3>
                <span class="badge bg-amber-500/10 text-amber-400 text-xs">Pasivo Laboral</span>
            </div>
            <div class="space-y-2 text-sm font-mono">
                <div class="flex justify-between py-1.5 border-b border-[#1e2d47]/50 font-sans">
                    <span class="text-slate-400">Cesantías (8.33%):</span>
                    <span class="font-mono font-bold text-slate-200">${{ number_format($provisiones['cesantias'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#1e2d47]/50 font-sans">
                    <span class="text-slate-400">Intereses sobre Cesantías (1% mensual):</span>
                    <span class="font-mono font-bold text-slate-200">${{ number_format($provisiones['intereses_cesantias'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#1e2d47]/50 font-sans">
                    <span class="text-slate-400">Prima de Servicios (8.33%):</span>
                    <span class="font-mono font-bold text-slate-200">${{ number_format($provisiones['prima'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-1.5 font-sans">
                    <span class="text-slate-400">Vacaciones (4.17%):</span>
                    <span class="font-mono font-bold text-slate-200">${{ number_format($provisiones['vacaciones'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

    </div>

    {{-- Tabla de Períodos Liquidados --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-[#1e2d47]">
            <h3 class="font-bold text-slate-200 text-sm">Historial de Nóminas Procesadas en el Año {{ $anio }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-[#1e2d47] bg-[#141c2e] text-xs text-slate-400 uppercase tracking-wider">
                        <th class="px-5 py-3 text-left">Período</th>
                        <th class="px-4 py-3 text-center">Empleados</th>
                        <th class="px-4 py-3 text-right">Devengado</th>
                        <th class="px-4 py-3 text-right">Deducciones</th>
                        <th class="px-4 py-3 text-right">Neto Pagado</th>
                        <th class="px-4 py-3 text-right">Aportes Empresa</th>
                        <th class="px-5 py-3 text-center">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1e2d47] font-mono">
                    @forelse($nominas as $nom)
                    <tr class="hover:bg-[#1a2235]/60 transition-colors">
                        <td class="px-5 py-3 font-sans">
                            <a href="{{ route('nomina.show', $nom) }}" class="font-bold text-amber-400 hover:underline">
                                {{ $nom->nombre }}
                            </a>
                            <span class="block text-xs text-slate-500">{{ $nom->periodo_inicio->format('d/m/Y') }} al {{ $nom->periodo_fin->format('d/m/Y') }}</span>
                        </td>
                        <td class="px-4 py-3 text-center text-slate-300 font-sans">{{ $nom->liquidaciones->count() }}</td>
                        <td class="px-4 py-3 text-right text-slate-200">${{ number_format($nom->total_devengado, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-red-400">-${{ number_format($nom->total_deducciones, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-bold text-emerald-400">${{ number_format($nom->total_neto, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-slate-300">${{ number_format($nom->total_aportes_empleador, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-center font-sans">
                            <span class="badge bg-{{ $nom->estado_color }}-500/10 text-{{ $nom->estado_color }}-400 text-xs px-2.5 py-0.5">
                                {{ ucfirst($nom->estado) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500 font-sans text-sm">
                            No hay períodos de nómina procesados o pagados en el año {{ $anio }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
