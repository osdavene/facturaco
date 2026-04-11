@extends('backoffice.layout')
@section('title', 'Crear admin')

@section('content')

<div class="mb-8">
    <a href="{{ route('backoffice.empresas') }}" class="text-slate-500 hover:text-slate-300 text-sm transition-colors">
        <i class="fas fa-arrow-left mr-2"></i>Volver a empresas
    </a>
    <h1 class="font-display font-black text-2xl text-white mt-3">Crear administrador</h1>
    <p class="text-slate-500 text-sm mt-1">Para: <span class="text-violet-400">{{ $empresa->razon_social }}</span></p>
</div>

<div class="max-w-md">
    <form method="POST" action="{{ route('backoffice.empresas.admin.store', $empresa) }}" class="space-y-5">
        @csrf

        <div class="bg-[#0d1117] border border-white/5 rounded-2xl p-6 space-y-5">

            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Nombre completo *</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white
                              focus:outline-none focus:border-violet-500 transition-colors"
                       placeholder="Juan Pérez">
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Correo electrónico *</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white
                              focus:outline-none focus:border-violet-500 transition-colors"
                       placeholder="admin@empresa.com">
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Contraseña *</label>
                <input type="password" name="password" required minlength="8"
                       class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white
                              focus:outline-none focus:border-violet-500 transition-colors"
                       placeholder="Mínimo 8 caracteres">
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Confirmar contraseña *</label>
                <input type="password" name="password_confirmation" required
                       class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white
                              focus:outline-none focus:border-violet-500 transition-colors">
            </div>

            <div class="bg-violet-600/5 border border-violet-600/20 rounded-xl px-4 py-3">
                <p class="text-xs text-violet-400">
                    <i class="fas fa-info-circle mr-1"></i>
                    Este usuario tendrá acceso a <strong>{{ $empresa->razon_social }}</strong>
                    @if($empresa->filiales->count())
                        y sus {{ $empresa->filiales->count() }} filial(es).
                    @endif
                    @if($empresa->esFilial())
                        y a la matriz <strong>{{ $empresa->padre->razon_social }}</strong>.
                    @endif
                </p>
            </div>

        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('backoffice.empresas') }}"
               class="px-5 py-2.5 rounded-xl text-sm text-slate-400 hover:text-white transition-colors">
                Cancelar
            </a>
            <button type="submit"
                    class="bg-emerald-600 hover:bg-emerald-500 text-white text-sm px-6 py-2.5 rounded-xl transition-colors">
                <i class="fas fa-user-plus mr-2"></i>Crear y asignar
            </button>
        </div>
    </form>
</div>

@endsection
