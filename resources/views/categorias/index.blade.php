@extends('layouts.app')
@section('title', 'Categorías')
@section('page-title', 'Configuración · Categorías')

@section('content')
<div class="max-w-4xl mx-auto">

    {{-- Mensajes --}}
    @if(session('success'))
    <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400
                rounded-xl px-5 py-3 mb-5 flex items-center gap-3">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-500/10 border border-red-500/30 text-red-400
                rounded-xl px-5 py-3 mb-5 flex items-center gap-3">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="font-display font-bold text-2xl">Categorías de Productos</h1>
            <p class="text-slate-500 text-sm mt-0.5">
                Organiza tus productos por categorías. Solo visible para administradores.
            </p>
        </div>
        <a href="{{ route('categorias.create') }}"
           class="flex items-center gap-2 bg-amber-500 hover:bg-amber-600
                  text-black font-bold text-sm px-4 py-2.5 rounded-xl transition-colors">
            <i class="fas fa-plus text-xs"></i> Nueva Categoría
        </a>
    </div>

    {{-- Tabla --}}
    <div class="card overflow-hidden">
        @if($categorias->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-slate-500">
            <i class="fas fa-tags text-5xl mb-4 opacity-20"></i>
            <p class="font-semibold text-base">No hay categorías registradas</p>
            <p class="text-sm mt-1 text-slate-600">
                Crea categorías para organizar los productos del inventario
            </p>
            <a href="{{ route('categorias.create') }}"
               class="mt-5 bg-amber-500 hover:bg-amber-600 text-black font-bold
                      text-sm px-5 py-2.5 rounded-xl transition-colors">
                <i class="fas fa-plus text-xs mr-1"></i> Crear primera categoría
            </a>
        </div>
        @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-[#1e2d47] text-xs text-slate-500 uppercase tracking-wider">
                    <th class="px-5 py-3 text-left">Nombre</th>
                    <th class="px-5 py-3 text-left hidden sm:table-cell">Descripción</th>
                    <th class="px-5 py-3 text-center">Productos</th>
                    <th class="px-5 py-3 text-center">Estado</th>
                    <th class="px-5 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#1e2d47]">
                @foreach($categorias as $cat)
                <tr class="hover:bg-[#1a2235] transition-colors">
                    <td class="px-5 py-3.5 font-semibold text-slate-200">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full {{ $cat->activo ? 'bg-emerald-500' : 'bg-slate-600' }}"></div>
                            {{ $cat->nombre }}
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-slate-400 hidden sm:table-cell">
                        {{ $cat->descripcion ?: '—' }}
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8
                                     bg-amber-500/10 text-amber-400 rounded-lg text-xs font-bold">
                            {{ $cat->productos_count }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        @if($cat->activo)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1
                                         bg-emerald-500/10 text-emerald-400 rounded-lg text-xs font-semibold">
                                <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full"></span> Activa
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1
                                         bg-slate-500/10 text-slate-500 rounded-lg text-xs font-semibold">
                                <span class="w-1.5 h-1.5 bg-slate-500 rounded-full"></span> Inactiva
                            </span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('categorias.edit', $cat) }}"
                               class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                      flex items-center justify-center text-slate-400
                                      hover:text-amber-500 hover:border-amber-500/50 transition-colors"
                               title="Editar">
                                <i class="fas fa-pencil text-xs"></i>
                            </a>
                            @if($cat->productos_count === 0)
                            <form method="POST" action="{{ route('categorias.destroy', $cat) }}"
                                  onsubmit="return confirm('¿Eliminar la categoría \'{{ addslashes($cat->nombre) }}\'?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                               flex items-center justify-center text-slate-400
                                               hover:text-red-400 hover:border-red-500/50 transition-colors"
                                        title="Eliminar">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                            @else
                            <div class="w-8 h-8 flex items-center justify-center text-slate-700"
                                 title="No se puede eliminar: tiene productos">
                                <i class="fas fa-lock text-xs"></i>
                            </div>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Paginación --}}
        @if($categorias->hasPages())
        <div class="px-5 py-4 border-t border-[#1e2d47]">
            {{ $categorias->links() }}
        </div>
        @endif
        @endif
    </div>

    {{-- Info --}}
    <div class="mt-4 bg-[#141c2e] border border-[#1e2d47] rounded-xl px-5 py-4
                flex items-start gap-3 text-sm text-slate-500">
        <i class="fas fa-info-circle text-amber-500 mt-0.5 flex-shrink-0"></i>
        <div>
            Las categorías aparecen en el formulario de <strong class="text-slate-400">Nuevo Producto</strong>
            como opciones del selector. Solo las categorías <strong class="text-slate-400">activas</strong>
            aparecen disponibles. Las categorías con productos no se pueden eliminar.
        </div>
    </div>

</div>
@endsection