@extends('backoffice.layout')
@section('title', 'Dashboard')

@section('content')

<div class="mb-8">
    <h1 class="font-display font-black text-2xl text-white">Panel de Plataforma</h1>
    <p class="text-slate-500 text-sm mt-1">Administración general de FacturaCO</p>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
    @php
        $stats = [
            ['label' => 'Empresas totales',  'valor' => $totalEmpresas,  'icon' => 'fa-building',    'color' => 'violet'],
            ['label' => 'Matrices (grupos)', 'valor' => $totalMatrices,  'icon' => 'fa-sitemap',     'color' => 'blue'],
            ['label' => 'Filiales',          'valor' => $totalFiliales,  'icon' => 'fa-code-branch', 'color' => 'cyan'],
            ['label' => 'Usuarios clientes', 'valor' => $totalUsuarios,  'icon' => 'fa-users',       'color' => 'emerald'],
        ];
    @endphp
    @foreach($stats as $s)
    <div class="bg-[#0d1117] border border-{{ $s['color'] }}-900/30 rounded-2xl p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-slate-400 text-xs">{{ $s['label'] }}</span>
            <div class="w-8 h-8 bg-{{ $s['color'] }}-600/20 rounded-lg flex items-center justify-center">
                <i class="fas {{ $s['icon'] }} text-{{ $s['color'] }}-400 text-xs"></i>
            </div>
        </div>
        <p class="font-display font-black text-3xl text-white">{{ $s['valor'] }}</p>
    </div>
    @endforeach
</div>

{{-- Lista de grupos empresariales --}}
<div class="flex items-center justify-between mb-4">
    <h2 class="font-display font-bold text-lg text-white">Grupos empresariales</h2>
    <a href="{{ route('backoffice.empresas.crear') }}"
       class="bg-violet-600 hover:bg-violet-500 text-white text-sm px-4 py-2 rounded-xl transition-colors">
        <i class="fas fa-plus mr-2"></i>Nueva empresa
    </a>
</div>

<div class="space-y-3">
    @forelse($empresas as $emp)
    <div class="bg-[#0d1117] border border-white/5 rounded-2xl p-5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-violet-600/10 border border-violet-600/20 rounded-xl
                            flex items-center justify-center font-display font-black text-violet-400 text-sm">
                    {{ strtoupper(substr($emp->razon_social, 0, 2)) }}
                </div>
                <div>
                    <p class="font-medium text-white">{{ $emp->razon_social }}</p>
                    <p class="text-slate-500 text-xs">NIT: {{ $emp->nit }} · {{ $emp->usuarios_count }} usuario(s)</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('backoffice.impersonar', $emp) }}">
                    @csrf
                    <button type="submit"
                            class="text-xs px-3 py-1.5 rounded-lg border border-amber-500/30
                                   text-amber-400 hover:bg-amber-500/10 transition-colors">
                        <i class="fas fa-eye mr-1"></i>Ver como cliente
                    </button>
                </form>
                <a href="{{ route('backoffice.empresas.editar', $emp) }}"
                   class="text-xs px-3 py-1.5 rounded-lg border border-white/10
                          text-slate-400 hover:bg-white/5 transition-colors">
                    <i class="fas fa-pen mr-1"></i>Editar
                </a>
            </div>
        </div>

        {{-- Filiales --}}
        @if($emp->filiales->count())
        <div class="mt-4 ml-14 space-y-2">
            @foreach($emp->filiales as $filial)
            <div class="flex items-center gap-3 bg-white/3 rounded-xl px-4 py-2.5">
                <i class="fas fa-code-branch text-slate-600 text-xs"></i>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-slate-300">{{ $filial->razon_social }}</p>
                    <p class="text-xs text-slate-600">NIT: {{ $filial->nit }}</p>
                </div>
                <a href="{{ route('backoffice.empresas.editar', $filial) }}"
                   class="text-xs text-slate-500 hover:text-slate-300 transition-colors">
                    <i class="fas fa-pen"></i>
                </a>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @empty
    <div class="bg-[#0d1117] border border-white/5 rounded-2xl p-12 text-center">
        <i class="fas fa-building text-3xl text-slate-700 mb-3"></i>
        <p class="text-slate-500">Aún no hay empresas registradas.</p>
        <a href="{{ route('backoffice.empresas.crear') }}"
           class="inline-block mt-4 bg-violet-600 hover:bg-violet-500 text-white text-sm px-5 py-2 rounded-xl transition-colors">
            Crear primera empresa
        </a>
    </div>
    @endforelse
</div>

@endsection
