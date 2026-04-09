@extends('layouts.app')
@section('title', 'Remisión '.$remision->numero)
@section('page-title', 'Remisión · '.$remision->numero)

@section('content')
<div class="max-w-5xl mx-auto">

    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('remisiones.index') }}"
               class="w-9 h-9 bg-[#141c2e] border border-[#1e2d47] rounded-xl
                      flex items-center justify-center text-slate-400
                      hover:text-amber-500 hover:border-amber-500/50 transition-colors">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="font-display font-bold text-2xl font-mono text-amber-500">
                    {{ $remision->numero }}
                </h1>
                <p class="text-slate-500 text-sm">{{ $remision->fecha_emision->format('d/m/Y') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('remisiones.pdf', $remision) }}" target="_blank"
               class="inline-flex items-center gap-2 bg-red-500/10 border border-red-500/30
                      text-red-400 hover:bg-red-500/20 px-4 py-2.5 rounded-xl text-sm transition-colors">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
            @if(!in_array($remision->estado, ['facturada','anulada']))
            <form method="POST"
                  action="{{ route('remisiones.convertir', $remision) }}"
                  onsubmit="return confirm('¿Convertir esta remisión en factura?')">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-purple-500 hover:bg-purple-600
                               text-white font-semibold px-5 py-2.5 rounded-xl transition-colors text-sm">
                    <i class="fas fa-file-invoice"></i> Convertir a Factura
                </button>
            </form>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400
                rounded-xl px-5 py-3 mb-5 flex items-center gap-3">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        @if($remision->factura)
        — <a href="{{ route('facturas.show', $remision->factura) }}"
             class="underline font-semibold">
            Ver factura {{ $remision->factura->numero }}
        </a>
        @endif
    </div>
    @endif

    {{-- Estado --}}
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-4 mb-4">
        <div class="flex items-center gap-3 flex-wrap">
            <span class="text-sm text-slate-400">Estado:</span>
            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full
                bg-{{ $remision->estado_color }}-500/10
                text-{{ $remision->estado_color }}-{{ $remision->estado_color=='slate'?'400':'500' }}">
                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                {{ ucfirst($remision->estado) }}
            </span>
            @if(!in_array($remision->estado, ['facturada','anulada']))
            <form method="POST"
                  action="{{ route('remisiones.estado', $remision) }}"
                  class="flex gap-2 ml-auto">
                @csrf @method('PATCH')
                <select name="estado"
                        class="bg-[#1a2235] border border-[#1e2d47] rounded-xl px-3 py-1.5
                               text-sm focus:outline-none focus:border-amber-500"
                        style="color:#e2e8f0">
                    @foreach(['borrador','enviada','entregada','anulada'] as $e)
                    <option value="{{ $e }}" {{ $remision->estado==$e?'selected':'' }}>
                        {{ ucfirst($e) }}
                    </option>
                    @endforeach
                </select>
                <button type="submit"
                        class="bg-amber-500 hover:bg-amber-600 text-black font-semibold
                               px-4 py-1.5 rounded-xl text-sm transition-colors">
                    Actualizar
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Si fue facturada --}}
    @if($remision->estado === 'facturada' && $remision->factura)
    <div class="bg-purple-500/5 border border-purple-500/20 rounded-2xl p-4 mb-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fas fa-check-circle text-purple-400"></i>
                <span class="text-sm text-purple-300">
                    Facturada como
                    <strong class="font-mono">{{ $remision->factura->numero }}</strong>
                </span>
            </div>
            <a href="{{ route('facturas.show', $remision->factura) }}"
               class="text-xs bg-purple-500/10 text-purple-400 hover:bg-purple-500/20
                      px-3 py-1.5 rounded-lg transition-colors">
                Ver Factura →
            </a>
        </div>
    </div>
    @endif

    {{-- Info --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5">
            <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Cliente</h3>
            <div class="font-semibold text-base" style="color:#e2e8f0">{{ $remision->cliente_nombre }}</div>
            @if($remision->cliente_documento)
            <div class="text-sm text-slate-400 mt-1">{{ $remision->cliente_documento }}</div>
            @endif
            @if($remision->cliente_telefono)
            <div class="text-sm text-slate-500">{{ $remision->cliente_telefono }}</div>
            @endif
            @if($remision->cliente_direccion)
            <div class="text-sm text-slate-500">{{ $remision->cliente_direccion }}</div>
            @endif
        </div>
        <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5">
            <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Detalles de Envío</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">Emisión</span>
                    <span style="color:#e2e8f0">{{ $remision->fecha_emision->format('d/m/Y') }}</span>
                </div>
                @if($remision->fecha_entrega)
                <div class="flex justify-between">
                    <span class="text-slate-500">Entrega</span>
                    <span style="color:#e2e8f0">{{ $remision->fecha_entrega->format('d/m/Y') }}</span>
                </div>
                @endif
                @if($remision->lugar_entrega)
                <div class="flex justify-between">
                    <span class="text-slate-500">Lugar</span>
                    <span style="color:#e2e8f0">{{ $remision->lugar_entrega }}</span>
                </div>
                @endif
                @if($remision->transportador)
                <div class="flex justify-between">
                    <span class="text-slate-500">Transportador</span>
                    <span style="color:#e2e8f0">{{ $remision->transportador }}</span>
                </div>
                @endif
                @if($remision->guia)
                <div class="flex justify-between">
                    <span class="text-slate-500">Guía</span>
                    <span class="font-mono text-xs" style="color:#e2e8f0">{{ $remision->guia }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Items --}}
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl overflow-hidden mb-4">
        <div class="px-5 py-4 border-b border-[#1e2d47]">
            <div class="font-display font-bold text-base">Productos Despachados</div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-[#1e2d47]">
                        <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-5 py-3">Descripción</th>
                        <th class="text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Unidad</th>
                        <th class="text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Cantidad</th>
                        <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-5 py-3">Precio Ref.</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($remision->items as $item)
                    <tr class="border-b border-[#1e2d47]/50">
                        <td class="px-5 py-3">
                            <div class="text-sm font-medium" style="color:#e2e8f0">{{ $item->descripcion }}</div>
                            @if($item->codigo)
                            <div class="text-xs text-slate-500 font-mono">{{ $item->codigo }}</div>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center text-xs text-slate-400">{{ $item->unidad }}</td>
                        <td class="px-3 py-3 text-center text-sm font-bold" style="color:#e2e8f0">
                            {{ number_format($item->cantidad, 0) }}
                        </td>
                        <td class="px-5 py-3 text-right text-sm text-slate-400">
                            ${{ number_format($item->precio_unitario, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t border-[#1e2d47]">
                        <td colspan="2" class="px-5 py-3 text-xs text-slate-500">
                            * Precios de referencia. Los impuestos se aplican al facturar.
                        </td>
                        <td class="px-3 py-3 text-center text-sm font-bold text-amber-500">
                            {{ $remision->items->sum('cantidad') }} uds
                        </td>
                        <td class="px-5 py-3 text-right text-sm font-bold text-amber-500">
                            ${{ number_format($remision->total, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @if($remision->observaciones)
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5">
        <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Observaciones</h3>
        <p class="text-sm text-slate-300">{{ $remision->observaciones }}</p>
    </div>
    @endif

</div>
@endsection