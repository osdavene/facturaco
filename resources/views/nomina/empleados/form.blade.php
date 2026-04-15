@extends('layouts.app')
@section('title', isset($empleado) ? 'Editar Empleado' : 'Nuevo Empleado')
@section('page-title', 'Nómina · ' . (isset($empleado) ? 'Editar Empleado' : 'Nuevo Empleado'))

@section('content')
<div class="max-w-4xl mx-auto pb-10">

    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('nomina.empleados.index') }}"
           class="w-9 h-9 bg-[#141c2e] border border-[#1e2d47] rounded-xl
                  flex items-center justify-center text-slate-400
                  hover:text-amber-500 hover:border-amber-500/50 transition-colors">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="font-display font-bold text-2xl">
                {{ isset($empleado) ? $empleado->nombre_completo : 'Nuevo Empleado' }}
            </h1>
            @if(isset($empleado))
            <p class="text-slate-500 text-sm">{{ $empleado->tipo_documento }}: {{ $empleado->numero_documento }}</p>
            @endif
        </div>
    </div>

    @if($errors->any())
    <div class="alert-error mb-5">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
    </div>
    @endif

    <form method="POST"
          action="{{ isset($empleado) ? route('nomina.empleados.update', $empleado) : route('nomina.empleados.store') }}">
        @csrf
        @if(isset($empleado)) @method('PUT') @endif

        {{-- DATOS PERSONALES --}}
        <div class="card p-5 mb-4">
            <h2 class="font-display font-bold text-sm mb-4 flex items-center gap-2">
                <i class="fas fa-user text-amber-500"></i> Datos Personales
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Nombres *</label>
                    <input type="text" name="nombres" value="{{ old('nombres', $empleado->nombres ?? '') }}"
                           class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Apellidos *</label>
                    <input type="text" name="apellidos" value="{{ old('apellidos', $empleado->apellidos ?? '') }}"
                           class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Tipo de Documento *</label>
                    <select name="tipo_documento" class="form-input" required>
                        @foreach(['CC'=>'Cédula de Ciudadanía','CE'=>'Cédula Extranjería','PA'=>'Pasaporte','PPT'=>'Permiso Protección Temporal','TI'=>'Tarjeta Identidad'] as $val=>$lbl)
                        <option value="{{ $val }}" {{ old('tipo_documento', $empleado->tipo_documento ?? 'CC') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Número de Documento *</label>
                    <input type="text" name="numero_documento"
                           value="{{ old('numero_documento', $empleado->numero_documento ?? '') }}"
                           class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Fecha de Nacimiento</label>
                    <input type="date" name="fecha_nacimiento"
                           value="{{ old('fecha_nacimiento', isset($empleado) ? $empleado->fecha_nacimiento?->format('Y-m-d') : '') }}"
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">Sexo</label>
                    <select name="sexo" class="form-input">
                        <option value="">— No especificar —</option>
                        <option value="M" {{ old('sexo', $empleado->sexo ?? '') === 'M' ? 'selected' : '' }}>Masculino</option>
                        <option value="F" {{ old('sexo', $empleado->sexo ?? '') === 'F' ? 'selected' : '' }}>Femenino</option>
                        <option value="O" {{ old('sexo', $empleado->sexo ?? '') === 'O' ? 'selected' : '' }}>Otro</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email"
                           value="{{ old('email', $empleado->email ?? '') }}"
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">Teléfono / Celular</label>
                    <input type="text" name="telefono"
                           value="{{ old('telefono', $empleado->telefono ?? '') }}"
                           class="form-input">
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Dirección</label>
                    <input type="text" name="direccion"
                           value="{{ old('direccion', $empleado->direccion ?? '') }}"
                           class="form-input">
                </div>
            </div>
        </div>

        {{-- DATOS LABORALES --}}
        <div class="card p-5 mb-4">
            <h2 class="font-display font-bold text-sm mb-4 flex items-center gap-2">
                <i class="fas fa-briefcase text-amber-500"></i> Datos Laborales
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Cargo *</label>
                    <input type="text" name="cargo"
                           value="{{ old('cargo', $empleado->cargo ?? '') }}"
                           class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Área / Departamento</label>
                    <input type="text" name="departamento"
                           value="{{ old('departamento', $empleado->departamento ?? '') }}"
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">Fecha de Ingreso *</label>
                    <input type="date" name="fecha_ingreso"
                           value="{{ old('fecha_ingreso', isset($empleado) ? $empleado->fecha_ingreso->format('Y-m-d') : '') }}"
                           class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Fecha de Retiro</label>
                    <input type="date" name="fecha_retiro"
                           value="{{ old('fecha_retiro', isset($empleado) ? $empleado->fecha_retiro?->format('Y-m-d') : '') }}"
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">Tipo de Contrato *</label>
                    <select name="tipo_contrato" class="form-input" required>
                        <option value="indefinido"           {{ old('tipo_contrato', $empleado->tipo_contrato ?? 'indefinido') === 'indefinido'           ? 'selected':'' }}>Término Indefinido</option>
                        <option value="fijo"                 {{ old('tipo_contrato', $empleado->tipo_contrato ?? '') === 'fijo'                 ? 'selected':'' }}>Término Fijo</option>
                        <option value="obra_labor"           {{ old('tipo_contrato', $empleado->tipo_contrato ?? '') === 'obra_labor'           ? 'selected':'' }}>Obra o Labor</option>
                        <option value="prestacion_servicios" {{ old('tipo_contrato', $empleado->tipo_contrato ?? '') === 'prestacion_servicios' ? 'selected':'' }}>Prestación de Servicios</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Tipo de Salario *</label>
                    <select name="tipo_salario" class="form-input" required>
                        <option value="ordinario" {{ old('tipo_salario', $empleado->tipo_salario ?? 'ordinario') === 'ordinario' ? 'selected':'' }}>Ordinario</option>
                        <option value="integral"  {{ old('tipo_salario', $empleado->tipo_salario ?? '') === 'integral'  ? 'selected':'' }}>Integral (≥ 10 SMMLV)</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Salario Base Mensual *</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm">$</span>
                        <input type="number" name="salario_base"
                               value="{{ old('salario_base', $empleado->salario_base ?? '') }}"
                               min="1" step="1"
                               class="form-input pl-7" required>
                    </div>
                    <p class="text-[10px] text-slate-600 mt-1">SMMLV 2025: $1.423.500</p>
                </div>
                <div>
                    <label class="form-label">Periodicidad de Pago *</label>
                    <select name="periodicidad_pago" class="form-input" required>
                        <option value="mensual"    {{ old('periodicidad_pago', $empleado->periodicidad_pago ?? 'mensual') === 'mensual'    ? 'selected':'' }}>Mensual</option>
                        <option value="quincenal"  {{ old('periodicidad_pago', $empleado->periodicidad_pago ?? '') === 'quincenal'  ? 'selected':'' }}>Quincenal</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Nivel de Riesgo ARL *</label>
                    <select name="nivel_riesgo_arl" class="form-input" required>
                        @foreach([1=>'Nivel I (0.522%)',2=>'Nivel II (1.044%)',3=>'Nivel III (2.436%)',4=>'Nivel IV (4.350%)',5=>'Nivel V (6.960%)'] as $n=>$lbl)
                        <option value="{{ $n }}" {{ old('nivel_riesgo_arl', $empleado->nivel_riesgo_arl ?? 1) == $n ? 'selected':'' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- SEGURIDAD SOCIAL --}}
        <div class="card p-5 mb-4">
            <h2 class="font-display font-bold text-sm mb-4 flex items-center gap-2">
                <i class="fas fa-shield-alt text-amber-500"></i> Seguridad Social
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">EPS</label>
                    <input type="text" name="eps"
                           value="{{ old('eps', $empleado->eps ?? '') }}"
                           placeholder="ej. Sura, Compensar..."
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">Fondo de Pensiones (AFP)</label>
                    <input type="text" name="afp"
                           value="{{ old('afp', $empleado->afp ?? '') }}"
                           placeholder="ej. Protección, Porvenir..."
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">Caja de Compensación</label>
                    <input type="text" name="caja_compensacion"
                           value="{{ old('caja_compensacion', $empleado->caja_compensacion ?? '') }}"
                           placeholder="ej. Compensar, Cafam..."
                           class="form-input">
                </div>
            </div>
        </div>

        {{-- DATOS BANCARIOS --}}
        <div class="card p-5 mb-4">
            <h2 class="font-display font-bold text-sm mb-4 flex items-center gap-2">
                <i class="fas fa-university text-amber-500"></i> Datos Bancarios
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Banco</label>
                    <input type="text" name="banco"
                           value="{{ old('banco', $empleado->banco ?? '') }}"
                           placeholder="ej. Bancolombia, Davivienda..."
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">Tipo de Cuenta</label>
                    <select name="tipo_cuenta" class="form-input">
                        <option value="">— Seleccionar —</option>
                        <option value="ahorros"   {{ old('tipo_cuenta', $empleado->tipo_cuenta ?? '') === 'ahorros'   ? 'selected':'' }}>Ahorros</option>
                        <option value="corriente" {{ old('tipo_cuenta', $empleado->tipo_cuenta ?? '') === 'corriente' ? 'selected':'' }}>Corriente</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Número de Cuenta</label>
                    <input type="text" name="numero_cuenta"
                           value="{{ old('numero_cuenta', $empleado->numero_cuenta ?? '') }}"
                           class="form-input">
                </div>
            </div>
        </div>

        {{-- OBSERVACIONES --}}
        <div class="card p-5 mb-6">
            <h2 class="font-display font-bold text-sm mb-3 flex items-center gap-2">
                <i class="fas fa-comment text-amber-500"></i> Observaciones
            </h2>
            <textarea name="observaciones" rows="2"
                      placeholder="Notas adicionales sobre el empleado..."
                      class="form-input resize-none">{{ old('observaciones', $empleado->observaciones ?? '') }}</textarea>

            @if(isset($empleado))
            <div class="mt-3 flex items-center gap-3">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="activo" value="0">
                    <input type="checkbox" name="activo" value="1"
                           {{ old('activo', $empleado->activo) ? 'checked':'' }}
                           class="w-4 h-4 rounded accent-amber-500">
                    <span class="text-sm text-slate-400">Empleado activo</span>
                </label>
            </div>
            @endif
        </div>

        {{-- Acciones --}}
        <div class="flex gap-3 justify-end">
            <a href="{{ route('nomina.empleados.index') }}"
               class="btn-secondary">
                Cancelar
            </a>
            <button type="submit" class="btn-primary">
                <i class="fas fa-save mr-1"></i>
                {{ isset($empleado) ? 'Guardar Cambios' : 'Registrar Empleado' }}
            </button>
        </div>
    </form>
</div>
@endsection
