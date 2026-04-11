@extends('backoffice.layout')
@section('title', 'Editar empresa')

@section('content')

<div class="mb-8">
    <a href="{{ route('backoffice.empresas') }}"
       class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-200 text-sm transition-colors mb-4">
        <i class="fas fa-arrow-left text-xs"></i>Volver a empresas
    </a>
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-[#1a2235] border border-[#1e2d47] rounded-xl
                    flex items-center justify-center font-display font-black text-amber-500">
            {{ strtoupper(substr($empresa->razon_social, 0, 2)) }}
        </div>
        <div>
            <h1 class="font-display font-black text-2xl">{{ $empresa->razon_social }}</h1>
            <p class="text-slate-500 text-sm">
                NIT: {{ $empresa->nit }}
                @if($empresa->esFilial())
                    · Filial de <span class="text-amber-500">{{ $empresa->padre->razon_social }}</span>
                @else
                    · <span class="text-amber-500">Empresa matriz</span>
                @endif
            </p>
        </div>
    </div>
</div>

<div class="max-w-2xl mx-auto space-y-5">

    {{-- Datos básicos --}}
    <form method="POST" action="{{ route('backoffice.empresas.update', $empresa) }}">
        @csrf @method('PUT')

        <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-6 space-y-5">
            <h2 class="font-display font-bold text-base flex items-center gap-2">
                <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center text-black text-xs font-black">1</span>
                Datos de la empresa
            </h2>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Razón social *</label>
                <input type="text" name="razon_social" value="{{ old('razon_social', $empresa->razon_social) }}" required
                       class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5 text-sm
                              focus:outline-none focus:border-amber-500 transition-colors">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">NIT *</label>
                    <input type="text" name="nit" value="{{ old('nit', $empresa->nit) }}" required
                           class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5 text-sm
                                  focus:outline-none focus:border-amber-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Teléfono</label>
                    <input type="text" name="telefono" value="{{ old('telefono', $empresa->telefono) }}"
                           class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5 text-sm
                                  focus:outline-none focus:border-amber-500 transition-colors">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email', $empresa->email) }}"
                           class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5 text-sm
                                  focus:outline-none focus:border-amber-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Municipio</label>
                    <input type="text" name="municipio" value="{{ old('municipio', $empresa->municipio) }}"
                           class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5 text-sm
                                  focus:outline-none focus:border-amber-500 transition-colors">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Jerarquía</label>
                <select name="empresa_padre_id"
                        class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5 text-sm
                               focus:outline-none focus:border-amber-500 transition-colors">
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

        <div class="flex justify-end mt-4">
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600
                           text-black font-semibold text-sm px-6 py-2.5 rounded-xl transition-colors">
                <i class="fas fa-check text-xs"></i>Guardar cambios
            </button>
        </div>
    </form>

    {{-- Admins asignados --}}
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-display font-bold text-base">Administradores</h2>
            <a href="{{ route('backoffice.empresas.admin.crear', $empresa) }}"
               class="inline-flex items-center gap-1.5 bg-[#1a2235] border border-[#1e2d47]
                      hover:border-emerald-500/50 hover:text-emerald-400
                      text-slate-400 text-xs px-3 py-1.5 rounded-lg transition-colors">
                <i class="fas fa-user-plus text-[10px]"></i>Nuevo admin
            </a>
        </div>

        @if($adminUsuarios->count())
        <div class="space-y-2">
            @foreach($adminUsuarios as $u)
            <div class="flex items-center gap-3 bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-3">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-sm
                            text-white flex-shrink-0 bg-gradient-to-br from-blue-500 to-purple-600">
                    {{ strtoupper(substr($u->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold">{{ $u->name }}</p>
                    <p class="text-xs text-slate-500">{{ $u->email }}</p>
                </div>
                <span class="text-xs px-2 py-0.5 bg-amber-500/10 text-amber-500 rounded-full font-semibold">Admin</span>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-slate-500 text-sm">Sin administradores asignados.</p>
        @endif
    </div>

    {{-- Zona peligrosa --}}
    <div class="bg-[#141c2e] border border-red-900/30 rounded-2xl p-6">
        <h2 class="font-display font-bold text-base text-red-400 mb-2">Zona peligrosa</h2>
        <p class="text-slate-500 text-xs mb-4">
            Eliminar esta empresa es permanente.
            @if(!$empresa->esFilial())Las filiales quedarán como matrices independientes.@endif
        </p>
        <form method="POST" action="{{ route('backoffice.empresas.destroy', $empresa) }}"
              onsubmit="return confirm('¿Seguro que deseas eliminar {{ $empresa->razon_social }}?')">
            @csrf @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center gap-2 border border-red-500/30 text-red-400
                           hover:bg-red-500/10 text-sm px-4 py-2 rounded-xl transition-colors">
                <i class="fas fa-trash text-xs"></i>Eliminar empresa
            </button>
        </form>
    </div>

</div>
@endsection
