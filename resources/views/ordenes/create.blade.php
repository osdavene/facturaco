@extends('layouts.app')
@section('title', 'Nueva Orden de Compra')
@section('page-title', 'Órdenes · Nueva')

@section('content')
<div class="max-w-6xl mx-auto">

    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('ordenes.index') }}"
           class="w-9 h-9 bg-[#141c2e] border border-[#1e2d47] rounded-xl
                  flex items-center justify-center text-slate-400
                  hover:text-amber-500 hover:border-amber-500/50 transition-colors">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="font-display font-bold text-2xl">Nueva Orden de Compra</h1>
            <p class="text-slate-500 text-sm font-mono">
                {{ isset($orden) ? $orden->numero : $consecutivo['numero'] }}
            </p>
        </div>
    </div>

    <form method="POST"
          action="{{ isset($orden) ? route('ordenes.update',$orden) : route('ordenes.store') }}"
          id="form-orden">
        @csrf
        @if(isset($orden)) @method('PUT') @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

            {{-- COLUMNA PRINCIPAL --}}
            <div class="lg:col-span-2 space-y-4">

                {{-- Proveedor --}}
                <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5">
                    <h2 class="font-display font-bold text-sm mb-4 flex items-center gap-2">
                        <i class="fas fa-truck text-amber-500"></i> Proveedor
                    </h2>
                    <div class="relative mb-3">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                        <input type="text" id="buscar-proveedor"
                               placeholder="Buscar proveedor por nombre o NIT..."
                               autocomplete="off"
                               class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl
                                      pl-9 pr-4 py-2.5 text-sm placeholder-slate-600
                                      focus:outline-none focus:border-amber-500"
                               style="color:#e2e8f0">
                        <div id="resultados-proveedor"
                             class="absolute top-full left-0 right-0 mt-1 bg-[#1a2235]
                                    border border-[#1e2d47] rounded-xl shadow-xl z-50
                                    hidden max-h-48 overflow-y-auto"></div>
                    </div>
                    <input type="hidden" name="proveedor_id" id="proveedor_id">
                    <div id="proveedor-info" class="hidden bg-[#1a2235] border border-[#1e2d47] rounded-xl p-4">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="font-semibold text-sm" id="prov-nombre" style="color:#e2e8f0"></div>
                                <div class="text-xs text-slate-500 mt-0.5" id="prov-doc"></div>
                            </div>
                            <button type="button" onclick="limpiarProveedor()"
                                    class="text-slate-500 hover:text-red-400 text-xs">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    @error('proveedor_id')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Items --}}
                <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-display font-bold text-sm flex items-center gap-2">
                            <i class="fas fa-boxes text-amber-500"></i> Productos a Comprar
                        </h2>
                        <button type="button" onclick="agregarItem()"
                                class="text-xs bg-amber-500/10 text-amber-500 border border-amber-500/30
                                       px-3 py-1.5 rounded-lg hover:bg-amber-500/20 transition-colors">
                            <i class="fas fa-plus mr-1"></i> Agregar línea
                        </button>
                    </div>

                    {{-- Buscador producto --}}
                    <div class="relative mb-4">
                        <i class="fas fa-box absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                        <input type="text" id="buscar-producto"
                               placeholder="Buscar producto del inventario..."
                               autocomplete="off"
                               class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl
                                      pl-9 pr-4 py-2.5 text-sm placeholder-slate-600
                                      focus:outline-none focus:border-amber-500"
                               style="color:#e2e8f0">
                        <div id="resultados-producto"
                             class="absolute top-full left-0 right-0 mt-1 bg-[#1a2235]
                                    border border-[#1e2d47] rounded-xl shadow-xl z-50
                                    hidden max-h-48 overflow-y-auto"></div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm" id="tabla-items">
                            <thead>
                                <tr class="border-b border-[#1e2d47]">
                                    <th class="text-left text-[10px] font-semibold text-slate-500 uppercase pb-2 pr-2">Descripción</th>
                                    <th class="text-center text-[10px] font-semibold text-slate-500 uppercase pb-2 px-2 w-20">Cant.</th>
                                    <th class="text-right text-[10px] font-semibold text-slate-500 uppercase pb-2 px-2 w-28">Precio</th>
                                    <th class="text-center text-[10px] font-semibold text-slate-500 uppercase pb-2 px-2 w-16">%IVA</th>
                                    <th class="text-right text-[10px] font-semibold text-slate-500 uppercase pb-2 pl-2 w-28">Total</th>
                                    <th class="w-8"></th>
                                </tr>
                            </thead>
                            <tbody id="items-body"></tbody>
                        </table>
                    </div>
                    <div id="sin-items" class="py-8 text-center text-slate-500 text-sm">
                        <i class="fas fa-plus-circle text-2xl mb-2 block text-slate-700"></i>
                        Busca un producto o agrega una línea manual
                    </div>
                </div>

                {{-- Observaciones --}}
                <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5">
                    <h2 class="font-display font-bold text-sm mb-3 flex items-center gap-2">
                        <i class="fas fa-comment text-amber-500"></i> Observaciones
                    </h2>
                    <textarea name="observaciones" rows="2"
                              placeholder="NOTAS PARA EL PROVEEDOR..."
                              data-uppercase
                              class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                                     text-sm placeholder-slate-600
                                     focus:outline-none focus:border-amber-500 resize-none"
                              style="color:#e2e8f0">{{ old('observaciones', isset($orden)?$orden->observaciones:'') }}</textarea>
                </div>
            </div>

            {{-- COLUMNA DERECHA --}}
            <div class="space-y-4">

                {{-- Datos OC --}}
                <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5">
                    <h2 class="font-display font-bold text-sm mb-4 flex items-center gap-2">
                        <i class="fas fa-file-alt text-amber-500"></i> Datos
                    </h2>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Fecha Emisión *</label>
                            <input type="date" name="fecha_emision"
                                   value="{{ old('fecha_emision', date('Y-m-d')) }}"
                                   class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                                          text-sm focus:outline-none focus:border-amber-500"
                                   style="color:#e2e8f0">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Fecha Esperada</label>
                            <input type="date" name="fecha_esperada"
                                   value="{{ old('fecha_esperada') }}"
                                   class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                                          text-sm focus:outline-none focus:border-amber-500"
                                   style="color:#e2e8f0">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Forma de Pago</label>
                            <select name="forma_pago"
                                    class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                                           text-sm focus:outline-none focus:border-amber-500"
                                    style="color:#e2e8f0">
                                <option value="credito">Crédito</option>
                                <option value="contado">Contado</option>
                                <option value="transferencia">Transferencia</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Plazo (días)</label>
                            <input type="text" inputmode="decimal" name="plazo_pago"
                                   value="{{ old('plazo_pago', 30) }}"
                                   data-numeric
                                   class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                                          text-sm focus:outline-none focus:border-amber-500"
                                   style="color:#e2e8f0">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Estado</label>
                            <select name="estado"
                                    class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                                           text-sm focus:outline-none focus:border-amber-500"
                                    style="color:#e2e8f0">
                                <option value="borrador">Borrador</option>
                                <option value="enviada">Enviada al proveedor</option>
                                <option value="aprobada">Aprobada</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Totales --}}
                <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5">
                    <h2 class="font-display font-bold text-sm mb-4 flex items-center gap-2">
                        <i class="fas fa-calculator text-amber-500"></i> Totales
                    </h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Subtotal</span>
                            <span id="display-subtotal" class="font-mono">$0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">IVA</span>
                            <span id="display-iva" class="font-mono text-blue-400">+$0</span>
                        </div>
                        <div class="border-t border-[#1e2d47] pt-3 mt-3 flex justify-between">
                            <span class="font-display font-bold">TOTAL</span>
                            <span id="display-total"
                                  class="font-display font-bold text-xl text-amber-500">$0</span>
                        </div>
                    </div>
                </div>

                <button type="submit"
                        class="w-full py-3 bg-amber-500 hover:bg-amber-600 text-black
                               font-bold rounded-xl transition-colors flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i> Guardar Orden
                </button>
                <a href="{{ route('ordenes.index') }}"
                   class="w-full py-3 bg-[#1a2235] border border-[#1e2d47] text-slate-400
                          hover:text-slate-200 rounded-xl transition-colors flex items-center
                          justify-center gap-2 text-sm">
                    Cancelar
                </a>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
let items = [], itemCounter = 0;
const fmt = n => '$' + Number(n||0).toLocaleString('es-CO', {minimumFractionDigits:0,maximumFractionDigits:0});

// Mayúsculas y numérico
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

// Buscar proveedor
let timerProv;
document.getElementById('buscar-proveedor').addEventListener('input', function() {
    clearTimeout(timerProv);
    const q = this.value.trim();
    if (q.length < 2) { document.getElementById('resultados-proveedor').classList.add('hidden'); return; }
    timerProv = setTimeout(async () => {
        const res  = await fetch(`/api/proveedores/buscar?q=${encodeURIComponent(q)}`);
        const data = await res.json();
        const div  = document.getElementById('resultados-proveedor');
        if (!data.length) { div.classList.add('hidden'); return; }
        div.innerHTML = data.map(p => `
            <div class="px-4 py-2.5 hover:bg-[#141c2e] cursor-pointer border-b border-[#1e2d47]/50 last:border-0"
                 onclick="seleccionarProveedor(${JSON.stringify(p).replace(/"/g,'&quot;')})">
                <div class="text-sm font-medium" style="color:#e2e8f0">${p.razon_social}</div>
                <div class="text-xs text-slate-500">${p.tipo_documento}: ${p.numero_documento}</div>
            </div>`).join('');
        div.classList.remove('hidden');
    }, 300);
});

function seleccionarProveedor(p) {
    document.getElementById('proveedor_id').value       = p.id;
    document.getElementById('prov-nombre').textContent  = p.razon_social.toUpperCase();
    document.getElementById('prov-doc').textContent     = p.tipo_documento + ': ' + p.numero_documento;
    document.getElementById('proveedor-info').classList.remove('hidden');
    document.getElementById('buscar-proveedor').value   = '';
    document.getElementById('resultados-proveedor').classList.add('hidden');
}

function limpiarProveedor() {
    document.getElementById('proveedor_id').value = '';
    document.getElementById('proveedor-info').classList.add('hidden');
}

// Buscar producto
let timerProd;
document.getElementById('buscar-producto').addEventListener('input', function() {
    clearTimeout(timerProd);
    const q = this.value.trim();
    if (q.length < 2) { document.getElementById('resultados-producto').classList.add('hidden'); return; }
    timerProd = setTimeout(async () => {
        const res  = await fetch(`/api/productos/buscar?q=${encodeURIComponent(q)}`);
        const data = await res.json();
        const div  = document.getElementById('resultados-producto');
        if (!data.length) { div.classList.add('hidden'); return; }
        div.innerHTML = data.map(p => `
            <div class="px-4 py-2.5 hover:bg-[#141c2e] cursor-pointer border-b border-[#1e2d47]/50 last:border-0"
                 onclick="agregarProducto(${JSON.stringify(p).replace(/"/g,'&quot;')})">
                <div class="flex justify-between items-center">
                    <div>
                        <div class="text-sm font-medium" style="color:#e2e8f0">${p.nombre}</div>
                        <div class="text-xs text-slate-500">${p.codigo}</div>
                    </div>
                    <div class="text-xs text-slate-400 ml-4">P.Compra: ${fmt(p.precio_venta)}</div>
                </div>
            </div>`).join('');
        div.classList.remove('hidden');
    }, 300);
});

function agregarProducto(p) {
    document.getElementById('buscar-producto').value = '';
    document.getElementById('resultados-producto').classList.add('hidden');
    agregarItem({ producto_id: p.id, codigo: p.codigo, descripcion: p.nombre,
                  cantidad: 1, precio_unitario: p.precio_venta, iva_pct: p.iva_pct });
}

function agregarItem(data = {}) {
    const id = ++itemCounter;
    items.push({ id,
        producto_id:     data.producto_id     || '',
        codigo:          data.codigo          || '',
        descripcion:     data.descripcion     || '',
        cantidad:        data.cantidad        || 1,
        precio_unitario: data.precio_unitario || 0,
        iva_pct:         data.iva_pct         ?? 19,
    });
    renderItems();
    calcularTotales();
}

function eliminarItem(id) {
    items = items.filter(i => i.id !== id);
    renderItems();
    calcularTotales();
}

function updateItem(id, campo, valor) {
    const item = items.find(i => i.id === id);
    if (!item) return;
    item[campo] = ['cantidad','precio_unitario','iva_pct'].includes(campo) ? parseFloat(valor)||0 : valor;
    actualizarFilaTotales(id);
    calcularTotales();
}

function actualizarFilaTotales(id) {
    const item = items.find(i => i.id === id);
    if (!item) return;
    const base = item.cantidad * item.precio_unitario;
    const iva  = base * (item.iva_pct / 100);
    const span = document.getElementById('total-item-' + id);
    if (span) span.textContent = fmt(base + iva);
}

function renderItems() {
    const tbody    = document.getElementById('items-body');
    const sinItems = document.getElementById('sin-items');
    if (!items.length) { tbody.innerHTML = ''; sinItems.classList.remove('hidden'); return; }
    sinItems.classList.add('hidden');

    tbody.innerHTML = items.map((item, idx) => {
        const base = item.cantidad * item.precio_unitario;
        const iva  = base * (item.iva_pct / 100);
        const tot  = base + iva;
        return `
        <tr class="border-b border-[#1e2d47]/30" data-id="${item.id}">
            <td class="py-2 pr-2">
                <input type="hidden" name="items[${idx}][producto_id]" value="${item.producto_id}">
                <input type="hidden" name="items[${idx}][codigo]"      value="${item.codigo}">
                <input type="text" name="items[${idx}][descripcion]"
                       value="${item.descripcion.toUpperCase()}"
                       onchange="updateItem(${item.id},'descripcion',this.value)"
                       placeholder="DESCRIPCIÓN..."
                       style="text-transform:uppercase;color:#e2e8f0"
                       class="w-full bg-transparent border-b border-[#1e2d47] text-sm
                              py-1 focus:outline-none focus:border-amber-500">
            </td>
            <td class="py-2 px-2 w-20">
                <input type="text" inputmode="decimal" name="items[${idx}][cantidad]"
                       value="${item.cantidad}"
                       onchange="updateItem(${item.id},'cantidad',this.value)"
                       style="color:#e2e8f0"
                       class="w-full bg-transparent border-b border-[#1e2d47] text-sm
                              text-center py-1 focus:outline-none focus:border-amber-500">
            </td>
            <td class="py-2 px-2 w-28">
                <input type="text" inputmode="decimal" name="items[${idx}][precio_unitario]"
                       value="${item.precio_unitario}"
                       onchange="updateItem(${item.id},'precio_unitario',this.value)"
                       style="color:#e2e8f0"
                       class="w-full bg-transparent border-b border-[#1e2d47] text-sm
                              text-right py-1 focus:outline-none focus:border-amber-500">
            </td>
            <td class="py-2 px-2 w-16">
                <select name="items[${idx}][iva_pct]"
                        onchange="updateItem(${item.id},'iva_pct',this.value)"
                        style="color:#e2e8f0"
                        class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-lg text-xs
                               px-2 py-1 focus:outline-none focus:border-amber-500">
                    <option value="0"  ${item.iva_pct==0  ?'selected':''}>0%</option>
                    <option value="5"  ${item.iva_pct==5  ?'selected':''}>5%</option>
                    <option value="19" ${item.iva_pct==19 ?'selected':''}>19%</option>
                </select>
            </td>
            <td class="py-2 pl-2 text-right w-28">
                <span id="total-item-${item.id}" class="font-mono text-sm font-semibold" style="color:#e2e8f0">
                    ${fmt(tot)}
                </span>
            </td>
            <td class="py-2 pl-1">
                <button type="button" onclick="eliminarItem(${item.id})"
                        class="text-slate-600 hover:text-red-400 transition-colors">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </td>
        </tr>`;
    }).join('');
}

function calcularTotales() {
    let subtotal = 0, iva = 0;
    items.forEach(item => {
        const base = item.cantidad * item.precio_unitario;
        subtotal += base;
        iva      += base * (item.iva_pct / 100);
    });
    document.getElementById('display-subtotal').textContent = fmt(subtotal);
    document.getElementById('display-iva').textContent      = '+' + fmt(iva);
    document.getElementById('display-total').textContent    = fmt(subtotal + iva);
}

document.addEventListener('click', e => {
    if (!e.target.closest('#buscar-proveedor') && !e.target.closest('#resultados-proveedor'))
        document.getElementById('resultados-proveedor').classList.add('hidden');
    if (!e.target.closest('#buscar-producto') && !e.target.closest('#resultados-producto'))
        document.getElementById('resultados-producto').classList.add('hidden');
});
</script>
@endpush
@endsection