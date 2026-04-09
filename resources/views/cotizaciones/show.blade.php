@extends('layouts.app')
@section('title', 'Cotización '.$cotizacion->numero)
@section('page-title', 'Cotización · '.$cotizacion->numero)

@section('content')
<div class="max-w-5xl mx-auto">

    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('cotizaciones.index') }}"
               class="w-9 h-9 bg-[#141c2e] border border-[#1e2d47] rounded-xl
                      flex items-center justify-center text-slate-400
                      hover:text-amber-500 hover:border-amber-500/50 transition-colors">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="font-display font-bold text-2xl font-mono text-blue-400">
                    {{ $cotizacion->numero }}
                </h1>
                <p class="text-slate-500 text-sm">{{ $cotizacion->fecha_emision->format('d/m/Y') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('cotizaciones.pdf', $cotizacion) }}" target="_blank"
               class="inline-flex items-center gap-2 bg-red-500/10 border border-red-500/30
                      text-red-400 hover:bg-red-500/20 px-4 py-2.5 rounded-xl text-sm transition-colors">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
            @if(!in_array($cotizacion->estado, ['convertida','rechazada']))
            <form method="POST"
                  action="{{ route('cotizaciones.convertir', $cotizacion) }}"
                  onsubmit="return confirm('¿Convertir esta cotización en factura?')">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600
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
        @if($cotizacion->factura)
        — <a href="{{ route('facturas.show', $cotizacion->factura) }}"
             class="underline font-semibold">
            Ver factura {{ $cotizacion->factura->numero }}
        </a>
        @endif
    </div>
    @endif

    {{-- Estado --}}
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-4 mb-4">
        <div class="flex items-center gap-3 flex-wrap">
            <span class="text-sm text-slate-400">Estado:</span>
            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full
                bg-{{ $cotizacion->estado_color }}-500/10
                text-{{ $cotizacion->estado_color }}-{{ $cotizacion->estado_color=='slate'?'400':'500' }}">
                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                {{ ucfirst($cotizacion->estado) }}
            </span>
            @if(!in_array($cotizacion->estado, ['convertida','rechazada']))
            <form method="POST"
                  action="{{ route('cotizaciones.estado', $cotizacion) }}"
                  class="flex gap-2 ml-auto">
                @csrf @method('PATCH')
                <select name="estado"
                        class="bg-[#1a2235] border border-[#1e2d47] rounded-xl px-3 py-1.5
                               text-sm focus:outline-none focus:border-amber-500"
                        style="color:#e2e8f0">
                    @foreach(['borrador','enviada','aceptada','rechazada'] as $e)
                    <option value="{{ $e }}" {{ $cotizacion->estado==$e?'selected':'' }}>
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

    {{-- Si fue convertida --}}
    @if($cotizacion->estado === 'convertida' && $cotizacion->factura)
    <div class="bg-purple-500/5 border border-purple-500/20 rounded-2xl p-4 mb-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fas fa-check-circle text-purple-400"></i>
                <span class="text-sm text-purple-300">
                    Convertida a factura
                    <strong class="font-mono">{{ $cotizacion->factura->numero }}</strong>
                </span>
            </div>
            <a href="{{ route('facturas.show', $cotizacion->factura) }}"
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
            <div class="font-semibold text-base" style="color:#e2e8f0">{{ $cotizacion->cliente_nombre }}</div>
            @if($cotizacion->cliente_documento)
            <div class="text-sm text-slate-400 mt-1">{{ $cotizacion->cliente_documento }}</div>
            @endif
            @if($cotizacion->cliente_email)
            <div class="text-sm text-slate-500">{{ $cotizacion->cliente_email }}</div>
            @endif
            @if($cotizacion->cliente_telefono)
            <div class="text-sm text-slate-500">{{ $cotizacion->cliente_telefono }}</div>
            @endif
            @if($cotizacion->cliente_direccion)
            <div class="text-sm text-slate-500">{{ $cotizacion->cliente_direccion }}</div>
            @endif
        </div>
        <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5">
            <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Detalles</h3>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <div class="text-xs text-slate-500">Emisión</div>
                    <div style="color:#e2e8f0">{{ $cotizacion->fecha_emision->format('d/m/Y') }}</div>
                </div>
                <div>
                    <div class="text-xs text-slate-500">Válida hasta</div>
                    <div class="{{ $cotizacion->vencida ? 'text-red-400' : '' }}" style="{{ $cotizacion->vencida ? '' : 'color:#e2e8f0' }}">
                        {{ $cotizacion->fecha_vencimiento->format('d/m/Y') }}
                    </div>
                </div>
                <div>
                    <div class="text-xs text-slate-500">Forma de pago</div>
                    <div class="capitalize" style="color:#e2e8f0">{{ $cotizacion->forma_pago }}</div>
                </div>
                <div>
                    <div class="text-xs text-slate-500">Plazo</div>
                    <div style="color:#e2e8f0">{{ $cotizacion->plazo_pago }} días</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Items --}}
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl overflow-hidden mb-4">
        <div class="px-5 py-4 border-b border-[#1e2d47]">
            <div class="font-display font-bold text-base">Productos / Servicios</div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-[#1e2d47]">
                        <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-5 py-3">Descripción</th>
                        <th class="text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Cant.</th>
                        <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden sm:table-cell">Precio Unit.</th>
                        <th class="text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden md:table-cell">IVA</th>
                        <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-5 py-3">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cotizacion->items as $item)
                    <tr class="border-b border-[#1e2d47]/50">
                        <td class="px-5 py-3">
                            <div class="text-sm font-medium" style="color:#e2e8f0">{{ $item->descripcion }}</div>
                            @if($item->codigo)
                            <div class="text-xs text-slate-500 font-mono">{{ $item->codigo }}</div>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center text-sm" style="color:#e2e8f0">
                            {{ number_format($item->cantidad, 0) }}
                        </td>
                        <td class="px-3 py-3 text-right text-sm text-slate-400 hidden sm:table-cell">
                            ${{ number_format($item->precio_unitario, 0, ',', '.') }}
                        </td>
                        <td class="px-3 py-3 text-center text-xs text-slate-400 hidden md:table-cell">
                            {{ $item->iva_pct }}%
                        </td>
                        <td class="px-5 py-3 text-right text-sm font-semibold" style="color:#e2e8f0">
                            ${{ number_format($item->total, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-[#1e2d47]">
            <div class="max-w-xs ml-auto space-y-1.5 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-400">Subtotal</span>
                    <span style="color:#e2e8f0">${{ number_format($cotizacion->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($cotizacion->descuento > 0)
                <div class="flex justify-between">
                    <span class="text-slate-400">Descuento</span>
                    <span class="text-red-400">-${{ number_format($cotizacion->descuento, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-slate-400">IVA</span>
                    <span class="text-blue-400">+${{ number_format($cotizacion->iva, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between border-t border-[#1e2d47] pt-2 mt-2">
                    <span class="font-display font-bold">TOTAL</span>
                    <span class="font-display font-bold text-xl text-amber-500">
                        ${{ number_format($cotizacion->total, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    @if($cotizacion->observaciones || $cotizacion->terminos)
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5">
        @if($cotizacion->observaciones)
        <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Observaciones</h3>
        <p class="text-sm text-slate-300 mb-4">{{ $cotizacion->observaciones }}</p>
        @endif
        @if($cotizacion->terminos)
        <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Términos y Condiciones</h3>
        <p class="text-sm text-slate-400">{{ $cotizacion->terminos }}</p>
        @endif
    </div>
    @endif

</div>
@endsection