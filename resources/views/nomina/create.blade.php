@extends('layouts.app')
@section('title', 'Nueva Nómina')
@section('page-title', 'Nómina · Nueva Liquidación')

@section('content')
<div class="max-w-3xl mx-auto pb-10">

    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('nomina.index') }}"
           class="w-9 h-9 bg-[#141c2e] border border-[#1e2d47] rounded-xl
                  flex items-center justify-center text-slate-400
                  hover:text-amber-500 hover:border-amber-500/50 transition-colors">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="font-display font-bold text-2xl">Nueva Nómina</h1>
            <p class="text-slate-500 text-sm">Define el período y selecciona los empleados</p>
        </div>
    </div>

    @if($errors->any())
    <div class="alert-error mb-5">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
    </div>
    @endif

    @if($empleados->isEmpty())
    <div class="card p-8 text-center text-slate-500">
        <i class="fas fa-users text-4xl mb-3 opacity-20 block"></i>
        <p class="font-semibold mb-2">No hay empleados activos</p>
        <a href="{{ route('nomina.empleados.create') }}"
           class="inline-flex items-center gap-2 bg-amber-500 text-black font-bold
                  px-5 py-2.5 rounded-xl transition-colors mt-2">
            <i class="fas fa-plus"></i> Registrar Empleado
        </a>
    </div>
    @else

    <form method="POST" action="{{ route('nomina.store') }}">
        @csrf

        {{-- Período --}}
        <div class="card p-5 mb-4">
            <h2 class="font-display font-bold text-sm mb-4 flex items-center gap-2">
                <i class="fas fa-calendar-alt text-amber-500"></i> Período de Nómina
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="form-label">Nombre del Período *</label>
                    <input type="text" name="nombre"
                           value="{{ old('nombre', $nombreSug) }}"
                           placeholder="ej. Nómina Mayo 2025"
                           class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Fecha Inicio *</label>
                    <input type="date" name="periodo_inicio"
                           value="{{ old('periodo_inicio', $periodoInicio) }}"
                           class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Fecha Fin *</label>
                    <input type="date" name="periodo_fin"
                           value="{{ old('periodo_fin', $periodoFin) }}"
                           class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Periodicidad *</label>
                    <select name="periodicidad" class="form-input" required>
                        <option value="mensual"   {{ old('periodicidad','mensual')  === 'mensual'   ? 'selected':'' }}>Mensual (30 días)</option>
                        <option value="quincenal" {{ old('periodicidad','mensual')  === 'quincenal' ? 'selected':'' }}>Quincenal (15 días)</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Fecha de Pago</label>
                    <input type="date" name="fecha_pago"
                           value="{{ old('fecha_pago') }}"
                           class="form-input">
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Observaciones</label>
                    <textarea name="observaciones" rows="2"
                              class="form-input resize-none"
                              placeholder="Notas del período...">{{ old('observaciones') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Selección de empleados --}}
        <div class="card p-5 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-display font-bold text-sm flex items-center gap-2">
                    <i class="fas fa-users text-amber-500"></i>
                    Empleados a Liquidar
                </h2>
                <button type="button" onclick="seleccionarTodos()"
                        class="text-xs text-amber-500 hover:text-amber-400 transition-colors">
                    Seleccionar todos
                </button>
            </div>

            @if($errors->has('empleados'))
            <p class="text-red-400 text-xs mb-3">{{ $errors->first('empleados') }}</p>
            @endif

            <div class="space-y-2 max-h-80 overflow-y-auto pr-1" id="lista-empleados">
                @foreach($empleados as $emp)
                <label class="flex items-center gap-3 bg-[#1a2235] border border-[#1e2d47] rounded-xl
                               px-4 py-3 cursor-pointer hover:border-amber-500/50 transition-colors
                               has-[:checked]:border-amber-500/60 has-[:checked]:bg-amber-500/5">
                    <input type="checkbox" name="empleados[]" value="{{ $emp->id }}"
                           {{ in_array($emp->id, old('empleados', [])) || true ? 'checked' : '' }}
                           class="w-4 h-4 accent-amber-500 flex-shrink-0">
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-sm text-slate-200">{{ $emp->nombre_completo }}</div>
                        <div class="text-xs text-slate-500">{{ $emp->cargo }}</div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <div class="text-sm font-bold text-amber-400">
                            ${{ number_format($emp->salario_base, 0, ',', '.') }}
                        </div>
                        <div class="text-[10px] text-slate-600 capitalize">{{ $emp->periodicidad_pago }}</div>
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        <div class="flex gap-3 justify-end">
            <a href="{{ route('nomina.index') }}" class="btn-secondary">Cancelar</a>
            <button type="submit" class="btn-primary">
                <i class="fas fa-calculator mr-1"></i> Calcular Nómina
            </button>
        </div>
    </form>
    @endif
</div>

<script>
function seleccionarTodos() {
    const checks = document.querySelectorAll('#lista-empleados input[type="checkbox"]');
    const todosChecked = [...checks].every(c => c.checked);
    checks.forEach(c => c.checked = !todosChecked);
}
</script>
@endsection
