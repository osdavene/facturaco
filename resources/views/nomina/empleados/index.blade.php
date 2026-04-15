@extends('layouts.app')
@section('title', 'Empleados')
@section('page-title', 'Nómina · Empleados')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-display font-bold text-2xl">Empleados</h1>
        <p class="text-slate-500 text-sm mt-1">Gestión del personal de la empresa</p>
    </div>
    <a href="{{ route('nomina.empleados.create') }}"
       class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600
              text-black font-semibold px-5 py-2.5 rounded-xl transition-colors">
        <i class="fas fa-plus"></i> Nuevo Empleado
    </a>
</div>

{{-- Filtros --}}
<form method="GET" action="{{ route('nomina.empleados.index') }}"
      class="card p-4 mb-5">
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1 relative">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
            <input type="text" name="buscar" value="{{ $buscar }}"
                   placeholder="Buscar por nombre, documento o cargo..."
                   class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl
                          pl-9 pr-4 py-2.5 text-sm placeholder-slate-600
                          focus:outline-none focus:border-amber-500">
        </div>
        <select name="estado" class="form-input sm:w-40">
            <option value="">Todos</option>
            <option value="activo"   {{ $estado==='activo'   ? 'selected':'' }}>Activos</option>
            <option value="inactivo" {{ $estado==='inactivo' ? 'selected':'' }}>Inactivos</option>
        </select>
        <button type="submit"
                class="bg-amber-500 hover:bg-amber-600 text-black font-semibold
                       px-5 py-2.5 rounded-xl transition-colors whitespace-nowrap">
            <i class="fas fa-filter mr-1"></i> Filtrar
        </button>
        @if($buscar || $estado)
        <a href="{{ route('nomina.empleados.index') }}"
           class="bg-[#1a2235] border border-[#1e2d47] hover:border-red-500/50
                  text-slate-400 hover:text-red-400 px-4 py-2.5 rounded-xl transition-colors">
            <i class="fas fa-times"></i>
        </a>
        @endif
    </div>
</form>

@if(session('success'))
<div class="alert-success mb-4">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="alert-error mb-4">{{ session('error') }}</div>
@endif

{{-- Tabla --}}
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
    @if($empleados->isEmpty())
    <div class="flex flex-col items-center justify-center py-16 text-slate-500">
        <i class="fas fa-users text-5xl mb-4 opacity-20"></i>
        <p class="font-semibold text-base">No hay empleados registrados</p>
        <p class="text-sm mt-1">Crea el primer empleado para comenzar con la nómina</p>
        <a href="{{ route('nomina.empleados.create') }}"
           class="mt-5 bg-amber-500 hover:bg-amber-600 text-black font-bold
                  text-sm px-5 py-2.5 rounded-xl transition-colors">
            <i class="fas fa-plus mr-1"></i> Nuevo Empleado
        </a>
    </div>
    @else
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-[#1e2d47] text-xs text-slate-500 uppercase tracking-wider">
                <th class="px-5 py-3 text-left">Empleado</th>
                <th class="px-4 py-3 text-left hidden md:table-cell">Cargo</th>
                <th class="px-4 py-3 text-left hidden lg:table-cell">Contrato</th>
                <th class="px-4 py-3 text-right">Salario Base</th>
                <th class="px-4 py-3 text-left hidden sm:table-cell">Ingreso</th>
                <th class="px-4 py-3 text-center">Estado</th>
                <th class="px-5 py-3 text-right">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($empleados as $emp)
            <tr class="table-row">
                <td class="px-5 py-4">
                    <div class="font-semibold text-slate-200">{{ $emp->nombre_completo }}</div>
                    <div class="text-xs text-slate-500">{{ $emp->tipo_documento }}: {{ $emp->numero_documento }}</div>
                </td>
                <td class="px-4 py-4 hidden md:table-cell">
                    <div class="text-slate-300">{{ $emp->cargo }}</div>
                    @if($emp->departamento)
                    <div class="text-xs text-slate-500">{{ $emp->departamento }}</div>
                    @endif
                </td>
                <td class="px-4 py-4 hidden lg:table-cell">
                    <div class="text-slate-400 text-xs">{{ $emp->tipo_contrato_label }}</div>
                    <div class="text-[10px] text-slate-600 capitalize">{{ $emp->tipo_salario }}</div>
                </td>
                <td class="px-4 py-4 text-right">
                    <div class="font-bold text-amber-400">
                        ${{ number_format($emp->salario_base, 0, ',', '.') }}
                    </div>
                    <div class="text-[10px] text-slate-600 capitalize">{{ $emp->periodicidad_pago }}</div>
                </td>
                <td class="px-4 py-4 hidden sm:table-cell">
                    <div class="text-slate-400 text-xs">{{ $emp->fecha_ingreso->format('d/m/Y') }}</div>
                    <div class="text-[10px] text-slate-600">{{ $emp->antiguedad }}</div>
                </td>
                <td class="px-4 py-4 text-center">
                    @if($emp->activo)
                    <span class="badge bg-emerald-500/10 text-emerald-400">Activo</span>
                    @else
                    <span class="badge bg-slate-500/10 text-slate-400">Inactivo</span>
                    @endif
                </td>
                <td class="px-5 py-4 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('nomina.empleados.edit', $emp) }}" title="Editar"
                           class="btn-icon hover:text-amber-400 hover:border-amber-500/50">
                            <i class="fas fa-edit text-xs"></i>
                        </a>
                        <form method="POST" action="{{ route('nomina.empleados.toggle', $emp) }}">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    title="{{ $emp->activo ? 'Desactivar' : 'Activar' }}"
                                    class="btn-icon {{ $emp->activo ? 'hover:text-red-400 hover:border-red-500/50' : 'hover:text-emerald-400 hover:border-emerald-500/50' }}">
                                <i class="fas {{ $emp->activo ? 'fa-toggle-on text-emerald-400' : 'fa-toggle-off text-slate-500' }} text-sm"></i>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('nomina.empleados.destroy', $emp) }}"
                              onsubmit="return confirm('¿Eliminar empleado {{ addslashes($emp->nombre_completo) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" title="Eliminar"
                                    class="btn-icon hover:text-red-400 hover:border-red-500/50">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
    </div>

    @if($empleados->hasPages())
    <div class="px-5 py-4 border-t border-[#1e2d47]">
        {{ $empleados->links() }}
    </div>
    @endif
</div>

@endsection
