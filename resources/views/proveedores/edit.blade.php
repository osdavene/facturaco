@extends('layouts.app')
@section('title', 'Editar Proveedor')
@section('page-title', 'Proveedores · Editar')

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
            <h1 class="font-display font-bold text-2xl">Editar Proveedor</h1>
            <p class="text-slate-500 text-sm uppercase">{{ $proveedor->razon_social }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('proveedores.update', $proveedor) }}">
        @csrf @method('PUT')

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
                           value="{{ old('razon_social',$proveedor->razon_social) }}"
                           data-uppercase
                           class="form-input @error('razon_social') border-red-500 @enderror">
                    @error('razon_social') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Tipo Documento</label>
                    <select name="tipo_documento"
                            class="form-input">
                        @foreach(['NIT','CC','CE'] as $td)
                        <option value="{{ $td }}" {{ old('tipo_documento',$proveedor->tipo_documento)==$td ? 'selected':'' }}>{{ $td }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div class="col-span-2">
                        <label class="form-label">Número Documento *</label>
                        <input type="text" name="numero_documento"
                               value="{{ old('numero_documento',$proveedor->numero_documento) }}"
                               data-numeric
                               class="form-input">
                    </div>
                    <div>
                        <label class="form-label">DV</label>
                        <input type="text" name="digito_verificacion" maxlength="1"
                               value="{{ old('digito_verificacion',$proveedor->digito_verificacion) }}"
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
                Contacto y Dirección
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach([
                    ['nombre_contacto','Nombre Contacto','text',  true,  false],
                    ['cargo_contacto', 'Cargo',          'text',  true,  false],
                    ['email',          'Email',          'email', false, false],
                    ['telefono',       'Teléfono',       'text',  false, true],
                    ['celular',        'Celular',        'text',  false, true],
                    ['departamento',   'Departamento',   'text',  true,  false],
                    ['municipio',      'Municipio',      'text',  true,  false],
                    ['direccion',      'Dirección',      'text',  true,  false],
                ] as [$name, $label, $type, $upper, $numeric])
                <div>
                    <label class="form-label">{{ $label }}</label>
                    <input type="{{ $type }}" name="{{ $name }}"
                           value="{{ old($name,$proveedor->$name) }}"
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
                Tributario y Bancario
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <div>
                    <label class="form-label">Régimen</label>
                    <select name="regimen"
                            class="form-input">
                        <option value="responsable_iva" {{ old('regimen',$proveedor->regimen)=='responsable_iva' ? 'selected':'' }}>Resp. IVA</option>
                        <option value="simple"          {{ old('regimen',$proveedor->regimen)=='simple' ? 'selected':'' }}>Simple</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Banco</label>
                    <input type="text" name="banco"
                           value="{{ old('banco',$proveedor->banco) }}"
                           data-uppercase
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">Tipo Cuenta</label>
                    <select name="tipo_cuenta"
                            class="form-input">
                        <option value="">Seleccionar</option>
                        <option value="ahorros"   {{ old('tipo_cuenta',$proveedor->tipo_cuenta)=='ahorros'   ? 'selected':'' }}>Ahorros</option>
                        <option value="corriente" {{ old('tipo_cuenta',$proveedor->tipo_cuenta)=='corriente' ? 'selected':'' }}>Corriente</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">N° Cuenta</label>
                    <input type="text" name="cuenta_bancaria"
                           value="{{ old('cuenta_bancaria',$proveedor->cuenta_bancaria) }}"
                           data-numeric
                           class="form-input">
                </div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
                <div>
                    <label class="form-label">Estado</label>
                    <select name="activo"
                            class="form-input">
                        <option value="1" {{ old('activo',$proveedor->activo) ? 'selected':'' }}>Activo</option>
                        <option value="0" {{ !old('activo',$proveedor->activo) ? 'selected':'' }}>Inactivo</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Plazo (días)</label>
                    <input type="text" inputmode="decimal" name="plazo_pago"
                           value="{{ old('plazo_pago',$proveedor->plazo_pago) }}"
                           data-numeric
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">% ReteFuente</label>
                    <input type="text" inputmode="decimal" name="retefuente_pct"
                           value="{{ old('retefuente_pct',$proveedor->retefuente_pct) }}"
                           data-numeric
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">% ReteICA</label>
                    <input type="text" inputmode="decimal" name="reteica_pct"
                           value="{{ old('reteica_pct',$proveedor->reteica_pct) }}"
                           data-numeric
                           class="form-input">
                </div>
            </div>
            <div class="flex flex-wrap gap-5">
                @foreach([['gran_contribuyente','Gran Contribuyente'],['autoretenedor','Autoretenedor']] as [$f,$l])
                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" name="{{ $f }}" value="1"
                           class="w-4 h-4 accent-amber-500"
                           {{ old($f,$proveedor->$f) ? 'checked':'' }}>
                    <span class="text-sm text-slate-400">{{ $l }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <div class="flex items-center justify-between">
            <form method="POST" action="{{ route('proveedores.destroy', $proveedor) }}"
                  onsubmit="return confirm('¿Eliminar a {{ $proveedor->razon_social }}?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="px-5 py-2.5 bg-red-500/10 border border-red-500/30
                               text-red-400 hover:bg-red-500/20 rounded-xl text-sm
                               flex items-center gap-2 transition-colors">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </form>
            <div class="flex gap-3">
                <a href="{{ route('proveedores.index') }}"
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