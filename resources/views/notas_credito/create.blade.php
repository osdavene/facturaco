@extends('layouts.app')
@section('title', 'Nueva Nota de Crédito')
@section('page-title', 'Facturación · Nueva Nota de Crédito')

@section('content')
<div class="max-w-4xl mx-auto">

    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('facturas.show', $factura) }}"
           class="w-9 h-9 bg-[#141c2e] border border-[#1e2d47] rounded-xl
                  flex items-center justify-center text-slate-400
                  hover:text-amber-500 hover:border-amber-500/50 transition-colors">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="font-display font-bold text-2xl">Nueva Nota de Crédito</h1>
            <p class="text-slate-500 text-sm">Sobre factura
                <span class="font-mono text-amber-400">{{ $factura->numero }}</span>
                — {{ $factura->cliente_nombre }}
            </p>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-red-500/10 border border-red-500/30 text-red-400
                rounded-xl px-5 py-3 mb-5 flex items-center gap-3">
        <i class="fas fa-exclamation-circle flex-shrink-0"></i>
        {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="{{ route('notas_credito.store') }}">
        @csrf
        <input type="hidden" name="factura_id" value="{{ $factura->id }}">

        <div class="space-y-4">

            {{-- Datos generales --}}
            <div class="card p-6">
                <h2 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 bg-violet-500 rounded-lg flex items-center justify-center
                                 text-white text-xs font-black">1</span>
                    Datos Generales
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                    <div>
                        <label class="form-label">
                            Fecha *
                        </label>
                        <input type="date" name="fecha" value="{{ old('fecha', date('Y-m-d')) }}"
                               class="form-input">
                    </div>

                    <div>
                        <label class="form-label">
                            Tipo *
                        </label>
                        <select name="tipo" id="tipo"
                                class="form-input">
                            <option value="parcial">Parcial</option>
                            <option value="total">Total (anula la factura)</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">
                            Motivo *
                        </label>
                        <select name="motivo"
                                class="form-input">
                            <option value="devolucion_mercancia">Devolución de mercancía</option>
                            <option value="descuento_posterior">Descuento posterior</option>
                            <option value="error_facturacion">Error en facturación</option>
                            <option value="anulacion">Anulación</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>

                    <div class="sm:col-span-3">
                        <label class="form-label">
                            Observaciones <span class="text-slate-600 normal-case font-normal">(opcional)</span>
                        </label>
                        <textarea name="observaciones" rows="2"
                                  class="form-input resize-none
                                         focus:outline-none focus:border-amber-500 transition-colors"
                                  placeholder="Describe el motivo de la nota de crédito...">{{ old('observaciones') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Items a devolver --}}
            <div class="card p-6">
                <h2 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 bg-violet-500 rounded-lg flex items-center justify-center
                                 text-white text-xs font-black">2</span>
                    Ítems a Devolver
                </h2>

                <div class="bg-amber-500/5 border border-amber-500/20 rounded-xl px-4 py-3 mb-4
                            text-xs text-slate-500 flex items-start gap-2">
                    <i class="fas fa-info-circle text-amber-500 mt-0.5 flex-shrink-0"></i>
                    Selecciona los productos de la factura original que serán devueltos.
                    Puedes modificar las cantidades si la devolución es parcial.
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm" id="tabla-items">
                        <thead>
                            <tr class="border-b border-[#1e2d47] text-xs text-slate-500 uppercase tracking-wider">
                                <th class="px-3 py-2 text-center w-10">
                                    <input type="checkbox" id="selecTodos" class="accent-amber-500"
                                           onchange="seleccionarTodos(this.checked)">
                                </th>
                                <th class="px-3 py-2 text-left">Descripción</th>
                                <th class="px-3 py-2 text-center">Cant. Facturada</th>
                                <th class="px-3 py-2 text-center">Cant. a Devolver</th>
                                <th class="px-3 py-2 text-right">Precio</th>
                                <th class="px-3 py-2 text-center">IVA %</th>
                                <th class="px-3 py-2 text-right">Total</th>
                                <th class="px-3 py-2 text-center">Dev. Stock</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#1e2d47]">
                            @foreach($factura->items as $i => $item)
                            <tr class="hover:bg-[#1a2235]/50 transition-colors" data-index="{{ $i }}">
                                <td class="px-3 py-3 text-center">
                                    <input type="checkbox" class="item-check accent-amber-500"
                                           onchange="toggleItem({{ $i }}, this.checked)" checked>
                                    <input type="hidden" name="items[{{ $i }}][factura_item_id]" value="{{ $item->id }}">
                                    <input type="hidden" name="items[{{ $i }}][producto_id]" value="{{ $item->producto_id }}">
                                    <input type="hidden" name="items[{{ $i }}][codigo]" value="{{ $item->codigo }}">
                                    <input type="hidden" name="items[{{ $i }}][descripcion]" value="{{ $item->descripcion }}">
                                    <input type="hidden" name="items[{{ $i }}][unidad]" value="{{ $item->unidad }}">
                                    <input type="hidden" name="items[{{ $i }}][precio_unitario]" value="{{ $item->precio_unitario }}" class="precio-{{ $i }}">
                                    <input type="hidden" name="items[{{ $i }}][iva_pct]" value="{{ $item->iva_pct }}" class="ivapct-{{ $i }}">
                                </td>
                                <td class="px-3 py-3">
                                    <div class="font-medium text-slate-200">{{ $item->descripcion }}</div>
                                    <div class="text-xs text-slate-600 font-mono">{{ $item->codigo }}</div>
                                </td>
                                <td class="px-3 py-3 text-center text-slate-400">
                                    {{ number_format($item->cantidad, 2) }} {{ $item->unidad }}
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <input type="text" inputmode="decimal"
                                           name="items[{{ $i }}][cantidad]"
                                           value="{{ number_format($item->cantidad, 2, '.', '') }}"
                                           class="w-20 bg-[#1a2235] border border-[#1e2d47] rounded-lg px-2 py-1
                                                  text-sm text-center text-slate-200
                                                  focus:outline-none focus:border-amber-500 transition-colors cant-{{ $i }}"
                                           onchange="recalcularFila({{ $i }})">
                                </td>
                                <td class="px-3 py-3 text-right text-slate-300">
                                    ${{ number_format($item->precio_unitario, 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-3 text-center text-slate-400">
                                    {{ $item->iva_pct }}%
                                </td>
                                <td class="px-3 py-3 text-right font-semibold text-slate-200 total-{{ $i }}">
                                    ${{ number_format($item->total, 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-3 text-center">
                                    @if($item->producto_id && !optional($item->producto)->es_servicio)
                                    <input type="checkbox" name="items[{{ $i }}][devolver_stock]" value="1"
                                           class="accent-emerald-500 w-4 h-4" checked>
                                    @else
                                    <span class="text-xs text-slate-600">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Total nota --}}
                <div class="mt-4 flex justify-end">
                    <div class="bg-[#1a2235] border border-[#1e2d47] rounded-xl px-5 py-3 text-right">
                        <div class="text-xs text-slate-500 mb-1">Total Nota de Crédito</div>
                        <div class="font-display font-bold text-xl text-violet-400" id="total-nota">
                            $0
                        </div>
                    </div>
                </div>
            </div>

            {{-- Aviso nota total --}}
            <div id="aviso-total" class="hidden bg-red-500/10 border border-red-500/30 rounded-xl px-5 py-4
                        flex items-center gap-3 text-sm text-red-400">
                <i class="fas fa-exclamation-triangle flex-shrink-0"></i>
                <div>
                    <strong>Nota de crédito total:</strong> La factura
                    <span class="font-mono">{{ $factura->numero }}</span>
                    quedará <strong>anulada</strong> al guardar esta nota.
                </div>
            </div>

        </div>

        <div class="flex gap-3 mt-4">
            <a href="{{ route('facturas.show', $factura) }}"
               class="flex-1 text-center bg-[#141c2e] border border-[#1e2d47]
                      text-slate-400 font-semibold text-sm py-2.5 rounded-xl
                      hover:border-slate-500 transition-colors">
                Cancelar
            </a>
            <button type="submit"
                    class="flex-1 bg-violet-600 hover:bg-violet-700 text-white
                           font-bold text-sm py-2.5 rounded-xl transition-colors">
                <i class="fas fa-file-invoice mr-2"></i> Generar Nota de Crédito
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function formatCOP(v) {
    return '$' + Math.round(v).toLocaleString('es-CO');
}

function recalcularFila(i) {
    const cant   = parseFloat(document.querySelector(`.cant-${i}`)?.value || 0);
    const precio = parseFloat(document.querySelector(`.precio-${i}`)?.value || 0);
    const ivaPct = parseFloat(document.querySelector(`.ivapct-${i}`)?.value || 0);
    const total  = cant * precio * (1 + ivaPct / 100);
    const el     = document.querySelector(`.total-${i}`);
    if (el) el.textContent = formatCOP(total);
    recalcularTotal();
}

function recalcularTotal() {
    let total = 0;
    document.querySelectorAll('.item-check').forEach((cb, i) => {
        if (cb.checked) {
            const cant   = parseFloat(document.querySelector(`.cant-${i}`)?.value || 0);
            const precio = parseFloat(document.querySelector(`.precio-${i}`)?.value || 0);
            const ivaPct = parseFloat(document.querySelector(`.ivapct-${i}`)?.value || 0);
            total += cant * precio * (1 + ivaPct / 100);
        }
    });
    document.getElementById('total-nota').textContent = formatCOP(total);
}

function toggleItem(i, checked) {
    const row = document.querySelector(`tr[data-index="${i}"]`);
    if (row) row.style.opacity = checked ? '1' : '0.4';
    recalcularTotal();
}

function seleccionarTodos(checked) {
    document.querySelectorAll('.item-check').forEach((cb, i) => {
        cb.checked = checked;
        toggleItem(i, checked);
    });
}

// Mostrar aviso si tipo = total
document.getElementById('tipo').addEventListener('change', function() {
    document.getElementById('aviso-total').classList.toggle('hidden', this.value !== 'total');
});

// Calcular totales al cargar
document.addEventListener('DOMContentLoaded', recalcularTotal);
</script>
@endpush