<!DOCTYPE html>
@php $temaActual = auth()->check() ? (auth()->user()->tema ?? 'dark') : 'dark'; @endphp
<html lang="es" class="{{ $temaActual === 'light' ? '' : 'dark' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — @yield('title', 'Dashboard')</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-[#0b0f1a] text-slate-200 font-sans">

    {{-- Overlay móvil --}}
    <div id="overlay" onclick="closeSidebar()"
         class="hidden fixed inset-0 bg-black/50 z-[99] lg:hidden"></div>

    <div class="flex min-h-screen">

        {{-- ═══════════════════════════════════════
             SIDEBAR
        ═══════════════════════════════════════ --}}
        <aside id="sidebar"
               class="fixed top-0 left-0 h-full w-64 bg-[#111827] border-r border-[#1e2d47]
                      flex flex-col z-[100]
                      -translate-x-full lg:translate-x-0 transition-transform duration-300">

            {{-- Logo --}}
            @php $emp = \App\Models\Empresa::obtener(); @endphp
            <div class="px-6 py-5 border-b border-[#1e2d47]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-amber-500 rounded-xl flex items-center justify-center
                                font-display font-black text-black text-lg flex-shrink-0 overflow-hidden">
                        @if($emp->logo)
                            <img src="{{ Storage::url($emp->logo) }}"
                                 class="w-10 h-10 object-contain" alt="Logo">
                        @else
                            FC
                        @endif
                    </div>
                    <div class="min-w-0">
                        <div class="font-display font-black text-xl text-white leading-tight">
                            Factura<span class="text-amber-500">CO</span>
                        </div>
                        <div class="text-[10px] text-slate-500 tracking-wide truncate">
                            {{ $emp->nombre_comercial ?: $emp->razon_social }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Búsqueda rápida móvil (solo visible en móvil) --}}
            <div class="px-3 py-2.5 border-b border-[#1e2d47] lg:hidden">
                <button onclick="closeSidebar(); setTimeout(() => document.getElementById('busqueda-global').focus(), 300);"
                        class="w-full flex items-center gap-2 bg-[#1a2235] border border-[#1e2d47]
                               rounded-xl px-3 py-2 text-sm text-slate-500
                               hover:border-amber-500/50 transition-colors">
                    <i class="fas fa-search text-xs"></i>
                    <span>Buscar en el sistema...</span>
                    <span class="ml-auto text-[10px] bg-[#141c2e] px-1.5 py-0.5 rounded text-slate-600">
                        Ctrl+K
                    </span>
                </button>
            </div>

            {{-- Navegación --}}
            <nav class="flex-1 overflow-y-auto py-4 px-3">

                {{-- PRINCIPAL --}}
                <x-nav-section label="Principal">
                    <x-nav-item href="{{ route('dashboard') }}"
                                icon="fa-chart-line"
                                :active="request()->routeIs('dashboard')">
                        Dashboard
                    </x-nav-item>
                    <x-nav-item href="{{ route('facturas.index') }}"
                                icon="fa-file-invoice"
                                :active="request()->routeIs('facturas.*')">
                        Facturación
                    </x-nav-item>
                    <x-nav-item href="{{ route('cotizaciones.index') }}"
                                icon="fa-file-alt"
                                :active="request()->routeIs('cotizaciones.*')">
                        Cotizaciones
                    </x-nav-item>
                    <x-nav-item href="{{ route('remisiones.index') }}"
                                icon="fa-receipt"
                                :active="request()->routeIs('remisiones.*')">
                        Remisiones
                    </x-nav-item>
                </x-nav-section>

                {{-- GESTIÓN --}}
                <x-nav-section label="Gestión">
                    <x-nav-item href="{{ route('inventario.index') }}"
                                icon="fa-boxes"
                                :active="request()->routeIs('inventario.*')">
                        Inventario
                    </x-nav-item>
                    <x-nav-item href="{{ route('clientes.index') }}"
                                icon="fa-users"
                                :active="request()->routeIs('clientes.*')">
                        Clientes
                    </x-nav-item>
                    <x-nav-item href="{{ route('proveedores.index') }}"
                                icon="fa-truck"
                                :active="request()->routeIs('proveedores.*')">
                        Proveedores
                    </x-nav-item>
                    <x-nav-item href="{{ route('ordenes.index') }}"
                                icon="fa-shopping-cart"
                                :active="request()->routeIs('ordenes.*')">
                        Órdenes de Compra
                    </x-nav-item>
                </x-nav-section>

                {{-- FINANZAS --}}
                <x-nav-section label="Finanzas">
                    <x-nav-item href="{{ route('recibos.index') }}"
                                icon="fa-hand-holding-usd"
                                :active="request()->routeIs('recibos.*')">
                        Recibos de Caja
                    </x-nav-item>
                    <x-nav-item href="{{ route('reportes.index') }}"
                                icon="fa-chart-bar"
                                :active="request()->routeIs('reportes.*')">
                        Reportes
                    </x-nav-item>
                    <x-nav-item href="{{ route('impuestos.index') }}"
                                icon="fa-percent"
                                :active="request()->routeIs('impuestos.*')">
                        Impuestos / DIAN
                    </x-nav-item>
                </x-nav-section>

                {{-- CONFIGURACIÓN --}}
                @can('ver usuarios')
                <x-nav-section label="Configuración">
                    <x-nav-item href="{{ route('usuarios.index') }}"
                                icon="fa-users-cog"
                                :active="request()->routeIs('usuarios.*')">
                        Usuarios & Roles
                    </x-nav-item>
                    <x-nav-item href="{{ route('empresa.index') }}"
                                icon="fa-building"
                                :active="request()->routeIs('empresa.*')">
                        Empresa
                    </x-nav-item>
                    <x-nav-item href="{{ route('categorias.index') }}"
                                icon="fa-tags"
                                :active="request()->routeIs('categorias.*')">
                        Categorías
                    </x-nav-item>
                    <x-nav-item href="{{ route('unidades.index') }}"
                                icon="fa-ruler"
                                :active="request()->routeIs('unidades.*')">
                        Unidades de Medida
                    </x-nav-item>
                    <x-nav-item href="{{ route('sesiones.index') }}"
                                icon="fas fa-users"
                                :active="request()->routeIs('sesiones.*')">
                        Sesiones
                    </x-nav-item>
                </x-nav-section>
                @endcan

            </nav>

            {{-- Usuario / Logout --}}
            <div class="p-4 border-t border-[#1e2d47]">
                <div class="flex items-center gap-3">
                    <a href="{{ route('perfil.index') }}"
                    class="flex-shrink-0 relative group">
                        <img src="{{ auth()->user()->avatar_url }}"
                            class="w-9 h-9 rounded-xl object-cover border-2 border-transparent
                                    group-hover:border-amber-500 transition-colors"
                            alt="{{ auth()->user()->name }}">
                        <div class="absolute inset-0 rounded-xl bg-amber-500/0 group-hover:bg-amber-500/10
                                    transition-colors flex items-center justify-center">
                        </div>
                    </a>
                    <a href="{{ route('perfil.index') }}" class="flex-1 min-w-0 hover:opacity-80 transition-opacity">
                        <div class="text-sm font-semibold text-white truncate">
                            {{ auth()->user()->name }}
                        </div>
                        <div class="text-xs text-slate-500 capitalize truncate">
                            {{ auth()->user()->cargo ?? auth()->user()->getRoleNames()->first() ?? 'Sin rol' }}
                        </div>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Cerrar sesión"
                                class="w-8 h-8 rounded-lg bg-[#1a2235] border border-[#1e2d47]
                                    flex items-center justify-center flex-shrink-0
                                    text-slate-500 hover:text-red-400 hover:border-red-500/50
                                    transition-colors">
                            <i class="fas fa-sign-out-alt text-xs"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>
        {{-- FIN SIDEBAR --}}

        {{-- ═══════════════════════════════════════
             CONTENIDO PRINCIPAL
        ═══════════════════════════════════════ --}}
        <div class="flex-1 flex flex-col lg:ml-64 min-w-0">

            {{-- ── TOPBAR ─────────────────────────── --}}
            <header class="sticky top-0 z-50 bg-[#111827]/95 backdrop-blur
                           border-b border-[#1e2d47] px-4 lg:px-7 py-3
                           flex items-center gap-3">

                {{-- Menú hamburguesa (solo móvil) --}}
                <button onclick="toggleSidebar()"
                        class="lg:hidden text-slate-300 text-xl hover:text-white
                               transition-colors flex-shrink-0">
                    <i class="fas fa-bars"></i>
                </button>

                {{-- Título de la página (solo desktop) --}}
                <div class="font-display font-bold text-lg truncate min-w-0 hidden lg:block">
                    @yield('page-title', 'Dashboard')
                </div>

                {{-- Búsqueda global --}}
                <div class="flex-1 max-w-xs lg:max-w-md mx-0 lg:mx-4 relative"
                     id="busqueda-container">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2
                                  text-slate-500 text-sm pointer-events-none"></i>
                        <input type="text"
                               id="busqueda-global"
                               placeholder="Buscar... (Ctrl+K)"
                               autocomplete="off"
                               class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl
                                      pl-9 pr-8 py-2 text-sm placeholder-slate-600
                                      focus:outline-none focus:border-amber-500 transition-all"
                               style="color:#e2e8f0">
                        <div id="busqueda-loading"
                             class="absolute right-3 top-1/2 -translate-y-1/2 hidden">
                            <i class="fas fa-spinner fa-spin text-slate-500 text-xs"></i>
                        </div>
                        <button id="busqueda-clear"
                                onclick="limpiarBusqueda()"
                                class="absolute right-3 top-1/2 -translate-y-1/2 hidden
                                       text-slate-500 hover:text-slate-300 transition-colors">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                    {{-- Dropdown resultados búsqueda --}}
                    <div id="busqueda-resultados"
                         class="absolute top-full left-0 right-0 mt-1.5 bg-[#141c2e]
                                border border-[#1e2d47] rounded-xl shadow-2xl z-[200]
                                hidden overflow-hidden max-h-[70vh] overflow-y-auto">
                    </div>
                </div>

                {{-- Acciones derecha --}}
                <div class="ml-auto flex items-center gap-2 flex-shrink-0">

                    {{-- Estado DIAN (solo desktop) --}}
                    @if($emp->resolucion_vencimiento)
                        @if(!$emp->resolucion_vigente)
                        <a href="{{ route('empresa.index') }}"
                           class="hidden lg:flex items-center gap-2 text-xs text-red-400
                                  bg-red-500/10 border border-red-500/30 px-3 py-1.5
                                  rounded-lg hover:bg-red-500/15 transition-colors">
                            <div class="w-1.5 h-1.5 rounded-full bg-red-500 flex-shrink-0"></div>
                            DIAN Vencida
                        </a>
                        @elseif($emp->dias_para_vencer <= 30)
                        <a href="{{ route('empresa.index') }}"
                           class="hidden lg:flex items-center gap-2 text-xs text-amber-500
                                  bg-amber-500/10 border border-amber-500/30 px-3 py-1.5
                                  rounded-lg hover:bg-amber-500/15 transition-colors">
                            <div class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse flex-shrink-0"></div>
                            DIAN {{ $emp->dias_para_vencer }}d
                        </a>
                        @else
                        <div class="hidden lg:flex items-center gap-2 text-xs text-emerald-500">
                            <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                            DIAN Vigente
                        </div>
                        @endif
                    @else
                    <a href="{{ route('empresa.index') }}"
                       class="hidden lg:flex items-center gap-2 text-xs text-slate-500
                              hover:text-amber-500 transition-colors">
                        <div class="w-1.5 h-1.5 rounded-full bg-slate-600"></div>
                        Sin resolución
                    </a>
                    @endif
                    
                    {{-- Perfil (solo desktop) --}}
                    <a href="{{ route('perfil.index') }}"
                    class="hidden lg:flex items-center gap-2 text-slate-400 hover:text-slate-200
                            transition-colors text-sm">
                        <img src="{{ auth()->user()->avatar_url }}"
                            class="w-7 h-7 rounded-lg object-cover border border-[#1e2d47]"
                            alt="{{ auth()->user()->name }}">
                    </a>

                    {{-- Nueva Factura --}}
                    <a href="{{ route('facturas.create') }}"
                       class="flex items-center gap-2 bg-amber-500 hover:bg-amber-600
                              text-black font-semibold text-sm px-3 lg:px-4 py-2
                              rounded-lg transition-colors flex-shrink-0">
                        <i class="fas fa-plus"></i>
                        <span class="hidden sm:inline">Nueva Factura</span>
                    </a>

                    {{-- Theme Toggle --}}
                    @php $esOscuro = ($temaActual ?? 'dark') === 'dark'; @endphp
                    <form method="POST" action="{{ route('tema.cambiar') }}">
                        @csrf
                        <input type="hidden" name="tema" value="{{ $esOscuro ? 'light' : 'dark' }}">
                        <button type="submit"
                                title="{{ $esOscuro ? 'Cambiar a tema claro' : 'Cambiar a tema oscuro' }}"
                                class="w-9 h-9 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                    flex items-center justify-center transition-colors
                                    text-slate-400 hover:text-amber-500 hover:border-amber-500/50">
                            <i class="fas {{ $esOscuro ? 'fa-sun' : 'fa-moon' }} text-sm"></i>
                        </button>
                    </form>

                    {{-- Notificaciones --}}
                    <div class="relative group">
                        <button class="relative w-9 h-9 bg-[#1a2235] border border-[#1e2d47]
                                       rounded-lg flex items-center justify-center
                                       text-slate-400 hover:text-white hover:border-slate-500
                                       transition-colors">
                            <i class="fas fa-bell text-sm"></i>
                            @php
                                $hayAlertas = ($emp->resolucion_vencimiento &&
                                              (!$emp->resolucion_vigente || $emp->dias_para_vencer <= 30))
                                           || !$emp->resolucion_numero;
                            @endphp
                            @if($hayAlertas)
                            <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5
                                         bg-red-500 rounded-full"></span>
                            @endif
                        </button>

                        {{-- Dropdown notificaciones --}}
                        <div class="absolute right-0 top-full mt-2 w-72 bg-[#141c2e]
                                    border border-[#1e2d47] rounded-xl shadow-xl z-50
                                    hidden group-hover:block">
                            <div class="px-4 py-3 border-b border-[#1e2d47]">
                                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                                    Notificaciones
                                </div>
                            </div>
                            <div class="py-1">
                                @if(!$emp->resolucion_numero)
                                <a href="{{ route('empresa.index') }}"
                                   class="flex items-start gap-3 px-4 py-3 hover:bg-[#1a2235] transition-colors">
                                    <div class="w-8 h-8 bg-amber-500/10 rounded-lg flex items-center
                                                justify-center text-amber-500 flex-shrink-0 mt-0.5">
                                        <i class="fas fa-exclamation text-xs"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold" style="color:#e2e8f0">Sin resolución DIAN</div>
                                        <div class="text-xs text-slate-500 mt-0.5">Configura tu resolución en Empresa</div>
                                    </div>
                                </a>
                                @elseif(!$emp->resolucion_vigente)
                                <a href="{{ route('empresa.index') }}"
                                   class="flex items-start gap-3 px-4 py-3 hover:bg-[#1a2235] transition-colors">
                                    <div class="w-8 h-8 bg-red-500/10 rounded-lg flex items-center
                                                justify-center text-red-400 flex-shrink-0 mt-0.5">
                                        <i class="fas fa-times text-xs"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-red-400">Resolución DIAN vencida</div>
                                        <div class="text-xs text-slate-500 mt-0.5">
                                            Venció el {{ $emp->resolucion_vencimiento->format('d/m/Y') }}
                                        </div>
                                    </div>
                                </a>
                                @elseif($emp->dias_para_vencer <= 30)
                                <a href="{{ route('empresa.index') }}"
                                   class="flex items-start gap-3 px-4 py-3 hover:bg-[#1a2235] transition-colors">
                                    <div class="w-8 h-8 bg-amber-500/10 rounded-lg flex items-center
                                                justify-center text-amber-500 flex-shrink-0 mt-0.5">
                                        <i class="fas fa-clock text-xs"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-amber-500">Resolución por vencer</div>
                                        <div class="text-xs text-slate-500 mt-0.5">
                                            Vence en {{ $emp->dias_para_vencer }} días
                                        </div>
                                    </div>
                                </a>
                                @else
                                <div class="px-4 py-6 text-center text-slate-500 text-xs">
                                    <i class="fas fa-check-circle text-emerald-500 text-lg mb-2 block"></i>
                                    Todo en orden
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    {{-- FIN Notificaciones --}}

                </div>
                {{-- FIN Acciones derecha --}}

            </header>
            {{-- FIN TOPBAR --}}

            {{-- Contenido de la página --}}
            <main class="flex-1 p-4 lg:p-7">
                @yield('content')
            </main>

            {{-- Footer --}}
            <footer class="px-4 lg:px-7 py-3 border-t border-[#1e2d47]">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-2">
                    <span class="text-xs text-slate-600">
                        FacturaCO © {{ now()->year }} · {{ $emp->razon_social }}
                    </span>
                    <span class="text-xs text-slate-600">
                        NIT: {{ $emp->nit_formateado }}
                        @if($emp->municipio) · {{ $emp->municipio }} @endif
                    </span>
                </div>
            </footer>

        </div>
        {{-- FIN CONTENIDO PRINCIPAL --}}

    </div>
    {{-- FIN FLEX --}}

    {{-- ═══════════════════════════════════════
         BOTTOM NAV MÓVIL
    ═══════════════════════════════════════ --}}
    <nav class="bottom-nav lg:hidden">
        @php
        $navItems = [
            ['route' => 'dashboard',        'icon' => 'fa-chart-line',   'label' => 'Inicio'],
            ['route' => 'facturas.index',   'icon' => 'fa-file-invoice', 'label' => 'Facturas'],
            ['route' => 'clientes.index',   'icon' => 'fa-users',        'label' => 'Clientes'],
            ['route' => 'inventario.index', 'icon' => 'fa-boxes',        'label' => 'Inventario'],
            ['route' => 'reportes.index',   'icon' => 'fa-chart-bar',    'label' => 'Reportes'],
        ];
        @endphp

        @foreach($navItems as $item)
        @php $isActive = request()->routeIs($item['route'].'*'); @endphp
        <a href="{{ route($item['route']) }}"
           class="flex-1 flex flex-col items-center justify-center py-1 gap-0.5 transition-colors
                  {{ $isActive ? 'text-amber-500' : 'text-slate-500 hover:text-slate-300' }}">
            <i class="fas {{ $item['icon'] }} text-lg"></i>
            <span class="text-[9px] font-semibold tracking-wide">{{ $item['label'] }}</span>
            @if($isActive)
            <div class="w-1 h-1 rounded-full bg-amber-500 mt-0.5"></div>
            @endif
        </a>
        @endforeach

        {{-- Botón central flotante --}}
        <a href="{{ route('facturas.create') }}"
           class="flex-shrink-0 w-14 h-14 bg-amber-500 rounded-2xl flex items-center
                  justify-center shadow-lg shadow-amber-500/30 -mt-5
                  hover:bg-amber-600 transition-colors">
            <i class="fas fa-plus text-black text-xl"></i>
        </a>
    </nav>

    {{-- ═══════════════════════════════════════
         SCRIPTS
    ═══════════════════════════════════════ --}}
    <script>
    // ── Sidebar toggle ────────────────────────
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('-translate-x-full');
        document.getElementById('overlay').classList.toggle('hidden');
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.add('-translate-x-full');
        document.getElementById('overlay').classList.add('hidden');
    }

    // ── Búsqueda Global ───────────────────────
    (function() {
        const input    = document.getElementById('busqueda-global');
        const dropdown = document.getElementById('busqueda-resultados');
        const loading  = document.getElementById('busqueda-loading');
        const clearBtn = document.getElementById('busqueda-clear');
        let   timer    = null;

        if (!input) return;

        const colores = {
            amber:   '#f59e0b', blue:    '#3b82f6',
            emerald: '#10b981', purple:  '#8b5cf6',
            cyan:    '#06b6d4', orange:  '#f97316',
            green:   '#22c55e', slate:   '#64748b',
        };

        input.addEventListener('input', function() {
            const q = this.value.trim();
            clearTimeout(timer);
            clearBtn.classList.toggle('hidden', q.length === 0);

            if (q.length < 2) {
                dropdown.classList.add('hidden');
                dropdown.innerHTML = '';
                loading.classList.add('hidden');
                return;
            }

            loading.classList.remove('hidden');
            clearBtn.classList.add('hidden');

            timer = setTimeout(async () => {
                try {
                    const res  = await fetch(`/busqueda?q=${encodeURIComponent(q)}`, {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        }
                    });
                    const data = await res.json();

                    loading.classList.add('hidden');
                    clearBtn.classList.remove('hidden');

                    if (!data.resultados.length) {
                        dropdown.innerHTML = `
                            <div class="px-5 py-8 text-center">
                                <i class="fas fa-search text-slate-600 text-2xl mb-2 block"></i>
                                <div class="text-slate-500 text-sm">
                                    Sin resultados para
                                    "<strong class="text-slate-400">${q}</strong>"
                                </div>
                            </div>`;
                        dropdown.classList.remove('hidden');
                        return;
                    }

                    // Agrupar por tipo
                    const grupos = {};
                    data.resultados.forEach(r => {
                        if (!grupos[r.tipo]) grupos[r.tipo] = [];
                        grupos[r.tipo].push(r);
                    });

                    let html = '';
                    Object.entries(grupos).forEach(([tipo, items]) => {
                        html += `
                            <div class="px-4 py-2 border-b border-[#1e2d47]/50 bg-[#1a2235]/30">
                                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                    ${tipo}s
                                </div>
                            </div>`;
                        items.forEach(item => {
                            const color = colores[item.color] || '#64748b';
                            html += `
                            <a href="${item.url}"
                               class="flex items-center gap-3 px-4 py-3
                                      hover:bg-[#1a2235] transition-colors
                                      border-b border-[#1e2d47]/30 last:border-0 resultado-item">
                                <div class="w-8 h-8 rounded-lg flex items-center
                                            justify-center flex-shrink-0"
                                     style="background:${color}18; color:${color}">
                                    <i class="fas ${item.icono} text-xs"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-semibold truncate"
                                         style="color:#e2e8f0">${item.titulo}</div>
                                    <div class="text-xs text-slate-500 truncate">${item.subtitulo}</div>
                                </div>
                                <div class="text-xs text-slate-500 flex-shrink-0 text-right ml-2">
                                    ${item.detalle}
                                </div>
                            </a>`;
                        });
                    });

                    html += `
                        <div class="px-4 py-2.5 border-t border-[#1e2d47] bg-[#1a2235]/50">
                            <div class="text-xs text-slate-500 text-center">
                                ${data.total} resultado${data.total !== 1 ? 's' : ''}
                                para "<span class="text-slate-400">${q}</span>"
                            </div>
                        </div>`;

                    dropdown.innerHTML = html;
                    dropdown.classList.remove('hidden');

                } catch(err) {
                    loading.classList.add('hidden');
                    clearBtn.classList.remove('hidden');
                    console.error('Error búsqueda:', err);
                }
            }, 350);
        });

        // Escape cierra
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') limpiarBusqueda();
            if (e.key === 'Enter') {
                const primer = dropdown.querySelector('.resultado-item');
                if (primer) primer.click();
            }
        });

        // Cerrar al clic fuera
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#busqueda-container')) {
                dropdown.classList.add('hidden');
            }
        });

        // Atajo Ctrl+K / Cmd+K
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                input.focus();
                input.select();
            }
        });
    })();

    function limpiarBusqueda() {
        const input    = document.getElementById('busqueda-global');
        const dropdown = document.getElementById('busqueda-resultados');
        const clearBtn = document.getElementById('busqueda-clear');
        if (input)    { input.value = ''; input.blur(); }
        if (dropdown)   dropdown.classList.add('hidden');
        if (clearBtn)   clearBtn.classList.add('hidden');
    }
    </script>

    @stack('scripts')

</body>
</html>