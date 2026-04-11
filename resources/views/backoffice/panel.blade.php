@php $temaActual = auth()->check() ? (auth()->user()->tema ?? 'dark') : 'dark'; @endphp
<!DOCTYPE html>
<html lang="es" class="{{ $temaActual === 'light' ? '' : 'dark' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>BackOffice — Panel de Plataforma</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0b0f1a] text-slate-200 font-sans" x-data="superPanel()">

{{-- ══════════════════════════════════════════════════════════
     MODAL: Nueva empresa
══════════════════════════════════════════════════════════ --}}
<div x-show="modal === 'empresa'" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60"
     @click.self="modal = null">
    <div class="bg-[#111827] border border-[#1e2d47] rounded-2xl w-full max-w-lg p-6 shadow-2xl slide-up">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-display font-black text-lg text-white">Nueva empresa</h3>
            <button @click="modal = null" class="text-slate-500 hover:text-white transition-colors w-8 h-8 flex items-center justify-center rounded-lg hover:bg-[#1a2235]">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('backoffice.empresas.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="form-label">Razón social *</label>
                    <input type="text" name="razon_social" required placeholder="Empresa S.A.S."
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">NIT *</label>
                    <input type="text" name="nit" required placeholder="900000000"
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" placeholder="contacto@empresa.com"
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" placeholder="601 000 0000"
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">Municipio</label>
                    <input type="text" name="municipio" placeholder="Bogotá D.C."
                           class="form-input">
                </div>
                <div class="col-span-2">
                    <label class="form-label">¿Es filial de otra empresa?</label>
                    <select name="empresa_padre_id"
                            class="form-input">
                        <option value="">— No, es empresa matriz independiente —</option>
                        @foreach($todasEmpresas->whereNull('empresa_padre_id') as $m)
                            <option value="{{ $m->id }}">{{ $m->razon_social }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" @click="modal = null"
                        class="px-5 py-2.5 bg-[#1a2235] border border-[#1e2d47] rounded-xl text-sm text-slate-400 hover:text-white transition-colors">
                    Cancelar
                </button>
                <button type="submit"
                        class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-black font-semibold text-sm rounded-xl transition-colors flex items-center gap-2">
                    <i class="fas fa-plus text-xs"></i>Crear empresa
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     MODAL: Nuevo admin
══════════════════════════════════════════════════════════ --}}
<div x-show="modal === 'admin'" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60"
     @click.self="modal = null">
    <div class="bg-[#111827] border border-[#1e2d47] rounded-2xl w-full max-w-md p-6 shadow-2xl slide-up">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="font-display font-black text-lg text-white">Nuevo administrador</h3>
                <p class="text-xs text-slate-500 mt-0.5" x-text="adminEmpresaNombre ? 'Para: ' + adminEmpresaNombre : ''"></p>
            </div>
            <button @click="modal = null" class="text-slate-500 hover:text-white transition-colors w-8 h-8 flex items-center justify-center rounded-lg hover:bg-[#1a2235]">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
        <form :action="adminFormUrl" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="form-label">Nombre completo *</label>
                <input type="text" name="name" required placeholder="Juan Pérez"
                       class="form-input">
            </div>
            <div>
                <label class="form-label">Correo electrónico *</label>
                <input type="email" name="email" required placeholder="admin@empresa.com"
                       class="form-input">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="form-label">Contraseña *</label>
                    <input type="password" name="password" required minlength="8" placeholder="••••••••"
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">Confirmar *</label>
                    <input type="password" name="password_confirmation" required placeholder="••••••••"
                           class="form-input">
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" @click="modal = null"
                        class="px-5 py-2.5 bg-[#1a2235] border border-[#1e2d47] rounded-xl text-sm text-slate-400 hover:text-white transition-colors">
                    Cancelar
                </button>
                <button type="submit"
                        class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-black font-semibold text-sm rounded-xl transition-colors flex items-center gap-2">
                    <i class="fas fa-user-plus text-xs"></i>Crear admin
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     TOPBAR
══════════════════════════════════════════════════════════ --}}
<header class="sticky top-0 z-40 bg-[#111827]/95 backdrop-blur border-b border-[#1e2d47] px-6 py-3">
    <div class="max-w-7xl mx-auto flex items-center justify-between">

        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center">
                <i class="fas fa-layer-group text-black text-xs"></i>
            </div>
            <span class="font-display font-black text-white text-base">BackOffice</span>
            <span class="text-[#1e2d47]">·</span>
            <span class="text-slate-500 text-sm hidden sm:block">Panel de plataforma</span>
        </div>

        <div class="flex items-center gap-2">
            @if(session('backoffice_impersonando'))
            <form method="POST" action="{{ route('backoffice.salir') }}">
                @csrf
                <button class="text-xs bg-amber-500/10 text-amber-500 border border-amber-500/30
                               px-3 py-1.5 rounded-lg hover:bg-amber-500/20 transition-colors flex items-center gap-1.5">
                    <i class="fas fa-eye"></i><span class="hidden sm:inline">Salir vista cliente</span>
                </button>
            </form>
            @endif

            {{-- Toggle tema --}}
            <form method="POST" action="{{ route('tema.cambiar') }}">
                @csrf
                <input type="hidden" name="tema" value="{{ $temaActual === 'dark' ? 'light' : 'dark' }}">
                <button type="submit" title="Cambiar tema"
                        class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                               flex items-center justify-center text-slate-400
                               hover:text-amber-500 hover:border-amber-500/50 transition-colors">
                    <i class="fas {{ $temaActual === 'dark' ? 'fa-sun' : 'fa-moon' }} text-xs"></i>
                </button>
            </form>

            <div class="hidden sm:flex items-center gap-2 bg-[#1a2235] border border-[#1e2d47] rounded-xl px-3 py-1.5">
                <div class="w-5 h-5 bg-amber-500/10 rounded-md flex items-center justify-center">
                    <i class="fas fa-crown text-amber-500 text-[9px]"></i>
                </div>
                <span class="text-sm text-slate-300">{{ auth()->user()->name }}</span>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Cerrar sesión"
                        class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                               flex items-center justify-center text-slate-400
                               hover:text-red-400 hover:border-red-500/30 transition-colors">
                    <i class="fas fa-right-from-bracket text-xs"></i>
                </button>
            </form>
        </div>
    </div>
</header>

{{-- ══════════════════════════════════════════════════════════
     CONTENIDO
══════════════════════════════════════════════════════════ --}}
<div class="max-w-7xl mx-auto w-full px-6 py-8">

    {{-- Alertas --}}
    @if(session('success'))
    <div class="mb-6 alert-success fade-in">
        <i class="fas fa-check-circle"></i>{{ session('success') }}
    </div>
    @endif
    @if($errors->any())
    <div class="mb-6 alert-error fade-in">
        @foreach($errors->all() as $e)<div><i class="fas fa-circle-exclamation mr-1.5"></i>{{ $e }}</div>@endforeach
    </div>
    @endif

    {{-- Stats (clicables → llevan al tab) --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @php
        $stats = [
            ['label'=>'Empresas totales',  'valor'=>$totalEmpresas, 'icon'=>'fa-building',    'color'=>'blue',   'tab'=>'empresas'],
            ['label'=>'Matrices',          'valor'=>$totalMatrices, 'icon'=>'fa-sitemap',     'color'=>'amber',  'tab'=>'empresas'],
            ['label'=>'Filiales',          'valor'=>$totalFiliales, 'icon'=>'fa-code-branch', 'color'=>'amber',  'tab'=>'empresas'],
            ['label'=>'Usuarios clientes', 'valor'=>$totalUsuarios, 'icon'=>'fa-users',       'color'=>'emerald','tab'=>'usuarios'],
        ];
        @endphp
        @foreach($stats as $s)
        <button @click="tab = '{{ $s['tab'] }}'"
                class="card p-5 text-left
                       hover:border-amber-500/40 transition-colors cursor-pointer group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-slate-500 text-xs">{{ $s['label'] }}</span>
                <div class="w-8 h-8 bg-{{ $s['color'] }}-500/10 rounded-lg flex items-center justify-center
                            group-hover:bg-{{ $s['color'] }}-500/20 transition-colors">
                    <i class="fas {{ $s['icon'] }} text-{{ $s['color'] }}-500 text-xs"></i>
                </div>
            </div>
            <p class="font-display font-black text-3xl text-white">{{ $s['valor'] }}</p>
        </button>
        @endforeach
    </div>

    {{-- Tabs --}}
    <div class="flex items-center gap-1 mb-6 card p-1.5 w-fit">
        @foreach([['resumen','fa-gauge-high','Resumen'],['empresas','fa-building','Empresas'],['usuarios','fa-users','Usuarios']] as [$t,$icon,$label])
        <button @click="tab = '{{ $t }}'"
                :class="tab === '{{ $t }}'
                    ? 'bg-amber-500 text-black font-bold shadow-lg'
                    : 'text-slate-400 hover:text-white hover:bg-[#1a2235]'"
                class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium transition-all">
            <i class="fas {{ $icon }} text-xs"></i>
            {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- ── TAB: RESUMEN ───────────────────────────────────────��─── --}}
    <div x-show="tab === 'resumen'" x-cloak class="fade-in">

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Grupos recientes --}}
            <div class="card overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-[#1e2d47]">
                    <div>
                        <h2 class="font-display font-bold text-base">Grupos empresariales</h2>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $empresas->count() }} grupo(s) registrados</p>
                    </div>
                    <button @click="modal = 'empresa'"
                            class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600
                                   text-black font-semibold text-xs px-3 py-2 rounded-xl transition-colors">
                        <i class="fas fa-plus"></i>Nueva
                    </button>
                </div>
                <div class="divide-y divide-[#1e2d47]/50">
                    @forelse($empresas->take(6) as $emp)
                    <div class="px-5 py-3 flex items-center gap-3 hover:bg-[#1a2235]/50 transition-colors">
                        <div class="w-9 h-9 bg-[#1a2235] border border-[#1e2d47] rounded-xl
                                    flex items-center justify-center font-display font-black text-amber-500 text-xs flex-shrink-0">
                            {{ strtoupper(substr($emp->razon_social, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold truncate">{{ $emp->razon_social }}</p>
                            <p class="text-xs text-slate-500">
                                {{ $emp->filiales_count }} filial(es) · {{ $emp->usuarios_count }} usuario(s)
                            </p>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <button @click="abrirAdmin({{ $emp->id }}, '{{ addslashes($emp->razon_social) }}')"
                                    title="Crear admin"
                                    class="w-7 h-7 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                           flex items-center justify-center text-slate-400
                                           hover:text-emerald-400 hover:border-emerald-500/50 transition-colors">
                                <i class="fas fa-user-plus text-[10px]"></i>
                            </button>
                            <form method="POST" action="{{ route('backoffice.impersonar', $emp) }}" class="inline">
                                @csrf
                                <button type="submit" title="Ver como cliente"
                                        class="w-7 h-7 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                               flex items-center justify-center text-slate-400
                                               hover:text-amber-500 hover:border-amber-500/50 transition-colors">
                                    <i class="fas fa-eye text-[10px]"></i>
                                </button>
                            </form>
                            <a href="{{ route('backoffice.empresas.editar', $emp) }}"
                               title="Editar"
                               class="w-7 h-7 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                      flex items-center justify-center text-slate-400
                                      hover:text-amber-500 hover:border-amber-500/50 transition-colors">
                                <i class="fas fa-pen text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="px-5 py-10 text-center">
                        <i class="fas fa-building text-3xl text-slate-700 mb-3 block"></i>
                        <p class="text-slate-500 text-sm">Sin empresas.</p>
                        <button @click="modal = 'empresa'"
                                class="mt-3 text-xs text-amber-500 hover:text-amber-400 transition-colors underline">
                            Crear la primera
                        </button>
                    </div>
                    @endforelse
                </div>
                @if($empresas->count() > 6)
                <div class="px-5 py-3 border-t border-[#1e2d47]">
                    <button @click="tab = 'empresas'" class="text-xs text-slate-500 hover:text-amber-500 transition-colors">
                        Ver todas ({{ $empresas->count() }}) <i class="fas fa-arrow-right ml-1"></i>
                    </button>
                </div>
                @endif
            </div>

            {{-- Últimos usuarios --}}
            <div class="card overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-[#1e2d47]">
                    <div>
                        <h2 class="font-display font-bold text-base">Usuarios</h2>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $totalUsuarios }} usuario(s) registrados</p>
                    </div>
                    <button @click="tab = 'usuarios'"
                            class="text-xs text-slate-400 hover:text-amber-500 transition-colors">
                        Ver todos <i class="fas fa-arrow-right ml-1"></i>
                    </button>
                </div>
                <div class="divide-y divide-[#1e2d47]/50">
                    @forelse($usuarios->take(6) as $u)
                    <div class="px-5 py-3 flex items-center gap-3 hover:bg-[#1a2235]/50 transition-colors">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-sm flex-shrink-0
                                    bg-gradient-to-br from-blue-500 to-purple-600 text-white">
                            {{ strtoupper(substr($u->name, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold truncate">{{ $u->name }}</p>
                            <p class="text-xs text-slate-500 truncate">{{ $u->email }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-xs text-slate-500">{{ $u->empresas->count() }} empresa(s)</p>
                            <a href="{{ route('backoffice.usuarios.editar', $u) }}"
                               class="text-xs text-amber-500 hover:text-amber-400 transition-colors">
                                Editar
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="px-5 py-10 text-center text-slate-500 text-sm">Sin usuarios registrados.</div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- Acciones rápidas --}}
        <div class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-3">
            @php
            $acciones = [
                ['icon'=>'fa-building-circle-arrow-right','label'=>'Nueva empresa',    'color'=>'amber',  'alpine'=>"modal = 'empresa'",     'href'=>null],
                ['icon'=>'fa-code-branch',                'label'=>'Vincular filiales', 'color'=>'blue',   'alpine'=>"tab = 'empresas'",       'href'=>null],
                ['icon'=>'fa-users',                      'label'=>'Ver usuarios',      'color'=>'emerald','alpine'=>"tab = 'usuarios'",       'href'=>null],
                ['icon'=>'fa-list-ul',                    'label'=>'Lista detallada',   'color'=>'slate',  'alpine'=>null,                     'href'=>route('backoffice.empresas')],
            ];
            @endphp
            @foreach($acciones as $a)
            @if($a['href'])
            <a href="{{ $a['href'] }}"
               class="flex flex-col items-center gap-2 bg-[#141c2e] border border-[#1e2d47]
                      hover:border-amber-500/40 rounded-2xl p-5 transition-colors group">
            @else
            <button @click="{{ $a['alpine'] }}"
                    class="flex flex-col items-center gap-2 bg-[#141c2e] border border-[#1e2d47]
                           hover:border-amber-500/40 rounded-2xl p-5 transition-colors group">
            @endif
                <div class="w-10 h-10 bg-{{ $a['color'] }}-500/10 rounded-xl flex items-center justify-center
                            group-hover:bg-{{ $a['color'] }}-500/20 transition-colors">
                    <i class="fas {{ $a['icon'] }} text-{{ $a['color'] }}-500"></i>
                </div>
                <span class="text-xs text-slate-400 group-hover:text-white transition-colors">{{ $a['label'] }}</span>
            @if($a['href'])</a>@else</button>@endif
            @endforeach
        </div>
    </div>

    {{-- ── TAB: EMPRESAS ────────────────────────────────────────── --}}
    <div x-show="tab === 'empresas'" x-cloak class="fade-in">

        <div class="flex items-center justify-between mb-4">
            <p class="text-slate-500 text-sm">{{ $empresas->count() }} grupo(s) empresarial(es)</p>
            <button @click="modal = 'empresa'"
                    class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600
                           text-black font-semibold text-sm px-4 py-2.5 rounded-xl transition-colors">
                <i class="fas fa-plus text-xs"></i>Nueva empresa
            </button>
        </div>

        <div class="space-y-3">
            @forelse($empresas as $emp)
            <div class="card overflow-hidden">

                {{-- Fila Matriz --}}
                <div class="flex items-center justify-between px-5 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-[#1a2235] border border-[#1e2d47] rounded-xl
                                    flex items-center justify-center font-display font-black text-amber-500 text-sm">
                            {{ strtoupper(substr($emp->razon_social, 0, 2)) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="font-semibold">{{ $emp->razon_social }}</p>
                                <span class="text-[10px] px-2 py-0.5 bg-amber-500/10 text-amber-500 rounded-full font-semibold">Matriz</span>
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
                                class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                       flex items-center justify-center text-slate-400
                                       hover:text-emerald-400 hover:border-emerald-500/50 transition-colors"
                                title="Crear admin">
                            <i class="fas fa-user-plus text-xs"></i>
                        </button>
                        <form method="POST" action="{{ route('backoffice.impersonar', $emp) }}" class="inline">
                            @csrf
                            <button type="submit" title="Ver como cliente"
                                    class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                           flex items-center justify-center text-slate-400
                                           hover:text-amber-500 hover:border-amber-500/50 transition-colors">
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
                        <form method="POST" action="{{ route('backoffice.empresas.destroy', $emp) }}" class="inline"
                              onsubmit="return confirm('¿Eliminar {{ addslashes($emp->razon_social) }}? Las filiales quedarán como matrices independientes.')">
                            @csrf @method('DELETE')
                            <button type="submit" title="Eliminar"
                                    class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                           flex items-center justify-center text-slate-400
                                           hover:text-red-400 hover:border-red-500/50 transition-colors">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Filiales --}}
                @if($emp->filiales->count())
                <div class="border-t border-[#1e2d47] divide-y divide-[#1e2d47]/50">
                    @foreach($emp->filiales as $filial)
                    <div class="flex items-center justify-between px-5 py-3 bg-[#0b0f1a]/30">
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
                            <button @click="abrirAdmin({{ $filial->id }}, '{{ addslashes($filial->razon_social) }}')"
                                    class="w-7 h-7 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                           flex items-center justify-center text-slate-400
                                           hover:text-emerald-400 hover:border-emerald-500/50 transition-colors"
                                    title="Crear admin">
                                <i class="fas fa-user-plus text-[10px]"></i>
                            </button>
                            <form method="POST" action="{{ route('backoffice.impersonar', $filial) }}" class="inline">
                                @csrf
                                <button type="submit" title="Ver como cliente"
                                        class="w-7 h-7 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                               flex items-center justify-center text-slate-400
                                               hover:text-amber-500 hover:border-amber-500/50 transition-colors">
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
                    <div class="px-5 py-2.5 bg-[#0b0f1a]/30">
                        <button @click="modal = 'empresa'"
                                class="text-xs text-slate-600 hover:text-amber-500 transition-colors">
                            <i class="fas fa-plus mr-1"></i>Agregar filial a {{ Str::limit($emp->razon_social, 30) }}
                        </button>
                    </div>
                </div>
                @else
                <div class="border-t border-[#1e2d47] px-5 py-2.5">
                    <button @click="modal = 'empresa'"
                            class="text-xs text-slate-600 hover:text-amber-500 transition-colors">
                        <i class="fas fa-plus mr-1"></i>Agregar filial
                    </button>
                </div>
                @endif
            </div>
            @empty
            <div class="card p-16 text-center">
                <i class="fas fa-building text-4xl text-slate-700 mb-4 block"></i>
                <p class="text-slate-400 font-medium">No hay empresas registradas</p>
                <button @click="modal = 'empresa'"
                        class="inline-block mt-5 bg-amber-500 hover:bg-amber-600 text-black font-semibold text-sm px-6 py-2.5 rounded-xl transition-colors">
                    Crear primera empresa
                </button>
            </div>
            @endforelse
        </div>
    </div>

    {{-- ── TAB: USUARIOS ────────────────────────────────────────── --}}
    <div x-show="tab === 'usuarios'" x-cloak class="fade-in">

        <div class="flex items-center justify-between mb-4">
            <p class="text-slate-500 text-sm">{{ $usuarios->total() }} usuario(s) en la plataforma</p>
        </div>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-[#1e2d47]">
                            <th class="table-th">Usuario</th>
                            <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden sm:table-cell">Email</th>
                            <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Empresas asignadas</th>
                            <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-5 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usuarios as $u)
                        <tr class="table-row">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-sm
                                                text-white flex-shrink-0 bg-gradient-to-br from-blue-500 to-purple-600">
                                        {{ strtoupper(substr($u->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold">{{ $u->name }}</p>
                                        @if($u->getRoleNames()->isNotEmpty())
                                        <p class="text-xs text-slate-500 capitalize">{{ str_replace('-',' ',$u->getRoleNames()->first()) }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-4 hidden sm:table-cell">
                                <span class="text-sm text-slate-400">{{ $u->email }}</span>
                            </td>
                            <td class="px-3 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($u->empresas as $emp)
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold
                                                 {{ $emp->pivot->rol === 'admin'
                                                    ? 'bg-amber-500/10 text-amber-500'
                                                    : 'bg-[#1a2235] text-slate-400' }}">
                                        {{ $emp->pivot->rol === 'admin' ? '★ ' : '' }}{{ Str::limit($emp->razon_social, 18) }}
                                    </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('backoffice.usuarios.editar', $u) }}"
                                   class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                          inline-flex items-center justify-center text-slate-400
                                          hover:text-amber-500 hover:border-amber-500/50 transition-colors">
                                    <i class="fas fa-pen text-xs"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-5 py-16 text-center">
                                <i class="fas fa-users text-3xl text-slate-700 mb-3 block"></i>
                                <p class="text-slate-500">Sin usuarios registrados.</p>
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
    </div>

</div>{{-- /max-w --}}

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
