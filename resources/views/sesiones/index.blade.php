@extends('layouts.app')
@section('title', 'Sesiones')
@section('page-title', 'Administración · Sesiones')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- Mensajes --}}
    @if(session('success'))
    <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-xl px-5 py-3 flex items-center gap-3">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-5 py-3 flex items-center gap-3">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════
         BLOQUE 1: SESIONES ACTIVAS
    ════════════════════════════════════════════════════════ --}}
    <div>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="font-display font-bold text-2xl">Sesiones Activas</h1>
                <p class="text-slate-500 text-sm mt-0.5">
                    {{ $sesiones->count() }} sesión(es) ·
                    <span class="text-emerald-400 font-semibold">{{ $totalEnLinea }} en línea ahora</span>
                </p>
            </div>
            @if($sesiones->count() > 1)
            <form method="POST" action="{{ route('sesiones.destroyAll') }}"
                  onsubmit="return confirm('¿Cerrar todas las sesiones excepto la tuya?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="flex items-center gap-2 bg-red-500/10 border border-red-500/30
                               text-red-400 hover:bg-red-500/20 font-semibold text-sm
                               px-4 py-2.5 rounded-xl transition-colors">
                    <i class="fas fa-power-off text-xs"></i> Cerrar todas las demás
                </button>
            </form>
            @endif
        </div>

        <div class="space-y-3">
            @forelse($sesiones as $s)
            <div class="bg-[#141c2e] border {{ $s->es_yo ? 'border-amber-500/40' : 'border-[#1e2d47]' }} rounded-2xl px-5 py-4">
                <div class="flex items-center gap-4">
                    <div class="relative flex-shrink-0">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($s->name) }}&background=f59e0b&color=000&bold=true&size=80"
                             class="w-11 h-11 rounded-xl object-cover">
                        <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-[#141c2e]
                                     {{ $s->en_linea ? 'bg-emerald-500' : 'bg-slate-600' }}"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-semibold text-slate-200 text-sm">{{ $s->name }}</span>
                            @if($s->es_yo)
                            <span class="text-xs bg-amber-500/20 text-amber-400 border border-amber-500/30 px-2 py-0.5 rounded-full font-semibold">Tú</span>
                            @endif
                            @if($s->en_linea)
                            <span class="text-xs bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 px-2 py-0.5 rounded-full font-semibold">En línea</span>
                            @endif
                        </div>
                        <div class="text-xs text-slate-500 mt-0.5">{{ $s->email }}</div>
                        @if($s->cargo)
                        <div class="text-xs text-slate-600 mt-0.5">{{ $s->cargo }}</div>
                        @endif
                    </div>
                    <div class="hidden sm:flex flex-col items-end gap-1 text-right">
                        <div class="flex items-center gap-1.5 text-xs text-slate-400">
                            <i class="fas fa-{{ $s->dispositivo === 'Móvil' ? 'mobile-alt' : ($s->dispositivo === 'Tablet' ? 'tablet-alt' : 'desktop') }} text-slate-600"></i>
                            {{ $s->navegador }} · {{ $s->dispositivo }}
                        </div>
                        <div class="flex items-center gap-1.5 text-xs text-slate-500">
                            <i class="fas fa-network-wired text-slate-600"></i>
                            {{ $s->ip_address ?? 'IP desconocida' }}
                        </div>
                        <div class="flex items-center gap-1.5 text-xs {{ $s->en_linea ? 'text-emerald-400' : 'text-slate-600' }}">
                            <i class="fas fa-clock"></i> {{ $s->activo_hace }}
                        </div>
                    </div>
                    <div class="flex-shrink-0 ml-2">
                        @if(!$s->es_yo)
                        <form method="POST" action="{{ route('sesiones.destroy', $s->id) }}"
                              onsubmit="return confirm('¿Cerrar la sesión de {{ addslashes($s->name) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="w-9 h-9 bg-[#1a2235] border border-[#1e2d47] rounded-xl
                                           flex items-center justify-center text-slate-500
                                           hover:text-red-400 hover:border-red-500/50 transition-colors">
                                <i class="fas fa-sign-out-alt text-xs"></i>
                            </button>
                        </form>
                        @else
                        <div class="w-9 h-9 flex items-center justify-center text-slate-700">
                            <i class="fas fa-lock text-xs"></i>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="card py-12 flex flex-col items-center text-slate-500">
                <i class="fas fa-users-slash text-4xl mb-3 opacity-20"></i>
                <p class="font-semibold text-sm">No hay sesiones activas</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         BLOQUE 2: HISTORIAL
    ════════════════════════════════════════════════════════ --}}
    <div>
        <h2 class="font-display font-bold text-xl mb-4">Historial de Accesos</h2>

        {{-- Filtros --}}
        <form method="GET" action="{{ route('sesiones.index') }}"
              class="card p-4 mb-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">

                <div>
                    <label class="block text-xs text-slate-500 mb-1 uppercase tracking-wider">Usuario</label>
                    <input type="text" name="usuario" value="{{ request('usuario') }}"
                           placeholder="Nombre o email..."
                           class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-3 py-2
                                  text-sm text-slate-200 placeholder-slate-600
                                  focus:outline-none focus:border-amber-500 transition-colors">
                </div>

                <div>
                    <label class="block text-xs text-slate-500 mb-1 uppercase tracking-wider">Acción</label>
                    <select name="accion"
                            class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-3 py-2
                                   text-sm text-slate-200 focus:outline-none focus:border-amber-500 transition-colors">
                        <option value="">Todas</option>
                        <option value="login"  {{ request('accion') === 'login'  ? 'selected' : '' }}>Login</option>
                        <option value="logout" {{ request('accion') === 'logout' ? 'selected' : '' }}>Logout</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs text-slate-500 mb-1 uppercase tracking-wider">Desde</label>
                    <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"
                           class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-3 py-2
                                  text-sm text-slate-200 focus:outline-none focus:border-amber-500 transition-colors">
                </div>

                <div>
                    <label class="block text-xs text-slate-500 mb-1 uppercase tracking-wider">Hasta</label>
                    <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}"
                           class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-3 py-2
                                  text-sm text-slate-200 focus:outline-none focus:border-amber-500 transition-colors">
                </div>
            </div>

            <div class="flex gap-2 mt-3">
                <button type="submit"
                        class="bg-amber-500 hover:bg-amber-600 text-black font-bold text-sm
                               px-4 py-2 rounded-xl transition-colors">
                    <i class="fas fa-search mr-1"></i> Filtrar
                </button>
                <a href="{{ route('sesiones.index') }}"
                   class="bg-[#1a2235] border border-[#1e2d47] text-slate-400 font-semibold text-sm
                          px-4 py-2 rounded-xl hover:border-slate-500 transition-colors">
                    Limpiar
                </a>
            </div>
        </form>

        {{-- Tabla historial --}}
        <div class="card overflow-hidden">
            @if($historial->isEmpty())
            <div class="py-12 flex flex-col items-center text-slate-500">
                <i class="fas fa-history text-4xl mb-3 opacity-20"></i>
                <p class="font-semibold text-sm">No hay registros con esos filtros</p>
            </div>
            @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-[#1e2d47] text-xs text-slate-500 uppercase tracking-wider">
                        <th class="px-5 py-3 text-left">Usuario</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">IP</th>
                        <th class="px-5 py-3 text-left hidden lg:table-cell">Navegador</th>
                        <th class="px-5 py-3 text-center">Acción</th>
                        <th class="px-5 py-3 text-right">Fecha y Hora</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1e2d47]">
                    @foreach($historial as $log)
                    <tr class="hover:bg-[#1a2235] transition-colors">
                        <td class="px-5 py-3">
                            <div class="font-semibold text-slate-200 text-sm">{{ $log->user->name ?? '—' }}</div>
                            <div class="text-xs text-slate-500">{{ $log->user->email ?? '' }}</div>
                        </td>
                        <td class="px-5 py-3 text-slate-400 text-xs hidden md:table-cell font-mono">
                            {{ $log->ip_address ?? '—' }}
                        </td>
                        <td class="px-5 py-3 hidden lg:table-cell">
                            <div class="text-xs text-slate-400">{{ $log->navegador }}</div>
                            <div class="text-xs text-slate-600">{{ $log->dispositivo }}</div>
                        </td>
                        <td class="px-5 py-3 text-center">
                            @if($log->accion === 'login')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1
                                         bg-emerald-500/10 text-emerald-400 rounded-lg text-xs font-semibold">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1
                                         bg-slate-500/10 text-slate-400 rounded-lg text-xs font-semibold">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right text-xs text-slate-400">
                            <div>{{ $log->fecha_hora->format('d/m/Y') }}</div>
                            <div class="text-slate-600">{{ $log->fecha_hora->format('h:i:s A') }}</div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if($historial->hasPages())
            <div class="px-5 py-4 border-t border-[#1e2d47]">
                {{ $historial->links() }}
            </div>
            @endif
            @endif
        </div>
    </div>

</div>
@endsection