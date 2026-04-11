<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>BackOffice — @yield('title', 'Panel de Plataforma')</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#07090f] text-slate-200 font-sans">

<div class="flex min-h-screen">

    {{-- Sidebar --}}
    <aside class="fixed top-0 left-0 h-full w-60 bg-[#0d1117] border-r border-violet-900/30 flex flex-col z-50">

        {{-- Logo --}}
        <div class="px-5 py-5 border-b border-violet-900/30">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-violet-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-layer-group text-white text-sm"></i>
                </div>
                <div>
                    <p class="font-display font-black text-white text-sm leading-none">BackOffice</p>
                    <p class="text-violet-400 text-xs mt-0.5">Panel de plataforma</p>
                </div>
            </div>
        </div>

        {{-- Navegación --}}
        <nav class="flex-1 px-3 py-4 space-y-1">
            @php
                $ruta = request()->routeIs('backoffice.dashboard') ? 'activa' : '';
            @endphp
            <a href="{{ route('backoffice.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-colors
                      {{ request()->routeIs('backoffice.dashboard') ? 'bg-violet-600/20 text-violet-300' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="fas fa-gauge-high w-4 text-center"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('backoffice.empresas') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-colors
                      {{ request()->routeIs('backoffice.empresas*') ? 'bg-violet-600/20 text-violet-300' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="fas fa-building w-4 text-center"></i>
                <span>Empresas</span>
            </a>
        </nav>

        {{-- Usuario + salir --}}
        <div class="px-3 py-4 border-t border-violet-900/30 space-y-1">
            <div class="px-3 py-2">
                <p class="text-xs text-slate-500">Conectado como</p>
                <p class="text-sm text-white font-medium truncate">{{ auth()->user()->name }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm
                               text-slate-400 hover:bg-white/5 hover:text-red-400 transition-colors">
                    <i class="fas fa-right-from-bracket w-4 text-center"></i>
                    <span>Cerrar sesión</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- Contenido --}}
    <main class="ml-60 flex-1 p-8">

        {{-- Banner impersonando --}}
        @if(session('backoffice_impersonando'))
        <div class="mb-6 bg-amber-500/10 border border-amber-500/30 rounded-xl px-4 py-3 flex items-center justify-between">
            <span class="text-amber-400 text-sm"><i class="fas fa-eye mr-2"></i>Estás viendo la app como cliente</span>
            <form method="POST" action="{{ route('backoffice.salir') }}">
                @csrf
                <button class="text-amber-400 hover:text-amber-300 text-sm underline">Salir y volver al BackOffice</button>
            </form>
        </div>
        @endif

        {{-- Alertas --}}
        @if(session('success'))
            <div class="mb-6 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-xl px-4 py-3 text-sm">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif
        @if(session('info'))
            <div class="mb-6 bg-blue-500/10 border border-blue-500/30 text-blue-400 rounded-xl px-4 py-3 text-sm">
                <i class="fas fa-info-circle mr-2"></i>{{ session('info') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-6 bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3 text-sm">
                <i class="fas fa-circle-exclamation mr-2"></i>
                @foreach($errors->all() as $e) {{ $e }}<br> @endforeach
            </div>
        @endif

        @yield('content')
    </main>

</div>

</body>
</html>
