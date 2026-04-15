<!DOCTYPE html>
@php $temaActual = auth()->check() ? (auth()->user()->tema ?? 'dark') : 'dark'; @endphp
<html lang="es" class="{{ $temaActual === 'light' ? '' : 'dark' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POS — {{ config('app.name') }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0b0f1a] text-slate-200 font-sans overflow-hidden" style="height:100vh;">

<div class="flex flex-col h-screen">

    {{-- ── TOPBAR POS ──────────────────────────────── --}}
    <header class="flex items-center gap-3 px-4 py-2.5 bg-[#111827] border-b border-[#1e2d47] flex-shrink-0">
        <a href="{{ route('dashboard') }}"
           class="w-8 h-8 rounded-lg bg-[#1a2235] border border-[#1e2d47] flex items-center justify-center
                  text-slate-400 hover:text-amber-400 hover:border-amber-500/50 transition-colors flex-shrink-0"
           title="Volver">
            <i class="fas fa-arrow-left text-xs"></i>
        </a>
        <div class="font-display font-black text-xl text-white">
            Factura<span class="text-amber-500">CO</span>
            <span class="text-slate-400 font-normal text-sm ml-2">Punto de Venta</span>
        </div>
        <div class="ml-auto flex items-center gap-3">
            <div class="text-xs text-slate-500">
                <i class="fas fa-user mr-1"></i>{{ auth()->user()->name }}
            </div>
            <div class="text-xs text-slate-500">
                <i class="fas fa-calendar mr-1"></i>{{ now()->locale('es')->isoFormat('D MMM YYYY') }}
            </div>
            <div class="text-xs font-semibold text-amber-400 bg-amber-500/10 border border-amber-500/20 px-2 py-1 rounded-lg">
                {{ $empresa->nombre_comercial ?: $empresa->razon_social }}
            </div>
        </div>
    </header>

    {{-- ── CUERPO PRINCIPAL ────────────────────────── --}}
    <div class="flex flex-1 overflow-hidden">

        {{-- ════════════════════════════════════════
             PANEL IZQUIERDO — PRODUCTOS
        ════════════════════════════════════════ --}}
        <div class="flex flex-col flex-1 overflow-hidden border-r border-[#1e2d47]">

            {{-- Barra de búsqueda + categorías --}}
            <div class="flex-shrink-0 px-4 pt-3 pb-2 space-y-2 bg-[#111827]">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm pointer-events-none"></i>
                    <input id="buscar-producto"
                           type="text"
                           placeholder="Buscar producto o escanear código de barras..."
                           autocomplete="off"
                           class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl pl-9 pr-4 py-2.5
                                  text-sm placeholder-slate-600 focus:outline-none focus:border-amber-500 transition-all text-slate-200">
                </div>
                {{-- Filtro por categorías --}}
                <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-none">
                    <button onclick="filtrarCategoria(null)"
                            data-cat=""
                            class="cat-btn flex-shrink-0 px-3 py-1.5 rounded-lg text-xs font-semibold
                                   bg-amber-500 text-black transition-colors">
                        Todos
                    </button>
                    @foreach($categorias as $cat)
                    <button onclick="filtrarCategoria({{ $cat->id }})"
                            data-cat="{{ $cat->id }}"
                            class="cat-btn flex-shrink-0 px-3 py-1.5 rounded-lg text-xs font-semibold
                                   bg-[#1a2235] border border-[#1e2d47] text-slate-400
                                   hover:border-amber-500/50 hover:text-amber-400 transition-colors">
                        {{ $cat->nombre }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Grid de productos --}}
            <div id="productos-grid"
                 class="flex-1 overflow-y-auto p-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 content-start">
                @foreach($productos as $prod)
                @php
                    $precioFinal = $prod->incluye_iva
                        ? $prod->precio_venta
                        : round($prod->precio_venta * (1 + $prod->iva_pct / 100));
                @endphp
                <div class="producto-card cursor-pointer group relative
                            bg-[#111827] border border-[#1e2d47] rounded-xl p-3
                            hover:border-amber-500/60 hover:bg-[#1a2235] transition-all
                            active:scale-95"
                     data-id="{{ $prod->id }}"
                     data-nombre="{{ $prod->nombre }}"
                     data-codigo="{{ $prod->codigo }}"
                     data-codigo-barras="{{ $prod->codigo_barras }}"
                     data-precio="{{ $prod->precio_venta }}"
                     data-precio-con-iva="{{ $precioFinal }}"
                     data-iva-pct="{{ $prod->iva_pct }}"
                     data-incluye-iva="{{ $prod->incluye_iva ? '1' : '0' }}"
                     data-stock="{{ $prod->es_servicio ? 9999 : $prod->stock_actual }}"
                     data-es-servicio="{{ $prod->es_servicio ? '1' : '0' }}"
                     data-categoria="{{ $prod->categoria_id }}"
                     onclick="agregarAlCarrito(this)">

                    {{-- Badge stock bajo --}}
                    @if(!$prod->es_servicio && $prod->stock_actual <= 0)
                    <span class="absolute top-2 right-2 text-[9px] bg-red-500/20 text-red-400 border border-red-500/30 px-1.5 py-0.5 rounded-full">
                        Sin stock
                    </span>
                    @endif

                    {{-- Icono / imagen --}}
                    <div class="w-full aspect-square rounded-lg bg-[#1a2235] flex items-center justify-center mb-2 overflow-hidden">
                        @if($prod->imagen)
                            <img src="{{ Storage::url($prod->imagen) }}" class="w-full h-full object-cover" alt="{{ $prod->nombre }}">
                        @else
                            <i class="fas {{ $prod->es_servicio ? 'fa-cog' : 'fa-box' }} text-2xl text-slate-600 group-hover:text-amber-500/50 transition-colors"></i>
                        @endif
                    </div>

                    <div class="text-xs font-semibold text-slate-200 leading-tight truncate" title="{{ $prod->nombre }}">
                        {{ $prod->nombre }}
                    </div>
                    <div class="text-[10px] text-slate-500 mt-0.5">{{ $prod->codigo }}</div>
                    <div class="text-sm font-black text-amber-400 mt-1">
                        ${{ number_format($precioFinal, 0, ',', '.') }}
                    </div>
                    @if(!$prod->es_servicio)
                    <div class="text-[10px] text-slate-600 mt-0.5">
                        Stock: {{ number_format($prod->stock_actual, 0) }}
                    </div>
                    @endif
                </div>
                @endforeach

                {{-- Estado vacío --}}
                <div id="sin-resultados" class="hidden col-span-full flex flex-col items-center justify-center py-16 text-slate-600">
                    <i class="fas fa-search text-4xl mb-3"></i>
                    <p>No se encontraron productos</p>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════
             PANEL DERECHO — CARRITO
        ════════════════════════════════════════ --}}
        <div class="w-80 xl:w-96 flex flex-col bg-[#111827] flex-shrink-0">

            {{-- Cliente --}}
            <div class="px-4 pt-3 pb-2 border-b border-[#1e2d47] flex-shrink-0">
                <div class="relative">
                    <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs pointer-events-none"></i>
                    <input id="buscar-cliente"
                           type="text"
                           placeholder="Cliente (opcional — Consumidor Final)"
                           autocomplete="off"
                           class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl pl-8 pr-8 py-2
                                  text-xs placeholder-slate-600 focus:outline-none focus:border-amber-500 transition-all text-slate-200">
                    <button id="limpiar-cliente"
                            onclick="limpiarCliente()"
                            class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-500 hover:text-red-400 transition-colors">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>
                <input type="hidden" id="cliente-id" value="">
                <div id="cliente-seleccionado" class="hidden mt-1.5 px-2 py-1 bg-amber-500/10 border border-amber-500/20 rounded-lg">
                    <span id="cliente-nombre-label" class="text-xs text-amber-400 font-semibold"></span>
                </div>
                {{-- Sugerencias cliente --}}
                <div id="cliente-sugerencias"
                     class="hidden absolute z-50 bg-[#1a2235] border border-[#1e2d47] rounded-xl shadow-xl mt-1 w-72 max-h-48 overflow-y-auto">
                </div>
            </div>

            {{-- Items del carrito --}}
            <div id="carrito-items" class="flex-1 overflow-y-auto px-3 py-2 space-y-2">
                <div id="carrito-vacio" class="flex flex-col items-center justify-center h-full text-slate-600 py-8">
                    <i class="fas fa-shopping-cart text-4xl mb-3"></i>
                    <p class="text-sm">El carrito está vacío</p>
                    <p class="text-xs mt-1">Toca un producto para agregarlo</p>
                </div>
            </div>

            {{-- Totales --}}
            <div class="flex-shrink-0 border-t border-[#1e2d47] px-4 py-3 space-y-1.5">
                <div class="flex justify-between text-xs text-slate-400">
                    <span>Subtotal</span>
                    <span id="total-subtotal">$0</span>
                </div>
                <div class="flex justify-between text-xs text-slate-400">
                    <span>IVA</span>
                    <span id="total-iva">$0</span>
                </div>
                <div class="flex justify-between text-xs text-red-400" id="fila-descuento" style="display:none!important">
                    <span>Descuento</span>
                    <span id="total-descuento">$0</span>
                </div>
                <div class="flex justify-between text-base font-black text-white pt-1 border-t border-[#1e2d47]">
                    <span>TOTAL</span>
                    <span id="total-general" class="text-amber-400">$0</span>
                </div>
            </div>

            {{-- Forma de pago --}}
            <div class="flex-shrink-0 px-4 pb-2 border-t border-[#1e2d47] pt-3">
                <div class="text-xs text-slate-500 mb-1.5">Forma de pago</div>
                <div class="grid grid-cols-4 gap-1.5">
                    @foreach([['contado','fa-money-bill','Efectivo'],['tarjeta','fa-credit-card','Tarjeta'],['transferencia','fa-university','Transfer.'],['nequi','fa-mobile-alt','Nequi']] as [$val,$ico,$label])
                    <label class="pago-btn cursor-pointer">
                        <input type="radio" name="forma_pago" value="{{ $val }}" class="sr-only" {{ $val==='contado'?'checked':'' }}>
                        <div class="flex flex-col items-center gap-1 py-2 px-1 rounded-xl border border-[#1e2d47]
                                    bg-[#1a2235] text-slate-500 text-[10px] font-semibold
                                    hover:border-amber-500/50 hover:text-amber-400 transition-all
                                    pago-option {{ $val==='contado'?'!border-amber-500 !text-amber-400 !bg-amber-500/10':'' }}">
                            <i class="fas {{ $ico }} text-sm"></i>
                            {{ $label }}
                        </div>
                    </label>
                    @endforeach
                </div>

                {{-- Efectivo / vuelto --}}
                <div id="panel-efectivo" class="mt-2 flex gap-2">
                    <div class="flex-1">
                        <input id="monto-efectivo"
                               type="number"
                               placeholder="Efectivo recibido"
                               min="0"
                               step="100"
                               oninput="calcularVuelto()"
                               class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-3 py-2
                                      text-sm text-slate-200 placeholder-slate-600
                                      focus:outline-none focus:border-amber-500 transition-all">
                    </div>
                    <div class="flex flex-col items-end justify-center text-right min-w-20">
                        <div class="text-[10px] text-slate-500">Vuelto</div>
                        <div id="vuelto-display" class="text-sm font-black text-emerald-400">$0</div>
                    </div>
                </div>
            </div>

            {{-- Botones acción --}}
            <div class="flex-shrink-0 px-4 pb-4 pt-2 flex gap-2">
                <button onclick="limpiarCarrito()"
                        class="flex-shrink-0 w-10 h-11 bg-[#1a2235] border border-[#1e2d47] rounded-xl
                               flex items-center justify-center text-slate-500
                               hover:text-red-400 hover:border-red-500/50 transition-colors">
                    <i class="fas fa-trash text-sm"></i>
                </button>
                <button id="btn-cobrar"
                        onclick="cobrar()"
                        disabled
                        class="flex-1 h-11 bg-amber-500 hover:bg-amber-400 disabled:opacity-40 disabled:cursor-not-allowed
                               rounded-xl font-black text-black text-base transition-colors
                               flex items-center justify-center gap-2">
                    <i class="fas fa-cash-register"></i>
                    <span id="btn-cobrar-texto">COBRAR</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── MODAL CANTIDAD ──────────────────────────────── --}}
<div id="modal-cantidad" class="hidden fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4">
    <div class="bg-[#111827] border border-[#1e2d47] rounded-2xl p-6 w-80 shadow-2xl">
        <div class="text-sm font-semibold text-white mb-1" id="modal-producto-nombre"></div>
        <div class="text-xs text-slate-500 mb-4" id="modal-producto-precio"></div>
        <div class="flex gap-3 items-center mb-4">
            <button onclick="ajustarCantidadModal(-1)"
                    class="w-10 h-10 bg-[#1a2235] border border-[#1e2d47] rounded-xl text-slate-300
                           hover:border-amber-500/50 hover:text-amber-400 transition-colors text-xl font-bold flex items-center justify-center">
                −
            </button>
            <input id="modal-cantidad-input"
                   type="number"
                   min="0.001"
                   step="1"
                   value="1"
                   class="flex-1 bg-[#1a2235] border border-amber-500/60 rounded-xl px-3 py-2
                          text-center text-xl font-black text-amber-400 focus:outline-none">
            <button onclick="ajustarCantidadModal(1)"
                    class="w-10 h-10 bg-[#1a2235] border border-[#1e2d47] rounded-xl text-slate-300
                           hover:border-amber-500/50 hover:text-amber-400 transition-colors text-xl font-bold flex items-center justify-center">
                +
            </button>
        </div>
        <div class="flex gap-2">
            <button onclick="cerrarModalCantidad()"
                    class="flex-1 py-2.5 bg-[#1a2235] border border-[#1e2d47] rounded-xl text-sm text-slate-400
                           hover:border-slate-500 transition-colors">
                Cancelar
            </button>
            <button onclick="confirmarCantidad()"
                    class="flex-1 py-2.5 bg-amber-500 hover:bg-amber-400 rounded-xl text-sm font-bold text-black transition-colors">
                Agregar
            </button>
        </div>
    </div>
</div>

{{-- ── TOAST ───────────────────────────────────────── --}}
<div id="toast"
     class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 z-[200]
            bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-3
            text-sm font-semibold shadow-2xl flex items-center gap-2 min-w-64">
    <i id="toast-icon" class="fas fa-check-circle text-emerald-400"></i>
    <span id="toast-texto"></span>
</div>

<script>
// ── Estado ───────────────────────────────────────────
const PRODUCTOS = @json($productos);
let carrito = [];
let productoModalActual = null;
let categoriaActiva = null;
let terminoBusqueda = '';

// ── Formatear moneda ─────────────────────────────────
function fmt(n) {
    return '$' + Number(n).toLocaleString('es-CO', {minimumFractionDigits: 0, maximumFractionDigits: 0});
}

// ── Filtrar productos ────────────────────────────────
function renderizarProductos() {
    const cards = document.querySelectorAll('.producto-card');
    let visibles = 0;
    cards.forEach(card => {
        const nombre = (card.dataset.nombre + ' ' + card.dataset.codigo + ' ' + (card.dataset.codigoBarras || '')).toLowerCase();
        const matchBusqueda = !terminoBusqueda || nombre.includes(terminoBusqueda.toLowerCase());
        const matchCategoria = !categoriaActiva || card.dataset.categoria == categoriaActiva;
        const visible = matchBusqueda && matchCategoria;
        card.style.display = visible ? '' : 'none';
        if (visible) visibles++;
    });
    document.getElementById('sin-resultados').classList.toggle('hidden', visibles > 0);
}

document.getElementById('buscar-producto').addEventListener('input', function () {
    terminoBusqueda = this.value;
    renderizarProductos();
});

// Soporte tecla Enter para escaneo de código de barras
document.getElementById('buscar-producto').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        const codigo = this.value.trim();
        const card = document.querySelector(`.producto-card[data-codigo="${codigo}"], .producto-card[data-codigo-barras="${codigo}"]`);
        if (card) {
            agregarAlCarrito(card);
            this.value = '';
            terminoBusqueda = '';
            renderizarProductos();
        }
    }
});

function filtrarCategoria(catId) {
    categoriaActiva = catId;
    document.querySelectorAll('.cat-btn').forEach(btn => {
        const activo = (catId === null && btn.dataset.cat === '') || (btn.dataset.cat == catId);
        btn.className = btn.className.replace(/bg-amber-500 text-black|bg-\[#1a2235\] border border-\[#1e2d47\] text-slate-400/g, '');
        btn.className = btn.className.trim();
        if (activo) {
            btn.classList.add('bg-amber-500', 'text-black');
            btn.classList.remove('bg-[#1a2235]', 'border', 'border-[#1e2d47]', 'text-slate-400');
        } else {
            btn.classList.add('bg-[#1a2235]', 'border', 'border-[#1e2d47]', 'text-slate-400');
            btn.classList.remove('bg-amber-500', 'text-black');
        }
    });
    renderizarProductos();
}

// ── Carrito ──────────────────────────────────────────
function agregarAlCarrito(card) {
    const stock = parseFloat(card.dataset.stock);
    if (stock <= 0 && card.dataset.esServicio !== '1') {
        mostrarToast('Sin stock disponible', 'error');
        return;
    }

    productoModalActual = {
        id:         card.dataset.id,
        nombre:     card.dataset.nombre,
        codigo:     card.dataset.codigo,
        precio:     parseFloat(card.dataset.precio),
        precioIva:  parseFloat(card.dataset.precioCon_iva || card.dataset['precio-con-iva']),
        ivaPct:     parseFloat(card.dataset.ivaPct || card.dataset['iva-pct']),
        incluyeIva: card.dataset.incluyeIva === '1' || card.dataset['incluye-iva'] === '1',
        stock:      stock,
    };

    // Si ya está en carrito, editar cantidad directamente
    const existente = carrito.find(i => i.id === productoModalActual.id);
    if (existente) {
        existente.cantidad += 1;
        renderizarCarrito();
        mostrarToast(`${productoModalActual.nombre} (+1)`, 'ok');
        return;
    }

    // Mostrar modal de cantidad para la primera vez
    document.getElementById('modal-producto-nombre').textContent = productoModalActual.nombre;
    document.getElementById('modal-producto-precio').textContent = fmt(productoModalActual.precioIva) + ' c/u';
    document.getElementById('modal-cantidad-input').value = 1;
    document.getElementById('modal-cantidad').classList.remove('hidden');
    document.getElementById('modal-cantidad-input').focus();
    document.getElementById('modal-cantidad-input').select();
}

function ajustarCantidadModal(delta) {
    const inp = document.getElementById('modal-cantidad-input');
    inp.value = Math.max(0.001, parseFloat(inp.value) + delta);
}

function cerrarModalCantidad() {
    document.getElementById('modal-cantidad').classList.add('hidden');
    productoModalActual = null;
}

function confirmarCantidad() {
    if (!productoModalActual) return;
    const cantidad = parseFloat(document.getElementById('modal-cantidad-input').value);
    if (isNaN(cantidad) || cantidad <= 0) { mostrarToast('Cantidad inválida', 'error'); return; }

    const p = productoModalActual;
    // precio_unitario siempre sin IVA para que DocumentoService calcule bien
    const precioSinIva = p.incluyeIva
        ? p.precio / (1 + p.ivaPct / 100)
        : p.precio;

    carrito.push({
        id:               p.id,
        nombre:           p.nombre,
        codigo:           p.codigo,
        cantidad:         cantidad,
        precio_unitario:  precioSinIva,
        precio_display:   p.precioIva,
        iva_pct:          p.ivaPct,
        descuento_pct:    0,
    });
    cerrarModalCantidad();
    renderizarCarrito();
    mostrarToast(p.nombre + ' agregado', 'ok');
}

document.getElementById('modal-cantidad-input').addEventListener('keydown', e => {
    if (e.key === 'Enter') confirmarCantidad();
    if (e.key === 'Escape') cerrarModalCantidad();
});

document.getElementById('modal-cantidad').addEventListener('click', e => {
    if (e.target === document.getElementById('modal-cantidad')) cerrarModalCantidad();
});

function eliminarItem(idx) {
    carrito.splice(idx, 1);
    renderizarCarrito();
}

function cambiarCantidad(idx, delta) {
    carrito[idx].cantidad = Math.max(0.001, carrito[idx].cantidad + delta);
    renderizarCarrito();
}

function limpiarCarrito() {
    carrito = [];
    renderizarCarrito();
}

function renderizarCarrito() {
    const cont  = document.getElementById('carrito-items');
    const vacio = document.getElementById('carrito-vacio');
    const btnCobrar = document.getElementById('btn-cobrar');

    if (carrito.length === 0) {
        cont.innerHTML = '';
        cont.appendChild(vacio);
        vacio.classList.remove('hidden');
        btnCobrar.disabled = true;
        actualizarTotales(0, 0, 0);
        return;
    }

    vacio.classList.add('hidden');
    let subtotalTotal = 0, ivaTotal = 0, descTotal = 0;

    cont.innerHTML = carrito.map((item, idx) => {
        const base = item.cantidad * item.precio_unitario;
        const desc = base * (item.descuento_pct / 100);
        const sub  = base - desc;
        const iva  = sub * (item.iva_pct / 100);
        const tot  = sub + iva;
        subtotalTotal += sub;
        ivaTotal      += iva;
        descTotal     += desc;
        return `
        <div class="flex items-center gap-2 bg-[#1a2235] border border-[#1e2d47] rounded-xl px-3 py-2">
            <div class="flex-1 min-w-0">
                <div class="text-xs font-semibold text-slate-200 truncate">${item.nombre}</div>
                <div class="text-[10px] text-slate-500">${fmt(item.precio_display)} c/u · IVA ${item.iva_pct}%</div>
                <div class="text-xs font-bold text-amber-400 mt-0.5">${fmt(tot)}</div>
            </div>
            <div class="flex items-center gap-1 flex-shrink-0">
                <button onclick="cambiarCantidad(${idx}, -1)"
                        class="w-7 h-7 bg-[#141c2e] border border-[#1e2d47] rounded-lg text-slate-400
                               hover:text-amber-400 hover:border-amber-500/50 transition-colors text-sm flex items-center justify-center font-bold">
                    −
                </button>
                <span class="text-sm font-bold text-white min-w-8 text-center">${item.cantidad % 1 === 0 ? item.cantidad : item.cantidad.toFixed(2)}</span>
                <button onclick="cambiarCantidad(${idx}, 1)"
                        class="w-7 h-7 bg-[#141c2e] border border-[#1e2d47] rounded-lg text-slate-400
                               hover:text-amber-400 hover:border-amber-500/50 transition-colors text-sm flex items-center justify-center font-bold">
                    +
                </button>
                <button onclick="eliminarItem(${idx})"
                        class="w-7 h-7 bg-[#141c2e] border border-[#1e2d47] rounded-lg text-red-500/60
                               hover:text-red-400 hover:border-red-500/50 transition-colors text-xs flex items-center justify-center ml-1">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>`;
    }).join('');

    actualizarTotales(subtotalTotal, ivaTotal, descTotal);
    btnCobrar.disabled = false;
    calcularVuelto();
}

function actualizarTotales(subtotal, iva, descuento) {
    const total = subtotal + iva - descuento;
    document.getElementById('total-subtotal').textContent = fmt(subtotal);
    document.getElementById('total-iva').textContent      = fmt(iva);
    document.getElementById('total-descuento').textContent = fmt(descuento);
    document.getElementById('total-general').textContent  = fmt(total);
}

// ── Efectivo / Vuelto ────────────────────────────────
function calcularVuelto() {
    const efectivo = parseFloat(document.getElementById('monto-efectivo').value) || 0;
    const totalStr = document.getElementById('total-general').textContent.replace(/[^0-9]/g, '');
    const total    = parseFloat(totalStr) || 0;
    const vuelto   = Math.max(0, efectivo - total);
    document.getElementById('vuelto-display').textContent = fmt(vuelto);
    document.getElementById('vuelto-display').className =
        'text-sm font-black ' + (vuelto > 0 ? 'text-emerald-400' : (efectivo > 0 && efectivo < total ? 'text-red-400' : 'text-slate-400'));
}

// ── Forma de pago toggle ─────────────────────────────
document.querySelectorAll('input[name="forma_pago"]').forEach(radio => {
    radio.addEventListener('change', function () {
        document.querySelectorAll('.pago-option').forEach(el => {
            el.classList.remove('!border-amber-500', '!text-amber-400', '!bg-amber-500/10');
        });
        this.closest('.pago-btn').querySelector('.pago-option')
            .classList.add('!border-amber-500', '!text-amber-400', '!bg-amber-500/10');
        document.getElementById('panel-efectivo').style.display =
            this.value === 'contado' ? 'flex' : 'none';
    });
});

// ── Búsqueda cliente ─────────────────────────────────
let timerCliente;
document.getElementById('buscar-cliente').addEventListener('input', function () {
    clearTimeout(timerCliente);
    const q = this.value.trim();
    if (q.length < 2) { ocultarSugerenciasCliente(); return; }
    timerCliente = setTimeout(() => buscarCliente(q), 300);
});

async function buscarCliente(q) {
    const res  = await fetch(`/api/clientes/buscar?q=${encodeURIComponent(q)}`);
    const data = await res.json();
    const cont = document.getElementById('cliente-sugerencias');
    if (!data.length) { ocultarSugerenciasCliente(); return; }
    cont.innerHTML = data.map(c => `
        <div onclick="seleccionarCliente(${c.id}, '${c.razon_social.replace(/'/g,"\\'")}', '${(c.numero_documento||'').replace(/'/g,"\\'")}', '${(c.tipo_documento||'').replace(/'/g,"\\'")}' )"
             class="px-3 py-2.5 hover:bg-[#141c2e] cursor-pointer border-b border-[#1e2d47] last:border-0">
            <div class="text-xs font-semibold text-slate-200">${c.razon_social || c.nombres}</div>
            <div class="text-[10px] text-slate-500">${c.tipo_documento}: ${c.numero_documento}</div>
        </div>`).join('');
    cont.classList.remove('hidden');

    // Posicionar debajo del input
    const rect = document.getElementById('buscar-cliente').getBoundingClientRect();
    cont.style.top  = (rect.bottom + window.scrollY + 4) + 'px';
    cont.style.left = rect.left + 'px';
    cont.style.position = 'fixed';
}

function seleccionarCliente(id, nombre, doc, tipo) {
    document.getElementById('cliente-id').value          = id;
    document.getElementById('buscar-cliente').value      = nombre;
    document.getElementById('cliente-nombre-label').textContent = `${nombre} · ${tipo}: ${doc}`;
    document.getElementById('cliente-seleccionado').classList.remove('hidden');
    document.getElementById('limpiar-cliente').classList.remove('hidden');
    ocultarSugerenciasCliente();
}

function limpiarCliente() {
    document.getElementById('cliente-id').value = '';
    document.getElementById('buscar-cliente').value = '';
    document.getElementById('cliente-seleccionado').classList.add('hidden');
    document.getElementById('limpiar-cliente').classList.add('hidden');
}

function ocultarSugerenciasCliente() {
    document.getElementById('cliente-sugerencias').classList.add('hidden');
}

document.addEventListener('click', e => {
    if (!e.target.closest('#buscar-cliente') && !e.target.closest('#cliente-sugerencias')) {
        ocultarSugerenciasCliente();
    }
});

// ── Cobrar ───────────────────────────────────────────
async function cobrar() {
    if (carrito.length === 0) return;
    const formaPago = document.querySelector('input[name="forma_pago"]:checked').value;
    const efectivo  = parseFloat(document.getElementById('monto-efectivo').value) || 0;

    if (formaPago === 'contado' && efectivo > 0) {
        const totalStr = document.getElementById('total-general').textContent.replace(/[^0-9]/g, '');
        const total    = parseFloat(totalStr) || 0;
        if (efectivo < total) { mostrarToast('El efectivo es menor al total', 'error'); return; }
    }

    const btn = document.getElementById('btn-cobrar');
    btn.disabled = true;
    document.getElementById('btn-cobrar-texto').textContent = 'Procesando...';

    const items = carrito.map(item => ({
        producto_id:     item.id,
        codigo:          item.codigo,
        descripcion:     item.nombre,
        cantidad:        item.cantidad,
        precio_unitario: item.precio_unitario,
        iva_pct:         item.iva_pct,
        descuento_pct:   item.descuento_pct,
        unidad:          'UN',
    }));

    const payload = {
        _token:     document.querySelector('meta[name="csrf-token"]').content,
        items:      items,
        forma_pago: formaPago,
        cliente_id: document.getElementById('cliente-id').value || null,
    };

    const res = await fetch('{{ route("pos.store") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': payload._token },
        body:    JSON.stringify(payload),
    });

    if (!res.ok) {
        const err = await res.json().catch(() => ({}));
        mostrarToast(err.message || 'Error al procesar la venta', 'error');
        btn.disabled = false;
        document.getElementById('btn-cobrar-texto').textContent = 'COBRAR';
        return;
    }

    const data = await res.json();
    // Abrir ticket en nueva ventana
    const ticketUrl = data.ticket_url + (efectivo > 0 ? `?efectivo=${efectivo}` : '');
    const ventana = window.open(ticketUrl, '_blank', 'width=420,height=700,scrollbars=yes');
    if (ventana) ventana.onload = () => ventana.print();

    mostrarToast('¡Venta registrada! ' + (data.factura_numero || ''), 'ok');
    limpiarCarrito();
    limpiarCliente();
    document.getElementById('monto-efectivo').value = '';
    calcularVuelto();
    btn.disabled = false;
    document.getElementById('btn-cobrar-texto').textContent = 'COBRAR';
}

// ── Toast ─────────────────────────────────────────────
function mostrarToast(texto, tipo = 'ok') {
    const toast = document.getElementById('toast');
    const icon  = document.getElementById('toast-icon');
    document.getElementById('toast-texto').textContent = texto;
    icon.className = 'fas ' + (tipo === 'ok' ? 'fa-check-circle text-emerald-400' : 'fa-exclamation-circle text-red-400');
    toast.classList.remove('hidden');
    clearTimeout(window._toastTimer);
    window._toastTimer = setTimeout(() => toast.classList.add('hidden'), 2500);
}

// ── Teclado global ───────────────────────────────────
document.addEventListener('keydown', e => {
    // Escape cierra modal
    if (e.key === 'Escape') cerrarModalCantidad();
    // F2 foco en búsqueda de producto
    if (e.key === 'F2') { e.preventDefault(); document.getElementById('buscar-producto').focus(); }
    // F4 cobrar
    if (e.key === 'F4') { e.preventDefault(); cobrar(); }
});
</script>
</body>
</html>
