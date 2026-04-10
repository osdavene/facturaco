@extends('layouts.app')
@section('title', 'Nuevo Cliente')
@section('page-title', 'Clientes · Nuevo')

@section('content')
<div class="max-w-4xl mx-auto">

    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('clientes.index') }}"
           class="w-9 h-9 bg-[#141c2e] border border-[#1e2d47] rounded-xl
                  flex items-center justify-center text-slate-400
                  hover:text-amber-500 hover:border-amber-500/50 transition-colors">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="font-display font-bold text-2xl">Nuevo Cliente</h1>
            <p class="text-slate-500 text-sm">Completa la información del cliente</p>
        </div>
    </div>

    <form method="POST" action="{{ route('clientes.store') }}">
        @csrf

        {{-- SECCIÓN 1 --}}
        <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-6 mb-4">
            <h2 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center text-black text-xs font-black">1</span>
                Tipo de Persona
            </h2>
            <div class="grid grid-cols-2 gap-3 mb-5">
                <label class="cursor-pointer">
                    <input type="radio" name="tipo_persona" value="natural" class="peer sr-only"
                           {{ old('tipo_persona','natural')=='natural' ? 'checked':'' }}>
                    <div class="border-2 border-[#1e2d47] rounded-xl p-4 text-center
                                peer-checked:border-amber-500 peer-checked:bg-amber-500/5
                                hover:border-amber-500/50 transition-colors">
                        <i class="fas fa-user text-2xl mb-2 block text-slate-400"></i>
                        <div class="font-semibold text-sm">Persona Natural</div>
                        <div class="text-xs text-slate-500 mt-1">CC, CE, PP, TI</div>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="tipo_persona" value="juridica" class="peer sr-only"
                           {{ old('tipo_persona')=='juridica' ? 'checked':'' }}>
                    <div class="border-2 border-[#1e2d47] rounded-xl p-4 text-center
                                peer-checked:border-amber-500 peer-checked:bg-amber-500/5
                                hover:border-amber-500/50 transition-colors">
                        <i class="fas fa-building text-2xl mb-2 block text-slate-400"></i>
                        <div class="font-semibold text-sm">Persona Jurídica</div>
                        <div class="text-xs text-slate-500 mt-1">NIT</div>
                    </div>
                </label>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Tipo Documento *</label>
                    <select name="tipo_documento"
                            class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                                   text-sm text-slate-200 focus:outline-none focus:border-amber-500">
                        <option value="CC"  {{ old('tipo_documento','CC')=='CC'  ? 'selected':'' }}>CC - Cédula de Ciudadanía</option>
                        <option value="NIT" {{ old('tipo_documento')=='NIT' ? 'selected':'' }}>NIT</option>
                        <option value="CE"  {{ old('tipo_documento')=='CE'  ? 'selected':'' }}>CE - Cédula Extranjería</option>
                        <option value="PP"  {{ old('tipo_documento')=='PP'  ? 'selected':'' }}>PP - Pasaporte</option>
                        <option value="TI"  {{ old('tipo_documento')=='TI'  ? 'selected':'' }}>TI - Tarjeta Identidad</option>
                        <option value="PEP" {{ old('tipo_documento')=='PEP' ? 'selected':'' }}>PEP</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Número Documento *</label>
                    <input type="text" name="numero_documento"
                           value="{{ old('numero_documento') }}"
                           placeholder="EJ: 900123456"
                           data-uppercase
                           class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                                  text-sm text-slate-200 placeholder-slate-600
                                  focus:outline-none focus:border-amber-500
                                  @error('numero_documento') border-red-500 @enderror">
                    @error('numero_documento') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">
                        Dígito Verificación <span class="text-slate-600">(Solo NIT)</span>
                    </label>
                    <input type="text" name="digito_verificacion" maxlength="1"
                           value="{{ old('digito_verificacion') }}" placeholder="0-9"
                           data-numeric
                           class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                                  text-sm text-slate-200 placeholder-slate-600
                                  focus:outline-none focus:border-amber-500">
                </div>
            </div>
        </div>

        {{-- SECCIÓN 2 --}}
        <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-6 mb-4">
            <h2 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center text-black text-xs font-black">2</span>
                Datos del Cliente
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">
                        Razón Social <span class="text-slate-600">(Jurídica)</span>
                    </label>
                    <input type="text" name="razon_social"
                           value="{{ old('razon_social') }}"
                           placeholder="NOMBRE DE LA EMPRESA"
                           data-uppercase
                           class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                                  text-sm text-slate-200 placeholder-slate-600
                                  focus:outline-none focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">
                        Nombres <span class="text-slate-600">(Natural)</span>
                    </label>
                    <input type="text" name="nombres"
                           value="{{ old('nombres') }}"
                           placeholder="NOMBRES"
                           data-uppercase
                           class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                                  text-sm text-slate-200 placeholder-slate-600
                                  focus:outline-none focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">
                        Apellidos <span class="text-slate-600">(Natural)</span>
                    </label>
                    <input type="text" name="apellidos"
                           value="{{ old('apellidos') }}"
                           placeholder="APELLIDOS"
                           data-uppercase
                           class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                                  text-sm text-slate-200 placeholder-slate-600
                                  focus:outline-none focus:border-amber-500">
                </div>
            </div>
        </div>

        {{-- SECCIÓN 3 --}}
        <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-6 mb-4">
            <h2 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center text-black text-xs font-black">3</span>
                Información Tributaria
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Régimen Tributario *</label>
                    <select name="regimen"
                            class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                                   text-sm text-slate-200 focus:outline-none focus:border-amber-500">
                        <option value="simple"          {{ old('regimen','simple')=='simple' ? 'selected':'' }}>Régimen Simple</option>
                        <option value="responsable_iva" {{ old('regimen')=='responsable_iva' ? 'selected':'' }}>Responsable de IVA</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">% ReteFuente</label>
                    <input type="text" inputmode="decimal" name="retefuente_pct"
                           value="{{ old('retefuente_pct', 0) }}"
                           data-numeric
                           class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                                  text-sm text-slate-200 focus:outline-none focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">% ReteICA</label>
                    <input type="text" inputmode="decimal" name="reteica_pct"
                           value="{{ old('reteica_pct', 0) }}"
                           data-numeric
                           class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                                  text-sm text-slate-200 focus:outline-none focus:border-amber-500">
                </div>
            </div>
            <div class="flex flex-wrap gap-5">
                @foreach([
                    ['responsable_iva',    'Responsable de IVA'],
                    ['gran_contribuyente', 'Gran Contribuyente'],
                    ['autoretenedor',      'Autoretenedor'],
                ] as [$field, $label])
                <label class="flex items-center gap-2.5 cursor-pointer group">
                    <input type="checkbox" name="{{ $field }}" value="1"
                           class="w-4 h-4 accent-amber-500"
                           {{ old($field) ? 'checked':'' }}>
                    <span class="text-sm text-slate-400 group-hover:text-slate-200 transition-colors">
                        {{ $label }}
                    </span>
                </label>
                @endforeach
            </div>
        </div>

        {{-- SECCIÓN 4 --}}
        <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-6 mb-4">
            <h2 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center text-black text-xs font-black">4</span>
                Contacto y Dirección
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           placeholder="correo@empresa.com"
                           class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                                  text-sm text-slate-200 placeholder-slate-600
                                  focus:outline-none focus:border-amber-500
                                  @error('email') border-red-500 @enderror">
                    @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                @foreach([
                    ['telefono',     'Teléfono',     '601 1234567'],
                    ['celular',      'Celular',      '300 1234567'],
                    ['departamento', 'Departamento', 'CÓRDOBA'],
                    ['municipio',    'Municipio',    'MONTERÍA'],
                    ['direccion',    'Dirección',    'CRA 5 # 10-20'],
                ] as [$name, $label, $ph])
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">{{ $label }}</label>
                    <input type="text" name="{{ $name }}"
                           value="{{ old($name) }}"
                           placeholder="{{ $ph }}"
                           data-uppercase
                           class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                                  text-sm text-slate-200 placeholder-slate-600
                                  focus:outline-none focus:border-amber-500">
                </div>
                @endforeach
            </div>
        </div>

        {{-- SECCIÓN 5 --}}
        <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-6 mb-6">
            <h2 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center text-black text-xs font-black">5</span>
                Condiciones Comerciales
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Plazo de Pago (días)</label>
                    <input type="text" inputmode="decimal" name="plazo_pago"
                           value="{{ old('plazo_pago', 0) }}"
                           data-numeric
                           class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                                  text-sm text-slate-200 focus:outline-none focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Cupo de Crédito ($)</label>
                    <input type="text" inputmode="decimal" name="cupo_credito"
                           value="{{ old('cupo_credito', 0) }}"
                           data-numeric
                           class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                                  text-sm text-slate-200 focus:outline-none focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">
                        Lista de Precios
                    </label>
                    <select name="lista_precio"
                            class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                                   text-sm text-slate-200 focus:outline-none focus:border-amber-500">
                        <option value="general"   {{ old('lista_precio', 'general')   === 'general'   ? 'selected' : '' }}>
                            Lista General (Precio 1)
                        </option>
                        <option value="mayorista" {{ old('lista_precio', 'general')   === 'mayorista' ? 'selected' : '' }}>
                            Mayorista (Precio 2)
                        </option>
                        <option value="especial"  {{ old('lista_precio', 'general')   === 'especial'  ? 'selected' : '' }}>
                            Especial (Precio 3)
                        </option>
                    </select>
                    <p class="text-xs text-slate-600 mt-1">
                        Se aplica automáticamente al facturar a este cliente.
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Observaciones</label>
                    <textarea name="observaciones" rows="1"                              placeholder="NOTAS ADICIONALES..."
                              data-uppercase
                              class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                                     text-sm text-slate-200 placeholder-slate-600
                                     focus:outline-none focus:border-amber-500 resize-none">{{ old('observaciones') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('clientes.index') }}"
               class="px-6 py-2.5 bg-[#1a2235] border border-[#1e2d47] rounded-xl
                      text-slate-400 hover:text-slate-200 text-sm transition-colors">
                Cancelar
            </a>
            <button type="submit"
                    class="px-8 py-2.5 bg-amber-500 hover:bg-amber-600 text-black
                           font-semibold rounded-xl transition-colors flex items-center gap-2">
                <i class="fas fa-save"></i> Guardar Cliente
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Mayúsculas en campos con data-uppercase
// Numérico limpio en campos con data-numeric
document.querySelectorAll('[data-uppercase]').forEach(el => {
    el.addEventListener('input', function() {
        const pos = this.selectionStart;
        this.value = this.value.toUpperCase();
        this.setSelectionRange(pos, pos);
    });
});
document.querySelectorAll('[data-numeric]').forEach(el => {
    el.addEventListener('input', function() {
        // Solo permite números, punto y coma
        this.value = this.value.replace(/[^0-9.,]/g, '');
    });
});
</script>
@endpush
@endsection