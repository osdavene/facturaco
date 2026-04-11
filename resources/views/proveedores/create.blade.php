@extends('layouts.app')
@section('title', 'Nuevo Proveedor')
@section('page-title', 'Proveedores · Nuevo')

@section('content')
<div class="max-w-4xl mx-auto">

    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('proveedores.index') }}"
           class="w-9 h-9 bg-[#141c2e] border border-[#1e2d47] rounded-xl
                  flex items-center justify-center text-slate-400
                  hover:text-amber-500 hover:border-amber-500/50 transition-colors">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="font-display font-bold text-2xl">Nuevo Proveedor</h1>
            <p class="text-slate-500 text-sm">Completa la información del proveedor</p>
        </div>
    </div>

    <form method="POST" action="{{ route('proveedores.store') }}">
        @csrf

        <x-form-errors class="mb-4" />

        {{-- SECCIÓN 1 --}}
        <div class="card p-6 mb-4">
            <h2 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center text-black text-xs font-black">1</span>
                Identificación
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="form-label">Razón Social *</label>
                    <input type="text" name="razon_social"
                           value="{{ old('razon_social') }}"
                           placeholder="NOMBRE DE LA EMPRESA PROVEEDORA"
                           data-uppercase
                           class="form-input @error('razon_social') border-red-500 @enderror">
                    @error('razon_social') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Tipo Documento *</label>
                    <select name="tipo_documento"
                            class="form-input @error('tipo_documento') border-red-500 @enderror">
                        <option value="NIT" {{ old('tipo_documento','NIT')=='NIT' ? 'selected':'' }}>NIT</option>
                        <option value="CC"  {{ old('tipo_documento')=='CC' ? 'selected':'' }}>CC</option>
                        <option value="CE"  {{ old('tipo_documento')=='CE' ? 'selected':'' }}>CE</option>
                    </select>
                    @error('tipo_documento') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div class="col-span-2">
                        <label class="form-label">Número Documento *</label>
                        <input type="text" name="numero_documento"
                               value="{{ old('numero_documento') }}"
                               placeholder="900123456"
                               data-numeric
                               class="form-input @error('numero_documento') border-red-500 @enderror">
                        @error('numero_documento') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">DV</label>
                        <input type="text" name="digito_verificacion" maxlength="1"
                               value="{{ old('digito_verificacion') }}"
                               placeholder="0"
                               data-numeric
                               class="form-input">
                    </div>
                </div>
            </div>
        </div>

        {{-- SECCIÓN 2 --}}
        <div class="card p-6 mb-4">
            <h2 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center text-black text-xs font-black">2</span>
                Persona de Contacto
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach([
                    ['nombre_contacto', 'Nombre Contacto', 'text',  'JUAN PÉREZ',          true,  false],
                    ['cargo_contacto',  'Cargo',           'text',  'GERENTE COMERCIAL',    true,  false],
                    ['email',           'Email',           'email', 'ventas@proveedor.com', false, false],
                    ['telefono',        'Teléfono',        'text',  '601 1234567',          false, true],
                    ['celular',         'Celular',         'text',  '300 1234567',          false, true],
                    ['departamento',    'Departamento',    'text',  'CÓRDOBA',              true,  false],
                    ['municipio',       'Municipio',       'text',  'MONTERÍA',             true,  false],
                    ['direccion',       'Dirección',       'text',  'CRA 5 # 10-20',        true,  false],
                ] as [$name, $label, $type, $ph, $upper, $numeric])
                <div>
                    <label class="form-label">{{ $label }}</label>
                    <input type="{{ $type }}" name="{{ $name }}"
                           value="{{ old($name) }}"
                           placeholder="{{ $ph }}"
                           @if($upper) data-uppercase @endif
                           @if($numeric) data-numeric @endif
                           class="form-input">
                </div>
                @endforeach
            </div>
        </div>

        {{-- SECCIÓN 3 --}}
        <div class="card p-6 mb-4">
            <h2 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center text-black text-xs font-black">3</span>
                Información Tributaria
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="form-label">Régimen *</label>
                    <select name="regimen"
                            class="form-input @error('regimen') border-red-500 @enderror">
                        <option value="responsable_iva" {{ old('regimen','responsable_iva')=='responsable_iva' ? 'selected':'' }}>Responsable de IVA</option>
                        <option value="simple"          {{ old('regimen')=='simple' ? 'selected':'' }}>Régimen Simple</option>
                    </select>
                    @error('regimen') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">% ReteFuente</label>
                    <input type="text" inputmode="decimal" name="retefuente_pct"
                           value="{{ old('retefuente_pct', 0) }}"
                           data-numeric
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">% ReteICA</label>
                    <input type="text" inputmode="decimal" name="reteica_pct"
                           value="{{ old('reteica_pct', 0) }}"
                           data-numeric
                           class="form-input">
                </div>
            </div>
            <div class="flex flex-wrap gap-5">
                @foreach([['gran_contribuyente','Gran Contribuyente'],['autoretenedor','Autoretenedor']] as [$f,$l])
                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" name="{{ $f }}" value="1"
                           class="w-4 h-4 accent-amber-500" {{ old($f) ? 'checked':'' }}>
                    <span class="text-sm text-slate-400">{{ $l }}</span>
                </label>
                @endforeach
            </div>
        </div>

        {{-- SECCIÓN 4 --}}
        <div class="card p-6 mb-6">
            <h2 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center text-black text-xs font-black">4</span>
                Información Bancaria y Comercial
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="form-label">Banco</label>
                    <input type="text" name="banco"
                           value="{{ old('banco') }}"
                           placeholder="BANCOLOMBIA"
                           data-uppercase
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">Tipo Cuenta</label>
                    <select name="tipo_cuenta"
                            class="form-input">
                        <option value="">Seleccionar</option>
                        <option value="ahorros"   {{ old('tipo_cuenta')=='ahorros'   ? 'selected':'' }}>Ahorros</option>
                        <option value="corriente" {{ old('tipo_cuenta')=='corriente' ? 'selected':'' }}>Corriente</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">N° Cuenta</label>
                    <input type="text" name="cuenta_bancaria"
                           value="{{ old('cuenta_bancaria') }}"
                           placeholder="000-000000-00"
                           data-numeric
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">Plazo Pago (días)</label>
                    <input type="text" inputmode="decimal" name="plazo_pago"
                           value="{{ old('plazo_pago', 30) }}"
                           data-numeric
                           class="form-input">
                </div>
            </div>
            <div class="mt-4">
                <label class="form-label">Observaciones</label>
                <textarea name="observaciones" rows="2"
                          placeholder="NOTAS ADICIONALES..."
                          data-uppercase
                          class="form-input resize-none">{{ old('observaciones') }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('proveedores.index') }}"
               class="px-6 py-2.5 bg-[#1a2235] border border-[#1e2d47] rounded-xl
                      text-slate-400 hover:text-slate-200 text-sm transition-colors">
                Cancelar
            </a>
            <button type="submit"
                    class="px-8 py-2.5 bg-amber-500 hover:bg-amber-600 text-black
                           font-semibold rounded-xl transition-colors flex items-center gap-2">
                <i class="fas fa-save"></i> Guardar Proveedor
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
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
</script>
@endpush
@endsection