@extends('backoffice.layout')
@section('title', 'Módulos de empresa')

@section('content')

<div class="mb-8">
    <a href="{{ route('backoffice.empresas.editar', $empresa) }}"
       class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-200 text-sm transition-colors mb-4">
        <i class="fas fa-arrow-left text-xs"></i>Volver a editar empresa
    </a>

    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-[#1a2235] border border-[#1e2d47] rounded-xl
                    flex items-center justify-center font-display font-black text-amber-500">
            {{ strtoupper(substr($empresa->razon_social, 0, 2)) }}
        </div>
        <div>
            <h1 class="font-display font-black text-2xl">Módulos habilitados</h1>
            <p class="text-slate-500 text-sm">{{ $empresa->razon_social }} · NIT: {{ $empresa->nit }}</p>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('backoffice.empresas.modulos.update', $empresa) }}" class="max-w-3xl">
    @csrf
    @method('PUT')

    <div class="card p-6">
        <h2 class="font-display font-bold text-base mb-4">Selecciona los módulos activos para esta empresa</h2>

        @if($modulos->count())
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach($modulos as $modulo)
                    <label class="flex items-start gap-3 bg-[#1a2235] border border-[#1e2d47] rounded-xl p-4 hover:border-amber-500/40 transition-colors cursor-pointer">
                        <input type="checkbox"
                               name="modulos[]"
                               value="{{ $modulo->id }}"
                               class="mt-1 w-4 h-4 accent-amber-500"
                               {{ in_array($modulo->id, old('modulos', $modulosActivos)) ? 'checked' : '' }}>

                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="font-semibold text-sm text-slate-100">{{ $modulo->nombre }}</p>
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-500 font-semibold">
                                    {{ $modulo->slug }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">
                                {{ $modulo->descripcion ?: 'Sin descripción.' }}
                            </p>
                        </div>
                    </label>
                @endforeach
            </div>
        @else
            <p class="text-slate-500 text-sm">No hay módulos disponibles para asignar.</p>
        @endif
    </div>

    <div class="flex items-center justify-end gap-3 mt-4">
        <a href="{{ route('backoffice.empresas.editar', $empresa) }}"
           class="inline-flex items-center gap-2 border border-[#1e2d47] text-slate-300
                  hover:border-slate-400 text-sm px-5 py-2.5 rounded-xl transition-colors">
            Cancelar
        </a>
        <button type="submit"
                class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600
                       text-black font-semibold text-sm px-6 py-2.5 rounded-xl transition-colors">
            <i class="fas fa-check text-xs"></i>Guardar módulos
        </button>
    </div>
</form>

@endsection
