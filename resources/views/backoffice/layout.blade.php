@php $temaActual = auth()->check() ? (auth()->user()->tema ?? 'dark') : 'dark'; @endphp
<!DOCTYPE html>
<html lang="es" class="{{ $temaActual === 'light' ? '' : 'dark' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>BackOffice — @yield('title', 'Panel de Plataforma')</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0b0f1a] text-slate-200 font-sans">

<div class="flex min-h-screen">

    {{-- ── Sidebar ────────────────────────────────────────────── --}}
    <aside class="fixed top-0 left-0 h-full w-60 bg-[#111827] border-r border-[#1e2d47] flex flex-col z-50">

        {{-- Logo --}}
        <div class="px-5 py-5 border-b border-[#1e2d47]">
            <a href="{{ route('backoffice.dashboard') }}" class="flex items-center gap-3">
                <div class="w-9 h-9 bg-amber-500 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-layer-group text-black text-sm"></i>
                </div>
                <div>
                    <p class="font-display font-black text-white text-sm leading-none">BackOffice</p>
                    <p class="text-slate-500 text-xs mt-0.5">Panel de plataforma</p>
                </div>
            </a>
        </div>

        {{-- Navegación --}}
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">

            <p class="text-[10px] font-semibold text-slate-600 uppercase tracking-widest px-3 mb-2">General</p>

            <a href="{{ route('backoffice.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-colors
                      {{ request()->routeIs('backoffice.dashboard') ? 'bg-amber-500/10 text-amber-500' : 'text-slate-500 hover:bg-[#1a2235] hover:text-slate-200' }}">
                <i class="fas fa-gauge-high w-4 text-center"></i>
                <span>Super Panel</span>
            </a>

            <p class="text-[10px] font-semibold text-slate-600 uppercase tracking-widest px-3 mt-4 mb-2">Gestión</p>

            <a href="{{ route('backoffice.empresas') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-colors
                      {{ request()->routeIs('backoffice.empresas*') ? 'bg-amber-500/10 text-amber-500' : 'text-slate-500 hover:bg-[#1a2235] hover:text-slate-200' }}">
                <i class="fas fa-building w-4 text-center"></i>
                <span>Empresas</span>
            </a>

            <a href="{{ route('backoffice.usuarios') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-colors
                      {{ request()->routeIs('backoffice.usuarios*') ? 'bg-amber-500/10 text-amber-500' : 'text-slate-500 hover:bg-[#1a2235] hover:text-slate-200' }}">
                <i class="fas fa-users w-4 text-center"></i>
                <span>Usuarios</span>
            </a>
        </nav>

        {{-- Footer sidebar --}}
        <div class="px-3 py-4 border-t border-[#1e2d47] space-y-2">

            {{-- Toggle tema --}}
            <form method="POST" action="{{ route('tema.cambiar') }}">
                @csrf
                <input type="hidden" name="tema" value="{{ $temaActual === 'dark' ? 'light' : 'dark' }}">
                <button type="submit"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm
                               text-slate-500 hover:bg-[#1a2235] hover:text-slate-200 transition-colors">
                    <i class="fas {{ $temaActual === 'dark' ? 'fa-sun' : 'fa-moon' }} w-4 text-center"></i>
                    <span>{{ $temaActual === 'dark' ? 'Tema claro' : 'Tema oscuro' }}</span>
                </button>
            </form>

            {{-- Usuario --}}
            <div class="px-3 py-2 bg-[#1a2235] rounded-xl border border-[#1e2d47]">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 bg-amber-500/10 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-crown text-amber-500 text-[9px]"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] text-slate-500 truncate">{{ auth()->user()->email }}</p>
                    </div>
                </div>
            </div>

            {{-- Cerrar sesión --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm
                               text-slate-500 hover:bg-red-500/10 hover:text-red-400 transition-colors">
                    <i class="fas fa-right-from-bracket w-4 text-center"></i>
                    <span>Cerrar sesión</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- ── Contenido principal ─────────────────────────────────── --}}
    <main class="ml-60 flex-1 p-8 min-w-0">

        {{-- Banner impersonando --}}
        @if(session('backoffice_impersonando'))
        <div class="mb-6 bg-amber-500/10 border border-amber-500/30 rounded-xl px-4 py-3 flex items-center justify-between">
            <span class="text-amber-500 text-sm">
                <i class="fas fa-eye mr-2"></i>Estás viendo la app como cliente
            </span>
            <form method="POST" action="{{ route('backoffice.salir') }}">
                @csrf
                <button class="text-amber-500 hover:text-amber-400 text-sm underline transition-colors">
                    Salir y volver al BackOffice
                </button>
            </form>
        </div>
        @endif

        {{-- Alertas de sesión --}}
        @if(session('success'))
        <div class="mb-6 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-xl px-4 py-3 text-sm flex items-center gap-2 fade-in">
            <i class="fas fa-check-circle flex-shrink-0"></i>{{ session('success') }}
        </div>
        @endif
        @if(session('info'))
        <div class="mb-6 bg-blue-500/10 border border-blue-500/30 text-blue-400 rounded-xl px-4 py-3 text-sm flex items-center gap-2 fade-in">
            <i class="fas fa-info-circle flex-shrink-0"></i>{{ session('info') }}
        </div>
        @endif
        @if($errors->any())
        <div class="mb-6 bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3 text-sm fade-in">
            @foreach($errors->all() as $e)
            <div class="flex items-center gap-2"><i class="fas fa-circle-exclamation flex-shrink-0"></i>{{ $e }}</div>
            @endforeach
        </div>
        @endif

        @yield('content')
    </main>

</div>

</body>
</html>
