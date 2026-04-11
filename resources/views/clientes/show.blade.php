@extends('layouts.app')
@section('title', 'Detalle Cliente')
@section('page-title', 'Clientes · Detalle')

@section('content')
<div class="max-w-4xl mx-auto">

    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('clientes.index') }}"
               class="w-9 h-9 bg-[#141c2e] border border-[#1e2d47] rounded-xl
                      flex items-center justify-center text-slate-400
                      hover:text-amber-500 hover:border-amber-500/50 transition-colors">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="font-display font-bold text-2xl">{{ $cliente->nombre_completo }}</h1>
                <p class="text-slate-500 text-sm">{{ $cliente->tipo_documento }}: {{ $cliente->documento_formateado }}</p>
            </div>
        </div>
        <a href="{{ route('clientes.edit', $cliente) }}"
           class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600
                  text-black font-semibold px-5 py-2.5 rounded-xl transition-colors">
            <i class="fas fa-pen"></i> Editar
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Tarjeta principal --}}
        <div class="lg:col-span-1">
            <div class="card p-6 text-center">
                <div class="w-20 h-20 rounded-2xl mx-auto mb-4 flex items-center justify-center
                            font-bold text-3xl text-white
                            {{ $cliente->tipo_persona == 'juridica'
                               ? 'bg-gradient-to-br from-blue-500 to-purple-600'
                               : 'bg-gradient-to-br from-emerald-500 to-teal-600' }}">
                    {{ strtoupper(substr($cliente->nombre_completo, 0, 2)) }}
                </div>
                <div class="font-display font-bold text-lg mb-1">{{ $cliente->nombre_completo }}</div>
                <div class="text-slate-500 text-sm mb-3">
                    {{ $cliente->tipo_persona == 'juridica' ? 'Persona Jurídica' : 'Persona Natural' }}
                </div>
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full
                    {{ $cliente->activo ? 'bg-emerald-500/10 text-emerald-500' : 'bg-red-500/10 text-red-400' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                    {{ $cliente->activo ? 'Activo' : 'Inactivo' }}
                </span>

                <div class="mt-5 pt-5 border-t border-[#1e2d47] space-y-3 text-left">
                    @if($cliente->email)
                    <div class="flex items-center gap-3 text-sm">
                        <i class="fas fa-envelope w-4 text-slate-500"></i>
                        <span class="text-slate-300">{{ $cliente->email }}</span>
                    </div>
                    @endif
                    @if($cliente->celular)
                    <div class="flex items-center gap-3 text-sm">
                        <i class="fas fa-mobile-alt w-4 text-slate-500"></i>
                        <span class="text-slate-300">{{ $cliente->celular }}</span>
                    </div>
                    @endif
                    @if($cliente->telefono)
                    <div class="flex items-center gap-3 text-sm">
                        <i class="fas fa-phone w-4 text-slate-500"></i>
                        <span class="text-slate-300">{{ $cliente->telefono }}</span>
                    </div>
                    @endif
                    @if($cliente->municipio)
                    <div class="flex items-center gap-3 text-sm">
                        <i class="fas fa-map-marker-alt w-4 text-slate-500"></i>
                        <span class="text-slate-300">
                            {{ $cliente->municipio }}{{ $cliente->departamento ? ', '.$cliente->departamento : '' }}
                        </span>
                    </div>
                    @endif
                    @if($cliente->direccion)
                    <div class="flex items-center gap-3 text-sm">
                        <i class="fas fa-home w-4 text-slate-500"></i>
                        <span class="text-slate-300">{{ $cliente->direccion }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Info tributaria y comercial --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Tributario --}}
            <div class="card p-6">
                <h3 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                    <i class="fas fa-percent text-amber-500 text-sm"></i>
                    Información Tributaria
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div>
                        <div class="text-xs text-slate-500 uppercase tracking-wider mb-1">Régimen</div>
                        <div class="text-sm font-semibold">
                            {{ $cliente->regimen == 'responsable_iva' ? 'Responsable IVA' : 'Simple' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 uppercase tracking-wider mb-1">ReteFuente</div>
                        <div class="text-sm font-semibold">{{ $cliente->retefuente_pct }}%</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 uppercase tracking-wider mb-1">ReteICA</div>
                        <div class="text-sm font-semibold">{{ $cliente->reteica_pct }}%</div>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 mt-4">
                    @if($cliente->responsable_iva)
                    <span class="text-xs bg-blue-500/10 text-blue-400 px-2.5 py-1 rounded-full">
                        Responsable IVA
                    </span>
                    @endif
                    @if($cliente->gran_contribuyente)
                    <span class="text-xs bg-purple-500/10 text-purple-400 px-2.5 py-1 rounded-full">
                        Gran Contribuyente
                    </span>
                    @endif
                    @if($cliente->autoretenedor)
                    <span class="text-xs bg-amber-500/10 text-amber-400 px-2.5 py-1 rounded-full">
                        Autoretenedor
                    </span>
                    @endif
                </div>
            </div>

            {{-- Comercial --}}
            <div class="card p-6">
                <h3 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                    <i class="fas fa-handshake text-amber-500 text-sm"></i>
                    Condiciones Comerciales
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-[#1a2235] rounded-xl p-4">
                        <div class="text-xs text-slate-500 uppercase tracking-wider mb-1">Plazo de Pago</div>
                        <div class="font-display font-bold text-2xl">{{ $cliente->plazo_pago }}</div>
                        <div class="text-xs text-slate-500">días</div>
                    </div>
                    <div class="bg-[#1a2235] rounded-xl p-4">
                        <div class="text-xs text-slate-500 uppercase tracking-wider mb-1">Cupo de Crédito</div>
                        <div class="font-display font-bold text-2xl">
                            ${{ number_format($cliente->cupo_credito, 0, ',', '.') }}
                        </div>
                        <div class="text-xs text-slate-500">COP</div>
                    </div>
                </div>
                @if($cliente->observaciones)
                <div class="mt-4 p-3 bg-[#1a2235] rounded-xl">
                    <div class="text-xs text-slate-500 mb-1">Observaciones</div>
                    <div class="text-sm text-slate-300">{{ $cliente->observaciones }}</div>
                </div>
                @endif
            </div>

            {{-- Fechas --}}
            <div class="card p-4">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <div class="text-xs text-slate-500 uppercase tracking-wider mb-1">Creado</div>
                        <div class="text-slate-300">{{ $cliente->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 uppercase tracking-wider mb-1">Última actualización</div>
                        <div class="text-slate-300">{{ $cliente->updated_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>

            <x-activity-log :model="$cliente" />

        </div>
    </div>
</div>
@endsection