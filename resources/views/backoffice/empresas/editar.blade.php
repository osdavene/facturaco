@extends('backoffice.layout')
@section('title', 'Editar empresa')

@section('content')

<div class="mb-8">
    <a href="{{ route('backoffice.empresas') }}" class="text-slate-500 hover:text-slate-300 text-sm transition-colors">
        <i class="fas fa-arrow-left mr-2"></i>Volver a empresas
    </a>
    <h1 class="font-display font-black text-2xl text-white mt-3">{{ $empresa->razon_social }}</h1>
    <p class="text-slate-500 text-sm mt-1">
        NIT: {{ $empresa->nit }}
        @if($empresa->esFilial())
            · Filial de <span class="text-violet-400">{{ $empresa->padre->razon_social }}</span>
        @else
            · <span class="text-violet-400">Empresa matriz</span>
        @endif
    </p>
</div>

<div class="max-w-2xl space-y-6">

    {{-- Datos básicos --}}
    <form method="POST" action="{{ route('backoffice.empresas.update', $empresa) }}">
        @csrf @method('PUT')

        <div class="bg-[#0d1117] border border-white/5 rounded-2xl p-6 space-y-5">
            <h2 class="font-display font-bold text-white text-sm">Datos de la empresa</h2>

            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Razón social *</label>
                <input type="text" name="razon_social" value="{{ old('razon_social', $empresa->razon_social) }}" required
                       class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white
                              focus:outline-none focus:border-violet-500 transition-colors">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">NIT *</label>
                    <input type="text" name="nit" value="{{ old('nit', $empresa->nit) }}" required
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white
                                  focus:outline-none focus:border-violet-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Teléfono</label>
                    <input type="text" name="telefono" value="{{ old('telefono', $empresa->telefono) }}"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white
                                  focus:outline-none focus:border-violet-500 transition-colors">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email', $empresa->email) }}"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white
                                  focus:outline-none focus:border-violet-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Municipio</label>
                    <input type="text" name="municipio" value="{{ old('municipio', $empresa->municipio) }}"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white
                                  focus:outline-none focus:border-violet-500 transition-colors">
                </div>
            </div>

            {{-- Jerarquía --}}
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Relación jerárquica</label>
                <select name="empresa_padre_id"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white
                               focus:outline-none focus:border-violet-500 transition-colors">
                    <option value="">— Empresa matriz independiente —</option>
                    @foreach($matrices as $m)
                        <option value="{{ $m->id }}"
                            {{ old('empresa_padre_id', $empresa->empresa_padre_id) == $m->id ? 'selected' : '' }}>
                            {{ $m->razon_social }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 mt-4">
            <button type="submit"
                    class="bg-violet-600 hover:bg-violet-500 text-white text-sm px-6 py-2.5 rounded-xl transition-colors">
                <i class="fas fa-check mr-2"></i>Guardar cambios
            </button>
        </div>
    </form>

    {{-- Admins asignados --}}
    <div class="bg-[#0d1117] border border-white/5 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-display font-bold text-white text-sm">Administradores</h2>
            <a href="{{ route('backoffice.empresas.admin.crear', $empresa) }}"
               class="text-xs px-3 py-1.5 rounded-lg border border-emerald-500/30
                      text-emerald-400 hover:bg-emerald-500/10 transition-colors">
                <i class="fas fa-user-plus mr-1"></i>Nuevo admin
            </a>
        </div>

        @if($adminUsuarios->count())
        <div class="space-y-2">
            @foreach($adminUsuarios as $u)
            <div class="flex items-center gap-3 bg-white/3 rounded-xl px-4 py-2.5">
                <div class="w-8 h-8 bg-emerald-600/20 rounded-lg flex items-center justify-center
                            text-emerald-400 text-xs font-bold">
                    {{ strtoupper(substr($u->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-white">{{ $u->name }}</p>
                    <p class="text-xs text-slate-500">{{ $u->email }}</p>
                </div>
                <span class="text-xs px-2 py-0.5 bg-emerald-600/10 text-emerald-400 rounded-full">Admin</span>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-slate-600 text-sm">Sin administradores asignados.</p>
        @endif
    </div>

    {{-- Zona peligrosa --}}
    <div class="bg-[#0d1117] border border-red-900/20 rounded-2xl p-6">
        <h2 class="font-display font-bold text-red-400 text-sm mb-3">Zona peligrosa</h2>
        <p class="text-slate-500 text-xs mb-4">
            Eliminar esta empresa es permanente.
            @if(!$empresa->esFilial())
                Las filiales quedarán como empresas independientes.
            @endif
        </p>
        <form method="POST" action="{{ route('backoffice.empresas.destroy', $empresa) }}"
              onsubmit="return confirm('¿Seguro que deseas eliminar {{ $empresa->razon_social }}?')">
            @csrf @method('DELETE')
            <button type="submit"
                    class="text-sm px-4 py-2 rounded-xl border border-red-500/30
                           text-red-400 hover:bg-red-500/10 transition-colors">
                <i class="fas fa-trash mr-2"></i>Eliminar empresa
            </button>
        </form>
    </div>

</div>

@endsection
