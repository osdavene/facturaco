@extends('layouts.app')
@section('title', 'Sesiones Activas')
@section('page-title', 'Administración · Sesiones Activas')

@section('content')
<div class="max-w-5xl mx-auto">

    {{-- Mensajes --}}
    @if(session('success'))
    <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400
                rounded-xl px-5 py-3 mb-5 flex items-center gap-3">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-500/10 border border-red-500/30 text-red-400
                rounded-xl px-5 py-3 mb-5 flex items-center gap-3">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="font-display font-bold text-2xl">Sesiones Activas</h1>
            <p class="text-slate-500 text-sm mt-0.5">
                {{ $sesiones->count() }} sesión(es) iniciada(s) ·
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

    {{-- Tarjetas de sesión --}}
    <div class="space-y-3">
        @forelse($sesiones as $s)
        <div class="bg-[#141c2e] border {{ $s->es_yo ? 'border-amber-500/40' : 'border-[#1e2d47]' }}
                    rounded-2xl px-5 py-4">
            <div class="flex items-center gap-4">

                {{-- Avatar --}}
                <div class="relative flex-shrink-0">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($s->name) }}&background=f59e0b&color=000&bold=true&size=80"
                         class="w-11 h-11 rounded-xl object-cover">
                    {{-- Indicador en línea --}}
                    <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-[#141c2e]
                                 {{ $s->en_linea ? 'bg-emerald-500' : 'bg-slate-600' }}"></span>
                </div>

                {{-- Info usuario --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-semibold text-slate-200 text-sm">{{ $s->name }}</span>
                        @if($s->es_yo)
                        <span class="text-xs bg-amber-500/20 text-amber-400 border border-amber-500/30
                                     px-2 py-0.5 rounded-full font-semibold">Tú</span>
                        @endif
                        @if($s->en_linea)
                        <span class="text-xs bg-emerald-500/10 text-emerald-400 border border-emerald-500/30
                                     px-2 py-0.5 rounded-full font-semibold">En línea</span>
                        @endif
                    </div>
                    <div class="text-xs text-slate-500 mt-0.5">{{ $s->email }}</div>
                    @if($s->cargo)
                    <div class="text-xs text-slate-600 mt-0.5">{{ $s->cargo }}</div>
                    @endif
                </div>

                {{-- Detalles técnicos --}}
                <div class="hidden sm:flex flex-col items-end gap-1 text-right">
                    <div class="flex items-center gap-1.5 text-xs text-slate-400">
                        <i class="fas fa-{{ $s->dispositivo === 'Móvil' ? 'mobile-alt' : ($s->dispositivo === 'Tablet' ? 'tablet-alt' : 'desktop') }}
                                  text-slate-600"></i>
                        {{ $s->navegador }} · {{ $s->dispositivo }}
                    </div>
                    <div class="flex items-center gap-1.5 text-xs text-slate-500">
                        <i class="fas fa-network-wired text-slate-600"></i>
                        {{ $s->ip_address ?? 'IP desconocida' }}
                    </div>
                    <div class="flex items-center gap-1.5 text-xs {{ $s->en_linea ? 'text-emerald-400' : 'text-slate-600' }}">
                        <i class="fas fa-clock"></i>
                        {{ $s->activo_hace }}
                    </div>
                </div>

                {{-- Acción cerrar --}}
                <div class="flex-shrink-0 ml-2">
                    @if(!$s->es_yo)
                    <form method="POST" action="{{ route('sesiones.destroy', $s->id) }}"
                          onsubmit="return confirm('¿Cerrar la sesión de {{ addslashes($s->name) }}?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="w-9 h-9 bg-[#1a2235] border border-[#1e2d47] rounded-xl
                                       flex items-center justify-center text-slate-500
                                       hover:text-red-400 hover:border-red-500/50 transition-colors"
                                title="Cerrar sesión">
                            <i class="fas fa-sign-out-alt text-xs"></i>
                        </button>
                    </form>
                    @else
                    <div class="w-9 h-9 flex items-center justify-center text-slate-700"
                         title="Es tu sesión actual">
                        <i class="fas fa-lock text-xs"></i>
                    </div>
                    @endif
                </div>

            </div>

            {{-- Info móvil (visible solo en pantallas pequeñas) --}}
            <div class="sm:hidden mt-3 pt-3 border-t border-[#1e2d47] flex flex-wrap gap-3 text-xs text-slate-500">
                <span><i class="fas fa-globe mr-1"></i>{{ $s->navegador }}</span>
                <span><i class="fas fa-network-wired mr-1"></i>{{ $s->ip_address ?? '—' }}</span>
                <span class="{{ $s->en_linea ? 'text-emerald-400' : '' }}">
                    <i class="fas fa-clock mr-1"></i>{{ $s->activo_hace }}
                </span>
            </div>
        </div>
        @empty
        <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl py-16
                    flex flex-col items-center text-slate-500">
            <i class="fas fa-users-slash text-5xl mb-4 opacity-20"></i>
            <p class="font-semibold">No hay sesiones activas</p>
        </div>
        @endforelse
    </div>

    {{-- Info --}}
    <div class="mt-4 bg-[#141c2e] border border-[#1e2d47] rounded-xl px-5 py-4
                flex items-start gap-3 text-sm text-slate-500">
        <i class="fas fa-info-circle text-amber-500 mt-0.5 flex-shrink-0"></i>
        <div>
            Un usuario aparece como <strong class="text-emerald-400">En línea</strong>
            si tuvo actividad en los últimos <strong class="text-slate-400">5 minutos</strong>.
            Puedes cerrar cualquier sesión excepto la tuya. La página no se actualiza sola —
            recarga para ver el estado más reciente.
        </div>
    </div>

</div>
@endsection