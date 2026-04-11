@extends('backoffice.layout')
@section('title', 'Usuarios')

@section('content')

<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="font-display font-black text-2xl text-white">Usuarios</h1>
        <p class="text-slate-500 text-sm mt-1">Todos los usuarios de la plataforma</p>
    </div>
</div>

<div class="bg-[#0d1117] border border-white/5 rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-white/5">
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-5 py-3">Usuario</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden md:table-cell">Email</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Empresas asignadas</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-5 py-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $usuario)
                <tr class="border-b border-white/3 hover:bg-white/2 transition-colors">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-violet-600/20 rounded-xl flex items-center justify-center
                                        font-bold text-sm text-violet-400 flex-shrink-0">
                                {{ strtoupper(substr($usuario->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-white">{{ $usuario->name }}</p>
                                @if($usuario->getRoleNames()->isNotEmpty())
                                <span class="text-xs text-slate-500 capitalize">
                                    {{ str_replace('-', ' ', $usuario->getRoleNames()->first()) }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-3 py-4 hidden md:table-cell">
                        <span class="text-sm text-slate-400">{{ $usuario->email }}</span>
                    </td>
                    <td class="px-3 py-4">
                        <div class="flex flex-wrap gap-1">
                            @forelse($usuario->empresas as $emp)
                            <span class="text-[10px] px-2 py-0.5 rounded-full
                                         {{ $emp->pivot->rol === 'admin' ? 'bg-amber-500/15 text-amber-400' : 'bg-slate-700/60 text-slate-400' }}">
                                {{ $emp->pivot->rol === 'admin' ? '★ ' : '' }}{{ Str::limit($emp->razon_social, 22) }}
                            </span>
                            @empty
                            <span class="text-xs text-slate-600">Sin empresas</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('backoffice.usuarios.editar', $usuario) }}"
                               class="text-xs px-3 py-1.5 rounded-lg border border-white/10
                                      text-slate-400 hover:bg-white/5 transition-colors">
                                <i class="fas fa-pen mr-1"></i>Editar
                            </a>
                            <form method="POST" action="{{ route('backoffice.usuarios.destroy', $usuario) }}"
                                  onsubmit="return confirm('¿Eliminar a {{ $usuario->name }}? Esta acción no se puede deshacer.')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="text-xs px-3 py-1.5 rounded-lg border border-red-500/20
                                               text-red-500/70 hover:bg-red-500/10 transition-colors">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-5 py-16 text-center text-slate-600">
                        <i class="fas fa-users text-3xl mb-3"></i>
                        <p>No hay usuarios registrados.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($usuarios->hasPages())
    <div class="px-5 py-4 border-t border-white/5">
        {{ $usuarios->links() }}
    </div>
    @endif
</div>

@endsection
