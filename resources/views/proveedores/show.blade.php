@extends('layouts.app')
@section('title', 'Detalle Proveedor')
@section('page-title', 'Proveedores · Detalle')

@section('content')
<div class="max-w-4xl mx-auto">

    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('proveedores.index') }}"
               class="w-9 h-9 bg-[#141c2e] border border-[#1e2d47] rounded-xl
                      flex items-center justify-center text-slate-400
                      hover:text-amber-500 hover:border-amber-500/50 transition-colors">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="font-display font-bold text-2xl">{{ $proveedor->razon_social }}</h1>
                <p class="text-slate-500 text-sm">{{ $proveedor->tipo_documento }}: {{ $proveedor->documento_formateado }}</p>
            </div>
        </div>
        <a href="{{ route('proveedores.edit', $proveedor) }}"
           class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600
                  text-black font-semibold px-5 py-2.5 rounded-xl transition-colors">
            <i class="fas fa-pen"></i> Editar
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-1">
            <div class="card p-6 text-center">
                <div class="w-20 h-20 rounded-2xl mx-auto mb-4 flex items-center justify-center
                            font-bold text-3xl text-white
                            bg-gradient-to-br from-blue-500 to-purple-600">
                    {{ strtoupper(substr($proveedor->razon_social, 0, 2)) }}
                </div>
                <div class="font-display font-bold text-lg mb-1">{{ $proveedor->razon_social }}</div>
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full
                    {{ $proveedor->activo ? 'bg-emerald-500/10 text-emerald-500' : 'bg-red-500/10 text-red-400' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                    {{ $proveedor->activo ? 'Activo' : 'Inactivo' }}
                </span>
                <div class="mt-5 pt-5 border-t border-[#1e2d47] space-y-3 text-left">
                    @if($proveedor->nombre_contacto)
                    <div class="flex items-center gap-3 text-sm">
                        <i class="fas fa-user w-4 text-slate-500"></i>
                        <div>
                            <div class="text-slate-300">{{ $proveedor->nombre_contacto }}</div>
                            @if($proveedor->cargo_contacto)
                            <div class="text-xs text-slate-500">{{ $proveedor->cargo_contacto }}</div>
                            @endif
                        </div>
                    </div>
                    @endif
                    @if($proveedor->email)
                    <div class="flex items-center gap-3 text-sm">
                        <i class="fas fa-envelope w-4 text-slate-500"></i>
                        <span class="text-slate-300">{{ $proveedor->email }}</span>
                    </div>
                    @endif
                    @if($proveedor->celular)
                    <div class="flex items-center gap-3 text-sm">
                        <i class="fas fa-mobile-alt w-4 text-slate-500"></i>
                        <span class="text-slate-300">{{ $proveedor->celular }}</span>
                    </div>
                    @endif
                    @if($proveedor->municipio)
                    <div class="flex items-center gap-3 text-sm">
                        <i class="fas fa-map-marker-alt w-4 text-slate-500"></i>
                        <span class="text-slate-300">{{ $proveedor->municipio }}{{ $proveedor->departamento ? ', '.$proveedor->departamento : '' }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-4">
            <div class="card p-6">
                <h3 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                    <i class="fas fa-percent text-amber-500 text-sm"></i>
                    Información Tributaria
                </h3>
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div>
                        <div class="text-xs text-slate-500 uppercase tracking-wider mb-1">Régimen</div>
                        <div class="text-sm font-semibold">{{ $proveedor->regimen == 'responsable_iva' ? 'Resp. IVA' : 'Simple' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 uppercase tracking-wider mb-1">ReteFuente</div>
                        <div class="text-sm font-semibold">{{ $proveedor->retefuente_pct }}%</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 uppercase tracking-wider mb-1">ReteICA</div>
                        <div class="text-sm font-semibold">{{ $proveedor->reteica_pct }}%</div>
                    </div>
                </div>
                <div class="flex gap-2 flex-wrap">
                    @if($proveedor->gran_contribuyente)
                    <span class="text-xs bg-purple-500/10 text-purple-400 px-2.5 py-1 rounded-full">Gran Contribuyente</span>
                    @endif
                    @if($proveedor->autoretenedor)
                    <span class="text-xs bg-amber-500/10 text-amber-400 px-2.5 py-1 rounded-full">Autoretenedor</span>
                    @endif
                </div>
            </div>

            <div class="card p-6">
                <h3 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                    <i class="fas fa-university text-amber-500 text-sm"></i>
                    Información Bancaria
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div>
                        <div class="text-xs text-slate-500 uppercase tracking-wider mb-1">Banco</div>
                        <div class="text-sm font-semibold">{{ $proveedor->banco ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 uppercase tracking-wider mb-1">Tipo Cuenta</div>
                        <div class="text-sm font-semibold capitalize">{{ $proveedor->tipo_cuenta ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 uppercase tracking-wider mb-1">N° Cuenta</div>
                        <div class="text-sm font-mono">{{ $proveedor->cuenta_bancaria ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 uppercase tracking-wider mb-1">Plazo Pago</div>
                        <div class="text-sm font-semibold">{{ $proveedor->plazo_pago }} días</div>
                    </div>
                </div>
            </div>

            <div class="card p-4">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <div class="text-xs text-slate-500 uppercase tracking-wider mb-1">Creado</div>
                        <div class="text-slate-300">{{ $proveedor->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 uppercase tracking-wider mb-1">Actualizado</div>
                        <div class="text-slate-300">{{ $proveedor->updated_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection