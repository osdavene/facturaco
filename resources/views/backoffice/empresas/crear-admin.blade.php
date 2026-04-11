@extends('backoffice.layout')
@section('title', 'Crear administrador')

@section('content')

<div class="mb-8">
    <a href="{{ route('backoffice.empresas') }}"
       class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-200 text-sm transition-colors mb-4">
        <i class="fas fa-arrow-left text-xs"></i>Volver a empresas
    </a>
    <h1 class="font-display font-black text-2xl">Nuevo administrador</h1>
    <p class="text-slate-500 text-sm mt-1">
        Para: <span class="text-amber-500 font-semibold">{{ $empresa->razon_social }}</span>
    </p>
</div>

<div class="max-w-md">
    <form method="POST" action="{{ route('backoffice.empresas.admin.store', $empresa) }}">
        @csrf

        <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-6 space-y-5 mb-5">

            <h2 class="font-display font-bold text-base flex items-center gap-2">
                <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center text-black text-xs font-black">1</span>
                Datos del usuario
            </h2>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Nombre completo *</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       placeholder="Juan Pérez"
                       class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5 text-sm
                              focus:outline-none focus:border-amber-500 transition-colors
                              @error('name') border-red-500 @enderror">
                @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Correo electrónico *</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       placeholder="admin@empresa.com"
                       class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5 text-sm
                              focus:outline-none focus:border-amber-500 transition-colors
                              @error('email') border-red-500 @enderror">
                @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Contraseña * <span class="text-slate-600 normal-case">(mín. 8)</span></label>
                    <input type="password" name="password" required minlength="8"
                           placeholder="••••••••"
                           class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5 text-sm
                                  focus:outline-none focus:border-amber-500 transition-colors
                                  @error('password') border-red-500 @enderror">
                    @error('password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Confirmar *</label>
                    <input type="password" name="password_confirmation" required
                           placeholder="••••••••"
                           class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5 text-sm
                                  focus:outline-none focus:border-amber-500 transition-colors">
                </div>
            </div>
        </div>

        {{-- Info acceso --}}
        <div class="bg-amber-500/5 border border-amber-500/20 rounded-xl px-4 py-3 mb-5">
            <p class="text-sm text-amber-500/80">
                <i class="fas fa-info-circle mr-2"></i>
                Este usuario tendrá acceso como admin a
                <strong>{{ $empresa->razon_social }}</strong>
                @if($empresa->filiales->count())
                    y sus {{ $empresa->filiales->count() }} filial(es).
                @endif
                @if($empresa->esFilial())
                    y a la matriz <strong>{{ $empresa->padre->razon_social }}</strong>.
                @endif
            </p>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('backoffice.empresas') }}"
               class="px-6 py-2.5 bg-[#1a2235] border border-[#1e2d47] rounded-xl
                      text-slate-400 hover:text-slate-200 text-sm transition-colors">
                Cancelar
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600
                           text-black font-semibold text-sm px-6 py-2.5 rounded-xl transition-colors">
                <i class="fas fa-user-plus text-xs"></i>Crear y asignar
            </button>
        </div>
    </form>
</div>

@endsection
