@extends('layouts.app')
@section('title', 'Usuarios')
@section('page-title', 'Usuarios & Roles')

@section('content')

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

{{-- Encabezado --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-display font-bold text-2xl">Usuarios & Roles</h1>
        <p class="text-slate-500 text-sm mt-1">Gestiona los usuarios y sus niveles de acceso</p>
    </div>
    <a href="{{ route('usuarios.create') }}"
       class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600
              text-black font-semibold px-5 py-2.5 rounded-xl transition-colors">
        <i class="fas fa-plus"></i> Nuevo Usuario
    </a>
</div>

{{-- Roles KPIs --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
    @foreach($roles as $rol)
    @php
        $colores = [
            'super-admin' => ['bg-amber-500/10',   'text-amber-500',   'bg-amber-500'],
            'admin'       => ['bg-blue-500/10',     'text-blue-400',    'bg-blue-500'],
            'vendedor'    => ['bg-emerald-500/10',  'text-emerald-500', 'bg-emerald-500'],
            'bodeguero'   => ['bg-purple-500/10',   'text-purple-400',  'bg-purple-500'],
            'contador'    => ['bg-cyan-500/10',     'text-cyan-400',    'bg-cyan-500'],
            'solo-lectura'=> ['bg-slate-500/10',    'text-slate-400',   'bg-slate-500'],
        ];
        $c = $colores[$rol->name] ?? ['bg-slate-500/10','text-slate-400','bg-slate-500'];
    @endphp
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-4 text-center">
        <div class="font-display font-bold text-2xl {{ $c[1] }}">{{ $rol->users_count }}</div>
        <div class="text-xs text-slate-500 mt-1 capitalize">{{ str_replace('-',' ',$rol->name) }}</div>
        <div class="w-full h-1 rounded-full mt-2 {{ $c[0] }}">
            <div class="h-1 rounded-full {{ $c[2] }}"
                 style="width: {{ $totalUsuarios > 0 ? ($rol->users_count/$totalUsuarios)*100 : 0 }}%"></div>
        </div>
    </div>
    @endforeach
</div>

{{-- Tabla --}}
<div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl overflow-hidden">

    {{-- Stats --}}
    <div class="grid grid-cols-2 border-b border-[#1e2d47]">
        <div class="px-5 py-3 text-center border-r border-[#1e2d47]">
            <div class="font-display font-bold text-lg">{{ $totalUsuarios }}</div>
            <div class="text-xs text-slate-500">Total usuarios</div>
        </div>
        <div class="px-5 py-3 text-center">
            <div class="font-display font-bold text-lg text-emerald-500">
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
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Rol</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden lg:table-cell">Empresa(s)</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Estado</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-5 py-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $usuario)
                @php
                    $rolNombre = $usuario->getRoleNames()->first() ?? 'sin-rol';
                    $c = $colores[$rolNombre] ?? ['bg-slate-500/10','text-slate-400','bg-slate-500'];
                    $esActual  = $usuario->id === auth()->id();
                @endphp
                <tr class="border-b border-[#1e2d47]/50 hover:bg-[#1a2235]/50 transition-colors
                           {{ $esActual ? 'bg-amber-500/3' : '' }}">

                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center
                                        font-bold text-sm text-white flex-shrink-0
                                        bg-gradient-to-br from-blue-500 to-purple-600">
                                {{ strtoupper(substr($usuario->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="text-sm font-semibold flex items-center gap-2">
                                    {{ $usuario->name }}
                                    @if($esActual)
                                    <span class="text-[10px] bg-amber-500/15 text-amber-500
                                                 px-1.5 py-0.5 rounded-full font-medium">TÚ</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>

                    <td class="px-3 py-4 hidden md:table-cell">
                        <div class="text-sm text-slate-400">{{ $usuario->email }}</div>
                    </td>

                    <td class="px-3 py-4">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold
                                     px-2.5 py-1 rounded-full capitalize
                                     {{ $c[0] }} {{ $c[1] }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            {{ str_replace('-',' ', $rolNombre) }}
                        </span>
                    </td>

                    <td class="px-3 py-4 hidden lg:table-cell">
                        <div class="flex flex-wrap gap-1">
                            @foreach($usuario->empresas as $emp)
                            <span class="text-[10px] px-2 py-0.5 rounded-full
                                         {{ $emp->empresa_padre_id ? 'bg-slate-700/60 text-slate-400' : 'bg-violet-600/15 text-violet-400' }}">
                                {{ $emp->pivot->rol === 'admin' ? '★ ' : '' }}{{ Str::limit($emp->razon_social, 20) }}
                            </span>
                            @endforeach
                        </div>
                    </td>

                    <td class="px-3 py-4">
                        <form method="POST" action="{{ route('usuarios.activo', $usuario) }}">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    {{ $esActual ? 'disabled' : '' }}
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold
                                           px-2.5 py-1 rounded-full transition-colors
                                           {{ ($usuario->activo ?? true)
                                              ? 'bg-emerald-500/10 text-emerald-500 hover:bg-red-500/10 hover:text-red-400'
                                              : 'bg-red-500/10 text-red-400 hover:bg-emerald-500/10 hover:text-emerald-500' }}
                                           {{ $esActual ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                {{ ($usuario->activo ?? true) ? 'Activo' : 'Inactivo' }}
                            </button>
                        </form>
                    </td>

                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('usuarios.edit', $usuario) }}"
                               title="Editar"
                               class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                      flex items-center justify-center text-slate-400
                                      hover:text-amber-500 hover:border-amber-500/50 transition-colors">
                                <i class="fas fa-pen text-xs"></i>
                            </a>
                            @if(!$esActual)
                            <form method="POST" action="{{ route('usuarios.destroy', $usuario) }}"
                                  onsubmit="return confirm('¿Eliminar a {{ $usuario->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Eliminar"
                                        class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                               flex items-center justify-center text-slate-400
                                               hover:text-red-400 hover:border-red-500/50 transition-colors">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-16 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-14 h-14 bg-[#1a2235] rounded-2xl flex items-center
                                        justify-center text-slate-600 text-2xl">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="text-slate-500">No hay usuarios registrados</div>
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

{{-- Tabla de permisos por rol --}}
<div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl overflow-hidden mt-6">
    <div class="px-5 py-4 border-b border-[#1e2d47]">
        <div class="font-display font-bold text-base">Permisos por Rol</div>
        <div class="text-xs text-slate-500 mt-1">Referencia de lo que puede hacer cada rol</div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead>
                <tr class="border-b border-[#1e2d47]">
                    <th class="text-left font-semibold text-slate-500 uppercase tracking-wider px-5 py-3">Módulo</th>
                    @foreach(['Super Admin','Admin','Vendedor','Bodeguero','Contador','Solo Lectura'] as $r)
                    <th class="text-center font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">{{ $r }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php
                $grupos = [
                    'FACTURACIÓN' => [
                        ['Facturación — Ver',       [1,1,1,0,1,1]],
                        ['Facturación — Crear',     [1,1,1,0,0,0]],
                        ['Facturación — Anular',    [1,1,0,0,1,0]],
                    ],
                    'COTIZACIONES' => [
                        ['Cotizaciones — Ver',      [1,1,1,0,1,1]],
                        ['Cotizaciones — Crear',    [1,1,1,0,0,0]],
                        ['Cotizaciones — Convertir',[1,1,1,0,0,0]],
                    ],
                    'REMISIONES' => [
                        ['Remisiones — Ver',        [1,1,1,0,1,1]],
                        ['Remisiones — Crear',      [1,1,1,0,0,0]],
                    ],
                    'CLIENTES' => [
                        ['Clientes — Ver',          [1,1,1,0,1,1]],
                        ['Clientes — Crear',        [1,1,1,0,0,0]],
                        ['Clientes — Editar',       [1,1,1,0,0,0]],
                        ['Clientes — Eliminar',     [1,1,0,0,0,0]],
                    ],
                    'PROVEEDORES' => [
                        ['Proveedores — Ver',       [1,1,0,1,1,0]],
                        ['Proveedores — Crear',     [1,1,0,1,0,0]],
                        ['Proveedores — Editar',    [1,1,0,1,0,0]],
                    ],
                    'INVENTARIO' => [
                        ['Inventario — Ver',        [1,1,1,1,0,1]],
                        ['Inventario — Crear',      [1,1,0,1,0,0]],
                        ['Inventario — Editar',     [1,1,0,1,0,0]],
                        ['Inventario — Ajustar Stock',[1,1,0,1,0,0]],
                    ],
                    'COMPRAS' => [
                        ['Compras — Ver',           [1,1,0,1,1,0]],
                        ['Compras — Crear',         [1,1,0,1,0,0]],
                        ['Compras — Aprobar',       [1,1,0,0,0,0]],
                        ['Compras — Recibir',       [1,1,0,1,0,0]],
                    ],
                    'RECIBOS DE CAJA' => [
                        ['Recibos — Ver',           [1,1,1,0,1,1]],
                        ['Recibos — Crear',         [1,1,1,0,0,0]],
                    ],
                    'REPORTES' => [
                        ['Reportes — Ver',          [1,1,1,0,1,1]],
                        ['Reportes — Exportar',     [1,1,0,0,1,0]],
                        ['Impuestos / DIAN',        [1,1,0,0,1,0]],
                    ],
                    'CONFIGURACIÓN' => [
                        ['Categorías',              [1,1,0,0,0,0]],
                        ['Unidades de Medida',      [1,1,0,0,0,0]],
                        ['Empresa',                 [1,1,0,0,0,0]],
                        ['Usuarios — Gestionar',    [1,1,0,0,0,0]],
                    ],
                    'ADMINISTRACIÓN' => [
                        ['Sesiones Activas',        [1,1,0,0,0,0]],
                        ['Auditoría',               [1,1,0,0,0,0]],
                        ['Backup',                  [1,0,0,0,0,0]],
                    ],
                ];
                @endphp

                @foreach($grupos as $grupo => $filas)
                {{-- Encabezado de grupo --}}
                <tr class="bg-[#1a2235]/50">
                    <td colspan="7" class="px-5 py-2">
                        <span class="text-xs font-bold text-amber-500/70 uppercase tracking-widest">
                            {{ $grupo }}
                        </span>
                    </td>
                </tr>
                {{-- Filas del grupo --}}
                @foreach($filas as [$modulo, $perms])
                <tr class="border-b border-[#1e2d47]/40 hover:bg-[#1a2235]/30">
                    <td class="px-5 py-2.5 text-slate-400 pl-8">{{ $modulo }}</td>
                    @foreach($perms as $p)
                    <td class="px-3 py-2.5 text-center">
                        @if($p)
                        <span class="text-emerald-500"><i class="fas fa-check"></i></span>
                        @else
                        <span class="text-slate-700"><i class="fas fa-times"></i></span>
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endforeach
                @endforeach

            </tbody>
        </table>
    </div>
</div>

@endsection