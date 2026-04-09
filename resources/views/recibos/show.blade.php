@extends('layouts.app')
@section('title', 'Recibo '.$recibo->numero)
@section('page-title', 'Recibo · '.$recibo->numero)

@section('content')
<div class="max-w-3xl mx-auto">

    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('recibos.index') }}"
               class="w-9 h-9 bg-[#141c2e] border border-[#1e2d47] rounded-xl
                      flex items-center justify-center text-slate-400
                      hover:text-amber-500 hover:border-amber-500/50 transition-colors">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="font-display font-bold text-2xl font-mono text-emerald-500">
                    {{ $recibo->numero }}
                </h1>
                <p class="text-slate-500 text-sm">{{ $recibo->fecha->format('d/m/Y') }}</p>
            </div>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('recibos.pdf', $recibo) }}" target="_blank"
               class="inline-flex items-center gap-2 bg-red-500/10 border border-red-500/30
                      text-red-400 hover:bg-red-500/20 px-4 py-2.5 rounded-xl text-sm transition-colors">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
        </div>
    </div>

    {{-- Estado --}}
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5 mb-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center
                            {{ $recibo->estado === 'activo' ? 'bg-emerald-500/10' : 'bg-red-500/10' }}">
                    <i class="fas {{ $recibo->estado === 'activo' ? 'fa-check-circle text-emerald-500' : 'fa-ban text-red-400' }} text-xl"></i>
                </div>
                <div>
                    <div class="font-display font-bold text-3xl text-emerald-500">
                        ${{ number_format($recibo->valor, 0, ',', '.') }}
                    </div>
                    <div class="text-xs text-slate-500 uppercase tracking-wider mt-0.5">
                        {{ ucfirst($recibo->estado) }} · {{ ucfirst($recibo->forma_pago) }}
                    </div>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full
                {{ $recibo->estado === 'activo'
                   ? 'bg-emerald-500/10 text-emerald-500'
                   : 'bg-red-500/10 text-red-400' }}">
                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                {{ ucfirst($recibo->estado) }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">

        {{-- Cliente --}}
        <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5">
            <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">
                <i class="fas fa-user mr-1"></i> Cliente
            </h3>
            <div class="font-semibold" style="color:#e2e8f0">{{ $recibo->cliente_nombre }}</div>
            <div class="text-sm text-slate-400 mt-1">{{ $recibo->cliente_documento }}</div>
        </div>

        {{-- Detalles --}}
        <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5">
            <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">
                <i class="fas fa-info-circle mr-1"></i> Detalles
            </h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">Fecha</span>
                    <span style="color:#e2e8f0">{{ $recibo->fecha->format('d/m/Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Forma de pago</span>
                    <span class="capitalize" style="color:#e2e8f0">{{ $recibo->forma_pago }}</span>
                </div>
                @if($recibo->banco)
                <div class="flex justify-between">
                    <span class="text-slate-500">Banco</span>
                    <span style="color:#e2e8f0">{{ $recibo->banco }}</span>
                </div>
                @endif
                @if($recibo->num_referencia)
                <div class="flex justify-between">
                    <span class="text-slate-500">Referencia</span>
                    <span class="font-mono text-xs" style="color:#e2e8f0">{{ $recibo->num_referencia }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-slate-500">Registrado por</span>
                    <span style="color:#e2e8f0">{{ $recibo->usuario->name ?? '—' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Concepto --}}
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5 mb-4">
        <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Concepto</h3>
        <p class="text-sm" style="color:#e2e8f0">{{ $recibo->concepto }}</p>
        @if($recibo->observaciones)
        <div class="mt-3 pt-3 border-t border-[#1e2d47]">
            <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Observaciones</h3>
            <p class="text-sm text-slate-400">{{ $recibo->observaciones }}</p>
        </div>
        @endif
    </div>

    {{-- Factura relacionada --}}
    @if($recibo->factura)
    <div class="bg-amber-500/5 border border-amber-500/20 rounded-2xl p-5 mb-4">
        <h3 class="text-xs font-semibold text-amber-500 uppercase tracking-wider mb-3">
            <i class="fas fa-file-invoice mr-1"></i> Factura Relacionada
        </h3>
        <div class="flex items-center justify-between">
            <div>
                <div class="font-mono font-semibold text-amber-500">{{ $recibo->factura->numero }}</div>
                <div class="text-xs text-slate-500 mt-0.5">
                    Total: ${{ number_format($recibo->factura->total, 0, ',', '.') }} —
                    Pagado: ${{ number_format($recibo->factura->total_pagado, 0, ',', '.') }}
                </div>
            </div>
            <a href="{{ route('facturas.show', $recibo->factura) }}"
               class="text-xs bg-amber-500/10 text-amber-500 hover:bg-amber-500/20
                      px-3 py-1.5 rounded-lg transition-colors">
                Ver factura →
            </a>
        </div>
    </div>
    @endif

</div>
@endsection