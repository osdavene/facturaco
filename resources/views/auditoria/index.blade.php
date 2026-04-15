@extends('layouts.app')
@section('title', 'Auditoría')
@section('page-title', 'Administración · Auditoría')

@section('content')
<div class="max-w-6xl mx-auto space-y-5">

    {{-- Header --}}
    <div>
        <h1 class="font-display font-bold text-2xl">Auditoría del Sistema</h1>
        <p class="text-slate-500 text-sm mt-0.5">
            Registro de quién creó o modificó cada elemento del sistema.
        </p>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('auditoria.index') }}"
          class="card p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">

            <div>
                <label class="block text-xs text-slate-500 mb-1 uppercase tracking-wider">Módulo</label>
                <select name="modulo"
                        class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-3 py-2
                               text-sm text-slate-200 focus:outline-none focus:border-amber-500 transition-colors">
                    <option value="">Todos</option>
                    @foreach($modulos as $m)
                    <option value="{{ $m }}" {{ request('modulo') === $m ? 'selected' : '' }}>
                        {{ $m }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs text-slate-500 mb-1 uppercase tracking-wider">Usuario</label>
                <input type="text" name="usuario" value="{{ request('usuario') }}"
                       placeholder="Nombre del usuario..."
                       class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-3 py-2
                              text-sm text-slate-200 placeholder-slate-600
                              focus:outline-none focus:border-amber-500 transition-colors">
            </div>

            <div>
                <label class="block text-xs text-slate-500 mb-1 uppercase tracking-wider">Desde</label>
                <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"
                       class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-3 py-2
                              text-sm text-slate-200 focus:outline-none focus:border-amber-500 transition-colors">
            </div>

            <div>
                <label class="block text-xs text-slate-500 mb-1 uppercase tracking-wider">Hasta</label>
                <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}"
                       class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-3 py-2
                              text-sm text-slate-200 focus:outline-none focus:border-amber-500 transition-colors">
            </div>
        </div>

        <div class="flex gap-2 mt-3">
            <button type="submit"
                    class="bg-amber-500 hover:bg-amber-600 text-black font-bold text-sm
                           px-4 py-2 rounded-xl transition-colors">
                <i class="fas fa-search mr-1"></i> Filtrar
            </button>
            <a href="{{ route('auditoria.index') }}"
               class="bg-[#1a2235] border border-[#1e2d47] text-slate-400 font-semibold text-sm
                      px-4 py-2 rounded-xl hover:border-slate-500 transition-colors">
                Limpiar
            </a>
        </div>
    </form>

    {{-- Tabla --}}
    <div class="card overflow-hidden">
        @if($registros->isEmpty())
        <div class="py-16 flex flex-col items-center text-slate-500">
            <i class="fas fa-clipboard-list text-5xl mb-4 opacity-20"></i>
            <p class="font-semibold">No hay registros con esos filtros</p>
        </div>
        @else
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-[#1e2d47] text-xs text-slate-500 uppercase tracking-wider">
                    <th class="px-5 py-3 text-left">Módulo</th>
                    <th class="px-5 py-3 text-left">Descripción</th>
                    <th class="px-5 py-3 text-left hidden md:table-cell">Creado por</th>
                    <th class="px-5 py-3 text-left hidden lg:table-cell">Modificado por</th>
                    <th class="px-5 py-3 text-right">Fecha</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#1e2d47]">
                @foreach($registros as $r)
                @php
                    $colores = [
                        'Factura'        => 'bg-amber-500/10 text-amber-400',
                        'Cotización'     => 'bg-blue-500/10 text-blue-400',
                        'Orden de Compra'=> 'bg-purple-500/10 text-purple-400',
                        'Recibo de Caja' => 'bg-emerald-500/10 text-emerald-400',
                        'Remisión'       => 'bg-cyan-500/10 text-cyan-400',
                        'Cliente'        => 'bg-sky-500/10 text-sky-400',
                        'Proveedor'      => 'bg-orange-500/10 text-orange-400',
                        'Producto'       => 'bg-pink-500/10 text-pink-400',
                        'Categoría'      => 'bg-violet-500/10 text-violet-400',
                    ];
                    $color = $colores[$r->modulo] ?? 'bg-slate-500/10 text-slate-400';
                @endphp
                <tr class="hover:bg-[#1a2235] transition-colors">
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold {{ $color }}">
                            {{ $r->modulo }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-slate-300 max-w-xs truncate">
                        {{ $r->descripcion }}
                    </td>
                    <td class="px-5 py-3.5 hidden md:table-cell">
                        @if($r->creado_por)
                        <div class="flex items-center gap-2">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($r->creado_por) }}&background=f59e0b&color=000&bold=true&size=40"
                                 class="w-6 h-6 rounded-lg">
                            <span class="text-slate-300 text-xs">{{ $r->creado_por }}</span>
                        </div>
                        @else
                        <span class="text-slate-600 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 hidden lg:table-cell">
                        @if($r->actualizado_por && $r->actualizado_por !== $r->creado_por)
                        <div class="flex items-center gap-2">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($r->actualizado_por) }}&background=64748b&color=fff&bold=true&size=40"
                                 class="w-6 h-6 rounded-lg">
                            <span class="text-slate-400 text-xs">{{ $r->actualizado_por }}</span>
                        </div>
                        @else
                        <span class="text-slate-600 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="text-xs text-slate-300">
                            {{ \Carbon\Carbon::parse($r->created_at)->format('d/m/Y') }}
                        </div>
                        <div class="text-xs text-slate-600">
                            {{ \Carbon\Carbon::parse($r->created_at)->format('h:i A') }}
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>

        @if($registros->hasPages())
        <div class="px-5 py-4 border-t border-[#1e2d47]">
            {{ $registros->links() }}
        </div>
        @endif
        @endif
    </div>

    {{-- Contador --}}
    <p class="text-xs text-slate-600 text-right">
        {{ $registros->total() }} registro(s) encontrado(s)
    </p>

</div>
@endsection