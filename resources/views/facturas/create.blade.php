@extends('layouts.app')
@section('title', 'Nueva Factura')
@section('page-title', 'Facturación · Nueva Factura')

@section('content')
<div class="max-w-6xl mx-auto pb-36 lg:pb-0">

    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('facturas.index') }}"
           class="w-9 h-9 bg-[#141c2e] border border-[#1e2d47] rounded-xl
                  flex items-center justify-center text-slate-400
                  hover:text-amber-500 hover:border-amber-500/50 transition-colors">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="font-display font-bold text-2xl">Nueva Factura</h1>
            <p class="text-slate-500 text-sm font-mono">
                {{ isset($factura) ? $factura->numero : $consecutivo['numero'] }}
            </p>
        </div>
    </div>

    <form method="POST"
          action="{{ isset($factura) ? route('facturas.update',$factura) : route('facturas.store') }}"
          id="form-factura">
        @csrf
        @if(isset($factura)) @method('PUT') @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

            {{-- COLUMNA IZQUIERDA --}}
            <div class="lg:col-span-2 space-y-4">

                {{-- Cliente --}}
                <div class="card p-5">
                    <h2 class="font-display font-bold text-sm mb-4 flex items-center gap-2">
                        <i class="fas fa-user text-amber-500"></i> Cliente
                    </h2>
                    <div class="relative mb-3">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                        <input type="text" id="buscar-cliente"
                               placeholder="Buscar cliente por nombre o NIT..."
                               autocomplete="off"
                               class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl
                                      pl-9 pr-4 py-2.5 text-sm text-slate-200 placeholder-slate-600
                                      focus:outline-none focus:border-amber-500">
                        <div id="resultados-cliente"
                             class="absolute top-full left-0 right-0 mt-1 bg-[#1a2235] border border-[#1e2d47]
                                    rounded-xl shadow-xl z-50 hidden max-h-48 overflow-y-auto">
                        </div>
                    </div>
                    <input type="hidden" name="cliente_id" id="cliente_id"
                           value="{{ old('cliente_id', isset($factura) ? $factura->cliente_id : '') }}">
                    <div id="cliente-info"
                         class="hidden bg-[#1a2235] border border-[#1e2d47] rounded-xl p-4">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="font-semibold text-sm" id="cli-nombre"></div>
                                <div class="text-xs text-slate-500 mt-0.5" id="cli-doc"></div>
                                <div class="text-xs text-slate-500" id="cli-dir"></div>
                            </div>
                            <button type="button" onclick="limpiarCliente()"
                                    class="text-slate-500 hover:text-red-400 text-xs">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="flex gap-3 mt-2 flex-wrap">
                            <span class="text-xs bg-blue-500/10 text-blue-400 px-2 py-0.5 rounded-full"
                                  id="cli-rete"></span>
                            <span class="text-xs bg-amber-500/10 text-amber-400 px-2 py-0.5 rounded-full"
                                  id="cli-reteica"></span>
                            {{-- Badge lista de precios --}}
                            <span class="text-xs bg-emerald-500/10 text-emerald-400 px-2 py-0.5 rounded-full"
                                  id="cli-lista"></span>
                        </div>
                    </div>
                </div>

                {{-- Items --}}
                <div class="card p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-display font-bold text-sm flex items-center gap-2">
                            <i class="fas fa-list text-amber-500"></i> Productos / Servicios
                        </h2>
                        <button type="button" onclick="agregarItem()"
                                class="text-xs bg-amber-500/10 text-amber-500 border border-amber-500/30
                                       px-3 py-1.5 rounded-lg hover:bg-amber-500/20 transition-colors">
                            <i class="fas fa-plus mr-1"></i> Agregar línea
                        </button>
                    </div>

                    {{-- Buscador de productos --}}
                    <div class="relative mb-4">
                        <i class="fas fa-box absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                        <input type="text" id="buscar-producto"
                               placeholder="Buscar producto por nombre o código..."
                               autocomplete="off"
                               class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl
                                      pl-9 pr-4 py-2.5 text-sm text-slate-200 placeholder-slate-600
                                      focus:outline-none focus:border-amber-500">
                        <div id="resultados-producto"
                             class="absolute top-full left-0 right-0 mt-1 bg-[#1a2235] border border-[#1e2d47]
                                    rounded-xl shadow-xl z-50 hidden max-h-48 overflow-y-auto">
                        </div>
                    </div>

                    {{-- Tabla de items --}}
                    <div>
                        <table class="w-full text-sm" id="tabla-items">
                            <thead>
                                <tr class="border-b border-[#1e2d47]">
                                    <th class="text-left text-[10px] font-semibold text-slate-500 uppercase pb-2 pr-2">Descripción</th>
                                    <th class="text-center text-[10px] font-semibold text-slate-500 uppercase pb-2 px-2 w-20">Cant.</th>
                                    <th class="text-right text-[10px] font-semibold text-slate-500 uppercase pb-2 px-2 w-28">Precio</th>
                                    <th class="text-center text-[10px] font-semibold text-slate-500 uppercase pb-2 px-2 w-16 hidden sm:table-cell">%Desc</th>
                                    <th class="text-center text-[10px] font-semibold text-slate-500 uppercase pb-2 px-2 w-16">%IVA</th>
                                    <th class="text-right text-[10px] font-semibold text-slate-500 uppercase pb-2 pl-2 w-28">Total</th>
                                    <th class="w-8"></th>
                                </tr>
                            </thead>
                            <tbody id="items-body">
                            </tbody>
                        </table>
                    </div>

                    <div id="sin-items" class="py-8 text-center text-slate-500 text-sm">
                        <i class="fas fa-plus-circle text-2xl mb-2 block text-slate-700"></i>
                        Busca un producto o agrega una línea manual
                    </div>
                </div>

                {{-- Observaciones --}}
                <div class="card p-5">
                    <h2 class="font-display font-bold text-sm mb-3 flex items-center gap-2">
                        <i class="fas fa-comment text-amber-500"></i> Observaciones
                    </h2>
                    <textarea name="observaciones" rows="2"
                              placeholder="Condiciones comerciales, notas adicionales..."
                              class="form-input resize-none">{{ old('observaciones', isset($factura) ? $factura->observaciones : '') }}</textarea>
                </div>
            </div>

            {{-- COLUMNA DERECHA --}}
            <div class="space-y-4">

                {{-- Datos factura --}}
                <div class="card p-5">
                    <h2 class="font-display font-bold text-sm mb-4 flex items-center gap-2">
                        <i class="fas fa-file-invoice text-amber-500"></i> Datos
                    </h2>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">
                                Fecha Emisión *
                            </label>
                            <input type="date" name="fecha_emision"
                                   value="{{ old('fecha_emision', date('Y-m-d')) }}"
                                   class="form-input">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">
                                Fecha Vencimiento *
                            </label>
                            <input type="date" name="fecha_vencimiento"
                                   value="{{ old('fecha_vencimiento', date('Y-m-d')) }}"
                                   id="fecha-vencimiento"
                                   class="form-input">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">
                                Forma de Pago
                            </label>
                            <select name="forma_pago"
                                    class="form-input">
                                <option value="contado">Contado</option>
                                <option value="credito">Crédito</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">
                                Estado
                            </label>
                            <select name="estado"
                                    class="form-input">
                                <option value="borrador">Borrador</option>
                                <option value="emitida">Emitida</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Totales --}}
                <div class="card p-5">
                    <h2 class="font-display font-bold text-sm mb-4 flex items-center gap-2">
                        <i class="fas fa-calculator text-amber-500"></i> Totales
                    </h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Subtotal</span>
                            <span id="display-subtotal" class="font-mono">$0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Descuento</span>
                            <span id="display-descuento" class="font-mono text-red-400">-$0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Base IVA</span>
                            <span id="display-base-iva" class="font-mono">$0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">IVA</span>
                            <span id="display-iva" class="font-mono text-blue-400">+$0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">ReteFuente</span>
                            <span id="display-retefuente" class="font-mono text-amber-400">-$0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">ReteICA</span>
                            <span id="display-reteica" class="font-mono text-amber-400">-$0</span>
                        </div>
                        <div class="border-t border-[#1e2d47] pt-3 mt-3 flex justify-between">
                            <span class="font-display font-bold">TOTAL</span>
                            <span id="display-total"
                                  class="font-display font-bold text-xl text-amber-500">$0</span>
                        </div>
                    </div>
                </div>

                {{-- Botones --}}
                <button type="submit"
                        class="w-full py-3 bg-amber-500 hover:bg-amber-600 text-black
                               font-bold rounded-xl transition-colors flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i> Guardar Factura
                </button>
                <a href="{{ route('facturas.index') }}"
                   class="w-full py-3 bg-[#1a2235] border border-[#1e2d47] text-slate-400
                          hover:text-slate-200 rounded-xl transition-colors flex items-center
                          justify-center gap-2 text-sm">
                    Cancelar
                </a>
            </div>
        </div>
    </form>

    {{-- Barra flotante móvil: total + guardar (se posiciona sobre el bottom-nav) --}}
    <div class="lg:hidden fixed bottom-16 left-0 right-0 z-[95]
                bg-[#0d1526]/95 backdrop-blur border-t border-[#1e2d47]
                px-4 py-3 flex items-center gap-3">
        <div class="flex-1">
            <div class="text-xs text-slate-500 uppercase tracking-wider">Total</div>
            <div id="mobile-total" class="font-display font-bold text-lg text-amber-500">$0</div>
        </div>
        <button form="form-factura" type="submit"
                class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-black
                       font-bold rounded-xl transition-colors flex items-center gap-2 text-sm">
            <i class="fas fa-save"></i> Guardar
        </button>
    </div>
</div>

@push('scripts')
<script>
// ── Estado global ─────────────────────────────
let items        = [];
let clienteData  = null;
let itemCounter  = 0;
let listaPrecios = 'general'; // ← lista activa según el cliente

// ── Formato moneda ────────────────────────────
const fmt = n => '$' + Number(n||0).toLocaleString('es-CO', {minimumFractionDigits:0, maximumFractionDigits:0});

// ── Texto legible de lista ────────────────────
function textoLista(lista) {
    return { general: 'Lista General', mayorista: 'Lista Mayorista', especial: 'Lista Especial' }[lista] || 'Lista General';
}

// ── Mayúsculas automáticas ────────────────────
document.addEventListener('input', function(e) {
    if (e.target.matches('input[type="text"], textarea')) {
        if (['fecha_emision','fecha_vencimiento'].includes(e.target.name)) return;
        if (e.target.id && e.target.id.startsWith('buscar-')) return;
        const pos = e.target.selectionStart;
        e.target.value = e.target.value.toUpperCase();
        e.target.setSelectionRange(pos, pos);
    }
});

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
            <div class="px-4 py-2.5 hover:bg-[#141c2e] cursor-pointer border-b border-[#1e2d47]/50 last:border-0"
                 onclick="seleccionarCliente(${JSON.stringify(c).replace(/"/g,'&quot;')})">
                <div class="text-sm font-medium text-slate-200">
                    ${c.razon_social || ((c.nombres||'')+(c.apellidos?' '+c.apellidos:''))}
                </div>
                <div class="text-xs text-slate-500">${c.tipo_documento}: ${c.numero_documento}</div>
            </div>`).join('');
        div.classList.remove('hidden');
    }, 300);
});

function seleccionarCliente(c) {
    clienteData  = c;
    listaPrecios = c.lista_precio || 'general'; // ← guardar lista del cliente

    document.getElementById('cliente_id').value = c.id;
    document.getElementById('buscar-cliente').value = '';
    document.getElementById('resultados-cliente').classList.add('hidden');

    const nombre = c.razon_social || ((c.nombres||'') + (c.apellidos?' '+c.apellidos:''));
    document.getElementById('cli-nombre').textContent  = nombre.toUpperCase();
    document.getElementById('cli-doc').textContent     = c.tipo_documento + ': ' + c.numero_documento;
    document.getElementById('cli-dir').textContent     = (c.direccion || '').toUpperCase();
    document.getElementById('cli-rete').textContent    = 'RETEFUENTE: ' + c.retefuente_pct + '%';
    document.getElementById('cli-reteica').textContent = 'RETEICA: ' + c.reteica_pct + '%';
    document.getElementById('cli-lista').textContent   = textoLista(listaPrecios); // ← mostrar lista
    document.getElementById('cliente-info').classList.remove('hidden');

    if (c.plazo_pago > 0) {
        const d = new Date();
        d.setDate(d.getDate() + c.plazo_pago);
        document.getElementById('fecha-vencimiento').value = d.toISOString().split('T')[0];
    }
    calcularTotales();
}

function limpiarCliente() {
    clienteData  = null;
    listaPrecios = 'general'; // ← resetear lista
    document.getElementById('cliente_id').value = '';
    document.getElementById('cliente-info').classList.add('hidden');
    calcularTotales();
}

// ── Buscar producto ───────────────────────────
let timerProducto;
document.getElementById('buscar-producto').addEventListener('input', function() {
    clearTimeout(timerProducto);
    const q = this.value.trim();
    if (q.length < 2) {
        document.getElementById('resultados-producto').classList.add('hidden');
        return;
    }
    timerProducto = setTimeout(async () => {
        // ← Enviar lista_precio activa en la petición
        const res  = await fetch(`/api/productos/buscar?q=${encodeURIComponent(q)}&lista_precio=${listaPrecios}`);
        const data = await res.json();
        const div  = document.getElementById('resultados-producto');
        if (!data.length) { div.classList.add('hidden'); return; }
        div.innerHTML = data.map(p => `
            <div class="px-4 py-2.5 hover:bg-[#141c2e] cursor-pointer border-b border-[#1e2d47]/50 last:border-0"
                 onclick="agregarProducto(${JSON.stringify(p).replace(/"/g,'&quot;')})">
                <div class="flex justify-between items-center">
                    <div>
                        <div class="text-sm font-medium text-slate-200">${p.nombre}</div>
                        <div class="text-xs text-slate-500">${p.codigo}</div>
                    </div>
                    <div class="flex flex-col items-end">
                        <div class="text-sm font-semibold text-amber-500 ml-4">
                            ${fmt(p.precio_aplicado || p.precio_venta)}
                        </div>
                        ${listaPrecios !== 'general'
                            ? `<div class="text-[10px] text-emerald-400">${textoLista(listaPrecios)}</div>`
                            : ''
                        }
                    </div>
                </div>
            </div>`).join('');
        div.classList.remove('hidden');
    }, 300);
});

function agregarProducto(p) {
    document.getElementById('buscar-producto').value = '';
    document.getElementById('resultados-producto').classList.add('hidden');
    const precio = parseFloat(p.precio_aplicado ?? p.precio_venta) || 0;
    agregarItem({
        producto_id:     p.id,
        codigo:          p.codigo,
        descripcion:     p.nombre,
        cantidad:        1,
        precio_unitario: precio,
        iva_pct:         p.iva_pct ?? 19,
        descuento_pct:   0,
        sin_precio:      precio === 0,
    });
}

// ── Agregar item ──────────────────────────────
function agregarItem(data = {}) {
    const id = ++itemCounter;
    items.push({
        id,
        producto_id:     data.producto_id     || '',
        codigo:          data.codigo          || '',
        descripcion:     data.descripcion     || '',
        cantidad:        data.cantidad        || 1,
        precio_unitario: parseFloat(data.precio_unitario) || 0,
        descuento_pct:   data.descuento_pct   || 0,
        iva_pct:         parseFloat(data.iva_pct ?? 19),
        sin_precio:      data.sin_precio      || false,
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
    item[campo] = ['cantidad','precio_unitario','descuento_pct','iva_pct'].includes(campo)
        ? parseFloat(valor) || 0
        : valor;
    if (campo === 'precio_unitario') {
        if (item.precio_unitario > 0) item.sin_precio = false;
        const input = document.querySelector(`tr[data-id="${id}"] input[name*="precio_unitario"]`);
        if (input) {
            input.classList.toggle('border-amber-500', item.sin_precio && item.precio_unitario === 0);
            input.classList.toggle('text-amber-400',   item.sin_precio && item.precio_unitario === 0);
            input.classList.toggle('border-[#1e2d47]', !(item.sin_precio && item.precio_unitario === 0));
            input.classList.toggle('text-slate-200',   !(item.sin_precio && item.precio_unitario === 0));
        }
    }
    actualizarFilaTotales(id);
    calcularTotales();
}

function actualizarFilaTotales(id) {
    const item = items.find(i => i.id === id);
    if (!item) return;
    const sub  = item.cantidad * item.precio_unitario;
    const desc = sub * (item.descuento_pct / 100);
    const base = sub - desc;
    const iva  = base * (item.iva_pct / 100);
    const tot  = base + iva;
    const span = document.getElementById('total-item-' + id);
    if (span) span.textContent = fmt(tot);
}

// ── Render items ──────────────────────────────
function renderItems() {
    const tbody    = document.getElementById('items-body');
    const sinItems = document.getElementById('sin-items');

    if (!items.length) {
        tbody.innerHTML = '';
        sinItems.classList.remove('hidden');
        return;
    }
    sinItems.classList.add('hidden');

    tbody.innerHTML = items.map((item, idx) => {
        const sub  = item.cantidad * item.precio_unitario;
        const desc = sub * (item.descuento_pct / 100);
        const base = sub - desc;
        const iva  = base * (item.iva_pct / 100);
        const tot  = base + iva;

        return `
        <tr class="border-b border-[#1e2d47]/30" data-id="${item.id}">
            <td class="py-2 pr-2 relative">
                <input type="hidden" name="items[${idx}][producto_id]" id="pid-${item.id}" value="${item.producto_id}">
                <input type="hidden" name="items[${idx}][codigo]"      id="cod-${item.id}" value="${item.codigo}">
                <input type="text"
                       id="desc-${item.id}"
                       name="items[${idx}][descripcion]"
                       value="${item.descripcion ? item.descripcion.toUpperCase() : ''}"
                       oninput="buscarEnLinea(${item.id}, this.value)"
                       onchange="updateItem(${item.id},'descripcion',this.value)"
                       onblur="cerrarDropdownLinea(${item.id})"
                       placeholder="DESCRIPCIÓN..."
                       autocomplete="off"
                       style="text-transform:uppercase"
                       class="w-full bg-transparent border-b border-[#1e2d47] text-sm text-slate-200
                              py-1 focus:outline-none focus:border-amber-500 transition-colors uppercase">
                <div id="dropdown-linea-${item.id}"
                     class="absolute top-full left-0 mt-1 bg-[#1a2235] border border-[#1e2d47]
                            rounded-xl shadow-2xl z-[60] hidden max-h-52 overflow-y-auto"
                     style="min-width:320px">
                </div>
            </td>
            <td class="py-2 px-2 w-20">
                <input type="text"
                       inputmode="decimal"
                       name="items[${idx}][cantidad]"
                       value="${item.cantidad}"
                       onchange="updateItem(${item.id},'cantidad',this.value)"
                       class="w-full bg-transparent border-b border-[#1e2d47] text-sm text-slate-200
                              text-center py-1 focus:outline-none focus:border-amber-500">
            </td>
            <td class="py-2 px-2 w-28">
                <input type="text"
                       inputmode="decimal"
                       name="items[${idx}][precio_unitario]"
                       value="${item.precio_unitario}"
                       onchange="updateItem(${item.id},'precio_unitario',this.value)"
                       class="w-full bg-transparent border-b text-sm text-right py-1 focus:outline-none transition-colors
                              ${item.sin_precio && item.precio_unitario===0
                                ? 'border-amber-500 text-amber-400 focus:border-amber-300'
                                : 'border-[#1e2d47] text-slate-200 focus:border-amber-500'}">
            </td>
            <td class="py-2 px-2 w-16 hidden sm:table-cell">
                <input type="text"
                       inputmode="decimal"
                       name="items[${idx}][descuento_pct]"
                       value="${item.descuento_pct}"
                       onchange="updateItem(${item.id},'descuento_pct',this.value)"
                       class="w-full bg-transparent border-b border-[#1e2d47] text-sm text-slate-200
                              text-center py-1 focus:outline-none focus:border-amber-500">
            </td>
            <td class="py-2 px-2 w-16">
                <select name="items[${idx}][iva_pct]"
                        onchange="updateItem(${item.id},'iva_pct',this.value)"
                        class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-lg text-xs
                               text-slate-200 px-2 py-1 focus:outline-none focus:border-amber-500">
                    <option value="0"  ${item.iva_pct==0  ? 'selected':''}>0%</option>
                    <option value="5"  ${item.iva_pct==5  ? 'selected':''}>5%</option>
                    <option value="19" ${item.iva_pct==19 ? 'selected':''}>19%</option>
                </select>
            </td>
            <td class="py-2 pl-2 text-right w-28">
                <span id="total-item-${item.id}"
                      class="font-mono text-sm font-semibold text-slate-200">
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

// ── Calcular totales ──────────────────────────
function calcularTotales() {
    let subtotal = 0, descuento = 0, iva = 0;

    items.forEach(item => {
        const sub  = item.cantidad * item.precio_unitario;
        const desc = sub * (item.descuento_pct / 100);
        const base = sub - desc;
        subtotal  += base;
        descuento += desc;
        iva       += base * (item.iva_pct / 100);
    });

    const retefuente = clienteData ? subtotal * (parseFloat(clienteData.retefuente_pct)||0) / 100 : 0;
    const reteica    = clienteData ? subtotal * (parseFloat(clienteData.reteica_pct)||0)    / 100 : 0;
    const total      = subtotal + iva - retefuente - reteica;

    document.getElementById('display-subtotal').textContent   = fmt(subtotal);
    document.getElementById('display-descuento').textContent  = '-' + fmt(descuento);
    document.getElementById('display-base-iva').textContent   = fmt(subtotal);
    document.getElementById('display-iva').textContent        = '+' + fmt(iva);
    document.getElementById('display-retefuente').textContent = '-' + fmt(retefuente);
    document.getElementById('display-reteica').textContent    = '-' + fmt(reteica);
    document.getElementById('display-total').textContent      = fmt(total);
    const mt = document.getElementById('mobile-total');
    if (mt) mt.textContent = fmt(total);
}

// ── Búsqueda en línea de descripción ──────────
let timerLinea = {};

function buscarEnLinea(itemId, q) {
    q = q.trim();
    clearTimeout(timerLinea[itemId]);
    const dropdown = document.getElementById('dropdown-linea-' + itemId);
    if (!dropdown) return;
    if (q.length < 2) { dropdown.classList.add('hidden'); return; }

    timerLinea[itemId] = setTimeout(async () => {
        const res  = await fetch(`/api/productos/buscar?q=${encodeURIComponent(q)}&lista_precio=${listaPrecios}`);
        const data = await res.json();
        if (!data.length) { dropdown.classList.add('hidden'); return; }
        dropdown.innerHTML = data.map(p => `
            <div class="px-4 py-2.5 hover:bg-[#141c2e] cursor-pointer border-b border-[#1e2d47]/50 last:border-0"
                 onmousedown="event.preventDefault(); seleccionarEnLinea(${itemId}, ${JSON.stringify(p).replace(/"/g,'&quot;')})">
                <div class="flex justify-between items-center gap-3">
                    <div class="min-w-0">
                        <div class="text-sm font-medium text-slate-200 truncate">${p.nombre}</div>
                        <div class="text-xs text-slate-500">${p.codigo}${!p.es_servicio ? ' · Stock: '+p.stock_actual : ''}</div>
                    </div>
                    <div class="text-sm font-semibold text-amber-500 flex-shrink-0">
                        ${fmt(p.precio_aplicado || p.precio_venta)}
                    </div>
                </div>
            </div>`).join('');
        dropdown.classList.remove('hidden');
    }, 280);
}

function seleccionarEnLinea(itemId, p) {
    const item = items.find(i => i.id === itemId);
    if (!item) return;

    const precio = parseFloat(p.precio_aplicado ?? p.precio_venta) || 0;
    item.producto_id     = p.id;
    item.codigo          = p.codigo;
    item.descripcion     = p.nombre;
    item.precio_unitario = precio;
    item.iva_pct         = parseFloat(p.iva_pct ?? 19);
    item.sin_precio      = precio === 0;

    renderItems();
    calcularTotales();

    // Foco en cantidad después de seleccionar
    setTimeout(() => {
        const cantInput = document.querySelector(`tr[data-id="${itemId}"] input[name*="cantidad"]`);
        if (cantInput) { cantInput.focus(); cantInput.select(); }
    }, 40);
}

function cerrarDropdownLinea(itemId) {
    const dropdown = document.getElementById('dropdown-linea-' + itemId);
    if (dropdown) dropdown.classList.add('hidden');
}

// ── Cerrar dropdowns al hacer click fuera ─────
document.addEventListener('click', e => {
    if (!e.target.closest('#buscar-cliente') && !e.target.closest('#resultados-cliente'))
        document.getElementById('resultados-cliente').classList.add('hidden');
    if (!e.target.closest('#buscar-producto') && !e.target.closest('#resultados-producto'))
        document.getElementById('resultados-producto').classList.add('hidden');
});
</script>
@endpush
@endsection