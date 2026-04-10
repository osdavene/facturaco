@extends('layouts.app')
@section('title', 'Nota de Crédito '.$nota->numero)
@section('page-title', 'Facturación · Nota de Crédito')

@section('content')
<div class="max-w-4xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('notas_credito.index') }}"
               class="w-9 h-9 bg-[#141c2e] border border-[#1e2d47] rounded-xl
                      flex items-center justify-center text-slate-400
                      hover:text-amber-500 hover:border-amber-500/50 transition-colors">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="font-display font-bold text-2xl font-mono text-violet-400">
                    {{ $nota->numero }}
                </h1>
                <p class="text-slate-500 text-sm">{{ $nota->fecha->format('d/m/Y') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('notas_credito.pdf', $nota) }}" target="_blank"
               class="inline-flex items-center gap-2 bg-[#1a2235] border border-[#1e2d47]
                      hover:border-red-500/50 text-slate-400 hover:text-red-400
                      px-4 py-2.5 rounded-xl transition-colors text-sm">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
        </div>
    </div>

    {{-- Info --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5">
            <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Cliente</h3>
            <div class="font-semibold text-base text-slate-200">{{ $nota->cliente_nombre }}</div>
            <div class="text-sm text-slate-400 mt-1">{{ $nota->cliente_documento }}</div>
        </div>
        <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5">
            <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Detalles</h3>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <div class="text-xs text-slate-500">Factura origen</div>
                    <a href="{{ route('facturas.show', $nota->factura_id) }}"
                       class="font-mono text-amber-400 hover:underline">{{ $nota->factura_numero }}</a>
                </div>
                <div>
                    <div class="text-xs text-slate-500">Tipo</div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold
                        {{ $nota->tipo === 'total' ? 'bg-red-500/10 text-red-400' : 'bg-amber-500/10 text-amber-400' }}">
                        {{ ucfirst($nota->tipo) }}
                    </span>
                </div>
                <div>
                    <div class="text-xs text-slate-500">Motivo</div>
                    <div class="text-slate-300">{{ $nota->motivo_texto }}</div>
                </div>
                <div>
                    <div class="text-xs text-slate-500">Creado por</div>
                    <div class="text-slate-300">{{ $nota->usuario->name ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Items --}}
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl overflow-hidden mb-4">
        <div class="px-5 py-4 border-b border-[#1e2d47]">
            <div class="font-display font-bold text-base">Ítems Devueltos</div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-[#1e2d47] text-xs text-slate-500 uppercase tracking-wider">
                        <th class="px-5 py-3 text-left">Descripción</th>
                        <th class="px-3 py-3 text-center">Cantidad</th>
                        <th class="px-3 py-3 text-right">Precio</th>
                        <th class="px-3 py-3 text-center hidden sm:table-cell">IVA</th>
                        <th class="px-3 py-3 text-center hidden sm:table-cell">Stock</th>
                        <th class="px-5 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1e2d47]">
                    @foreach($nota->items as $item)
                    <tr class="hover:bg-[#1a2235]/50">
                        <td class="px-5 py-3">
                            <div class="font-medium text-slate-200">{{ $item->descripcion }}</div>
                            <div class="text-xs text-slate-600 font-mono">{{ $item->codigo }}</div>
                        </td>
                        <td class="px-3 py-3 text-center text-slate-300">
                            {{ number_format($item->cantidad, 2) }} {{ $item->unidad }}
                        </td>
                        <td class="px-3 py-3 text-right text-slate-300">
                            ${{ number_format($item->precio_unitario, 0, ',', '.') }}
                        </td>
                        <td class="px-3 py-3 text-center text-slate-500 text-xs hidden sm:table-cell">
                            {{ $item->iva_pct }}%
                        </td>
                        <td class="px-3 py-3 text-center hidden sm:table-cell">
                            @if($item->devolver_stock)
                            <span class="text-emerald-400 text-xs"><i class="fas fa-check"></i> Devuelto</span>
                            @else
                            <span class="text-slate-600 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right font-semibold text-slate-200">
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
                    <span>${{ number_format($nota->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">IVA</span>
                    <span class="text-blue-400">+${{ number_format($nota->iva, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between border-t border-[#1e2d47] pt-2 mt-2">
                    <span class="font-display font-bold">TOTAL NOTA</span>
                    <span class="font-display font-bold text-xl text-violet-400">
                        ${{ number_format($nota->total, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    @if($nota->observaciones)
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5">
        <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Observaciones</h3>
        <p class="text-sm text-slate-300">{{ $nota->observaciones }}</p>
    </div>
    @endif

</div>
@endsection