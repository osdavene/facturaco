@extends('layouts.app')
@section('title', 'Nuevo Recibo de Caja')
@section('page-title', 'Recibos · Nuevo')

@section('content')
<div class="max-w-3xl mx-auto">

    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('recibos.index') }}"
           class="w-9 h-9 bg-[#141c2e] border border-[#1e2d47] rounded-xl
                  flex items-center justify-center text-slate-400
                  hover:text-amber-500 hover:border-amber-500/50 transition-colors">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="font-display font-bold text-2xl">Nuevo Recibo de Caja</h1>
            <p class="text-slate-500 text-sm font-mono">{{ $consecutivo['numero'] }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('recibos.store') }}">
        @csrf

        <x-form-errors class="mb-4" />

        {{-- SECCIÓN 1: Cliente --}}
        <div class="card p-6 mb-4">
            <h2 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center
                             text-black text-xs font-black">1</span>
                Cliente
            </h2>

            {{-- Buscador de cliente --}}
            <div class="relative mb-3">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                <input type="text" id="buscar-cliente"
                       placeholder="Buscar cliente por nombre o documento..."
                       autocomplete="off"
                       class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl
                              pl-9 pr-4 py-2.5 text-sm placeholder-slate-600
                              focus:outline-none focus:border-amber-500"
                       style="color:#e2e8f0">
                <div id="resultados-cliente"
                     class="absolute top-full left-0 right-0 mt-1 bg-[#1a2235] border border-[#1e2d47]
                            rounded-xl shadow-xl z-50 hidden max-h-48 overflow-y-auto">
                </div>
            </div>

            <input type="hidden" name="cliente_id" id="cliente_id"
                   value="{{ old('cliente_id', $factura?->cliente_id) }}">

            <div id="cliente-info"
                 class="{{ ($factura || old('cliente_id')) ? '' : 'hidden' }}
                        bg-[#1a2235] border border-[#1e2d47] rounded-xl p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="font-semibold text-sm" id="cli-nombre"
                             style="color:#e2e8f0">
                            {{ $factura?->cliente_nombre ?? '' }}
                        </div>
                        <div class="text-xs text-slate-500 mt-0.5" id="cli-doc">
                            {{ $factura?->cliente_documento ?? '' }}
                        </div>
                    </div>
                    <button type="button" onclick="limpiarCliente()"
                            class="text-slate-500 hover:text-red-400 text-xs">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            @error('cliente_id')
            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- SECCIÓN 2: Factura relacionada --}}
        <div class="card p-6 mb-4">
            <h2 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center
                             text-black text-xs font-black">2</span>
                Factura a Pagar
                <span class="text-xs text-slate-500 font-normal">(opcional)</span>
            </h2>

            <div class="relative mb-3">
                <i class="fas fa-file-invoice absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                <input type="text" id="buscar-factura"
                       placeholder="Buscar factura por número..."
                       autocomplete="off"
                       class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl
                              pl-9 pr-4 py-2.5 text-sm placeholder-slate-600
                              focus:outline-none focus:border-amber-500"
                       style="color:#e2e8f0">
                <div id="resultados-factura"
                     class="absolute top-full left-0 right-0 mt-1 bg-[#1a2235] border border-[#1e2d47]
                            rounded-xl shadow-xl z-50 hidden max-h-48 overflow-y-auto">
                </div>
            </div>

            <input type="hidden" name="factura_id" id="factura_id"
                   value="{{ old('factura_id', $factura?->id) }}">

            <div id="factura-info"
                 class="{{ $factura ? '' : 'hidden' }}
                        bg-[#1a2235] border border-amber-500/30 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-mono text-sm font-semibold text-amber-500" id="fac-numero">
                            {{ $factura?->numero ?? '' }}
                        </div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            Total:
                            <span id="fac-total" class="text-slate-300 font-semibold">
                                ${{ $factura ? number_format($factura->total, 0, ',', '.') : '' }}
                            </span>
                            — Saldo:
                            <span id="fac-saldo" class="text-amber-500 font-semibold">
                                ${{ $factura ? number_format(max(0,$factura->total-$factura->total_pagado), 0, ',', '.') : '' }}
                            </span>
                        </div>
                    </div>
                    <button type="button" onclick="limpiarFactura()"
                            class="text-slate-500 hover:text-red-400 text-xs">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- SECCIÓN 3: Pago --}}
        <div class="card p-6 mb-4">
            <h2 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center
                             text-black text-xs font-black">3</span>
                Datos del Pago
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">
                        Fecha *
                    </label>
                    <input type="date" name="fecha"
                           value="{{ old('fecha', date('Y-m-d')) }}"
                           class="form-input @error('fecha') border-red-500 @enderror"
                           style="color:#e2e8f0">
                    @error('fecha') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">
                        Valor Recibido *
                    </label>
                    <input type="text" inputmode="decimal" name="valor"
                           value="{{ old('valor', $factura ? max(0,$factura->total-$factura->total_pagado) : '') }}"
                           id="valor-pago"
                           placeholder="0"
                           data-numeric
                           class="form-input @error('valor') border-red-500 @enderror"
                           style="color:#e2e8f0">
                    @error('valor') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">
                        Forma de Pago *
                    </label>
                    <select name="forma_pago" id="forma_pago"
                            onchange="toggleBanco(this.value)"
                            class="form-input"
                            style="color:#e2e8f0">
                        @foreach([
                            'efectivo'     => 'Efectivo',
                            'transferencia'=> 'Transferencia Bancaria',
                            'cheque'       => 'Cheque',
                            'tarjeta'      => 'Tarjeta Débito/Crédito',
                            'consignacion' => 'Consignación',
                        ] as $val => $label)
                        <option value="{{ $val }}" {{ old('forma_pago')==$val ? 'selected':'' }}>
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div id="campo-banco">
                    <label class="form-label">
                        Banco / Entidad
                    </label>
                    <input type="text" name="banco"
                           value="{{ old('banco') }}"
                           placeholder="BANCOLOMBIA, DAVIVIENDA..."
                           data-uppercase
                           class="form-input"
                           style="color:#e2e8f0">
                </div>
                <div>
                    <label class="form-label">
                        N° Referencia / Cheque
                    </label>
                    <input type="text" name="num_referencia"
                           value="{{ old('num_referencia') }}"
                           placeholder="NÚMERO DE TRANSACCIÓN"
                           data-uppercase
                           class="form-input"
                           style="color:#e2e8f0">
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">
                        Concepto *
                    </label>
                    <input type="text" name="concepto"
                           value="{{ old('concepto', $factura ? 'PAGO FACTURA '.$factura->numero : '') }}"
                           placeholder="DESCRIPCIÓN DEL PAGO"
                           data-uppercase
                           class="form-input @error('concepto') border-red-500 @enderror"
                           style="color:#e2e8f0">
                    @error('concepto') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">
                        Observaciones
                    </label>
                    <textarea name="observaciones" rows="2"
                              placeholder="NOTAS ADICIONALES..."
                              data-uppercase
                              class="form-input resize-none"
                              style="color:#e2e8f0">{{ old('observaciones') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('recibos.index') }}"
               class="px-6 py-2.5 bg-[#1a2235] border border-[#1e2d47] rounded-xl
                      text-slate-400 hover:text-slate-200 text-sm transition-colors">
                Cancelar
            </a>
            <button type="submit"
                    class="px-8 py-2.5 bg-amber-500 hover:bg-amber-600 text-black
                           font-semibold rounded-xl transition-colors flex items-center gap-2">
                <i class="fas fa-save"></i> Guardar Recibo
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
// ── Mayúsculas y numérico ─────────────────────
document.querySelectorAll('[data-uppercase]').forEach(el => {
    el.addEventListener('input', function() {
        const pos = this.selectionStart;
        this.value = this.value.toUpperCase();
        this.setSelectionRange(pos, pos);
    });
});
document.querySelectorAll('[data-numeric]').forEach(el => {
    el.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9.,]/g, '');
    });
});

// ── Mostrar/ocultar banco ─────────────────────
function toggleBanco(val) {
    const campo = document.getElementById('campo-banco');
    campo.style.display = val === 'efectivo' ? 'none' : 'block';
}
toggleBanco(document.getElementById('forma_pago').value);

// ── Buscar cliente ────────────────────────────
let timerCliente;
document.getElementById('buscar-cliente').addEventListener('input', function() {
    clearTimeout(timerCliente);
    const q = this.value.trim();
    if (q.length < 2) {
        document.getElementById('resultados-cliente').classList.add('hidden');
        return;
    }
    timerCliente = setTimeout(async () => {
        const res  = await fetch(`/api/clientes/buscar?q=${encodeURIComponent(q)}`);
        const data = await res.json();
        const div  = document.getElementById('resultados-cliente');
        if (!data.length) { div.classList.add('hidden'); return; }
        div.innerHTML = data.map(c => `
            <div class="px-4 py-2.5 hover:bg-[#141c2e] cursor-pointer
                        border-b border-[#1e2d47]/50 last:border-0"
                 onclick="seleccionarCliente(${JSON.stringify(c).replace(/"/g,'&quot;')})">
                <div class="text-sm font-medium" style="color:#e2e8f0">
                    ${c.razon_social || ((c.nombres||'') + ' ' + (c.apellidos||''))}
                </div>
                <div class="text-xs text-slate-500">${c.tipo_documento}: ${c.numero_documento}</div>
            </div>`).join('');
        div.classList.remove('hidden');
    }, 300);
});

function seleccionarCliente(c) {
    const nombre = c.razon_social || ((c.nombres||'') + ' ' + (c.apellidos||''));
    document.getElementById('cliente_id').value    = c.id;
    document.getElementById('cli-nombre').textContent = nombre.toUpperCase();
    document.getElementById('cli-doc').textContent    = c.tipo_documento + ': ' + c.numero_documento;
    document.getElementById('cliente-info').classList.remove('hidden');
    document.getElementById('buscar-cliente').value = '';
    document.getElementById('resultados-cliente').classList.add('hidden');
}

function limpiarCliente() {
    document.getElementById('cliente_id').value = '';
    document.getElementById('cliente-info').classList.add('hidden');
}

// ── Buscar factura ────────────────────────────
let timerFactura;
document.getElementById('buscar-factura').addEventListener('input', function() {
    clearTimeout(timerFactura);
    const q = this.value.trim();
    if (q.length < 2) {
        document.getElementById('resultados-factura').classList.add('hidden');
        return;
    }
    timerFactura = setTimeout(async () => {
        const res  = await fetch(`/api/facturas/buscar?q=${encodeURIComponent(q)}`);
        const data = await res.json();
        const div  = document.getElementById('resultados-factura');
        if (!data.length) { div.classList.add('hidden'); return; }
        div.innerHTML = data.map(f => `
            <div class="px-4 py-2.5 hover:bg-[#141c2e] cursor-pointer
                        border-b border-[#1e2d47]/50 last:border-0"
                 onclick="seleccionarFactura(${JSON.stringify(f).replace(/"/g,'&quot;')})">
                <div class="flex justify-between items-center">
                    <div>
                        <div class="text-sm font-mono font-semibold text-amber-500">${f.numero}</div>
                        <div class="text-xs text-slate-500">${f.cliente_nombre}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-slate-400">Saldo: <span class="text-amber-500 font-semibold">$${Number(f.saldo).toLocaleString('es-CO')}</span></div>
                    </div>
                </div>
            </div>`).join('');
        div.classList.remove('hidden');
    }, 300);
});

function seleccionarFactura(f) {
    document.getElementById('factura_id').value       = f.id;
    document.getElementById('fac-numero').textContent = f.numero;
    document.getElementById('fac-total').textContent  = '$' + Number(f.total).toLocaleString('es-CO');
    document.getElementById('fac-saldo').textContent  = '$' + Number(f.saldo).toLocaleString('es-CO');
    document.getElementById('factura-info').classList.remove('hidden');
    document.getElementById('buscar-factura').value = '';
    document.getElementById('resultados-factura').classList.add('hidden');
    // Autocompletar valor con el saldo
    document.getElementById('valor-pago').value = Number(f.saldo).toFixed(0);
}

function limpiarFactura() {
    document.getElementById('factura_id').value = '';
    document.getElementById('factura-info').classList.add('hidden');
}

// Cerrar dropdowns
document.addEventListener('click', e => {
    if (!e.target.closest('#buscar-cliente') && !e.target.closest('#resultados-cliente'))
        document.getElementById('resultados-cliente').classList.add('hidden');
    if (!e.target.closest('#buscar-factura') && !e.target.closest('#resultados-factura'))
        document.getElementById('resultados-factura').classList.add('hidden');
});
</script>
@endpush
@endsection