@extends('layouts.app')
@section('title', 'Unidades de Medida')
@section('page-title', 'Configuración · Unidades de Medida')

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
            <h1 class="font-display font-bold text-2xl">Unidades de Medida</h1>
            <p class="text-slate-500 text-sm mt-0.5">
                Define las unidades para medir tus productos (UN, KG, LT, MT...)
            </p>
        </div>
        <a href="{{ route('unidades.create') }}"
           class="flex items-center gap-2 bg-amber-500 hover:bg-amber-600
                  text-black font-bold text-sm px-4 py-2.5 rounded-xl transition-colors">
            <i class="fas fa-plus text-xs"></i> Nueva Unidad
        </a>
    </div>

    {{-- Tabla --}}
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl overflow-hidden">
        @if($unidades->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-slate-500">
            <i class="fas fa-ruler text-5xl mb-4 opacity-20"></i>
            <p class="font-semibold text-base">No hay unidades de medida registradas</p>
            <p class="text-sm mt-1 text-slate-600">
                Crea unidades como: Unidad, Kilogramo, Litro, Metro, Caja...
            </p>
            <a href="{{ route('unidades.create') }}"
               class="mt-5 bg-amber-500 hover:bg-amber-600 text-black font-bold
                      text-sm px-5 py-2.5 rounded-xl transition-colors">
                <i class="fas fa-plus text-xs mr-1"></i> Crear primera unidad
            </a>
        </div>
        @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-[#1e2d47] text-xs text-slate-500 uppercase tracking-wider">
                    <th class="px-5 py-3 text-left">Nombre</th>
                    <th class="px-5 py-3 text-left">Abreviatura</th>
                    <th class="px-5 py-3 text-center">Productos</th>
                    <th class="px-5 py-3 text-center">Estado</th>
                    <th class="px-5 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#1e2d47]">
                @foreach($unidades as $unidad)
                <tr class="hover:bg-[#1a2235] transition-colors">
                    <td class="px-5 py-3.5 font-semibold text-slate-200">
                        {{ $unidad->nombre }}
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center px-3 py-1
                                     bg-[#1a2235] border border-[#1e2d47]
                                     text-amber-400 text-xs font-bold rounded-lg
                                     tracking-widest font-mono">
                            {{ $unidad->abreviatura }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8
                                     bg-amber-500/10 text-amber-400 rounded-lg text-xs font-bold">
                            {{ $unidad->productos_count }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        @if($unidad->activo)
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
                            <a href="{{ route('unidades.edit', $unidad) }}"
                               class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                      flex items-center justify-center text-slate-400
                                      hover:text-amber-500 hover:border-amber-500/50 transition-colors"
                               title="Editar">
                                <i class="fas fa-pencil text-xs"></i>
                            </a>
                            @if($unidad->productos_count === 0)
                            <form method="POST" action="{{ route('unidades.destroy', $unidad) }}"
                                  onsubmit="return confirm('¿Eliminar la unidad \'{{ addslashes($unidad->nombre) }}\'?')">
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
                                 title="No se puede eliminar: tiene productos asociados">
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
        @if($unidades->hasPages())
        <div class="px-5 py-4 border-t border-[#1e2d47]">
            {{ $unidades->links() }}
        </div>
        @endif
        @endif
    </div>

    {{-- Referencia rápida de unidades comunes --}}
    <div class="mt-4 bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5">
        <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3 flex items-center gap-2">
            <i class="fas fa-lightbulb text-amber-500"></i>
            Unidades comunes en Colombia
        </h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
            @foreach([
                ['UN','Unidad'],['KG','Kilogramo'],['GR','Gramo'],['LB','Libra'],
                ['LT','Litro'],['ML','Mililitro'],['GL','Galón'],['MT','Metro'],
                ['CM','Centímetro'],['M2','Metro Cuad.'],['CJ','Caja'],['BL','Bulto'],
                ['PQ','Paquete'],['RS','Resma'],['PR','Par'],['DC','Docena'],
                ['RL','Rollo'],['TN','Tonelada'],['SV','Servicio'],['HR','Hora'],
            ] as [$abr, $nom])
            <div class="flex items-center gap-2 text-xs text-slate-500 bg-[#1a2235] rounded-lg px-3 py-2">
                <span class="font-bold text-amber-500/70 w-8 font-mono">{{ $abr }}</span>
                <span>{{ $nom }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Info --}}
    <div class="mt-3 bg-[#141c2e] border border-[#1e2d47] rounded-xl px-5 py-4
                flex items-start gap-3 text-sm text-slate-500">
        <i class="fas fa-info-circle text-amber-500 mt-0.5 flex-shrink-0"></i>
        <div>
            Las unidades aparecen en el campo <strong class="text-slate-400">Unidad de Medida</strong>
            del formulario de <strong class="text-slate-400">Nuevo Producto</strong> y también
            se imprimen en las <strong class="text-slate-400">facturas</strong>.
            Solo las unidades <strong class="text-slate-400">activas</strong> aparecen disponibles.
        </div>
    </div>

</div>
@endsection