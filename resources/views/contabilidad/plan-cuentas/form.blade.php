@extends('layouts.app')
@section('title', isset($cuenta) ? 'Editar Cuenta' : 'Nueva Cuenta')
@section('page-title', 'Plan de Cuentas')

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('contabilidad.plan-cuentas.index') }}"
           class="w-9 h-9 bg-[#141c2e] border border-[#1e2d47] rounded-xl
                  flex items-center justify-center text-slate-400
                  hover:text-amber-500 hover:border-amber-500/50 transition-colors">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="font-display font-bold text-2xl">
                {{ isset($cuenta) ? 'Editar Cuenta' : 'Nueva Cuenta' }}
            </h1>
            <p class="text-slate-500 text-sm">
                {{ isset($cuenta) ? $cuenta->codigo . ' — ' . $cuenta->nombre : 'Agregar al plan de cuentas' }}
            </p>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl p-4 mb-5 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST"
          action="{{ isset($cuenta) ? route('contabilidad.plan-cuentas.update', $cuenta) : route('contabilidad.plan-cuentas.store') }}"
          class="card p-6 space-y-5">
        @csrf
        @if(isset($cuenta)) @method('PUT') @endif

        {{-- Código (solo en creación) --}}
        @unless(isset($cuenta))
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1.5">
                Código <span class="text-red-400">*</span>
            </label>
            <input type="text" name="codigo" value="{{ old('codigo') }}"
                   maxlength="10" required
                   placeholder="Ej: 110501"
                   class="input w-full font-mono">
            <p class="text-xs text-slate-500 mt-1">Código PUC (hasta 10 dígitos)</p>
        </div>
        @endunless

        {{-- Nombre --}}
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1.5">
                Nombre <span class="text-red-400">*</span>
            </label>
            <input type="text" name="nombre" value="{{ old('nombre', $cuenta->nombre ?? '') }}"
                   maxlength="250" required
                   placeholder="Ej: Caja General"
                   class="input w-full">
        </div>

        {{-- Tipo y Naturaleza --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">
                    Tipo <span class="text-red-400">*</span>
                </label>
                <select name="tipo" required class="input w-full">
                    @foreach(['activo'=>'Activo','pasivo'=>'Pasivo','patrimonio'=>'Patrimonio','ingreso'=>'Ingreso','gasto'=>'Gasto','costo'=>'Costo'] as $val => $label)
                    <option value="{{ $val }}" @selected(old('tipo', $cuenta->tipo ?? '') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">
                    Naturaleza <span class="text-red-400">*</span>
                </label>
                <select name="naturaleza" required class="input w-full">
                    <option value="debito"  @selected(old('naturaleza', $cuenta->naturaleza ?? '') === 'debito')>Débito</option>
                    <option value="credito" @selected(old('naturaleza', $cuenta->naturaleza ?? '') === 'credito')>Crédito</option>
                </select>
            </div>
        </div>

        {{-- Nivel (solo en creación) --}}
        @unless(isset($cuenta))
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1.5">
                Nivel <span class="text-red-400">*</span>
            </label>
            <select name="nivel" required class="input w-full">
                @foreach([1=>'1 – Clase',2=>'2 – Grupo',3=>'3 – Cuenta',4=>'4 – Subcuenta'] as $n => $l)
                <option value="{{ $n }}" @selected(old('nivel') == $n)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        @endunless

        {{-- Cuenta padre --}}
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1.5">Cuenta Padre</label>
            <select name="cuenta_padre_id" class="input w-full">
                <option value="">— Sin padre (cuenta raíz) —</option>
                @foreach($padres as $padre)
                <option value="{{ $padre->id }}"
                    @selected(old('cuenta_padre_id', $cuenta->cuenta_padre_id ?? null) == $padre->id)>
                    {{ $padre->codigo }} – {{ $padre->nombre }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- Switches --}}
        <div class="flex flex-col sm:flex-row gap-4">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="hidden" name="acepta_movimientos" value="0">
                <input type="checkbox" name="acepta_movimientos" value="1"
                       class="w-4 h-4 rounded accent-amber-500"
                       @checked(old('acepta_movimientos', $cuenta->acepta_movimientos ?? true))>
                <span class="text-sm text-slate-300">Acepta movimientos directos</span>
            </label>

            @if(isset($cuenta))
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="hidden" name="activo" value="0">
                <input type="checkbox" name="activo" value="1"
                       class="w-4 h-4 rounded accent-amber-500"
                       @checked(old('activo', $cuenta->activo ?? true))>
                <span class="text-sm text-slate-300">Cuenta activa</span>
            </label>
            @endif
        </div>

        {{-- Botones --}}
        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="flex-1 bg-amber-500 hover:bg-amber-600 text-black
                           font-semibold py-2.5 rounded-xl transition-colors">
                {{ isset($cuenta) ? 'Actualizar Cuenta' : 'Crear Cuenta' }}
            </button>
            <a href="{{ route('contabilidad.plan-cuentas.index') }}"
               class="px-5 py-2.5 rounded-xl border border-[#1e2d47] text-slate-400
                      hover:text-slate-200 hover:border-slate-500 transition-colors text-sm">
                Cancelar
            </a>
        </div>
    </form>
</div>
@endsection
