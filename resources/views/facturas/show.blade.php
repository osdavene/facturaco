@extends('layouts.app')
@section('title', 'Factura '.$factura->numero)
@section('page-title', 'Factura · '.$factura->numero)

@section('content')
<div class="max-w-4xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('facturas.index') }}"
               class="w-9 h-9 bg-[#141c2e] border border-[#1e2d47] rounded-xl
                      flex items-center justify-center text-slate-400
                      hover:text-amber-500 hover:border-amber-500/50 transition-colors">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="font-display font-bold text-2xl font-mono">{{ $factura->numero }}</h1>
                <p class="text-slate-500 text-sm">{{ $factura->fecha_emision->format('d/m/Y') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-3 flex-wrap justify-end">

            {{-- PDF --}}
            <a href="{{ route('facturas.pdf', $factura) }}" target="_blank"
               class="inline-flex items-center gap-2 bg-[#1a2235] border border-[#1e2d47]
                      hover:border-red-500/50 text-slate-400 hover:text-red-400
                      px-4 py-2.5 rounded-xl transition-colors text-sm">
                <i class="fas fa-file-pdf"></i> PDF
            </a>

            {{-- Enviar email --}}
            <a href="{{ route('facturas.formEnviar', $factura) }}"
               class="inline-flex items-center gap-2 bg-[#1a2235] border border-[#1e2d47]
                      hover:border-amber-500/50 text-slate-400 hover:text-amber-400
                      px-4 py-2.5 rounded-xl transition-colors text-sm">
                <i class="fas fa-envelope"></i> Enviar
            </a>

            {{-- Nota de crédito --}}
            @if(in_array($factura->estado, ['emitida', 'pagada', 'vencida']))
            <a href="{{ route('notas_credito.create', ['factura_id' => $factura->id]) }}"
               class="inline-flex items-center gap-2 bg-[#1a2235] border border-[#1e2d47]
                      hover:border-violet-500/50 text-slate-400 hover:text-violet-400
                      px-4 py-2.5 rounded-xl transition-colors text-sm">
                <i class="fas fa-undo-alt"></i> Nota de Crédito
            </a>
            @endif

            {{-- Pagar con Wompi --}}
            @if($empresa->wompi_configurado && in_array($factura->estado, ['emitida', 'vencida']) && $factura->saldo_pendiente > 0)
            @php
                $wompiRef    = 'FCO-' . $factura->empresa_id . '-' . $factura->numero;
                $wompiAmount = intval($factura->saldo_pendiente * 100);
                $wompiUrl    = 'https://checkout.wompi.co/p/'
                    . '?public-key='      . urlencode($empresa->wompi_public_key)
                    . '&currency='        . urlencode($empresa->wompi_currency ?? 'COP')
                    . '&amount-in-cents=' . $wompiAmount
                    . '&reference='       . urlencode($wompiRef)
                    . '&redirect-url='    . urlencode(route('wompi.retorno', $factura));
            @endphp
            <a href="{{ $wompiUrl }}" target="_blank"
               class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600
                      text-white font-bold px-4 py-2.5 rounded-xl transition-colors text-sm">
                <i class="fas fa-credit-card"></i> Pagar en línea
                <span class="bg-white/20 text-xs px-1.5 py-0.5 rounded-lg font-mono">
                    ${{ number_format($factura->saldo_pendiente, 0, ',', '.') }}
                </span>
            </a>
            @endif

            {{-- Editar (solo borrador) --}}
            @if($factura->estado == 'borrador')
            <a href="{{ route('facturas.edit', $factura) }}"
               class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600
                      text-black font-semibold px-5 py-2.5 rounded-xl transition-colors text-sm">
                <i class="fas fa-pen"></i> Editar
            </a>
            @endif

        </div>
    </div>

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

    @if(session('info'))
    <div class="bg-blue-500/10 border border-blue-500/30 text-blue-400
                rounded-xl px-5 py-3 mb-5 flex items-center gap-3">
        <i class="fas fa-clock"></i> {{ session('info') }}
    </div>
    @endif

    {{-- Cambiar estado --}}
    <div class="card p-4 mb-4">
        <div class="flex items-center gap-3 flex-wrap">
            <span class="text-sm text-slate-400">Estado actual:</span>
            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full
                bg-{{ $factura->estado_color }}-500/10
                text-{{ $factura->estado_color }}-{{ $factura->estado_color=='slate' ? '400':'500' }}">
                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                {{ ucfirst($factura->estado) }}
            </span>
            <form method="POST" action="{{ route('facturas.estado', $factura) }}" class="flex gap-2 ml-auto">
                @csrf @method('PATCH')
                <select name="estado"
                        class="bg-[#1a2235] border border-[#1e2d47] rounded-xl px-3 py-1.5
                               text-sm text-slate-200 focus:outline-none focus:border-amber-500">
                    @foreach(['borrador','emitida','pagada','vencida','anulada'] as $e)
                    <option value="{{ $e }}" {{ $factura->estado==$e ? 'selected':'' }}>
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
        </div>
    </div>

    {{-- Info factura --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div class="card p-5">
            <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Cliente</h3>
            <div class="font-semibold text-base">{{ $factura->cliente_nombre }}</div>
            <div class="text-sm text-slate-400 mt-1">{{ $factura->cliente_documento }}</div>
            @if($factura->cliente_direccion)
            <div class="text-sm text-slate-500 mt-1">{{ $factura->cliente_direccion }}</div>
            @endif
            @if($factura->cliente_email)
            <div class="text-sm text-slate-500">{{ $factura->cliente_email }}</div>
            @endif
        </div>
        <div class="card p-5">
            <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Detalles</h3>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <div class="text-xs text-slate-500">Emisión</div>
                    <div>{{ $factura->fecha_emision->format('d/m/Y') }}</div>
                </div>
                <div>
                    <div class="text-xs text-slate-500">Vencimiento</div>
                    <div class="{{ $factura->fecha_vencimiento < now() && $factura->estado=='emitida' ? 'text-red-400':'' }}">
                        {{ $factura->fecha_vencimiento->format('d/m/Y') }}
                    </div>
                </div>
                <div>
                    <div class="text-xs text-slate-500">Forma Pago</div>
                    <div class="capitalize">{{ $factura->forma_pago }}</div>
                </div>
                <div>
                    <div class="text-xs text-slate-500">Creado por</div>
                    <div>{{ $factura->usuario->name ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Items --}}
    <div class="card overflow-hidden mb-4">
        <div class="px-5 py-4 border-b border-[#1e2d47]">
            <div class="font-display font-bold text-base">Productos / Servicios</div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-[#1e2d47]">
                        <th class="table-th">Descripción</th>
                        <th class="text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Cant.</th>
                        <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Precio</th>
                        <th class="text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden sm:table-cell">IVA</th>
                        <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-5 py-3">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($factura->items as $item)
                    <tr class="border-b border-[#1e2d47]/50">
                        <td class="px-5 py-3">
                            <div class="text-sm font-medium">{{ $item->descripcion }}</div>
                            <div class="text-xs text-slate-500 font-mono">{{ $item->codigo }}</div>
                        </td>
                        <td class="px-3 py-3 text-center text-sm">{{ $item->cantidad }}</td>
                        <td class="px-3 py-3 text-right text-sm">
                            ${{ number_format($item->precio_unitario, 0, ',', '.') }}
                        </td>
                        <td class="px-3 py-3 text-center text-xs text-slate-400 hidden sm:table-cell">
                            {{ $item->iva_pct }}%
                        </td>
                        <td class="px-5 py-3 text-right font-semibold text-sm">
                            ${{ number_format($item->total, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Totales --}}
        <div class="px-5 py-4 border-t border-[#1e2d47]">
            <div class="max-w-xs ml-auto space-y-1.5 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-400">Subtotal</span>
                    <span>${{ number_format($factura->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($factura->descuento > 0)
                <div class="flex justify-between">
                    <span class="text-slate-400">Descuento</span>
                    <span class="text-red-400">-${{ number_format($factura->descuento, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-slate-400">IVA</span>
                    <span class="text-blue-400">+${{ number_format($factura->iva, 0, ',', '.') }}</span>
                </div>
                @if($factura->retefuente > 0)
                <div class="flex justify-between">
                    <span class="text-slate-400">ReteFuente</span>
                    <span class="text-amber-400">-${{ number_format($factura->retefuente, 0, ',', '.') }}</span>
                </div>
                @endif
                @if($factura->reteica > 0)
                <div class="flex justify-between">
                    <span class="text-slate-400">ReteICA</span>
                    <span class="text-amber-400">-${{ number_format($factura->reteica, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="flex justify-between border-t border-[#1e2d47] pt-2 mt-2">
                    <span class="font-display font-bold">TOTAL</span>
                    <span class="font-display font-bold text-xl text-amber-500">
                        ${{ number_format($factura->total, 0, ',', '.') }}
                    </span>
                </div>
                {{-- Saldo pendiente si hay pagos parciales --}}
                @if($factura->total_pagado > 0 && $factura->saldo_pendiente > 0)
                <div class="flex justify-between pt-1">
                    <span class="text-slate-500 text-xs">Pagado</span>
                    <span class="text-emerald-400 text-xs">
                        ${{ number_format($factura->total_pagado, 0, ',', '.') }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 text-xs font-semibold">Saldo pendiente</span>
                    <span class="text-red-400 text-xs font-semibold">
                        ${{ number_format($factura->saldo_pendiente, 0, ',', '.') }}
                    </span>
                </div>
                @endif
            </div>
        </div>
    </div>

    @if($factura->observaciones)
    <div class="card p-5">
        <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Observaciones</h3>
        <p class="text-sm text-slate-300">{{ $factura->observaciones }}</p>
    </div>
    @endif

</div>
@endsection