@extends('layouts.app')
@section('title', $empleado->nombre_completo)
@section('page-title', 'Recursos Humanos · ' . $empleado->nombre_completo)

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-10">

    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('nomina.empleados.index') }}"
               class="w-9 h-9 bg-[#141c2e] border border-[#1e2d47] rounded-xl
                      flex items-center justify-center text-slate-400
                      hover:text-amber-500 hover:border-amber-500/50 transition-colors">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="font-display font-bold text-2xl">{{ $empleado->nombre_completo }}</h1>
                    @if($empleado->activo)
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            Activo
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-500/10 text-red-400 border border-red-500/20">
                            Inactivo / Retirado
                        </span>
                    @endif
                </div>
                <p class="text-slate-500 text-xs mt-0.5">
                    {{ $empleado->cargo }} &nbsp;·&nbsp; {{ $empleado->tipo_documento }} {{ $empleado->numero_documento }}
                    &nbsp;·&nbsp; Ingreso: {{ $empleado->fecha_ingreso ? $empleado->fecha_ingreso->format('d/m/Y') : '—' }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('nomina.certificados.index', ['empleado_id' => $empleado->id]) }}"
               class="inline-flex items-center gap-1.5 bg-[#1a2235] hover:bg-[#222f48] border border-[#1e2d47]
                      hover:border-amber-500/50 text-amber-400 font-semibold px-3.5 py-2 rounded-xl text-xs transition-colors">
                <i class="fas fa-file-signature"></i> Certificado
            </a>
            <a href="{{ route('nomina.liquidacion-definitiva.index', ['empleado_id' => $empleado->id]) }}"
               class="inline-flex items-center gap-1.5 bg-[#1a2235] hover:bg-[#222f48] border border-[#1e2d47]
                      hover:border-purple-500/50 text-purple-400 font-semibold px-3.5 py-2 rounded-xl text-xs transition-colors">
                <i class="fas fa-calculator"></i> Liquidación
            </a>
            <a href="{{ route('nomina.empleados.edit', $empleado) }}"
               class="inline-flex items-center gap-1.5 bg-amber-500 hover:bg-amber-600 text-black font-bold px-4 py-2 rounded-xl text-xs transition-colors shadow-lg shadow-amber-500/20">
                <i class="fas fa-edit"></i> Editar
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-xl px-5 py-3 flex items-center gap-3 text-sm">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    {{-- SECCIÓN 1: MANUAL DE FUNCIONES Y PERFIL DEL CARGO --}}
    <div class="card p-6 space-y-4 border-amber-500/20 bg-gradient-to-br from-[#111827] to-[#141c2e]">
        <div class="flex items-center justify-between border-b border-[#1e2d47] pb-3">
            <div class="flex items-center gap-2 text-amber-400 font-bold text-sm">
                <i class="fas fa-briefcase"></i>
                <span>Manual de Funciones y Perfil del Cargo</span>
            </div>
            <span class="text-xs text-slate-500">Área: {{ $empleado->departamento ?: 'Operaciones / General' }}</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
            <div class="bg-[#0f172a]/60 p-3 rounded-xl border border-[#1e2d47]">
                <span class="text-slate-500 uppercase tracking-wider block mb-1">Cargo Desempeñado</span>
                <span class="font-bold text-slate-200 text-sm">{{ $empleado->cargo }}</span>
            </div>
            <div class="bg-[#0f172a]/60 p-3 rounded-xl border border-[#1e2d47]">
                <span class="text-slate-500 uppercase tracking-wider block mb-1">Jefe Inmediato / Reporta a</span>
                <span class="font-bold text-slate-200 text-sm">{{ $empleado->jefe_inmediato ?: 'Gerencia General' }}</span>
            </div>
            <div class="bg-[#0f172a]/60 p-3 rounded-xl border border-[#1e2d47]">
                <span class="text-slate-500 uppercase tracking-wider block mb-1">Horario y Jornada Laboral</span>
                <span class="font-bold text-slate-200 text-sm">{{ $empleado->horario ?: 'Lunes a Viernes 8:00 AM - 5:00 PM' }}</span>
            </div>
        </div>

        <div>
            <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                Descripción de Funciones y Responsabilidades Principales:
            </h4>
            <div class="bg-[#0f172a]/80 rounded-xl p-4 border border-[#1e2d47] text-slate-300 text-xs leading-relaxed whitespace-pre-line font-sans">
                {{ $empleado->funciones ?: 'No se han registrado funciones detalladas para este puesto. Haz clic en "Editar" para redactar el manual de funciones y responsabilidades del empleado.' }}
            </div>
        </div>

        @if($empleado->habilidades_requisitos)
        <div>
            <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                Habilidades y Requisitos del Puesto:
            </h4>
            <div class="bg-[#0f172a]/80 rounded-xl p-3 border border-[#1e2d47] text-slate-400 text-xs leading-relaxed whitespace-pre-line">
                {{ $empleado->habilidades_requisitos }}
            </div>
        </div>
        @endif
    </div>

    {{-- SECCIÓN 2: DATOS CONTRACTUALES Y SEGURIDAD SOCIAL --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Condiciones Contractuales --}}
        <div class="card p-5 space-y-3">
            <h3 class="font-bold text-slate-200 text-sm border-b border-[#1e2d47] pb-2 flex items-center gap-2">
                <i class="fas fa-file-contract text-blue-400"></i> Condiciones Contractuales
            </h3>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between py-1 border-b border-[#1e2d47]/50">
                    <span class="text-slate-400">Tipo de Contrato:</span>
                    <span class="font-semibold text-slate-200 uppercase">{{ $empleado->tipo_contrato }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-[#1e2d47]/50">
                    <span class="text-slate-400">Tipo de Salario:</span>
                    <span class="font-semibold text-slate-200 capitalize">{{ $empleado->tipo_salario }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-[#1e2d47]/50">
                    <span class="text-slate-400">Salario Básico Mensual:</span>
                    <span class="font-mono font-bold text-amber-400">${{ number_format($empleado->salario_base, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-[#1e2d47]/50">
                    <span class="text-slate-400">Periodicidad de Pago:</span>
                    <span class="font-semibold text-slate-200 capitalize">{{ $empleado->periodicidad_pago }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-400">Nivel de Riesgo ARL:</span>
                    <span class="font-semibold text-slate-200">Clase {{ $empleado->nivel_riesgo_arl }}</span>
                </div>
            </div>
        </div>

        {{-- Seguridad Social y Banco --}}
        <div class="card p-5 space-y-3">
            <h3 class="font-bold text-slate-200 text-sm border-b border-[#1e2d47] pb-2 flex items-center gap-2">
                <i class="fas fa-heartbeat text-emerald-400"></i> Afiliaciones y Datos de Pago
            </h3>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between py-1 border-b border-[#1e2d47]/50">
                    <span class="text-slate-400">EPS (Salud):</span>
                    <span class="font-semibold text-slate-200">{{ $empleado->eps ?: 'No asignada' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-[#1e2d47]/50">
                    <span class="text-slate-400">Fondo de Pensión (AFP):</span>
                    <span class="font-semibold text-slate-200">{{ $empleado->afp ?: 'No asignado' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-[#1e2d47]/50">
                    <span class="text-slate-400">Caja de Compensación:</span>
                    <span class="font-semibold text-slate-200">{{ $empleado->caja_compensacion ?: 'No asignada' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-[#1e2d47]/50">
                    <span class="text-slate-400">Banco:</span>
                    <span class="font-semibold text-slate-200">{{ $empleado->banco ?: 'No registrado' }} ({{ ucfirst($empleado->tipo_cuenta ?? 'Ahorros') }})</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-400">Número de Cuenta:</span>
                    <span class="font-mono font-semibold text-slate-200">{{ $empleado->numero_cuenta ?: 'Sin cuenta registrada' }}</span>
                </div>
            </div>
        </div>

    </div>

    {{-- SECCIÓN 3: HISTORIAL DE LIQUIDACIONES DE NÓMINA --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-[#1e2d47] flex items-center justify-between">
            <h3 class="font-bold text-slate-200 text-sm flex items-center gap-2">
                <i class="fas fa-history text-amber-500"></i> Historial de Nóminas y Pagos
            </h3>
            <span class="text-xs text-slate-500">{{ $empleado->liquidaciones->count() }} liquidación(es)</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b border-[#1e2d47] bg-[#141c2e] text-slate-400 uppercase tracking-wider">
                        <th class="px-5 py-3 text-left">Período</th>
                        <th class="px-4 py-3 text-center">Días</th>
                        <th class="px-4 py-3 text-right">Devengado</th>
                        <th class="px-4 py-3 text-right">Deducciones</th>
                        <th class="px-4 py-3 text-right">Neto Pagado</th>
                        <th class="px-5 py-3 text-right">Colilla</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1e2d47] font-mono">
                    @forelse($empleado->liquidaciones as $liq)
                    <tr class="hover:bg-[#1a2235]/60 transition-colors">
                        <td class="px-5 py-3 font-sans font-medium text-slate-200">
                            {{ $liq->nomina->nombre ?? 'Nómina #'.$liq->nomina_id }}
                        </td>
                        <td class="px-4 py-3 text-center text-slate-400 font-sans">{{ $liq->dias_trabajados }}</td>
                        <td class="px-4 py-3 text-right text-slate-300">${{ number_format($liq->total_devengado, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-red-400">-${{ number_format($liq->total_deducciones, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-bold text-emerald-400">${{ number_format($liq->neto_pagar, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-right font-sans">
                            <a href="{{ route('nomina.colilla', [$liq->nomina_id, $liq->id]) }}" target="_blank"
                               class="text-amber-400 hover:underline">
                                <i class="fas fa-receipt mr-1"></i> Ver Colilla
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-slate-500 font-sans text-xs">
                            Aún no hay liquidaciones de nómina registradas para este empleado.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
