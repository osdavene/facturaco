@extends('backoffice.layout')
@section('title', 'Empresas')

@section('content')

<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="font-display font-black text-2xl">Empresas</h1>
        <p class="text-slate-500 text-sm mt-1">Todas las empresas registradas en la plataforma</p>
    </div>
    <a href="{{ route('backoffice.empresas.crear') }}"
       class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600
              text-black font-semibold text-sm px-5 py-2.5 rounded-xl transition-colors">
        <i class="fas fa-plus text-xs"></i>Nueva empresa
    </a>
</div>

<div class="space-y-4">
    @forelse($empresas as $emp)
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl overflow-hidden">

        {{-- Matriz --}}
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-[#1a2235] border border-[#1e2d47] rounded-xl
                            flex items-center justify-center font-display font-black text-amber-500 text-sm">
                    {{ strtoupper(substr($emp->razon_social, 0, 2)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <p class="font-semibold">{{ $emp->razon_social }}</p>
                        <span class="text-[10px] px-2 py-0.5 bg-amber-500/10 text-amber-500 rounded-full font-semibold">Matriz</span>
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
                   class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                          flex items-center justify-center text-slate-400
                          hover:text-emerald-400 hover:border-emerald-500/50 transition-colors"
                   title="Crear admin">
                    <i class="fas fa-user-plus text-xs"></i>
                </a>
                <form method="POST" action="{{ route('backoffice.impersonar', $emp) }}">
                    @csrf
                    <button type="submit"
                            class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                   flex items-center justify-center text-slate-400
                                   hover:text-amber-500 hover:border-amber-500/50 transition-colors"
                            title="Ver como cliente">
                        <i class="fas fa-eye text-xs"></i>
                    </button>
                </form>
                <a href="{{ route('backoffice.empresas.editar', $emp) }}"
                   class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                          flex items-center justify-center text-slate-400
                          hover:text-amber-500 hover:border-amber-500/50 transition-colors"
                   title="Editar">
                    <i class="fas fa-pen text-xs"></i>
                </a>
                <form method="POST" action="{{ route('backoffice.empresas.destroy', $emp) }}"
                      onsubmit="return confirm('¿Eliminar {{ $emp->razon_social }}? Las filiales quedarán como matrices independientes.')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                   flex items-center justify-center text-slate-400
                                   hover:text-red-400 hover:border-red-500/50 transition-colors"
                            title="Eliminar">
                        <i class="fas fa-trash text-xs"></i>
                    </button>
                </form>
            </div>
        </div>

        {{-- Filiales --}}
        @if($emp->filiales->count())
        <div class="border-t border-[#1e2d47] divide-y divide-[#1e2d47]/50">
            @foreach($emp->filiales as $filial)
            <div class="flex items-center justify-between px-6 py-3 bg-[#0b0f1a]/30">
                <div class="flex items-center gap-3 ml-6">
                    <i class="fas fa-code-branch text-slate-600 text-xs"></i>
                    <div>
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-medium text-slate-300">{{ $filial->razon_social }}</p>
                            <span class="text-[10px] px-2 py-0.5 bg-[#1a2235] text-slate-500 rounded-full">Filial</span>
                        </div>
                        <p class="text-xs text-slate-600">NIT: {{ $filial->nit }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('backoffice.empresas.admin.crear', $filial) }}"
                       class="w-7 h-7 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                              flex items-center justify-center text-slate-400
                              hover:text-emerald-400 hover:border-emerald-500/50 transition-colors"
                       title="Crear admin">
                        <i class="fas fa-user-plus text-[10px]"></i>
                    </a>
                    <form method="POST" action="{{ route('backoffice.impersonar', $filial) }}">
                        @csrf
                        <button type="submit"
                                class="w-7 h-7 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                       flex items-center justify-center text-slate-400
                                       hover:text-amber-500 hover:border-amber-500/50 transition-colors"
                                title="Ver como cliente">
                            <i class="fas fa-eye text-[10px]"></i>
                        </button>
                    </form>
                    <a href="{{ route('backoffice.empresas.editar', $filial) }}"
                       class="w-7 h-7 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                              flex items-center justify-center text-slate-400
                              hover:text-amber-500 hover:border-amber-500/50 transition-colors"
                       title="Editar">
                        <i class="fas fa-pen text-[10px]"></i>
                    </a>
                </div>
            </div>
            @endforeach
            <div class="px-6 py-2.5 bg-[#0b0f1a]/30">
                <a href="{{ route('backoffice.empresas.crear') }}?padre={{ $emp->id }}"
                   class="text-xs text-slate-600 hover:text-amber-500 transition-colors">
                    <i class="fas fa-plus mr-1"></i>Agregar filial a {{ $emp->razon_social }}
                </a>
            </div>
        </div>
        @else
        <div class="border-t border-[#1e2d47] px-6 py-2.5">
            <a href="{{ route('backoffice.empresas.crear') }}?padre={{ $emp->id }}"
               class="text-xs text-slate-600 hover:text-amber-500 transition-colors">
                <i class="fas fa-plus mr-1"></i>Agregar filial
            </a>
        </div>
        @endif

    </div>
    @empty
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-16 text-center">
        <i class="fas fa-building text-4xl text-slate-700 mb-4 block"></i>
        <p class="text-slate-400 font-medium">No hay empresas registradas</p>
        <a href="{{ route('backoffice.empresas.crear') }}"
           class="inline-block mt-5 bg-amber-500 hover:bg-amber-600 text-black font-semibold text-sm px-6 py-2.5 rounded-xl transition-colors">
            Crear primera empresa
        </a>
    </div>
    @endforelse
</div>

@endsection
