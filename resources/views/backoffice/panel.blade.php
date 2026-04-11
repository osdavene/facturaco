<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>BackOffice — Panel de Plataforma</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#07090f] text-slate-200 font-sans" x-data="superPanel()">

{{-- ══════════════════════════════════════════════════════════
     MODALES
══════════════════════════════════════════════════════════ --}}

{{-- Modal: Nueva empresa --}}
<div x-show="modal === 'empresa'" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
     @click.self="modal = null">
    <div class="bg-[#0d1117] border border-violet-900/40 rounded-2xl w-full max-w-lg p-6 shadow-2xl">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-display font-black text-lg text-white">Nueva empresa</h3>
            <button @click="modal = null" class="text-slate-500 hover:text-white transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('backoffice.empresas.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs text-slate-400 mb-1.5">Razón social *</label>
                    <input type="text" name="razon_social" required placeholder="Empresa S.A.S."
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white
                                  focus:outline-none focus:border-violet-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">NIT *</label>
                    <input type="text" name="nit" required placeholder="900000000"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white
                                  focus:outline-none focus:border-violet-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Email</label>
                    <input type="email" name="email" placeholder="contacto@empresa.com"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white
                                  focus:outline-none focus:border-violet-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Teléfono</label>
                    <input type="text" name="telefono" placeholder="601 000 0000"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white
                                  focus:outline-none focus:border-violet-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Municipio</label>
                    <input type="text" name="municipio" placeholder="Bogotá D.C."
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white
                                  focus:outline-none focus:border-violet-500 transition-colors">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs text-slate-400 mb-1.5">¿Es filial de otra empresa?</label>
                    <select name="empresa_padre_id"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white
                                   focus:outline-none focus:border-violet-500 transition-colors">
                        <option value="">— No, es empresa matriz independiente —</option>
                        @foreach($todasEmpresas->whereNull('empresa_padre_id') as $m)
                            <option value="{{ $m->id }}">{{ $m->razon_social }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" @click="modal = null"
                        class="px-4 py-2 text-sm text-slate-400 hover:text-white transition-colors">Cancelar</button>
                <button type="submit"
                        class="bg-violet-600 hover:bg-violet-500 text-white text-sm px-5 py-2 rounded-xl transition-colors">
                    <i class="fas fa-plus mr-1.5"></i>Crear empresa
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Nuevo admin --}}
<div x-show="modal === 'admin'" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
     @click.self="modal = null">
    <div class="bg-[#0d1117] border border-emerald-900/40 rounded-2xl w-full max-w-md p-6 shadow-2xl">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="font-display font-black text-lg text-white">Nuevo administrador</h3>
                <p class="text-xs text-slate-500 mt-0.5" x-text="adminEmpresaNombre ? 'Para: ' + adminEmpresaNombre : ''"></p>
            </div>
            <button @click="modal = null" class="text-slate-500 hover:text-white transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form :action="adminFormUrl" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Nombre completo *</label>
                <input type="text" name="name" required placeholder="Juan Pérez"
                       class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white
                              focus:outline-none focus:border-emerald-500 transition-colors">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Correo electrónico *</label>
                <input type="email" name="email" required placeholder="admin@empresa.com"
                       class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white
                              focus:outline-none focus:border-emerald-500 transition-colors">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Contraseña *</label>
                    <input type="password" name="password" required minlength="8" placeholder="••••••••"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white
                                  focus:outline-none focus:border-emerald-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Confirmar *</label>
                    <input type="password" name="password_confirmation" required placeholder="••••••••"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white
                                  focus:outline-none focus:border-emerald-500 transition-colors">
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" @click="modal = null"
                        class="px-4 py-2 text-sm text-slate-400 hover:text-white transition-colors">Cancelar</button>
                <button type="submit"
                        class="bg-emerald-600 hover:bg-emerald-500 text-white text-sm px-5 py-2 rounded-xl transition-colors">
                    <i class="fas fa-user-plus mr-1.5"></i>Crear admin
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     LAYOUT
══════════════════════════════════════════════════════════ --}}
<div class="min-h-screen flex flex-col">

    {{-- Topbar --}}
    <header class="sticky top-0 z-40 bg-[#07090f]/95 backdrop-blur border-b border-violet-900/20 px-6 py-3">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-violet-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-layer-group text-white text-xs"></i>
                </div>
                <span class="font-display font-black text-white">BackOffice</span>
                <span class="text-slate-600 text-sm">·</span>
                <span class="text-slate-400 text-sm">Panel de plataforma</span>
            </div>
            <div class="flex items-center gap-3">
                @if(session('backoffice_impersonando'))
                <form method="POST" action="{{ route('backoffice.salir') }}">
                    @csrf
                    <button class="text-xs bg-amber-500/15 text-amber-400 border border-amber-500/30
                                   px-3 py-1.5 rounded-lg hover:bg-amber-500/25 transition-colors">
                        <i class="fas fa-eye mr-1.5"></i>Salir vista cliente
                    </button>
                </form>
                @endif
                <span class="text-sm text-slate-500">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-xs text-slate-500 hover:text-red-400 transition-colors px-2 py-1.5">
                        <i class="fas fa-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto w-full px-6 py-8 flex-1">

        {{-- Alertas --}}
        @if(session('success'))
        <div class="mb-6 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
            <i class="fas fa-check-circle"></i>{{ session('success') }}
        </div>
        @endif
        @if($errors->any())
        <div class="mb-6 bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3 text-sm">
            @foreach($errors->all() as $e)<div><i class="fas fa-circle-exclamation mr-1.5"></i>{{ $e }}</div>@endforeach
        </div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            @php
            $stats = [
                ['label' => 'Empresas totales',  'valor' => $totalEmpresas, 'icon' => 'fa-building',    'color' => 'violet', 'tab' => 'empresas'],
                ['label' => 'Matrices (grupos)', 'valor' => $totalMatrices, 'icon' => 'fa-sitemap',     'color' => 'blue',   'tab' => 'empresas'],
                ['label' => 'Filiales',          'valor' => $totalFiliales, 'icon' => 'fa-code-branch', 'color' => 'cyan',   'tab' => 'empresas'],
                ['label' => 'Usuarios clientes', 'valor' => $totalUsuarios, 'icon' => 'fa-users',       'color' => 'emerald','tab' => 'usuarios'],
            ];
            @endphp
            @foreach($stats as $s)
            <button @click="tab = '{{ $s['tab'] }}'"
                    class="bg-[#0d1117] border border-{{ $s['color'] }}-900/30 rounded-2xl p-5 text-left
                           hover:border-{{ $s['color'] }}-600/40 transition-colors cursor-pointer">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-slate-400 text-xs">{{ $s['label'] }}</span>
                    <div class="w-8 h-8 bg-{{ $s['color'] }}-600/20 rounded-lg flex items-center justify-center">
                        <i class="fas {{ $s['icon'] }} text-{{ $s['color'] }}-400 text-xs"></i>
                    </div>
                </div>
                <p class="font-display font-black text-3xl text-white">{{ $s['valor'] }}</p>
            </button>
            @endforeach
        </div>

        {{-- Tabs --}}
        <div class="flex items-center gap-1 mb-6 bg-[#0d1117] border border-white/5 rounded-2xl p-1.5 w-fit">
            @foreach([['resumen','fa-gauge-high','Resumen'],['empresas','fa-building','Empresas'],['usuarios','fa-users','Usuarios']] as [$t,$icon,$label])
            <button @click="tab = '{{ $t }}'"
                    :class="tab === '{{ $t }}'
                        ? 'bg-violet-600 text-white shadow-lg shadow-violet-900/30'
                        : 'text-slate-400 hover:text-white'"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium transition-all">
                <i class="fas {{ $icon }} text-xs"></i>
                {{ $label }}
            </button>
            @endforeach
        </div>

        {{-- ── TAB: RESUMEN ─────────────────────────────────────────── --}}
        <div x-show="tab === 'resumen'" x-cloak>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Grupos recientes --}}
                <div class="bg-[#0d1117] border border-white/5 rounded-2xl">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-white/5">
                        <h2 class="font-display font-bold text-white text-sm">Grupos empresariales</h2>
                        <button @click="modal = 'empresa'"
                                class="text-xs bg-violet-600/20 text-violet-400 hover:bg-violet-600/30
                                       px-3 py-1.5 rounded-lg transition-colors">
                            <i class="fas fa-plus mr-1"></i>Nueva
                        </button>
                    </div>
                    <div class="divide-y divide-white/3">
                        @forelse($empresas->take(6) as $emp)
                        <div class="px-5 py-3 flex items-center gap-3 hover:bg-white/2 transition-colors">
                            <div class="w-8 h-8 bg-violet-600/10 rounded-lg flex items-center justify-center
                                        font-display font-black text-violet-400 text-xs flex-shrink-0">
                                {{ strtoupper(substr($emp->razon_social, 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-white truncate">{{ $emp->razon_social }}</p>
                                <p class="text-xs text-slate-600">
                                    {{ $emp->filiales_count }} filial(es) · {{ $emp->usuarios_count }} usuario(s)
                                </p>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <button @click="abrirAdmin({{ $emp->id }}, '{{ addslashes($emp->razon_social) }}')"
                                        class="text-[11px] px-2 py-1 rounded-lg border border-emerald-500/25
                                               text-emerald-500/80 hover:bg-emerald-500/10 transition-colors">
                                    <i class="fas fa-user-plus"></i>
                                </button>
                                <form method="POST" action="{{ route('backoffice.impersonar', $emp) }}" class="inline">
                                    @csrf
                                    <button class="text-[11px] px-2 py-1 rounded-lg border border-amber-500/25
                                                   text-amber-500/80 hover:bg-amber-500/10 transition-colors">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </form>
                                <a href="{{ route('backoffice.empresas.editar', $emp) }}"
                                   class="text-[11px] px-2 py-1 rounded-lg border border-white/10
                                          text-slate-500 hover:bg-white/5 transition-colors">
                                    <i class="fas fa-pen"></i>
                                </a>
                            </div>
                        </div>
                        @empty
                        <div class="px-5 py-8 text-center text-slate-600 text-sm">
                            Sin empresas. <button @click="modal = 'empresa'" class="text-violet-400 underline">Crear la primera</button>
                        </div>
                        @endforelse
                    </div>
                    @if($empresas->count() > 6)
                    <div class="px-5 py-3 border-t border-white/5">
                        <button @click="tab = 'empresas'" class="text-xs text-slate-500 hover:text-violet-400 transition-colors">
                            Ver todas ({{ $empresas->count() }}) <i class="fas fa-arrow-right ml-1"></i>
                        </button>
                    </div>
                    @endif
                </div>

                {{-- Últimos usuarios --}}
                <div class="bg-[#0d1117] border border-white/5 rounded-2xl">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-white/5">
                        <h2 class="font-display font-bold text-white text-sm">Usuarios recientes</h2>
                        <button @click="tab = 'usuarios'"
                                class="text-xs text-slate-500 hover:text-violet-400 transition-colors">
                            Ver todos <i class="fas fa-arrow-right ml-1"></i>
                        </button>
                    </div>
                    <div class="divide-y divide-white/3">
                        @forelse($usuarios->take(6) as $u)
                        <div class="px-5 py-3 flex items-center gap-3 hover:bg-white/2 transition-colors">
                            <div class="w-8 h-8 bg-emerald-600/10 rounded-lg flex items-center justify-center
                                        font-bold text-emerald-400 text-xs flex-shrink-0">
                                {{ strtoupper(substr($u->name, 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-white truncate">{{ $u->name }}</p>
                                <p class="text-xs text-slate-600 truncate">{{ $u->email }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-[11px] text-slate-500">{{ $u->empresas->count() }} empresa(s)</p>
                                <a href="{{ route('backoffice.usuarios.editar', $u) }}"
                                   class="text-[11px] text-violet-400 hover:text-violet-300 transition-colors">
                                    Editar
                                </a>
                            </div>
                        </div>
                        @empty
                        <div class="px-5 py-8 text-center text-slate-600 text-sm">Sin usuarios registrados.</div>
                        @endforelse
                    </div>
                </div>

            </div>

            {{-- Acciones rápidas --}}
            <div class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-3">
                <button @click="modal = 'empresa'"
                        class="flex flex-col items-center gap-2 bg-[#0d1117] border border-violet-900/30
                               hover:border-violet-600/50 rounded-2xl p-5 transition-colors group">
                    <div class="w-10 h-10 bg-violet-600/20 rounded-xl flex items-center justify-center
                                group-hover:bg-violet-600/30 transition-colors">
                        <i class="fas fa-building-circle-arrow-right text-violet-400"></i>
                    </div>
                    <span class="text-xs text-slate-400 group-hover:text-white transition-colors">Nueva empresa</span>
                </button>
                <button @click="tab = 'empresas'"
                        class="flex flex-col items-center gap-2 bg-[#0d1117] border border-blue-900/30
                               hover:border-blue-600/50 rounded-2xl p-5 transition-colors group">
                    <div class="w-10 h-10 bg-blue-600/20 rounded-xl flex items-center justify-center
                                group-hover:bg-blue-600/30 transition-colors">
                        <i class="fas fa-code-branch text-blue-400"></i>
                    </div>
                    <span class="text-xs text-slate-400 group-hover:text-white transition-colors">Vincular filiales</span>
                </button>
                <button @click="tab = 'usuarios'"
                        class="flex flex-col items-center gap-2 bg-[#0d1117] border border-emerald-900/30
                               hover:border-emerald-600/50 rounded-2xl p-5 transition-colors group">
                    <div class="w-10 h-10 bg-emerald-600/20 rounded-xl flex items-center justify-center
                                group-hover:bg-emerald-600/30 transition-colors">
                        <i class="fas fa-users text-emerald-400"></i>
                    </div>
                    <span class="text-xs text-slate-400 group-hover:text-white transition-colors">Gestionar usuarios</span>
                </button>
                <a href="{{ route('backoffice.empresas') }}"
                   class="flex flex-col items-center gap-2 bg-[#0d1117] border border-slate-800
                          hover:border-slate-600 rounded-2xl p-5 transition-colors group">
                    <div class="w-10 h-10 bg-slate-700/30 rounded-xl flex items-center justify-center
                                group-hover:bg-slate-700/50 transition-colors">
                        <i class="fas fa-list text-slate-400"></i>
                    </div>
                    <span class="text-xs text-slate-400 group-hover:text-white transition-colors">Vista detallada</span>
                </a>
            </div>
        </div>

        {{-- ── TAB: EMPRESAS ────────────────────────────────────────── --}}
        <div x-show="tab === 'empresas'" x-cloak>

            <div class="flex items-center justify-between mb-4">
                <p class="text-slate-500 text-sm">{{ $empresas->count() }} grupo(s) empresarial(es)</p>
                <button @click="modal = 'empresa'"
                        class="bg-violet-600 hover:bg-violet-500 text-white text-sm px-4 py-2 rounded-xl transition-colors">
                    <i class="fas fa-plus mr-2"></i>Nueva empresa
                </button>
            </div>

            <div class="space-y-3">
                @forelse($empresas as $emp)
                <div class="bg-[#0d1117] border border-white/5 rounded-2xl overflow-hidden">
                    {{-- Matriz --}}
                    <div class="flex items-center justify-between px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-violet-600/10 border border-violet-600/20 rounded-xl
                                        flex items-center justify-center font-display font-black text-violet-400 text-sm">
                                {{ strtoupper(substr($emp->razon_social, 0, 2)) }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <p class="font-medium text-white">{{ $emp->razon_social }}</p>
                                    <span class="text-[10px] px-2 py-0.5 bg-violet-600/20 text-violet-400 rounded-full">Matriz</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    NIT: {{ $emp->nit }}
                                    @if($emp->email) · {{ $emp->email }}@endif
                                    · {{ $emp->usuarios_count }} usuario(s)
                                    · {{ $emp->filiales_count }} filial(es)
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="abrirAdmin({{ $emp->id }}, '{{ addslashes($emp->razon_social) }}')"
                                    class="text-xs px-3 py-1.5 rounded-lg border border-emerald-500/30
                                           text-emerald-400 hover:bg-emerald-500/10 transition-colors">
                                <i class="fas fa-user-plus mr-1"></i>Admin
                            </button>
                            <form method="POST" action="{{ route('backoffice.impersonar', $emp) }}" class="inline">
                                @csrf
                                <button class="text-xs px-3 py-1.5 rounded-lg border border-amber-500/30
                                               text-amber-400 hover:bg-amber-500/10 transition-colors">
                                    <i class="fas fa-eye mr-1"></i>Ver
                                </button>
                            </form>
                            <a href="{{ route('backoffice.empresas.editar', $emp) }}"
                               class="text-xs px-3 py-1.5 rounded-lg border border-white/10
                                      text-slate-400 hover:bg-white/5 transition-colors">
                                <i class="fas fa-pen"></i>
                            </a>
                            <form method="POST" action="{{ route('backoffice.empresas.destroy', $emp) }}" class="inline"
                                  onsubmit="return confirm('¿Eliminar {{ addslashes($emp->razon_social) }}?')">
                                @csrf @method('DELETE')
                                <button class="text-xs px-3 py-1.5 rounded-lg border border-red-500/20
                                               text-red-500/60 hover:bg-red-500/10 transition-colors">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Filiales --}}
                    @if($emp->filiales->count())
                    <div class="border-t border-white/5 divide-y divide-white/3">
                        @foreach($emp->filiales as $filial)
                        <div class="flex items-center justify-between px-5 py-3 bg-white/1">
                            <div class="flex items-center gap-3 ml-5">
                                <i class="fas fa-code-branch text-slate-600 text-xs"></i>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm text-slate-300">{{ $filial->razon_social }}</p>
                                        <span class="text-[10px] px-2 py-0.5 bg-slate-700/50 text-slate-500 rounded-full">Filial</span>
                                    </div>
                                    <p class="text-xs text-slate-600">NIT: {{ $filial->nit }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="abrirAdmin({{ $filial->id }}, '{{ addslashes($filial->razon_social) }}')"
                                        class="text-xs px-2.5 py-1.5 rounded-lg border border-emerald-500/20
                                               text-emerald-500/70 hover:bg-emerald-500/10 transition-colors">
                                    <i class="fas fa-user-plus mr-1"></i>Admin
                                </button>
                                <form method="POST" action="{{ route('backoffice.impersonar', $filial) }}" class="inline">
                                    @csrf
                                    <button class="text-xs px-2.5 py-1.5 rounded-lg border border-amber-500/20
                                                   text-amber-500/70 hover:bg-amber-500/10 transition-colors">
                                        <i class="fas fa-eye mr-1"></i>Ver
                                    </button>
                                </form>
                                <a href="{{ route('backoffice.empresas.editar', $filial) }}"
                                   class="text-xs px-2.5 py-1.5 rounded-lg border border-white/10
                                          text-slate-500 hover:bg-white/5 transition-colors">
                                    <i class="fas fa-pen"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                        <div class="px-5 py-2 bg-white/1">
                            <button @click="modal = 'empresa'"
                                    class="text-xs text-slate-600 hover:text-violet-400 transition-colors">
                                <i class="fas fa-plus mr-1"></i>Agregar filial
                            </button>
                        </div>
                    </div>
                    @else
                    <div class="border-t border-white/5 px-5 py-2.5">
                        <button @click="modal = 'empresa'"
                                class="text-xs text-slate-600 hover:text-violet-400 transition-colors">
                            <i class="fas fa-plus mr-1"></i>Agregar filial
                        </button>
                    </div>
                    @endif
                </div>
                @empty
                <div class="bg-[#0d1117] border border-white/5 rounded-2xl p-16 text-center">
                    <i class="fas fa-building text-4xl text-slate-700 mb-4 block"></i>
                    <p class="text-slate-400">No hay empresas registradas.</p>
                    <button @click="modal = 'empresa'"
                            class="inline-block mt-4 bg-violet-600 hover:bg-violet-500 text-white text-sm px-6 py-2.5 rounded-xl transition-colors">
                        Crear primera empresa
                    </button>
                </div>
                @endforelse
            </div>
        </div>

        {{-- ── TAB: USUARIOS ────────────────────────────────────────── --}}
        <div x-show="tab === 'usuarios'" x-cloak>

            <div class="flex items-center justify-between mb-4">
                <p class="text-slate-500 text-sm">{{ $usuarios->total() }} usuario(s) en la plataforma</p>
            </div>

            <div class="bg-[#0d1117] border border-white/5 rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-white/5">
                                <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-5 py-3">Usuario</th>
                                <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden sm:table-cell">Email</th>
                                <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Empresas</th>
                                <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-5 py-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($usuarios as $u)
                            <tr class="border-b border-white/3 hover:bg-white/2 transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-emerald-600/15 rounded-lg flex items-center justify-center
                                                    font-bold text-emerald-400 text-xs flex-shrink-0">
                                            {{ strtoupper(substr($u->name, 0, 2)) }}
                                        </div>
                                        <p class="text-sm text-white">{{ $u->name }}</p>
                                    </div>
                                </td>
                                <td class="px-3 py-3.5 hidden sm:table-cell">
                                    <span class="text-sm text-slate-400">{{ $u->email }}</span>
                                </td>
                                <td class="px-3 py-3.5">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($u->empresas as $emp)
                                        <span class="text-[10px] px-2 py-0.5 rounded-full
                                                     {{ $emp->pivot->rol === 'admin' ? 'bg-amber-500/15 text-amber-400' : 'bg-slate-700/60 text-slate-400' }}">
                                            {{ $emp->pivot->rol === 'admin' ? '★ ' : '' }}{{ Str::limit($emp->razon_social, 18) }}
                                        </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('backoffice.usuarios.editar', $u) }}"
                                       class="text-xs px-3 py-1.5 rounded-lg border border-white/10
                                              text-slate-400 hover:bg-white/5 transition-colors">
                                        <i class="fas fa-pen mr-1"></i>Editar
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="px-5 py-12 text-center text-slate-600">Sin usuarios.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($usuarios->hasPages())
                <div class="px-5 py-4 border-t border-white/5">{{ $usuarios->links() }}</div>
                @endif
            </div>
        </div>

    </div>{{-- /max-w --}}
</div>{{-- /layout --}}

<script>
function superPanel() {
    return {
        tab: '{{ request('tab', 'resumen') }}',
        modal: @if($errors->any()) 'empresa' @else null @endif,
        adminFormUrl: '',
        adminEmpresaNombre: '',

        abrirAdmin(empresaId, nombre) {
            this.adminFormUrl = '/backoffice/empresas/' + empresaId + '/crear-admin';
            this.adminEmpresaNombre = nombre;
            this.modal = 'admin';
        }
    }
}
</script>
</body>
</html>
