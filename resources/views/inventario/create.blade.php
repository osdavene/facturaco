@extends('layouts.app')
@section('title', 'Nuevo Producto')
@section('page-title', 'Inventario · Nuevo Producto')

@section('content')
<div class="max-w-4xl mx-auto">

    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('inventario.index') }}"
           class="w-9 h-9 bg-[#141c2e] border border-[#1e2d47] rounded-xl
                  flex items-center justify-center text-slate-400
                  hover:text-amber-500 hover:border-amber-500/50 transition-colors">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="font-display font-bold text-2xl">Nuevo Producto</h1>
            <p class="text-slate-500 text-sm">Completa la información del producto</p>
        </div>
    </div>

    <form method="POST" action="{{ route('inventario.store') }}">
        @csrf

        {{-- SECCIÓN 1 --}}
        <div class="card p-6 mb-4">
            <h2 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center text-black text-xs font-black">1</span>
                Información Básica
            </h2>

            <div class="flex items-center gap-3 mb-5 p-3 bg-[#1a2235] rounded-xl">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="es_servicio" value="1"
                           class="w-4 h-4 accent-amber-500"
                           {{ old('es_servicio') ? 'checked':'' }}
                           onchange="toggleServicio(this)">
                    <span class="text-sm text-slate-300 font-medium">
                        Es un <strong>Servicio</strong> (no maneja stock físico)
                    </span>
                </label>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Código *</label>
                    <input type="text" name="codigo"
                           value="{{ old('codigo') }}"
                           placeholder="EJ: PROD-001"
                           style="text-transform:uppercase"
                           class="form-input @error('codigo') border-red-500 @enderror">
                    @error('codigo') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Código de Barras</label>
                    <input type="text" name="codigo_barras"
                           value="{{ old('codigo_barras') }}"
                           placeholder="EAN-13 O INTERNO"
                           style="text-transform:uppercase"
                           class="form-input">
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre"
                           value="{{ old('nombre') }}"
                           placeholder="NOMBRE DEL PRODUCTO"
                           style="text-transform:uppercase"
                           class="form-input @error('nombre') border-red-500 @enderror">
                    @error('nombre') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Categoría</label>
                    <select name="categoria_id"
                            class="form-input">
                        <option value="">Sin categoría</option>
                        @foreach($categorias as $cat)
                        <option value="{{ $cat->id }}" {{ old('categoria_id')==$cat->id ? 'selected':'' }}>
                            {{ $cat->nombre }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Unidad de Medida</label>
                    <select name="unidad_medida_id"
                            class="form-input">
                        <option value="">Seleccionar</option>
                        @foreach($unidades as $u)
                        <option value="{{ $u->id }}" {{ old('unidad_medida_id')==$u->id ? 'selected':'' }}>
                            {{ $u->nombre }} ({{ $u->abreviatura }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" rows="2"
                              placeholder="DESCRIPCIÓN OPCIONAL..."
                              style="text-transform:uppercase"
                              class="form-input resize-none">{{ old('descripcion') }}</textarea>
                </div>
            </div>
        </div>

        {{-- SECCIÓN 2: Precios --}}
        <div class="card p-6 mb-4">
            <h2 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center text-black text-xs font-black">2</span>
                Precios e IVA
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
                <div>
                    <label class="form-label">Precio Compra</label>
                    <input type="text" inputmode="decimal" name="precio_compra"
                           value="{{ old('precio_compra', 0) }}"
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">
                        Precio Venta * <span class="text-slate-600">(principal)</span>
                    </label>
                    <input type="text" inputmode="decimal" name="precio_venta"
                           value="{{ old('precio_venta', 0) }}"
                           class="form-input @error('precio_venta') border-red-500 @enderror">
                    @error('precio_venta') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">
                        Precio 2 <span class="text-slate-600">(mayorista)</span>
                    </label>
                    <input type="text" inputmode="decimal" name="precio_venta2"
                           value="{{ old('precio_venta2', 0) }}"
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">
                        Precio 3 <span class="text-slate-600">(especial)</span>
                    </label>
                    <input type="text" inputmode="decimal" name="precio_venta3"
                           value="{{ old('precio_venta3', 0) }}"
                           class="form-input">
                </div>
            </div>
            <div class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="form-label">% IVA</label>
                    <select name="iva_pct"
                            class="form-input">
                        <option value="0"  {{ old('iva_pct','19')=='0'  ? 'selected':'' }}>0% - Excluido</option>
                        <option value="5"  {{ old('iva_pct')=='5'       ? 'selected':'' }}>5%</option>
                        <option value="19" {{ old('iva_pct','19')=='19' ? 'selected':'' }}>19% - General</option>
                    </select>
                </div>
                <label class="flex items-center gap-2.5 cursor-pointer pb-2">
                    <input type="checkbox" name="incluye_iva" value="1"
                           class="w-4 h-4 accent-amber-500"
                           {{ old('incluye_iva') ? 'checked':'' }}>
                    <span class="text-sm text-slate-400">El precio ya incluye IVA</span>
                </label>
            </div>
        </div>

        {{-- SECCIÓN 3: Stock --}}
        <div id="seccion-stock" class="card p-6 mb-6">
            <h2 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center text-black text-xs font-black">3</span>
                Stock e Inventario
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <label class="form-label">Stock Inicial</label>
                    <input type="text" inputmode="decimal" name="stock_actual"
                           value="{{ old('stock_actual', 0) }}"
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">Stock Mínimo</label>
                    <input type="text" inputmode="decimal" name="stock_minimo"
                           value="{{ old('stock_minimo', 0) }}"
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">Stock Máximo</label>
                    <input type="text" inputmode="decimal" name="stock_maximo"
                           value="{{ old('stock_maximo', 0) }}"
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">Ubicación</label>
                    <input type="text" name="ubicacion"
                           value="{{ old('ubicacion') }}"
                           placeholder="BODEGA A / ESTANTE 3"
                           style="text-transform:uppercase"
                           class="form-input">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('inventario.index') }}"
               class="px-6 py-2.5 bg-[#1a2235] border border-[#1e2d47] rounded-xl
                      text-slate-400 hover:text-slate-200 text-sm transition-colors">
                Cancelar
            </a>
            <button type="submit"
                    class="px-8 py-2.5 bg-amber-500 hover:bg-amber-600 text-black
                           font-semibold rounded-xl transition-colors flex items-center gap-2">
                <i class="fas fa-save"></i> Guardar Producto
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Mayúsculas automáticas en todos los inputs de texto
document.querySelectorAll('input[type="text"], textarea').forEach(el => {
    el.addEventListener('input', function() {
        // Solo para campos que no son numéricos
        if (this.inputMode === 'decimal') return;
        const pos = this.selectionStart;
        this.value = this.value.toUpperCase();
        this.setSelectionRange(pos, pos);
    });
});

function toggleServicio(cb) {
    const seccion = document.getElementById('seccion-stock');
    seccion.style.opacity      = cb.checked ? '0.4' : '1';
    seccion.style.pointerEvents = cb.checked ? 'none' : 'auto';
}
</script>
@endpush
@endsection