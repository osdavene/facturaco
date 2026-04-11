@extends('layouts.app')
@section('title', 'Orden '.$orden->numero)
@section('page-title', 'Orden · '.$orden->numero)

@section('content')
<div class="max-w-5xl mx-auto">

    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('ordenes.index') }}"
               class="w-9 h-9 bg-[#141c2e] border border-[#1e2d47] rounded-xl
                      flex items-center justify-center text-slate-400
                      hover:text-amber-500 hover:border-amber-500/50 transition-colors">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="font-display font-bold text-2xl font-mono text-amber-500">
                    {{ $orden->numero }}
                </h1>
                <p class="text-slate-500 text-sm">{{ $orden->fecha_emision->format('d/m/Y') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('ordenes.pdf', $orden) }}" target="_blank"
               class="inline-flex items-center gap-2 bg-red-500/10 border border-red-500/30
                      text-red-400 hover:bg-red-500/20 px-4 py-2.5 rounded-xl text-sm transition-colors">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
            @if($orden->estado === 'borrador')
            <a href="{{ route('ordenes.edit', $orden) }}"
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

    {{-- Estado + cambiar --}}
    <div class="card p-4 mb-4">
        <div class="flex items-center gap-3 flex-wrap">
            <span class="text-sm text-slate-400">Estado:</span>
            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full
                bg-{{ $orden->estado_color }}-500/10
                text-{{ $orden->estado_color }}-{{ $orden->estado_color=='slate'?'400':'500' }}">
                <span class="w-1.5 h-1.5 rounded-full bg-current animate-pulse"></span>
                {{ ucfirst($orden->estado) }}
            </span>
            @if(!in_array($orden->estado, ['recibida','anulada']))
            <form method="POST" action="{{ route('ordenes.estado', $orden) }}"
                  class="flex gap-2 ml-auto">
                @csrf @method('PATCH')
                <select name="estado"
                        class="bg-[#1a2235] border border-[#1e2d47] rounded-xl px-3 py-1.5
                               text-sm focus:outline-none focus:border-amber-500"
                        style="color:#e2e8f0">
                    @foreach(['borrador','enviada','aprobada','anulada'] as $e)
                    <option value="{{ $e }}" {{ $orden->estado==$e?'selected':'' }}>
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

    {{-- Info --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div class="card p-5">
            <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Proveedor</h3>
            <div class="font-semibold text-base" style="color:#e2e8f0">{{ $orden->proveedor_nombre }}</div>
            <div class="text-sm text-slate-400 mt-1">{{ $orden->proveedor_documento }}</div>
        </div>
        <div class="card p-5">
            <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Detalles</h3>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <div class="text-xs text-slate-500">Emisión</div>
                    <div style="color:#e2e8f0">{{ $orden->fecha_emision->format('d/m/Y') }}</div>
                </div>
                <div>
                    <div class="text-xs text-slate-500">Entrega esperada</div>
                    <div style="color:#e2e8f0">{{ $orden->fecha_esperada?->format('d/m/Y') ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs text-slate-500">Forma de pago</div>
                    <div class="capitalize" style="color:#e2e8f0">{{ $orden->forma_pago }}</div>
                </div>
                <div>
                    <div class="text-xs text-slate-500">Plazo</div>
                    <div style="color:#e2e8f0">{{ $orden->plazo_pago }} días</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Items --}}
    <div class="card overflow-hidden mb-4">
        <div class="px-5 py-4 border-b border-[#1e2d47]">
            <div class="font-display font-bold text-base">Productos</div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-[#1e2d47]">
                        <th class="table-th">Descripción</th>
                        <th class="text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Cant.</th>
                        <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden sm:table-cell">Precio Unit.</th>
                        <th class="text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden md:table-cell">IVA</th>
                        <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-5 py-3">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orden->items as $item)
                    <tr class="border-b border-[#1e2d47]/50">
                        <td class="px-5 py-3">
                            <div class="text-sm font-medium" style="color:#e2e8f0">{{ $item->descripcion }}</div>
                            <div class="text-xs text-slate-500 font-mono">{{ $item->codigo }}</div>
                        </td>
                        <td class="px-3 py-3 text-center text-sm" style="color:#e2e8f0">
                            {{ format_cantidad($item->cantidad) }}
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
                    <span style="color:#e2e8f0">${{ number_format($orden->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">IVA</span>
                    <span class="text-blue-400">+${{ number_format($orden->iva, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between border-t border-[#1e2d47] pt-2 mt-2">
                    <span class="font-display font-bold">TOTAL</span>
                    <span class="font-display font-bold text-xl text-amber-500">
                        ${{ number_format($orden->total, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- RECEPCIÓN DE MERCANCÍA --}}
    @if($orden->estado === 'aprobada')
    <div class="bg-emerald-500/5 border border-emerald-500/20 rounded-2xl p-6 mb-4">
        <h3 class="font-display font-bold text-base mb-4 flex items-center gap-2">
            <i class="fas fa-box-open text-emerald-500"></i>
            Recibir Mercancía
        </h3>
        <form method="POST" action="{{ route('ordenes.recibir', $orden) }}">
            @csrf
            <div class="space-y-3 mb-4">
                @foreach($orden->items as $item)
                <div class="flex items-center gap-4 bg-[#1a2235] rounded-xl p-3">
                    <div class="flex-1">
                        <div class="text-sm font-medium" style="color:#e2e8f0">{{ $item->descripcion }}</div>
                        <div class="text-xs text-slate-500">Pedido: {{ format_cantidad($item->cantidad) }}</div>
                    </div>
                    <div class="w-32">
                        <label class="block text-xs text-slate-500 mb-1">Cant. recibida</label>
                        <input type="text" inputmode="decimal"
                               name="cantidad_{{ $item->id }}"
                               value="{{ $item->cantidad }}"
                               class="w-full bg-[#141c2e] border border-[#1e2d47] rounded-lg px-3 py-1.5
                                      text-sm text-center focus:outline-none focus:border-emerald-500"
                               style="color:#e2e8f0">
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mb-4">
                <label class="form-label">
                    Notas de Recepción
                </label>
                <textarea name="notas_recepcion" rows="2"
                          placeholder="OBSERVACIONES DE LA RECEPCIÓN..."
                          style="text-transform:uppercase;color:#e2e8f0"
                          class="form-input focus:border-emerald-500 resize-none"></textarea>
            </div>
            <button type="submit"
                    class="w-full py-3 bg-emerald-500 hover:bg-emerald-600 text-white
                           font-bold rounded-xl transition-colors flex items-center justify-center gap-2">
                <i class="fas fa-check"></i>
                Confirmar Recepción y Actualizar Inventario
            </button>
        </form>
    </div>
    @endif

    @if($orden->estado === 'recibida' && $orden->notas_recepcion)
    <div class="card p-5">
        <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
            Notas de Recepción
        </h3>
        <p class="text-sm text-slate-300">{{ $orden->notas_recepcion }}</p>
        @if($orden->fecha_recepcion)
        <div class="text-xs text-slate-500 mt-2">
            Recibida el {{ $orden->fecha_recepcion->format('d/m/Y') }}
        </div>
        @endif
    </div>
    @endif

    <x-activity-log :model="$orden" />

</div>
@endsection