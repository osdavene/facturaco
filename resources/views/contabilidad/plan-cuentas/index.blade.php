@extends('layouts.app')
@section('title', 'Plan de Cuentas')
@section('page-title', 'Plan de Cuentas')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-display font-bold text-2xl">Plan de Cuentas</h1>
        <p class="text-slate-500 text-sm mt-1">PUC Colombia · Cuentas estándar y personalizadas</p>
    </div>
    @can('crear recibos')
    <a href="{{ route('contabilidad.plan-cuentas.create') }}"
       class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600
              text-black font-semibold px-5 py-2.5 rounded-xl transition-colors">
        <i class="fas fa-plus"></i> Nueva Cuenta
    </a>
    @endcan
</div>

{{-- Filtros --}}
<form method="GET" action="{{ route('contabilidad.plan-cuentas.index') }}"
      class="card p-4 mb-5">
    <div class="flex flex-col sm:flex-row gap-3">
        <input type="text" name="buscar" value="{{ request('buscar') }}"
               placeholder="Código o nombre..."
               class="input flex-1">
        <select name="tipo" class="input sm:w-44">
            <option value="">Todos los tipos</option>
            @foreach(['activo'=>'Activo','pasivo'=>'Pasivo','patrimonio'=>'Patrimonio','ingreso'=>'Ingreso','gasto'=>'Gasto','costo'=>'Costo'] as $val => $label)
                <option value="{{ $val }}" @selected(request('tipo') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="nivel" class="input sm:w-32">
            <option value="">Todos los niveles</option>
            @foreach([1=>'Clase',2=>'Grupo',3=>'Cuenta',4=>'Subcuenta'] as $n => $l)
                <option value="{{ $n }}" @selected(request('nivel') == $n)>{{ $n }} – {{ $l }}</option>
            @endforeach
        </select>
        <button type="submit"
                class="inline-flex items-center gap-2 bg-slate-700 hover:bg-slate-600
                       text-white px-4 py-2.5 rounded-xl text-sm transition-colors">
            <i class="fas fa-search"></i> Filtrar
        </button>
        @if(request()->hasAny(['buscar','tipo','nivel']))
        <a href="{{ route('contabilidad.plan-cuentas.index') }}"
           class="inline-flex items-center gap-2 text-slate-400 hover:text-slate-200 px-3 py-2.5 text-sm">
            <i class="fas fa-times"></i> Limpiar
        </a>
        @endif
    </div>
</form>

{{-- Tabla --}}
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-[#1e2d47] text-xs text-slate-400 uppercase tracking-wider">
                    <th class="px-4 py-3 text-left">Código</th>
                    <th class="px-4 py-3 text-left">Nombre</th>
                    <th class="px-4 py-3 text-left hidden md:table-cell">Tipo</th>
                    <th class="px-4 py-3 text-left hidden md:table-cell">Naturaleza</th>
                    <th class="px-4 py-3 text-center hidden sm:table-cell">Nivel</th>
                    <th class="px-4 py-3 text-center hidden lg:table-cell">Movimientos</th>
                    <th class="px-4 py-3 text-center hidden lg:table-cell">Origen</th>
                    <th class="px-4 py-3 text-center">Estado</th>
                    <th class="px-4 py-3 text-right"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#1e2d47]">
                @forelse($cuentas as $cuenta)
                <tr class="hover:bg-[#141c2e]/50 transition-colors">
                    <td class="px-4 py-3 font-mono text-amber-400 font-semibold text-xs">
                        {{ $cuenta->codigo }}
                    </td>
                    <td class="px-4 py-3 font-medium" style="padding-left: {{ ($cuenta->nivel - 1) * 1.25 + 1 }}rem">
                        {{ $cuenta->nombre }}
                    </td>
                    <td class="px-4 py-3 hidden md:table-cell">
                        @php
                        $colores = ['activo'=>'blue','pasivo'=>'red','patrimonio'=>'purple','ingreso'=>'emerald','gasto'=>'orange','costo'=>'yellow'];
                        $color = $colores[$cuenta->tipo] ?? 'slate';
                        @endphp
                        <span class="px-2 py-0.5 rounded-full text-xs bg-{{ $color }}-500/10 text-{{ $color }}-400 border border-{{ $color }}-500/20">
                            {{ ucfirst($cuenta->tipo) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 hidden md:table-cell text-slate-400 capitalize text-xs">
                        {{ $cuenta->naturaleza }}
                    </td>
                    <td class="px-4 py-3 text-center hidden sm:table-cell text-slate-400 text-xs">
                        {{ $cuenta->nivel }}
                    </td>
                    <td class="px-4 py-3 text-center hidden lg:table-cell">
                        @if($cuenta->acepta_movimientos)
                            <span class="text-emerald-400"><i class="fas fa-check text-xs"></i></span>
                        @else
                            <span class="text-slate-600"><i class="fas fa-minus text-xs"></i></span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center hidden lg:table-cell">
                        @if($cuenta->empresa_id === null)
                            <span class="px-2 py-0.5 rounded text-xs bg-slate-500/10 text-slate-400">PUC</span>
                        @else
                            <span class="px-2 py-0.5 rounded text-xs bg-amber-500/10 text-amber-400">Empresa</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($cuenta->activo)
                            <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block"></span>
                        @else
                            <span class="w-2 h-2 rounded-full bg-slate-600 inline-block"></span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        @if($cuenta->empresa_id !== null)
                        <a href="{{ route('contabilidad.plan-cuentas.edit', $cuenta) }}"
                           class="text-slate-400 hover:text-amber-400 transition-colors text-xs">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-12 text-center text-slate-500">
                        <i class="fas fa-book text-3xl mb-3 block opacity-30"></i>
                        No se encontraron cuentas con esos filtros.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $cuentas->links() }}
</div>

@endsection
