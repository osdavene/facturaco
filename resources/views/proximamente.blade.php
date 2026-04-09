@extends('layouts.app')
@section('title', $titulo)
@section('page-title', $titulo)

@section('content')
<div class="max-w-2xl mx-auto py-12">

    {{-- Icono central --}}
    <div class="text-center mb-10">
        <div class="relative inline-block mb-6">
            <div class="w-24 h-24 bg-{{ $color }}-500/10 border-2 border-{{ $color }}-500/20
                        rounded-3xl flex items-center justify-center mx-auto">
                <i class="fas {{ $icono }} text-{{ $color }}-500 text-4xl"></i>
            </div>
            {{-- Badge "Próximamente" --}}
            <div class="absolute -top-2 -right-2 bg-amber-500 text-black text-[10px]
                        font-black px-2 py-0.5 rounded-full uppercase tracking-wider">
                Próximo
            </div>
        </div>

        <h1 class="font-display font-bold text-3xl mb-3">{{ $titulo }}</h1>
        <p class="text-slate-400 text-base leading-relaxed max-w-lg mx-auto">
            {{ $descripcion }}
        </p>
    </div>

    {{-- Features que tendrá --}}
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-6 mb-6">
        <h2 class="font-display font-bold text-sm text-slate-400 uppercase tracking-wider mb-5
                   flex items-center gap-2">
            <i class="fas fa-list-check text-{{ $color }}-500"></i>
            Lo que incluirá este módulo
        </h2>
        <div class="space-y-3">
            @foreach($features as $feature)
            <div class="flex items-center gap-3">
                <div class="w-6 h-6 rounded-lg bg-{{ $color }}-500/10 border border-{{ $color }}-500/20
                            flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-check text-{{ $color }}-500 text-xs"></i>
                </div>
                <span class="text-sm text-slate-300">{{ $feature }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Barra de progreso ficticia --}}
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5 mb-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                Progreso de desarrollo
            </span>
            <span class="text-xs font-bold text-{{ $color }}-500">En diseño</span>
        </div>
        <div class="w-full bg-[#1e2d47] rounded-full h-2">
            <div class="h-2 rounded-full bg-gradient-to-r from-{{ $color }}-600 to-{{ $color }}-400"
                 style="width: 25%"></div>
        </div>
        <div class="flex justify-between mt-2">
            <span class="text-[10px] text-slate-600">Diseño</span>
            <span class="text-[10px] text-slate-600">Desarrollo</span>
            <span class="text-[10px] text-slate-600">Pruebas</span>
            <span class="text-[10px] text-slate-600">Lanzamiento</span>
        </div>
    </div>

    {{-- Acciones --}}
    <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <a href="{{ route('dashboard') }}"
           class="inline-flex items-center justify-center gap-2
                  bg-[#1a2235] border border-[#1e2d47] hover:border-slate-500
                  text-slate-400 hover:text-slate-200 px-6 py-3 rounded-xl
                  transition-colors text-sm">
            <i class="fas fa-arrow-left text-xs"></i> Volver al Dashboard
        </a>
        <a href="{{ route('facturas.index') }}"
           class="inline-flex items-center justify-center gap-2
                  bg-amber-500 hover:bg-amber-600 text-black font-semibold
                  px-6 py-3 rounded-xl transition-colors text-sm">
            <i class="fas fa-file-invoice text-xs"></i> Ir a Facturación
        </a>
    </div>

</div>
@endsection