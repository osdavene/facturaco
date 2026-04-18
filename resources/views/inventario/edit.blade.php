@extends('layouts.app')
@section('title', 'Editar Producto')
@section('page-title', 'Inventario · Editar')

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
            <h1 class="font-display font-bold text-2xl">Editar Producto</h1>
            <p class="text-slate-500 text-sm uppercase">{{ $inventario->nombre }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('inventario.update', $inventario) }}">
        @csrf @method('PUT')

        <x-form-errors class="mb-4" />

        {{-- SECCIÓN 1 --}}
        <div class="card p-6 mb-4">
            <h2 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center text-black text-xs font-black">1</span>
                Información Básica
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Código *</label>
                    <input type="text" name="codigo"
                           value="{{ old('codigo', $inventario->codigo) }}"
                           style="text-transform:uppercase"
                           class="form-input @error('codigo') border-red-500 @enderror">
                    @error('codigo') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Código de Barras</label>
                    <input type="text" name="codigo_barras"
                           value="{{ old('codigo_barras', $inventario->codigo_barras) }}"
                           style="text-transform:uppercase"
                           class="form-input">
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre"
                           value="{{ old('nombre', $inventario->nombre) }}"
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
                        <option value="{{ $cat->id }}"
                            {{ old('categoria_id', $inventario->categoria_id)==$cat->id ? 'selected':'' }}>
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
                        <option value="{{ $u->id }}"
                            {{ old('unidad_medida_id', $inventario->unidad_medida_id)==$u->id ? 'selected':'' }}>
                            {{ $u->nombre }} ({{ $u->abreviatura }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Estado</label>
                    <select name="activo"
                            class="form-input">
                        <option value="1" {{ old('activo', $inventario->activo) ? 'selected':'' }}>Activo</option>
                        <option value="0" {{ !old('activo', $inventario->activo) ? 'selected':'' }}>Inactivo</option>
                    </select>
                </div>
                <div class="flex items-end pb-1">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="es_servicio" value="1"
                               class="w-4 h-4 accent-amber-500"
                               {{ old('es_servicio', $inventario->es_servicio) ? 'checked':'' }}>
                        <span class="text-sm text-slate-400">Es un Servicio</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- SECCIÓN 2 --}}
        <div class="card p-6 mb-4">
            <h2 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center text-black text-xs font-black">2</span>
                Precios e IVA
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
                @foreach([
                    ['precio_compra', 'Precio Compra'],
                    ['precio_venta',  'Precio Venta *'],
                    ['precio_venta2', 'Precio 2'],
                    ['precio_venta3', 'Precio 3'],
                ] as [$field, $label])
                <div>
                    <label class="form-label">{{ $label }}</label>
                    <input type="text" inputmode="decimal" name="{{ $field }}"
                           value="{{ old($field, $inventario->$field) }}"
                           class="form-input">
                </div>
                @endforeach
            </div>
            <div class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="form-label">% IVA</label>
                    <select name="iva_pct"
                            class="form-input">
                        <option value="0"  {{ old('iva_pct',$inventario->iva_pct)=='0'  ? 'selected':'' }}>0%</option>
                        <option value="5"  {{ old('iva_pct',$inventario->iva_pct)=='5'  ? 'selected':'' }}>5%</option>
                        <option value="19" {{ old('iva_pct',$inventario->iva_pct)=='19' ? 'selected':'' }}>19%</option>
                    </select>
                </div>
                <label class="flex items-center gap-2.5 cursor-pointer pb-2">
                    <input type="checkbox" name="incluye_iva" value="1"
                           class="w-4 h-4 accent-amber-500"
                           {{ old('incluye_iva', $inventario->incluye_iva) ? 'checked':'' }}>
                    <span class="text-sm text-slate-400">El precio ya incluye IVA</span>
                </label>
            </div>
        </div>

        {{-- SECCIÓN 3 --}}
        <div class="card p-6 mb-6">
            <h2 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center text-black text-xs font-black">3</span>
                Stock e Inventario
            </h2>
            <div class="bg-amber-500/5 border border-amber-500/20 rounded-xl px-4 py-3 mb-4 text-xs text-amber-400">
                <i class="fas fa-info-circle mr-1"></i>
                Para modificar el stock usa <strong>Ajustar Stock</strong> en el detalle. Aquí solo puedes cambiar los límites.
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <label class="form-label">Stock Actual</label>
                    <input type="text" value="{{ $inventario->stock_actual }}" disabled
                           class="w-full bg-[#0f1623] border border-[#1e2d47] rounded-xl px-4 py-2.5
                                  text-sm text-slate-500 cursor-not-allowed">
                </div>
                <div>
                    <label class="form-label">Stock Mínimo</label>
                    <input type="text" inputmode="decimal" name="stock_minimo"
                           value="{{ old('stock_minimo', $inventario->stock_minimo) }}"
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">Stock Máximo</label>
                    <input type="text" inputmode="decimal" name="stock_maximo"
                           value="{{ old('stock_maximo', $inventario->stock_maximo) }}"
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">Ubicación</label>
                    <input type="text" name="ubicacion"
                           value="{{ old('ubicacion', $inventario->ubicacion) }}"
                           style="text-transform:uppercase"
                           class="form-input">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <button type="button"
                    onclick="document.getElementById('form-eliminar-{{ $inventario->id }}').submit()"
                    class="px-5 py-2.5 bg-red-500/10 border border-red-500/30
                           text-red-400 hover:bg-red-500/20 rounded-xl text-sm
                           flex items-center gap-2 transition-colors">
                <i class="fas fa-trash"></i> Eliminar
            </button>
            <div class="flex gap-3">
                <a href="{{ route('inventario.show', $inventario) }}"
                   class="px-6 py-2.5 bg-[#1a2235] border border-[#1e2d47] rounded-xl
                          text-slate-400 hover:text-slate-200 text-sm transition-colors">
                    Cancelar
                </a>
                <button type="submit"
                        class="px-8 py-2.5 bg-amber-500 hover:bg-amber-600 text-black
                               font-semibold rounded-xl transition-colors flex items-center gap-2">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </form>

    {{-- Form de eliminar FUERA del form principal para evitar forms anidados --}}
    <form id="form-eliminar-{{ $inventario->id }}"
          method="POST" action="{{ route('inventario.destroy', $inventario) }}"
          onsubmit="return confirm('¿Eliminar {{ $inventario->nombre }}?')"
          class="hidden">
        @csrf @method('DELETE')
    </form>
</div>

@push('scripts')
<script>
document.querySelectorAll('input[type="text"], textarea').forEach(el => {
    if (el.disabled) return;
    el.addEventListener('input', function() {
        if (this.inputMode === 'decimal') return;
        const pos = this.selectionStart;
        this.value = this.value.toUpperCase();
        this.setSelectionRange(pos, pos);
    });
});
</script>
@endpush
@endsection