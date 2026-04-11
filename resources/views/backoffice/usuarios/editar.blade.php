@extends('backoffice.layout')
@section('title', 'Editar usuario')

@section('content')

<div class="mb-8">
    <a href="{{ route('backoffice.usuarios') }}"
       class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-200 text-sm transition-colors mb-4">
        <i class="fas fa-arrow-left text-xs"></i>Volver a usuarios
    </a>
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-white
                    bg-gradient-to-br from-blue-500 to-purple-600">
            {{ strtoupper(substr($usuario->name, 0, 2)) }}
        </div>
        <div>
            <h1 class="font-display font-black text-2xl">{{ $usuario->name }}</h1>
            <p class="text-slate-500 text-sm">{{ $usuario->email }}</p>
        </div>
    </div>
</div>

<div class="max-w-2xl mx-auto space-y-5">
    <form method="POST" action="{{ route('backoffice.usuarios.update', $usuario) }}">
        @csrf @method('PUT')

        {{-- Datos básicos --}}
        <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-6 space-y-5">
            <h2 class="font-display font-bold text-base flex items-center gap-2">
                <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center text-black text-xs font-black">1</span>
                Datos del usuario
            </h2>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Nombre completo *</label>
                <input type="text" name="name" value="{{ old('name', $usuario->name) }}" required
                       class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5 text-sm
                              focus:outline-none focus:border-amber-500 transition-colors">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Correo electrónico *</label>
                <input type="email" name="email" value="{{ old('email', $usuario->email) }}" required
                       class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5 text-sm
                              focus:outline-none focus:border-amber-500 transition-colors">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
                        Nueva contraseña <span class="text-slate-600 normal-case">(vacío = sin cambio)</span>
                    </label>
                    <input type="password" name="password" placeholder="••••••••"
                           class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5 text-sm
                                  focus:outline-none focus:border-amber-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation" placeholder="••••••••"
                           class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5 text-sm
                                  focus:outline-none focus:border-amber-500 transition-colors">
                </div>
            </div>
        </div>

        {{-- Empresas asignadas --}}
        <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-6">
            <h2 class="font-display font-bold text-base flex items-center gap-2 mb-1">
                <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center text-black text-xs font-black">2</span>
                Acceso a empresas
            </h2>
            <p class="text-xs text-slate-500 mb-4 ml-8">
                Marca las empresas a las que tiene acceso y si es admin en cada una.
            </p>

            <div class="space-y-2">
                @foreach($todasEmpresas as $emp)
                @php $asignado = in_array($emp->id, $empresasUsuario); @endphp
                <div class="bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-3 flex items-center gap-4
                            hover:border-amber-500/30 transition-colors">
                    <label class="flex items-center gap-3 flex-1 cursor-pointer">
                        <input type="checkbox" name="empresa_ids[]" value="{{ $emp->id }}"
                               {{ $asignado ? 'checked' : '' }}
                               class="w-4 h-4 accent-amber-500 flex-shrink-0">
                        <div class="min-w-0">
                            <p class="text-sm font-medium">{{ $emp->razon_social }}</p>
                            <p class="text-xs text-slate-500">
                                {{ $emp->esFilial() ? 'Filial' : 'Matriz' }} · NIT: {{ $emp->nit }}
                            </p>
                        </div>
                    </label>
                    @php
                        $esAdmin = $usuario->empresas->where('id', $emp->id)->first()?->pivot?->rol === 'admin';
                    @endphp
                    <label class="flex items-center gap-2 cursor-pointer shrink-0">
                        <input type="checkbox" name="admins[]" value="{{ $emp->id }}"
                               {{ $esAdmin ? 'checked' : '' }}
                               class="w-4 h-4 accent-amber-500">
                        <span class="text-xs text-slate-400">Admin</span>
                    </label>
                </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('backoffice.usuarios') }}"
               class="px-6 py-2.5 bg-[#1a2235] border border-[#1e2d47] rounded-xl
                      text-slate-400 hover:text-slate-200 text-sm transition-colors">
                Cancelar
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600
                           text-black font-semibold text-sm px-6 py-2.5 rounded-xl transition-colors">
                <i class="fas fa-check text-xs"></i>Guardar cambios
            </button>
        </div>
    </form>
</div>

@endsection
