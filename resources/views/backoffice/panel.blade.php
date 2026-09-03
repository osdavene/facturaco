@extends('backoffice.layout')

@section('title', 'Super Panel')

@section('content')
<div x-data="superPanel()" class="space-y-6">

    {{-- ══════════════════════════════════════════════════════════
         MODAL: Nueva empresa
    ══════════════════════════════════════════════════════════ --}}
    <div x-show="modal === 'empresa'" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
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
                        <select name="empresa_padre_id" class="form-input">
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
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
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

    {{-- Encabezado del Dashboard --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-display font-black text-2xl text-white">Super Panel Maestro</h1>
            <p class="text-slate-400 text-sm mt-0.5">Control central de empresas, planes SaaS y facturación DIAN</p>
        </div>
        <div>
            <button @click="modal = 'empresa'"
                    class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-black font-semibold text-sm rounded-xl transition-colors flex items-center gap-2 shadow-lg shadow-amber-500/20">
                <i class="fas fa-plus text-xs"></i>Nueva empresa
            </button>
        </div>
    </div>

    {{-- Stats Cards (Clicables) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        {{-- Empresas --}}
        <a href="{{ route('backoffice.empresas') }}"
           class="card p-5 text-left hover:border-blue-500/40 transition-colors cursor-pointer group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-slate-500 text-xs font-semibold uppercase tracking-wider">Empresas</span>
                <div class="w-8 h-8 bg-blue-500/10 rounded-lg flex items-center justify-center group-hover:bg-blue-500/20 transition-colors">
                    <i class="fas fa-building text-blue-500 text-xs"></i>
                </div>
            </div>
            <p class="font-display font-black text-3xl text-white">{{ $totalEmpresas }}</p>
            <p class="text-[11px] text-slate-500 mt-1">{{ $totalMatrices }} matrices · {{ $totalFiliales }} filiales</p>
        </a>

        {{-- Planes SaaS --}}
        <a href="{{ route('backoffice.planes') }}"
           class="card p-5 text-left hover:border-amber-500/40 transition-colors cursor-pointer group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-slate-500 text-xs font-semibold uppercase tracking-wider">Planes & Paquetes</span>
                <div class="w-8 h-8 bg-amber-500/10 rounded-lg flex items-center justify-center group-hover:bg-amber-500/20 transition-colors">
                    <i class="fas fa-cubes-stacked text-amber-500 text-xs"></i>
                </div>
            </div>
            <p class="font-display font-black text-3xl text-white">{{ $totalPlanes }}</p>
            <p class="text-[11px] text-amber-400 mt-1 flex items-center gap-1">
                <span>Gestionar paquetes</span>
                <i class="fas fa-arrow-right text-[9px] group-hover:translate-x-0.5 transition-transform"></i>
            </p>
        </a>

        {{-- Integración DIAN --}}
        <a href="{{ route('backoffice.dian') }}"
           class="card p-5 text-left hover:border-emerald-500/40 transition-colors cursor-pointer group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-slate-500 text-xs font-semibold uppercase tracking-wider">Facturación DIAN</span>
                <div class="w-8 h-8 bg-emerald-500/10 rounded-lg flex items-center justify-center group-hover:bg-emerald-500/20 transition-colors">
                    <i class="fas fa-file-invoice-dollar text-emerald-500 text-xs"></i>
                </div>
            </div>
            <p class="font-display font-black text-3xl text-white">{{ $totalFacturasDian }}</p>
            <p class="text-[11px] text-emerald-400 mt-1 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Motor {{ ucfirst($proveedorActivo) }} ({{ ucfirst($factusAmbiente) }})</span>
            </p>
        </a>

        {{-- Usuarios --}}
        <a href="{{ route('backoffice.usuarios') }}"
           class="card p-5 text-left hover:border-purple-500/40 transition-colors cursor-pointer group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-slate-500 text-xs font-semibold uppercase tracking-wider">Usuarios Clientes</span>
                <div class="w-8 h-8 bg-purple-500/10 rounded-lg flex items-center justify-center group-hover:bg-purple-500/20 transition-colors">
                    <i class="fas fa-users text-purple-500 text-xs"></i>
                </div>
            </div>
            <p class="font-display font-black text-3xl text-white">{{ $totalUsuarios }}</p>
            <p class="text-[11px] text-slate-500 mt-1">Cuentas activas en la plataforma</p>
        </a>

    </div>

    {{-- Tabs de Vistas Rápidas --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-1 card p-1.5 w-fit">
            <button @click="tab = 'resumen'"
                    :class="tab === 'resumen' ? 'bg-amber-500 text-black font-bold shadow-lg' : 'text-slate-400 hover:text-white hover:bg-[#1a2235]'"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium transition-all">
                <i class="fas fa-gauge-high text-xs"></i>
                Resumen
            </button>
            <button @click="tab = 'empresas'"
                    :class="tab === 'empresas' ? 'bg-amber-500 text-black font-bold shadow-lg' : 'text-slate-400 hover:text-white hover:bg-[#1a2235]'"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium transition-all">
                <i class="fas fa-building text-xs"></i>
                Empresas
            </button>
            <button @click="tab = 'usuarios'"
                    :class="tab === 'usuarios' ? 'bg-amber-500 text-black font-bold shadow-lg' : 'text-slate-400 hover:text-white hover:bg-[#1a2235]'"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium transition-all">
                <i class="fas fa-users text-xs"></i>
                Usuarios
            </button>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('backoffice.dian') }}"
               class="px-3.5 py-2 bg-[#1a2235] hover:bg-[#24314d] border border-[#1e2d47] text-slate-300 hover:text-white text-xs font-semibold rounded-xl transition-colors flex items-center gap-2">
                <i class="fas fa-file-invoice-dollar text-emerald-400"></i>
                <span>Configurar DIAN</span>
            </a>
            <a href="{{ route('backoffice.correo') }}"
               class="px-3.5 py-2 bg-[#1a2235] hover:bg-[#24314d] border border-[#1e2d47] text-slate-300 hover:text-white text-xs font-semibold rounded-xl transition-colors flex items-center gap-2">
                <i class="fas fa-envelope-open-text text-blue-400"></i>
                <span>Servidor Correo</span>
            </a>
            <a href="{{ route('backoffice.planes') }}"
               class="px-3.5 py-2 bg-[#1a2235] hover:bg-[#24314d] border border-[#1e2d47] text-slate-300 hover:text-white text-xs font-semibold rounded-xl transition-colors flex items-center gap-2">
                <i class="fas fa-cubes-stacked text-amber-400"></i>
                <span>Planes SaaS</span>
            </a>
        </div>
    </div>

    {{-- ── TAB: RESUMEN ────────────────────────────────────────── --}}
    <div x-show="tab === 'resumen'" x-cloak class="fade-in">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Grupos recientes --}}
            <div class="card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-display font-bold text-base text-white">Empresas registradas</h2>
                    <a href="{{ route('backoffice.empresas') }}" class="text-xs text-amber-400 hover:underline">Ver todas →</a>
                </div>
                <div class="space-y-3">
                    @foreach($empresas->take(5) as $emp)
                    <div class="flex items-center justify-between p-3.5 bg-[#1a2235]/60 border border-[#1e2d47] rounded-xl hover:border-slate-600 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-amber-500/10 text-amber-500 rounded-lg flex items-center justify-center font-bold text-xs">
                                {{ strtoupper(substr($emp->razon_social, 0, 2)) }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-semibold text-white">{{ $emp->razon_social }}</p>
                                    @if($emp->plan)
                                    <span class="text-[10px] px-2 py-0.2 bg-{{ $emp->plan->color }}-500/10 text-{{ $emp->plan->color }}-400 rounded-full font-medium">
                                        {{ $emp->plan->nombre }}
                                    </span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-500">NIT: {{ $emp->nit }} · {{ $emp->usuarios_count }} usuario(s)</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('backoffice.impersonar', $emp) }}">
                                @csrf
                                <button type="submit" class="text-xs px-2.5 py-1.5 bg-[#111827] text-slate-300 hover:text-white rounded-lg border border-[#1e2d47] hover:border-amber-500/40 transition-colors" title="Ver como cliente">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </form>
                            <a href="{{ route('backoffice.empresas.editar', $emp) }}" class="text-xs px-2.5 py-1.5 bg-[#111827] text-slate-300 hover:text-white rounded-lg border border-[#1e2d47] hover:border-amber-500/40 transition-colors">
                                <i class="fas fa-pen"></i>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Planes y Resumen de Suscripciones --}}
            <div class="card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-display font-bold text-base text-white">Planes & Paquetes Activos</h2>
                    <a href="{{ route('backoffice.planes') }}" class="text-xs text-amber-400 hover:underline">Gestionar planes →</a>
                </div>
                <div class="space-y-3">
                    @forelse($planes as $p)
                    <div class="flex items-center justify-between p-3.5 bg-[#1a2235]/60 border border-[#1e2d47] rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-{{ $p->color }}-500/10 text-{{ $p->color }}-400 rounded-lg flex items-center justify-center font-bold text-xs">
                                <i class="fas fa-cube"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-white">{{ $p->nombre }}</p>
                                <p class="text-xs text-slate-500">{{ $p->precio_formateado }} · {{ $p->limite_facturas_texto }}</p>
                            </div>
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-1 bg-[#111827] border border-[#1e2d47] rounded-lg text-slate-300">
                            {{ $p->empresas_count }} {{ $p->empresas_count == 1 ? 'empresa' : 'empresas' }}
                        </span>
                    </div>
                    @empty
                    <p class="text-xs text-slate-500 text-center py-4">No hay planes creados aún.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    {{-- ── TAB: EMPRESAS ───────────────────────────────────────── --}}
    <div x-show="tab === 'empresas'" x-cloak class="fade-in space-y-4">
        @forelse($empresas as $emp)
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-[#1a2235] border border-[#1e2d47] rounded-xl flex items-center justify-center font-display font-black text-amber-500 text-sm">
                        {{ strtoupper(substr($emp->razon_social, 0, 2)) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="font-semibold text-white">{{ $emp->razon_social }}</p>
                            <span class="text-[10px] px-2 py-0.5 bg-amber-500/10 text-amber-500 rounded-full font-semibold">Matriz</span>
                            @if($emp->plan)
                            <span class="text-[10px] px-2 py-0.5 bg-{{ $emp->plan->color }}-500/10 text-{{ $emp->plan->color }}-400 border border-{{ $emp->plan->color }}-500/20 rounded-full font-medium">
                                <i class="fas fa-cube text-[9px] mr-0.5"></i>{{ $emp->plan->nombre }} ({{ $emp->plan->limite_facturas_texto }})
                            </span>
                            @else
                            <span class="text-[10px] px-2 py-0.5 bg-slate-700 text-slate-400 rounded-full font-medium">Sin Plan</span>
                            @endif
                        </div>
                        <p class="text-slate-500 text-xs mt-0.5">
                            NIT: {{ $emp->nit }}
                            @if($emp->email) · {{ $emp->email }} @endif
                            · {{ $emp->usuarios_count }} usuario(s)
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button @click="abrirAdmin({{ $emp->id }}, '{{ addslashes($emp->razon_social) }}')"
                            class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg flex items-center justify-center text-slate-400 hover:text-emerald-400 hover:border-emerald-500/50 transition-colors"
                            title="Crear admin">
                        <i class="fas fa-user-plus text-xs"></i>
                    </button>
                    <form method="POST" action="{{ route('backoffice.impersonar', $emp) }}">
                        @csrf
                        <button type="submit" class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg flex items-center justify-center text-slate-400 hover:text-amber-500 hover:border-amber-500/50 transition-colors" title="Ver como cliente">
                            <i class="fas fa-eye text-xs"></i>
                        </button>
                    </form>
                    <a href="{{ route('backoffice.empresas.editar', $emp) }}"
                       class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg flex items-center justify-center text-slate-400 hover:text-amber-500 hover:border-amber-500/50 transition-colors"
                       title="Editar empresa y plan">
                        <i class="fas fa-pen text-xs"></i>
                    </a>
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
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-sm font-medium text-slate-300">{{ $filial->razon_social }}</p>
                                <span class="text-[10px] px-2 py-0.5 bg-[#1a2235] text-slate-500 rounded-full">Filial</span>
                                @if($filial->plan)
                                <span class="text-[10px] px-2 py-0.5 bg-{{ $filial->plan->color }}-500/10 text-{{ $filial->plan->color }}-400 rounded-full font-medium">
                                    {{ $filial->plan->nombre }}
                                </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-600">NIT: {{ $filial->nit }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('backoffice.empresas.editar', $filial) }}"
                           class="w-7 h-7 bg-[#1a2235] border border-[#1e2d47] rounded-lg flex items-center justify-center text-slate-400 hover:text-amber-500 transition-colors">
                            <i class="fas fa-pen text-[10px]"></i>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        @empty
        <div class="card p-12 text-center text-slate-500">
            <i class="fas fa-building text-3xl mb-3 text-slate-600"></i>
            <p>No hay empresas registradas.</p>
        </div>
        @endforelse
    </div>

    {{-- ── TAB: USUARIOS ───────────────────────────────────────── --}}
    <div x-show="tab === 'usuarios'" x-cloak class="fade-in">
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-[#141c2e] border-b border-[#1e2d47] text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">Usuario</th>
                            <th class="px-3 py-3.5 hidden sm:table-cell">Email</th>
                            <th class="px-3 py-3.5">Empresas</th>
                            <th class="px-5 py-3.5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#1e2d47]">
                        @forelse($usuarios as $u)
                        <tr class="hover:bg-[#1a2235]/40 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-amber-500/10 text-amber-500 flex items-center justify-center font-bold text-xs">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-white">{{ $u->name }}</p>
                                        @if($u->getRoleNames()->isNotEmpty())
                                        <p class="text-xs text-slate-500 capitalize">{{ str_replace('-',' ',$u->getRoleNames()->first()) }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-4 hidden sm:table-cell text-slate-400">
                                {{ $u->email }}
                            </td>
                            <td class="px-3 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($u->empresas as $emp)
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold {{ $emp->pivot->rol === 'admin' ? 'bg-amber-500/10 text-amber-500' : 'bg-[#1a2235] text-slate-400' }}">
                                        {{ $emp->pivot->rol === 'admin' ? '★ ' : '' }}{{ Str::limit($emp->razon_social, 18) }}
                                    </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('backoffice.usuarios.editar', $u) }}"
                                   class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg inline-flex items-center justify-center text-slate-400 hover:text-amber-500 hover:border-amber-500/50 transition-colors">
                                    <i class="fas fa-pen text-xs"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-5 py-12 text-center text-slate-500">
                                Sin usuarios registrados.
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

</div>

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
@endsection
