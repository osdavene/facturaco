@extends('layouts.app')

@section('title', 'Clientes')
@section('page-title', 'Clientes')

@section('content')

@if(session('success'))
<div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400
            rounded-xl px-5 py-3 mb-5 flex items-center gap-3">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

{{-- Encabezado --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-display font-bold text-2xl">Clientes</h1>
        <p class="text-slate-500 text-sm mt-1">Gestiona tu cartera de clientes</p>
    </div>
    <a href="{{ route('clientes.create') }}"
       class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600
              text-black font-semibold px-5 py-2.5 rounded-xl transition-colors">
        <i class="fas fa-plus"></i> Nuevo Cliente
    </a>
</div>

{{-- Filtros --}}
<form method="GET" action="{{ route('clientes.index') }}"
      class="card p-4 mb-5">
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1 relative">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
            <input type="text" name="buscar" value="{{ request('buscar') }}"
                   placeholder="Buscar por nombre, NIT, email..."
                   class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl
                          pl-9 pr-4 py-2.5 text-sm text-slate-200 placeholder-slate-600
                          focus:outline-none focus:border-amber-500 transition-colors">
        </div>
        <select name="tipo"
                class="form-input text-slate-300 focus:outline-none focus:border-amber-500">
            <option value="">Todos los tipos</option>
            <option value="natural"  {{ request('tipo')=='natural'  ? 'selected':'' }}>Natural</option>
            <option value="juridica" {{ request('tipo')=='juridica' ? 'selected':'' }}>Jurídica</option>
        </select>
        <select name="estado"
                class="form-input text-slate-300 focus:outline-none focus:border-amber-500">
            <option value="">Todos los estados</option>
            <option value="activo"   {{ request('estado')=='activo'   ? 'selected':'' }}>Activos</option>
            <option value="inactivo" {{ request('estado')=='inactivo' ? 'selected':'' }}>Inactivos</option>
        </select>
        <button type="submit"
                class="bg-amber-500 hover:bg-amber-600 text-black font-semibold
                       px-5 py-2.5 rounded-xl transition-colors whitespace-nowrap">
            <i class="fas fa-filter mr-2"></i>Filtrar
        </button>
        @if(request()->hasAny(['buscar','tipo','estado']))
        <a href="{{ route('clientes.index') }}"
           class="bg-[#1a2235] border border-[#1e2d47] hover:border-red-500/50
                  text-slate-400 hover:text-red-400 px-4 py-2.5 rounded-xl
                  transition-colors text-sm flex items-center gap-2">
            <i class="fas fa-times"></i> Limpiar
        </a>
        @endif
    </div>
</form>

{{-- Tabla --}}
<div class="card overflow-hidden">

    {{-- Stats --}}
    <div class="grid grid-cols-3 border-b border-[#1e2d47]">
        <div class="px-5 py-3 text-center border-r border-[#1e2d47]">
            <div class="font-display font-bold text-lg">{{ $clientes->total() }}</div>
            <div class="text-xs text-slate-500">Total</div>
        </div>
        <div class="px-5 py-3 text-center border-r border-[#1e2d47]">
            <div class="font-display font-bold text-lg text-emerald-500">
                {{ $clientes->where('activo', true)->count() }}
            </div>
            <div class="text-xs text-slate-500">Activos</div>
        </div>
        <div class="px-5 py-3 text-center">
            <div class="font-display font-bold text-lg text-amber-500">
                {{ $clientes->where('tipo_persona', 'juridica')->count() }}
            </div>
            <div class="text-xs text-slate-500">Jurídicas</div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-[#1e2d47]">
                    <th class="table-th">Cliente</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden md:table-cell">Documento</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden lg:table-cell">Contacto</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden lg:table-cell">Régimen</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Estado</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-5 py-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clientes as $cliente)
                <tr class="table-row">

                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center
                                        font-bold text-sm text-white flex-shrink-0
                                        {{ $cliente->tipo_persona == 'juridica'
                                           ? 'bg-gradient-to-br from-blue-500 to-purple-600'
                                           : 'bg-gradient-to-br from-emerald-500 to-teal-600' }}">
                                {{ strtoupper(substr($cliente->nombre_completo, 0, 2)) }}
                            </div>
                            <div>
                                <div class="text-sm font-semibold">{{ $cliente->nombre_completo }}</div>
                                <div class="text-xs text-slate-500">
                                    {{ $cliente->tipo_persona == 'juridica' ? 'Jurídica' : 'Natural' }}
                                </div>
                            </div>
                        </div>
                    </td>

                    <td class="px-3 py-4 hidden md:table-cell">
                        <div class="text-sm font-mono text-slate-300">
                            {{ $cliente->tipo_documento }}: {{ $cliente->documento_formateado }}
                        </div>
                    </td>

                    <td class="px-3 py-4 hidden lg:table-cell">
                        <div class="text-sm text-slate-400">{{ $cliente->email ?? '—' }}</div>
                        <div class="text-xs text-slate-600">{{ $cliente->celular ?? $cliente->telefono ?? '' }}</div>
                    </td>

                    <td class="px-3 py-4 hidden lg:table-cell">
                        <span class="text-xs px-2.5 py-1 rounded-full font-medium
                            {{ $cliente->regimen == 'responsable_iva'
                               ? 'bg-blue-500/10 text-blue-400'
                               : 'bg-slate-500/10 text-slate-400' }}">
                            {{ $cliente->regimen == 'responsable_iva' ? 'Resp. IVA' : 'Simple' }}
                        </span>
                    </td>

                    <td class="px-3 py-4">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full
                            {{ $cliente->activo
                               ? 'bg-emerald-500/10 text-emerald-500'
                               : 'bg-red-500/10 text-red-400' }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            {{ $cliente->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>

                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('clientes.show', $cliente) }}"
                               title="Ver detalle"
                               class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                      flex items-center justify-center text-slate-400
                                      hover:text-blue-400 hover:border-blue-500/50 transition-colors">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            <a href="{{ route('clientes.edit', $cliente) }}"
                               title="Editar"
                               class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                      flex items-center justify-center text-slate-400
                                      hover:text-amber-500 hover:border-amber-500/50 transition-colors">
                                <i class="fas fa-pen text-xs"></i>
                            </a>
                            <form method="POST" action="{{ route('clientes.destroy', $cliente) }}"
                                  onsubmit="return confirm('¿Eliminar a {{ $cliente->nombre_completo }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Eliminar"
                                        class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                               flex items-center justify-center text-slate-400
                                               hover:text-red-400 hover:border-red-500/50 transition-colors">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-16 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-14 h-14 bg-[#1a2235] rounded-2xl flex items-center
                                        justify-center text-slate-600 text-2xl">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="text-slate-500">No hay clientes registrados</div>
                            <a href="{{ route('clientes.create') }}"
                               class="text-amber-500 hover:underline text-sm font-medium">
                                + Crear el primero
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($clientes->hasPages())
    <div class="px-5 py-4 border-t border-[#1e2d47]">
        {{ $clientes->links() }}
    </div>
    @endif
</div>

@endsection