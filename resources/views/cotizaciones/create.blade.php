@extends('layouts.app')
@section('title', 'Nueva Cotización')
@section('page-title', 'Cotizaciones · Nueva')

@section('content')
<div class="max-w-6xl mx-auto pb-36 lg:pb-0">

    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('cotizaciones.index') }}"
           class="w-9 h-9 bg-[#141c2e] border border-[#1e2d47] rounded-xl
                  flex items-center justify-center text-slate-400
                  hover:text-amber-500 hover:border-amber-500/50 transition-colors">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="font-display font-bold text-2xl">Nueva Cotización</h1>
            <p class="text-slate-500 text-sm font-mono">{{ $consecutivo['numero'] }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('cotizaciones.store') }}" id="form-cotizacion">
        @csrf

        <x-form-errors class="mb-4" />

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

            {{-- COLUMNA PRINCIPAL --}}
            <div class="lg:col-span-2 space-y-4">

                {{-- Cliente --}}
                <div class="card p-5">
                    <h2 class="font-display font-bold text-sm mb-4 flex items-center gap-2">
                        <i class="fas fa-user text-amber-500"></i> Cliente
                    </h2>

                    {{-- Buscador --}}
                    <div class="relative mb-3">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                        <input type="text" id="buscar-cliente"
                               placeholder="Buscar cliente existente o escribe los datos manualmente..."
                               autocomplete="off"
                               class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl
                                      pl-9 pr-4 py-2.5 text-sm placeholder-slate-600
                                      focus:outline-none focus:border-amber-500"
                               style="color:#e2e8f0">
                        <div id="resultados-cliente"
                             class="absolute top-full left-0 right-0 mt-1 bg-[#1a2235]
                                    border border-[#1e2d47] rounded-xl shadow-xl z-50
                                    hidden max-h-48 overflow-y-auto"></div>
                    </div>

                    <input type="hidden" name="cliente_id" id="cliente_id">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">
                                Nombre / Razón Social *
                            </label>
                            <input type="text" name="cliente_nombre" id="cliente_nombre"
                                   value="{{ old('cliente_nombre') }}"
                                   placeholder="NOMBRE DEL CLIENTE"
                                   data-uppercase
                                   class="form-input @error('cliente_nombre') border-red-500 @enderror"
                                   style="color:#e2e8f0">
                            @error('cliente_nombre')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">
                                Documento
                            </label>
                            <input type="text" name="cliente_documento" id="cliente_documento"
                                   value="{{ old('cliente_documento') }}"
                                   placeholder="NIT / CC"
                                   class="form-input"
                                   style="color:#e2e8f0">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">
                                Email
                            </label>
                            <input type="email" name="cliente_email" id="cliente_email"
                                   value="{{ old('cliente_email') }}"
                                   placeholder="email@cliente.com"
                                   class="form-input"
                                   style="color:#e2e8f0">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">
                                Teléfono
                            </label>
                            <input type="text" name="cliente_telefono" id="cliente_telefono"
                                   value="{{ old('cliente_telefono') }}"
                                   placeholder="300 000 0000"
                                   class="form-input"
                                   style="color:#e2e8f0">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">
                                Dirección
                            </label>
                            <input type="text" name="cliente_direccion" id="cliente_direccion"
                                   value="{{ old('cliente_direccion') }}"
                                   placeholder="DIRECCIÓN"
                                   data-uppercase
                                   class="form-input"
                                   style="color:#e2e8f0">
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
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-[#1e2d47]">
                                    <th class="text-left text-[10px] font-semibold text-slate-500 uppercase pb-2 pr-2">Descripción</th>
                                    <th class="text-center text-[10px] font-semibold text-slate-500 uppercase pb-2 px-2 w-20">Cant.</th>
                                    <th class="text-right text-[10px] font-semibold text-slate-500 uppercase pb-2 px-2 w-28">Precio</th>
                                    <th class="text-center text-[10px] font-semibold text-slate-500 uppercase pb-2 px-2 w-16">% IVA</th>
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
                    @error('items')
                    <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Textos --}}
                <div class="card p-5">
                    <h2 class="font-display font-bold text-sm mb-4 flex items-center gap-2">
                        <i class="fas fa-comment text-amber-500"></i> Textos
                    </h2>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">
                                Observaciones
                            </label>
                            <textarea name="observaciones" rows="2" data-uppercase
                                      placeholder="CONDICIONES ESPECIALES, NOTAS..."
                                      class="form-input resize-none"
                                      style="color:#e2e8f0">{{ old('observaciones', $empresa->terminos_condiciones) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">
                                Términos y Condiciones
                            </label>
                            <textarea name="terminos" rows="2" data-uppercase
                                      placeholder="TÉRMINOS DE LA COTIZACIÓN..."
                                      class="form-input resize-none"
                                      style="color:#e2e8f0">{{ old('terminos') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- COLUMNA DERECHA --}}
            <div class="space-y-4">

                {{-- Datos --}}
                <div class="card p-5">
                    <h2 class="font-display font-bold text-sm mb-4 flex items-center gap-2">
                        <i class="fas fa-calendar text-amber-500"></i> Datos
                    </h2>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">
                                Fecha Emisión *
                            </label>
                            <input type="date" name="fecha_emision"
                                   value="{{ old('fecha_emision', date('Y-m-d')) }}"
                                   class="form-input"
                                   style="color:#e2e8f0">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">
                                Válida hasta *
                            </label>
                            <input type="date" name="fecha_vencimiento"
                                   value="{{ old('fecha_vencimiento', date('Y-m-d', strtotime('+30 days'))) }}"
                                   class="form-input @error('fecha_vencimiento') border-red-500 @enderror"
                                   style="color:#e2e8f0">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">
                                Forma de Pago
                            </label>
                            <select name="forma_pago"
                                    class="form-input"
                                    style="color:#e2e8f0">
                                <option value="contado">Contado</option>
                                <option value="credito">Crédito</option>
                                <option value="transferencia">Transferencia</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">
                                Plazo (días)
                            </label>
                            <input type="text" inputmode="decimal" name="plazo_pago"
                                   value="{{ old('plazo_pago', 0) }}"
                                   data-numeric
                                   class="form-input"
                                   style="color:#e2e8f0">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">
                                Estado
                            </label>
                            <select name="estado"
                                    class="form-input"
                                    style="color:#e2e8f0">
                                <option value="borrador">Borrador</option>
                                <option value="enviada">Enviada al cliente</option>
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
                    <i class="fas fa-save"></i> Guardar Cotización
                </button>
                <a href="{{ route('cotizaciones.index') }}"
                   class="w-full py-2.5 bg-[#1a2235] border border-[#1e2d47] text-slate-400
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
        <button form="form-cotizacion" type="submit"
                class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-black
                       font-bold rounded-xl transition-colors flex items-center gap-2 text-sm">
            <i class="fas fa-save"></i> Guardar
        </button>
    </div>
</div>

@push('scripts')
<script>
let items = [], itemCounter = 0;
const fmt = n => '$' + Number(n||0).toLocaleString('es-CO',{minimumFractionDigits:0,maximumFractionDigits:0});

document.querySelectorAll('[data-uppercase]').forEach(el => {
    el.addEventListener('input', function() {
        const pos = this.selectionStart;
        this.value = this.value.toUpperCase();
        this.setSelectionRange(pos, pos);
    });
});
document.querySelectorAll('[data-numeric]').forEach(el => {
    el.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9.,]/g,'');
    });
});

// Buscar cliente
let timerCli;
document.getElementById('buscar-cliente').addEventListener('input', function() {
    clearTimeout(timerCli);
    const q = this.value.trim();
    if (q.length < 2) { document.getElementById('resultados-cliente').classList.add('hidden'); return; }
    timerCli = setTimeout(async () => {
        const res  = await fetch(`/api/clientes/buscar?q=${encodeURIComponent(q)}`);
        const data = await res.json();
        const div  = document.getElementById('resultados-cliente');
        if (!data.length) { div.classList.add('hidden'); return; }
        div.innerHTML = data.map(c => `
            <div class="px-4 py-2.5 hover:bg-[#141c2e] cursor-pointer
                        border-b border-[#1e2d47]/50 last:border-0"
                 onclick="seleccionarCliente(${JSON.stringify(c).replace(/"/g,'&quot;')})">
                <div class="text-sm font-medium" style="color:#e2e8f0">
                    ${c.razon_social || ((c.nombres||'')+' '+(c.apellidos||''))}
                </div>
                <div class="text-xs text-slate-500">${c.tipo_documento}: ${c.numero_documento}</div>
            </div>`).join('');
        div.classList.remove('hidden');
    }, 300);
});

function seleccionarCliente(c) {
    const nombre = c.razon_social || ((c.nombres||'')+' '+(c.apellidos||''));
    document.getElementById('cliente_id').value        = c.id;
    document.getElementById('cliente_nombre').value    = nombre.toUpperCase();
    document.getElementById('cliente_documento').value = c.tipo_documento+': '+c.numero_documento;
    document.getElementById('cliente_email').value     = c.email || '';
    document.getElementById('cliente_telefono').value  = c.telefono || c.celular || '';
    document.getElementById('cliente_direccion').value = (c.direccion || '').toUpperCase();
    document.getElementById('buscar-cliente').value    = '';
    document.getElementById('resultados-cliente').classList.add('hidden');
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
            <div class="px-4 py-2.5 hover:bg-[#141c2e] cursor-pointer
                        border-b border-[#1e2d47]/50 last:border-0"
                 onclick="agregarProducto(${JSON.stringify(p).replace(/"/g,'&quot;')})">
                <div class="flex justify-between items-center">
                    <div>
                        <div class="text-sm font-medium" style="color:#e2e8f0">${p.nombre}</div>
                        <div class="text-xs text-slate-500">${p.codigo}</div>
                    </div>
                    <div class="text-xs text-emerald-500 font-semibold ml-4">${fmt(p.precio_venta)}</div>
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
    const base  = item.cantidad * item.precio_unitario;
    const iva   = base * (item.iva_pct / 100);
    const span  = document.getElementById('total-item-'+id);
    if (span) span.textContent = fmt(base + iva);
    calcularTotales();
}

function renderItems() {
    const tbody    = document.getElementById('items-body');
    const sinItems = document.getElementById('sin-items');
    if (!items.length) { tbody.innerHTML = ''; sinItems.classList.remove('hidden'); return; }
    sinItems.classList.add('hidden');
    tbody.innerHTML = items.map((item, idx) => {
        const base = item.cantidad * item.precio_unitario;
        const iva  = base * (item.iva_pct / 100);
        return `
        <tr class="border-b border-[#1e2d47]/30">
            <td class="py-2 pr-2">
                <input type="hidden" name="items[${idx}][producto_id]" value="${item.producto_id}">
                <input type="hidden" name="items[${idx}][codigo]"      value="${item.codigo}">
                <input type="text" name="items[${idx}][descripcion]"
                       value="${item.descripcion.toUpperCase()}"
                       onchange="updateItem(${item.id},'descripcion',this.value)"
                       style="text-transform:uppercase;color:#e2e8f0"
                       placeholder="DESCRIPCIÓN..."
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
                        class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-lg
                               text-xs px-2 py-1 focus:outline-none focus:border-amber-500">
                    <option value="0"  ${item.iva_pct==0 ?'selected':''}>0%</option>
                    <option value="5"  ${item.iva_pct==5 ?'selected':''}>5%</option>
                    <option value="19" ${item.iva_pct==19?'selected':''}>19%</option>
                </select>
            </td>
            <td class="py-2 pl-2 text-right w-28">
                <span id="total-item-${item.id}" class="font-mono text-sm font-semibold"
                      style="color:#e2e8f0">${fmt(base+iva)}</span>
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
    const total = subtotal + iva;
    document.getElementById('display-total').textContent    = fmt(total);
    const mt = document.getElementById('mobile-total');
    if (mt) mt.textContent = fmt(total);
}

document.addEventListener('click', e => {
    if (!e.target.closest('#buscar-cliente')  && !e.target.closest('#resultados-cliente'))
        document.getElementById('resultados-cliente').classList.add('hidden');
    if (!e.target.closest('#buscar-producto') && !e.target.closest('#resultados-producto'))
        document.getElementById('resultados-producto').classList.add('hidden');
});
</script>
@endpush
@endsection