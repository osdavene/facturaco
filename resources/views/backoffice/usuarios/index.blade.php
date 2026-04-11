@extends('backoffice.layout')
@section('title', 'Usuarios')

@section('content')

<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="font-display font-black text-2xl">Usuarios</h1>
        <p class="text-slate-500 text-sm mt-1">Todos los usuarios de la plataforma</p>
    </div>
</div>

<div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl overflow-hidden">

    <div class="grid grid-cols-2 border-b border-[#1e2d47]">
        <div class="px-5 py-3 text-center border-r border-[#1e2d47]">
            <div class="font-display font-bold text-lg">{{ $usuarios->total() }}</div>
            <div class="text-xs text-slate-500">Total usuarios</div>
        </div>
        <div class="px-5 py-3 text-center">
            <div class="font-display font-bold text-lg text-emerald-400">
                {{ $usuarios->where('activo', true)->count() }}
            </div>
            <div class="text-xs text-slate-500">Activos</div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-[#1e2d47]">
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-5 py-3">Usuario</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden md:table-cell">Email</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Empresas asignadas</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-5 py-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $usuario)
                <tr class="border-b border-[#1e2d47]/50 hover:bg-[#1a2235]/50 transition-colors">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-sm
                                        text-white flex-shrink-0 bg-gradient-to-br from-blue-500 to-purple-600">
                                {{ strtoupper(substr($usuario->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold">{{ $usuario->name }}</p>
                                @if($usuario->getRoleNames()->isNotEmpty())
                                <p class="text-xs text-slate-500 capitalize">
                                    {{ str_replace('-', ' ', $usuario->getRoleNames()->first()) }}
                                </p>
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
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold
                                         {{ $emp->pivot->rol === 'admin'
                                            ? 'bg-amber-500/10 text-amber-500'
                                            : 'bg-[#1a2235] text-slate-400' }}">
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
                               class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                      flex items-center justify-center text-slate-400
                                      hover:text-amber-500 hover:border-amber-500/50 transition-colors"
                               title="Editar">
                                <i class="fas fa-pen text-xs"></i>
                            </a>
                            <form method="POST" action="{{ route('backoffice.usuarios.destroy', $usuario) }}"
                                  onsubmit="return confirm('¿Eliminar a {{ $usuario->name }}? Esta acción no se puede deshacer.')">
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
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-5 py-16 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-14 h-14 bg-[#1a2235] rounded-2xl flex items-center justify-center text-slate-600 text-2xl">
                                <i class="fas fa-users"></i>
                            </div>
                            <p class="text-slate-500">No hay usuarios registrados</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($usuarios->hasPages())
    <div class="px-5 py-4 border-t border-[#1e2d47]">
        {{ $usuarios->links() }}
    </div>
    @endif
</div>

@endsection
