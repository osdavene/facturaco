@extends('backoffice.layout')
@section('title', 'Editar usuario')

@section('content')

<div class="mb-8">
    <a href="{{ route('backoffice.usuarios') }}" class="text-slate-500 hover:text-slate-300 text-sm transition-colors">
        <i class="fas fa-arrow-left mr-2"></i>Volver a usuarios
    </a>
    <h1 class="font-display font-black text-2xl text-white mt-3">{{ $usuario->name }}</h1>
    <p class="text-slate-500 text-sm mt-1">{{ $usuario->email }}</p>
</div>

<div class="max-w-2xl space-y-5">
    <form method="POST" action="{{ route('backoffice.usuarios.update', $usuario) }}">
        @csrf @method('PUT')

        {{-- Datos básicos --}}
        <div class="bg-[#0d1117] border border-white/5 rounded-2xl p-6 space-y-4">
            <h2 class="font-display font-bold text-white text-sm">Datos del usuario</h2>

            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Nombre completo *</label>
                <input type="text" name="name" value="{{ old('name', $usuario->name) }}" required
                       class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white
                              focus:outline-none focus:border-violet-500 transition-colors">
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Correo electrónico *</label>
                <input type="email" name="email" value="{{ old('email', $usuario->email) }}" required
                       class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white
                              focus:outline-none focus:border-violet-500 transition-colors">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Nueva contraseña <span class="text-slate-600">(dejar vacío para no cambiar)</span></label>
                    <input type="password" name="password"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white
                                  focus:outline-none focus:border-violet-500 transition-colors"
                           placeholder="••••••••">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white
                                  focus:outline-none focus:border-violet-500 transition-colors"
                           placeholder="••••••••">
                </div>
            </div>
        </div>

        {{-- Empresas asignadas --}}
        <div class="bg-[#0d1117] border border-white/5 rounded-2xl p-6">
            <h2 class="font-display font-bold text-white text-sm mb-1">Acceso a empresas</h2>
            <p class="text-xs text-slate-500 mb-4">Marca las empresas a las que tiene acceso y si es admin en cada una.</p>

            <div class="space-y-2">
                @foreach($todasEmpresas as $emp)
                @php $asignado = in_array($emp->id, $empresasUsuario); @endphp
                <div class="bg-white/3 rounded-xl px-4 py-3 flex items-center gap-4">
                    {{-- Checkbox asignación --}}
                    <label class="flex items-center gap-2 flex-1 cursor-pointer">
                        <input type="checkbox" name="empresa_ids[]" value="{{ $emp->id }}"
                               {{ $asignado ? 'checked' : '' }}
                               class="w-4 h-4 accent-violet-500">
                        <div>
                            <p class="text-sm text-slate-200">{{ $emp->razon_social }}</p>
                            <p class="text-xs text-slate-600">
                                {{ $emp->esFilial() ? 'Filial' : 'Matriz' }} · NIT: {{ $emp->nit }}
                            </p>
                        </div>
                    </label>

                    {{-- Toggle admin --}}
                    @php
                        $esAdmin = $usuario->empresas->where('id', $emp->id)->first()?->pivot?->rol === 'admin';
                    @endphp
                    <label class="flex items-center gap-2 cursor-pointer shrink-0">
                        <input type="checkbox" name="admins[]" value="{{ $emp->id }}"
                               {{ $esAdmin ? 'checked' : '' }}
                               class="w-4 h-4 accent-amber-500">
                        <span class="text-xs text-slate-500">Admin</span>
                    </label>
                </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('backoffice.usuarios') }}"
               class="px-5 py-2.5 rounded-xl text-sm text-slate-400 hover:text-white transition-colors">
                Cancelar
            </a>
            <button type="submit"
                    class="bg-violet-600 hover:bg-violet-500 text-white text-sm px-6 py-2.5 rounded-xl transition-colors">
                <i class="fas fa-check mr-2"></i>Guardar cambios
            </button>
        </div>
    </form>
</div>

@endsection
