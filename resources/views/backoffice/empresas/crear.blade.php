@extends('backoffice.layout')
@section('title', 'Nueva empresa')

@section('content')

<div class="mb-8">
    <a href="{{ route('backoffice.empresas') }}" class="text-slate-500 hover:text-slate-300 text-sm transition-colors">
        <i class="fas fa-arrow-left mr-2"></i>Volver a empresas
    </a>
    <h1 class="font-display font-black text-2xl text-white mt-3">Nueva empresa</h1>
</div>

<div class="max-w-2xl">
    <form method="POST" action="{{ route('backoffice.empresas.store') }}" class="space-y-5">
        @csrf

        <div class="bg-[#0d1117] border border-white/5 rounded-2xl p-6 space-y-5">

            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Razón social *</label>
                <input type="text" name="razon_social" value="{{ old('razon_social') }}" required
                       class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white
                              focus:outline-none focus:border-violet-500 transition-colors"
                       placeholder="Empresa S.A.S.">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">NIT *</label>
                    <input type="text" name="nit" value="{{ old('nit') }}" required
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white
                                  focus:outline-none focus:border-violet-500 transition-colors"
                           placeholder="900000000">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Teléfono</label>
                    <input type="text" name="telefono" value="{{ old('telefono') }}"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white
                                  focus:outline-none focus:border-violet-500 transition-colors"
                           placeholder="601 000 0000">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white
                                  focus:outline-none focus:border-violet-500 transition-colors"
                           placeholder="contacto@empresa.com">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Municipio</label>
                    <input type="text" name="municipio" value="{{ old('municipio') }}"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white
                                  focus:outline-none focus:border-violet-500 transition-colors"
                           placeholder="Bogotá D.C.">
                </div>
            </div>

            {{-- Empresa padre --}}
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">¿Es filial de otra empresa?</label>
                <select name="empresa_padre_id"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white
                               focus:outline-none focus:border-violet-500 transition-colors">
                    <option value="">— No, es una empresa matriz independiente —</option>
                    @foreach($matrices as $m)
                        <option value="{{ $m->id }}"
                            {{ (old('empresa_padre_id', request('padre')) == $m->id) ? 'selected' : '' }}>
                            {{ $m->razon_social }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-slate-600 mt-1.5">
                    Si seleccionas una matriz, los admins de esa empresa tendrán acceso automático a esta filial.
                </p>
            </div>

        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('backoffice.empresas') }}"
               class="px-5 py-2.5 rounded-xl text-sm text-slate-400 hover:text-white transition-colors">
                Cancelar
            </a>
            <button type="submit"
                    class="bg-violet-600 hover:bg-violet-500 text-white text-sm px-6 py-2.5 rounded-xl transition-colors">
                <i class="fas fa-check mr-2"></i>Crear empresa
            </button>
        </div>
    </form>
</div>

@endsection
