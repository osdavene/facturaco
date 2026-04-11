@extends('backoffice.layout')
@section('title', 'Empresas')

@section('content')

<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="font-display font-black text-2xl text-white">Empresas</h1>
        <p class="text-slate-500 text-sm mt-1">Todas las empresas registradas en la plataforma</p>
    </div>
    <a href="{{ route('backoffice.empresas.crear') }}"
       class="bg-violet-600 hover:bg-violet-500 text-white text-sm px-4 py-2 rounded-xl transition-colors">
        <i class="fas fa-plus mr-2"></i>Nueva empresa
    </a>
</div>

<div class="space-y-4">
    @forelse($empresas as $emp)
    <div class="bg-[#0d1117] border border-white/5 rounded-2xl overflow-hidden">

        {{-- Matriz --}}
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-violet-600/10 border border-violet-600/20 rounded-xl
                            flex items-center justify-center font-display font-black text-violet-400 text-sm">
                    {{ strtoupper(substr($emp->razon_social, 0, 2)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <p class="font-medium text-white">{{ $emp->razon_social }}</p>
                        <span class="text-xs px-2 py-0.5 bg-violet-600/20 text-violet-400 rounded-full">Matriz</span>
                    </div>
                    <p class="text-slate-500 text-xs mt-0.5">
                        NIT: {{ $emp->nit }}
                        @if($emp->email) · {{ $emp->email }} @endif
                        · {{ $emp->usuarios_count }} usuario(s)
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('backoffice.empresas.admin.crear', $emp) }}"
                   class="text-xs px-3 py-1.5 rounded-lg border border-emerald-500/30
                          text-emerald-400 hover:bg-emerald-500/10 transition-colors">
                    <i class="fas fa-user-plus mr-1"></i>Admin
                </a>
                <form method="POST" action="{{ route('backoffice.impersonar', $emp) }}">
                    @csrf
                    <button type="submit"
                            class="text-xs px-3 py-1.5 rounded-lg border border-amber-500/30
                                   text-amber-400 hover:bg-amber-500/10 transition-colors">
                        <i class="fas fa-eye mr-1"></i>Ver
                    </button>
                </form>
                <a href="{{ route('backoffice.empresas.editar', $emp) }}"
                   class="text-xs px-3 py-1.5 rounded-lg border border-white/10
                          text-slate-400 hover:bg-white/5 transition-colors">
                    <i class="fas fa-pen"></i>
                </a>
                <form method="POST" action="{{ route('backoffice.empresas.destroy', $emp) }}"
                      onsubmit="return confirm('¿Eliminar {{ $emp->razon_social }}? Las filiales quedarán como empresas independientes.')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="text-xs px-3 py-1.5 rounded-lg border border-red-500/20
                                   text-red-500/70 hover:bg-red-500/10 transition-colors">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>

        {{-- Filiales --}}
        @if($emp->filiales->count())
        <div class="border-t border-white/5">
            @foreach($emp->filiales as $filial)
            <div class="flex items-center justify-between px-6 py-3 bg-white/2
                        {{ !$loop->last ? 'border-b border-white/3' : '' }}">
                <div class="flex items-center gap-3 ml-6">
                    <i class="fas fa-code-branch text-slate-600 text-xs"></i>
                    <div>
                        <div class="flex items-center gap-2">
                            <p class="text-sm text-slate-300">{{ $filial->razon_social }}</p>
                            <span class="text-xs px-2 py-0.5 bg-slate-700/50 text-slate-500 rounded-full">Filial</span>
                        </div>
                        <p class="text-xs text-slate-600">NIT: {{ $filial->nit }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('backoffice.empresas.admin.crear', $filial) }}"
                       class="text-xs px-3 py-1.5 rounded-lg border border-emerald-500/20
                              text-emerald-500/70 hover:bg-emerald-500/10 transition-colors">
                        <i class="fas fa-user-plus mr-1"></i>Admin
                    </a>
                    <form method="POST" action="{{ route('backoffice.impersonar', $filial) }}">
                        @csrf
                        <button type="submit"
                                class="text-xs px-3 py-1.5 rounded-lg border border-amber-500/20
                                       text-amber-500/70 hover:bg-amber-500/10 transition-colors">
                            <i class="fas fa-eye mr-1"></i>Ver
                        </button>
                    </form>
                    <a href="{{ route('backoffice.empresas.editar', $filial) }}"
                       class="text-xs px-3 py-1.5 rounded-lg border border-white/10
                              text-slate-500 hover:bg-white/5 transition-colors">
                        <i class="fas fa-pen"></i>
                    </a>
                </div>
            </div>
            @endforeach

            {{-- Agregar filial --}}
            <div class="px-6 py-2.5 bg-white/1">
                <a href="{{ route('backoffice.empresas.crear') }}?padre={{ $emp->id }}"
                   class="text-xs text-slate-600 hover:text-violet-400 transition-colors">
                    <i class="fas fa-plus mr-1"></i>Agregar filial a {{ $emp->razon_social }}
                </a>
            </div>
        </div>
        @else
        <div class="border-t border-white/5 px-6 py-2.5">
            <a href="{{ route('backoffice.empresas.crear') }}?padre={{ $emp->id }}"
               class="text-xs text-slate-600 hover:text-violet-400 transition-colors">
                <i class="fas fa-plus mr-1"></i>Agregar filial
            </a>
        </div>
        @endif

    </div>
    @empty
    <div class="bg-[#0d1117] border border-white/5 rounded-2xl p-16 text-center">
        <i class="fas fa-building text-4xl text-slate-700 mb-4"></i>
        <p class="text-slate-400 font-medium">No hay empresas registradas</p>
        <a href="{{ route('backoffice.empresas.crear') }}"
           class="inline-block mt-5 bg-violet-600 hover:bg-violet-500 text-white text-sm px-6 py-2.5 rounded-xl transition-colors">
            Crear primera empresa
        </a>
    </div>
    @endforelse
</div>

@endsection
