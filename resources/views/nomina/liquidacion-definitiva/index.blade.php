@extends('layouts.app')
@section('title', 'Liquidación Definitiva de Contrato')
@section('page-title', 'Nómina · Liquidación Definitiva')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-display font-bold text-2xl">Liquidación Definitiva de Contrato</h1>
            <p class="text-slate-500 text-sm mt-0.5">Calcula prestaciones sociales, vacaciones e indemnización al retiro de personal</p>
        </div>
        @if($calculo && $empleadoSeleccionado)
        <div>
            <form method="POST" action="{{ route('nomina.liquidacion-definitiva.imprimir') }}" target="_blank">
                @csrf
                <input type="hidden" name="empleado_id" value="{{ $empleadoSeleccionado->id }}">
                <input type="hidden" name="fecha_retiro" value="{{ $calculo['fecha_retiro']->format('Y-m-d') }}">
                <input type="hidden" name="motivo_retiro" value="{{ $calculo['motivo'] }}">
                <input type="hidden" name="dias_cesantias" value="{{ $calculo['dias_cesantias'] }}">
                <input type="hidden" name="dias_prima" value="{{ $calculo['dias_prima'] }}">
                <input type="hidden" name="dias_vacaciones" value="{{ $calculo['dias_vacaciones'] }}">
                <input type="hidden" name="salario_pendiente" value="{{ $calculo['salario_pendiente'] }}">
                <input type="hidden" name="otras_bonificaciones" value="{{ $calculo['bonificaciones'] }}">
                <input type="hidden" name="descuentos_prestamos" value="{{ $calculo['deducciones'] }}">

                <button type="submit"
                        class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-black font-bold px-4 py-2.5 rounded-xl text-sm transition-colors shadow-lg shadow-amber-500/20">
                    <i class="fas fa-print"></i> Imprimir Acta de Liquidación
                </button>
            </form>
        </div>
        @endif
    </div>

    {{-- Formulario de Selección y Parámetros --}}
    <form method="GET" action="{{ route('nomina.liquidacion-definitiva.index') }}" class="card p-5 space-y-4">
        <h3 class="font-bold text-slate-200 text-sm border-b border-[#1e2d47] pb-2">
            1. Selecciona el Empleado y Datos de Retiro
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="form-label">Empleado a Liquidar <span class="text-amber-500">*</span></label>
                <select name="empleado_id" required class="form-input text-sm" onchange="this.form.submit()">
                    <option value="">-- Seleccionar empleado --</option>
                    @foreach($empleados as $e)
                    <option value="{{ $e->id }}" @selected(request('empleado_id') == $e->id)>
                        {{ $e->apellidos }} {{ $e->nombres }} ({{ $e->numero_documento }})
                    </option>
                    @endforeach
                </select>
            </div>

            @if($empleadoSeleccionado)
            <div>
                <label class="form-label">Fecha de Retiro / Terminación</label>
                <input type="date" name="fecha_retiro"
                       value="{{ request('fecha_retiro', $empleadoSeleccionado->fecha_retiro ? $empleadoSeleccionado->fecha_retiro->format('Y-m-d') : today()->format('Y-m-d')) }}"
                       class="form-input text-sm">
            </div>
            <div>
                <label class="form-label">Motivo de Terminación</label>
                <select name="motivo_retiro" class="form-input text-sm">
                    <option value="renuncia" @selected(request('motivo_retiro') === 'renuncia')>Renuncia voluntaria del trabajador</option>
                    <option value="fin_contrato" @selected(request('motivo_retiro') === 'fin_contrato')>Terminación de contrato a término fijo</option>
                    <option value="despido_con_justa_causa" @selected(request('motivo_retiro') === 'despido_con_justa_causa')>Despido con justa causa (sin indemnización)</option>
                    <option value="despido_sin_justa_causa" @selected(request('motivo_retiro') === 'despido_sin_justa_causa')>Despido sin justa causa (con indemnización)</option>
                </select>
            </div>
            @endif
        </div>

        @if($empleadoSeleccionado)
        <div class="pt-2 flex justify-end">
            <button type="submit"
                    class="bg-amber-500 hover:bg-amber-600 text-black font-bold text-sm px-5 py-2.5 rounded-xl transition-colors">
                <i class="fas fa-calculator mr-1"></i> Calcular Liquidación
            </button>
        </div>
        @endif
    </form>

    @if($calculo && $empleadoSeleccionado)
    {{-- Resumen Informativo --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="card p-4">
            <span class="text-xs text-slate-500 uppercase tracking-wider block">Salario Básico</span>
            <span class="font-mono font-bold text-base text-slate-200">${{ number_format($calculo['salario_base'], 0, ',', '.') }}</span>
        </div>
        <div class="card p-4">
            <span class="text-xs text-slate-500 uppercase tracking-wider block">Auxilio Transporte</span>
            <span class="font-mono font-bold text-base text-slate-200">${{ number_format($calculo['auxilio_transporte'], 0, ',', '.') }}</span>
        </div>
        <div class="card p-4">
            <span class="text-xs text-slate-500 uppercase tracking-wider block">Base Prestaciones</span>
            <span class="font-mono font-bold text-base text-amber-400">${{ number_format($calculo['base_prestaciones'], 0, ',', '.') }}</span>
        </div>
        <div class="card p-4">
            <span class="text-xs text-slate-500 uppercase tracking-wider block">Tiempo Laborado</span>
            <span class="font-mono font-bold text-base text-blue-400">{{ $calculo['dias_totales'] }} días</span>
        </div>
    </div>

    {{-- Desglose Detallado de Prestaciones --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-[#1e2d47]">
            <h3 class="font-bold text-slate-200 text-sm">
                Desglose de Prestaciones Sociales y Conceptos Finales
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-[#1e2d47] bg-[#141c2e] text-xs text-slate-400 uppercase tracking-wider">
                        <th class="px-5 py-3 text-left">Concepto</th>
                        <th class="px-4 py-3 text-left">Base de Cálculo</th>
                        <th class="px-4 py-3 text-center">Días Liquidados</th>
                        <th class="px-5 py-3 text-right">Valor Total (COP)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1e2d47] font-mono">
                    {{-- Cesantías --}}
                    <tr class="hover:bg-[#1a2235]/60 transition-colors">
                        <td class="px-5 py-3 font-sans">
                            <span class="font-bold text-slate-200 block">Cesantías</span>
                            <span class="text-xs text-slate-500 font-sans">(Base × Días) / 360</span>
                        </td>
                        <td class="px-4 py-3 text-slate-300">${{ number_format($calculo['base_prestaciones'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center text-slate-300 font-sans">{{ $calculo['dias_cesantias'] }} días</td>
                        <td class="px-5 py-3 text-right font-bold text-emerald-400">${{ number_format($calculo['valor_cesantias'], 0, ',', '.') }}</td>
                    </tr>

                    {{-- Intereses sobre Cesantías --}}
                    <tr class="hover:bg-[#1a2235]/60 transition-colors">
                        <td class="px-5 py-3 font-sans">
                            <span class="font-bold text-slate-200 block">Intereses sobre Cesantías</span>
                            <span class="text-xs text-slate-500 font-sans">(Cesantías × Días × 12%) / 360</span>
                        </td>
                        <td class="px-4 py-3 text-slate-300">${{ number_format($calculo['valor_cesantias'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center text-slate-300 font-sans">{{ $calculo['dias_cesantias'] }} días</td>
                        <td class="px-5 py-3 text-right font-bold text-emerald-400">${{ number_format($calculo['valor_intereses'], 0, ',', '.') }}</td>
                    </tr>

                    {{-- Prima de Servicios --}}
                    <tr class="hover:bg-[#1a2235]/60 transition-colors">
                        <td class="px-5 py-3 font-sans">
                            <span class="font-bold text-slate-200 block">Prima de Servicios</span>
                            <span class="text-xs text-slate-500 font-sans">(Base × Días Semestre) / 360</span>
                        </td>
                        <td class="px-4 py-3 text-slate-300">${{ number_format($calculo['base_prestaciones'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center text-slate-300 font-sans">{{ $calculo['dias_prima'] }} días</td>
                        <td class="px-5 py-3 text-right font-bold text-emerald-400">${{ number_format($calculo['valor_prima'], 0, ',', '.') }}</td>
                    </tr>

                    {{-- Vacaciones Compensadas --}}
                    <tr class="hover:bg-[#1a2235]/60 transition-colors">
                        <td class="px-5 py-3 font-sans">
                            <span class="font-bold text-slate-200 block">Vacaciones Compensadas</span>
                            <span class="text-xs text-slate-500 font-sans">(Salario Básico / 30) × Días pendientes</span>
                        </td>
                        <td class="px-4 py-3 text-slate-300">${{ number_format($calculo['salario_base'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center text-slate-300 font-sans">{{ $calculo['dias_vacaciones'] }} días</td>
                        <td class="px-5 py-3 text-right font-bold text-emerald-400">${{ number_format($calculo['valor_vacaciones'], 0, ',', '.') }}</td>
                    </tr>

                    @if($calculo['valor_indemnizacion'] > 0)
                    {{-- Indemnización --}}
                    <tr class="hover:bg-[#1a2235]/60 transition-colors bg-amber-500/5">
                        <td class="px-5 py-3 font-sans">
                            <span class="font-bold text-amber-400 block">Indemnización por Despido (Art. 64 CST)</span>
                            <span class="text-xs text-amber-400/80 font-sans">Terminación de contrato sin justa causa</span>
                        </td>
                        <td class="px-4 py-3 text-slate-300">${{ number_format($calculo['salario_base'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center text-slate-300 font-sans">—</td>
                        <td class="px-5 py-3 text-right font-bold text-amber-400">${{ number_format($calculo['valor_indemnizacion'], 0, ',', '.') }}</td>
                    </tr>
                    @endif
                </tbody>
                <tfoot class="border-t-2 border-[#1e2d47] bg-[#141c2e] font-mono">
                    <tr>
                        <td colspan="3" class="px-5 py-3 font-sans font-bold text-slate-200 uppercase tracking-wider text-right">
                            TOTAL LIQUIDACIÓN A PAGAR:
                        </td>
                        <td class="px-5 py-3 text-right font-bold text-amber-400 text-lg">
                            ${{ number_format($calculo['neto_pagar'], 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
