@extends('layouts.app')
@section('title', $nomina->nombre)
@section('page-title', 'Nómina · ' . $nomina->nombre)

@section('content')

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-6">
    <div class="flex items-start gap-4">
        <a href="{{ route('nomina.index') }}"
           class="w-9 h-9 bg-[#141c2e] border border-[#1e2d47] rounded-xl flex-shrink-0
                  flex items-center justify-center text-slate-400
                  hover:text-amber-500 hover:border-amber-500/50 transition-colors mt-0.5">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="font-display font-bold text-2xl">{{ $nomina->nombre }}</h1>
            <p class="text-slate-500 text-sm mt-1">
                {{ $nomina->periodo_inicio->format('d/m/Y') }} — {{ $nomina->periodo_fin->format('d/m/Y') }}
                &nbsp;·&nbsp;
                <span class="capitalize">{{ $nomina->periodicidad }}</span>
            </p>
        </div>
    </div>

    <div class="flex items-center gap-2 flex-wrap">
        <span class="badge bg-{{ $nomina->estado_color }}-500/10 text-{{ $nomina->estado_color }}-{{ in_array($nomina->estado_color,['slate']) ? '400':'500' }} text-sm px-3 py-1">
            {{ $nomina->estado_label }}
        </span>

        @if($nomina->estado === 'borrador')
        <form method="POST" action="{{ route('nomina.procesar', $nomina) }}">
            @csrf
            <button type="submit"
                    onclick="return confirm('¿Procesar la nómina? Esto la marca como procesada.')"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500
                           text-white font-semibold px-4 py-2 rounded-xl transition-colors text-sm">
                <i class="fas fa-check-circle"></i> Procesar
            </button>
        </form>
        @endif

        @if($nomina->estado === 'procesada')
        <form method="POST" action="{{ route('nomina.pagar', $nomina) }}">
            @csrf
            <button type="submit"
                    onclick="return confirm('¿Marcar como pagada?')"
                    class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500
                           text-white font-semibold px-4 py-2 rounded-xl transition-colors text-sm">
                <i class="fas fa-money-check-alt"></i> Marcar Pagada
            </button>
        </form>
        @endif

        @if($nomina->estado !== 'pagada')
        <form method="POST" action="{{ route('nomina.anular', $nomina) }}">
            @csrf
            <button type="submit"
                    onclick="return confirm('¿Anular esta nómina?')"
                    class="inline-flex items-center gap-2 border border-red-500/30 text-red-400
                           hover:bg-red-500/10 px-4 py-2 rounded-xl transition-colors text-sm">
                <i class="fas fa-ban"></i> Anular
            </button>
        </form>
        @endif
    </div>
</div>

@if(session('success'))
<div class="alert-success mb-4"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
@endif

{{-- KPIs --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
    <div class="card p-4">
        <div class="text-xs text-slate-500 uppercase tracking-wider mb-1">Empleados</div>
        <div class="font-display font-bold text-2xl">{{ $nomina->liquidaciones->count() }}</div>
    </div>
    <div class="card p-4">
        <div class="text-xs text-slate-500 uppercase tracking-wider mb-1">Total Devengado</div>
        <div class="font-display font-bold text-xl text-blue-400">
            ${{ number_format($nomina->total_devengado, 0, ',', '.') }}
        </div>
    </div>
    <div class="card p-4">
        <div class="text-xs text-slate-500 uppercase tracking-wider mb-1">Deducciones</div>
        <div class="font-display font-bold text-xl text-red-400">
            ${{ number_format($nomina->total_deducciones, 0, ',', '.') }}
        </div>
    </div>
    <div class="card p-4">
        <div class="text-xs text-slate-500 uppercase tracking-wider mb-1">Neto a Pagar</div>
        <div class="font-display font-bold text-xl text-amber-400">
            ${{ number_format($nomina->total_neto, 0, ',', '.') }}
        </div>
    </div>
</div>

{{-- Tabla de liquidaciones --}}
<div class="card overflow-hidden mb-6">
    <div class="px-5 py-3 border-b border-[#1e2d47] flex items-center justify-between">
        <h2 class="font-semibold text-sm">Detalle por Empleado</h2>
        <span class="text-xs text-slate-500">{{ $nomina->liquidaciones->count() }} empleado(s)</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-[#1e2d47] text-xs text-slate-500 uppercase tracking-wider">
                    <th class="px-5 py-3 text-left">Empleado</th>
                    <th class="px-4 py-3 text-center hidden md:table-cell">Días</th>
                    <th class="px-4 py-3 text-right hidden sm:table-cell">Salario</th>
                    <th class="px-4 py-3 text-right hidden lg:table-cell">Auxilio Transp.</th>
                    <th class="px-4 py-3 text-right hidden md:table-cell">Devengado</th>
                    <th class="px-4 py-3 text-right hidden sm:table-cell">Deducciones</th>
                    <th class="px-4 py-3 text-right font-bold">Neto</th>
                    <th class="px-5 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($nomina->liquidaciones as $liq)
                <tr class="table-row">
                    <td class="px-5 py-4">
                        <div class="font-semibold text-slate-200">{{ $liq->empleado->nombre_completo }}</div>
                        <div class="text-xs text-slate-500">{{ $liq->empleado->cargo }}</div>
                    </td>
                    <td class="px-4 py-4 text-center hidden md:table-cell text-slate-400">
                        {{ $liq->dias_trabajados }}
                    </td>
                    <td class="px-4 py-4 text-right hidden sm:table-cell text-slate-300">
                        ${{ number_format($liq->salario_basico, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-4 text-right hidden lg:table-cell text-slate-400">
                        ${{ number_format($liq->auxilio_transporte, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-4 text-right hidden md:table-cell text-blue-400">
                        ${{ number_format($liq->total_devengado, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-4 text-right hidden sm:table-cell text-red-400">
                        -${{ number_format($liq->total_deducciones, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-4 text-right font-bold text-amber-400">
                        ${{ number_format($liq->neto_pagar, 0, ',', '.') }}
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            {{-- Colilla --}}
                            <a href="{{ route('nomina.colilla', [$nomina, $liq]) }}" target="_blank"
                               title="Ver colilla"
                               class="btn-icon hover:text-emerald-400 hover:border-emerald-500/50">
                                <i class="fas fa-receipt text-xs"></i>
                            </a>
                            {{-- Editar novedades --}}
                            @if($nomina->estado === 'borrador')
                            <button onclick="abrirEditar({{ $liq->id }}, {{ json_encode($liq) }})"
                                    title="Editar novedades"
                                    class="btn-icon hover:text-amber-400 hover:border-amber-500/50">
                                <i class="fas fa-edit text-xs"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-[#1e2d47] bg-[#141c2e]">
                    <td class="px-5 py-3 font-bold text-slate-300" colspan="2">TOTALES</td>
                    <td class="px-4 py-3 text-right hidden sm:table-cell text-slate-300 font-bold">
                        ${{ number_format($nomina->liquidaciones->sum('salario_basico'), 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-right hidden lg:table-cell text-slate-400 font-bold">
                        ${{ number_format($nomina->liquidaciones->sum('auxilio_transporte'), 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-right hidden md:table-cell text-blue-400 font-bold">
                        ${{ number_format($nomina->total_devengado, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-right hidden sm:table-cell text-red-400 font-bold">
                        -${{ number_format($nomina->total_deducciones, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-right font-black text-amber-400 text-base">
                        ${{ number_format($nomina->total_neto, 0, ',', '.') }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- Resumen aportes empleador --}}
<div class="card p-5">
    <h2 class="font-display font-bold text-sm mb-4 flex items-center gap-2">
        <i class="fas fa-building text-amber-500"></i> Aportes a Cargo del Empleador
    </h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        @php
            $totalSalud   = $nomina->liquidaciones->sum('aporte_salud_empleador');
            $totalPension = $nomina->liquidaciones->sum('aporte_pension_empleador');
            $totalArl     = $nomina->liquidaciones->sum('aporte_arl');
            $totalCaja    = $nomina->liquidaciones->sum('aporte_caja_compensacion');
            $totalSena    = $nomina->liquidaciones->sum('aporte_sena');
            $totalIcbf    = $nomina->liquidaciones->sum('aporte_icbf');
            $totalApEmp   = $nomina->total_aportes_empleador;
        @endphp
        @foreach([
            ['Salud (8.5%)',    $totalSalud,   'blue'],
            ['Pensión (12%)',   $totalPension, 'purple'],
            ['ARL',            $totalArl,     'amber'],
            ['Caja Comp (4%)', $totalCaja,    'cyan'],
            ['SENA (2%)',      $totalSena,    'emerald'],
            ['ICBF (3%)',      $totalIcbf,    'orange'],
        ] as [$label, $valor, $color])
        <div class="bg-[#1a2235] border border-[#1e2d47] rounded-xl p-3 text-center">
            <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">{{ $label }}</div>
            <div class="font-bold text-{{ $color }}-400 text-sm">
                ${{ number_format($valor, 0, ',', '.') }}
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-3 pt-3 border-t border-[#1e2d47] flex justify-between items-center">
        <span class="text-sm text-slate-400">Total aportes empleador:</span>
        <span class="font-black text-white text-base">
            ${{ number_format($totalApEmp, 0, ',', '.') }}
        </span>
    </div>
</div>

{{-- Modal editar novedades --}}
<div id="modal-editar" class="hidden fixed inset-0 bg-black/70 z-50 flex items-start justify-center p-4 overflow-y-auto">
    <div class="bg-[#111827] border border-[#1e2d47] rounded-2xl w-full max-w-lg shadow-2xl my-6">
        <div class="flex items-center justify-between px-6 py-4 border-b border-[#1e2d47]">
            <h3 class="font-display font-bold text-base" id="modal-emp-nombre">Editar Novedades</h3>
            <button onclick="cerrarEditar()" class="text-slate-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="form-novedades" method="POST" class="p-6 space-y-4">
            @csrf @method('PATCH')

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="form-label">Días Trabajados</label>
                    <input type="number" name="dias_trabajados" step="0.5" min="0" max="31" class="form-input" value="30">
                </div>
                <div>
                    <label class="form-label">Días Vacaciones</label>
                    <input type="number" name="dias_vacaciones" step="0.5" min="0" class="form-input" value="0">
                </div>
                <div>
                    <label class="form-label">Días Incapacidad</label>
                    <input type="number" name="dias_incapacidad" step="0.5" min="0" class="form-input" value="0">
                </div>
                <div>
                    <label class="form-label">Días Lic. Remunerada</label>
                    <input type="number" name="dias_licencia_remunerada" step="0.5" min="0" class="form-input" value="0">
                </div>
            </div>

            <div class="border-t border-[#1e2d47] pt-4">
                <p class="text-xs text-slate-500 uppercase tracking-wider mb-3">Horas Extras</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">H.E. Diurnas (+25%)</label>
                        <input type="number" name="horas_extras_diurnas" step="0.5" min="0" class="form-input" value="0">
                    </div>
                    <div>
                        <label class="form-label">H.E. Nocturnas (+75%)</label>
                        <input type="number" name="horas_extras_nocturnas" step="0.5" min="0" class="form-input" value="0">
                    </div>
                    <div>
                        <label class="form-label">H.E. Fest. Diurnas (+100%)</label>
                        <input type="number" name="horas_extras_fest_diurnas" step="0.5" min="0" class="form-input" value="0">
                    </div>
                    <div>
                        <label class="form-label">H.E. Fest. Noct. (+150%)</label>
                        <input type="number" name="horas_extras_fest_nocturnas" step="0.5" min="0" class="form-input" value="0">
                    </div>
                    <div>
                        <label class="form-label">Recargo Noct. (+35%)</label>
                        <input type="number" name="horas_recargo_nocturno" step="0.5" min="0" class="form-input" value="0">
                    </div>
                </div>
            </div>

            <div class="border-t border-[#1e2d47] pt-4">
                <p class="text-xs text-slate-500 uppercase tracking-wider mb-3">Otros Conceptos</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Comisiones</label>
                        <input type="number" name="comisiones" step="1" min="0" class="form-input" value="0">
                    </div>
                    <div>
                        <label class="form-label">Bonificaciones</label>
                        <input type="number" name="bonificaciones" step="1" min="0" class="form-input" value="0">
                    </div>
                    <div>
                        <label class="form-label">Otros Devengados</label>
                        <input type="number" name="otros_devengados" step="1" min="0" class="form-input" value="0">
                    </div>
                    <div>
                        <label class="form-label">Retención en Fuente</label>
                        <input type="number" name="retencion_fuente" step="1" min="0" class="form-input" value="0">
                    </div>
                    <div>
                        <label class="form-label">Otras Deducciones</label>
                        <input type="number" name="otras_deducciones" step="1" min="0" class="form-input" value="0">
                    </div>
                </div>
            </div>

            <div class="border-t border-[#1e2d47] pt-4">
                <label class="form-label">Observaciones</label>
                <textarea name="observaciones" rows="2" class="form-input resize-none"></textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="cerrarEditar()"
                        class="flex-1 py-2.5 bg-[#1a2235] border border-[#1e2d47] rounded-xl
                               text-sm text-slate-400 hover:border-slate-500 transition-colors">
                    Cancelar
                </button>
                <button type="submit"
                        class="flex-1 py-2.5 bg-amber-500 hover:bg-amber-400
                               rounded-xl text-sm font-bold text-black transition-colors">
                    <i class="fas fa-calculator mr-1"></i> Recalcular
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirEditar(liqId, liq) {
    document.getElementById('modal-emp-nombre').textContent = 'Editar: ' + (liq.empleado?.nombre_completo || '');
    const form = document.getElementById('form-novedades');
    form.action = `/nomina/{{ $nomina->id }}/liquidacion/${liqId}`;

    // Rellenar campos
    const campos = [
        'dias_trabajados','dias_vacaciones','dias_incapacidad','dias_licencia_remunerada',
        'horas_extras_diurnas','horas_extras_nocturnas','horas_extras_fest_diurnas',
        'horas_extras_fest_nocturnas','horas_recargo_nocturno',
        'comisiones','bonificaciones','otros_devengados','retencion_fuente','otras_deducciones',
        'observaciones'
    ];
    campos.forEach(c => {
        const el = form.querySelector(`[name="${c}"]`);
        if (el) el.value = liq[c] ?? 0;
    });
    form.querySelector('[name="observaciones"]').value = liq.observaciones ?? '';

    document.getElementById('modal-editar').classList.remove('hidden');
}

function cerrarEditar() {
    document.getElementById('modal-editar').classList.add('hidden');
}

document.getElementById('modal-editar').addEventListener('click', e => {
    if (e.target === document.getElementById('modal-editar')) cerrarEditar();
});
</script>

@endsection
