@props([
    'departamento'      => null,
    'municipio'         => null,
    'nameDepartamento'  => 'departamento',
    'nameMunicipio'     => 'municipio',
    'required'          => false,
    'labelDepartamento' => 'Departamento',
    'labelMunicipio'    => 'Municipio / Ciudad',
])

<div
    x-data="ubicacionSelector(@js($departamento), @js($municipio))"
    x-init="init()"
    class="contents"
>
    {{-- Departamento --}}
    <div>
        <label class="form-label">
            {{ $labelDepartamento }}{{ $required ? ' *' : '' }}
        </label>
        <select
            name="{{ $nameDepartamento }}"
            x-model="depNombre"
            @change="onDepChange()"
            class="form-input"
        >
            <option value="">— Seleccionar departamento —</option>
            @foreach(\App\Models\Departamento::orderBy('nombre')->get() as $dep)
                <option value="{{ $dep->nombre }}">{{ $dep->nombre }}</option>
            @endforeach
        </select>
    </div>

    {{-- Municipio --}}
    <div>
        <label class="form-label">
            {{ $labelMunicipio }}{{ $required ? ' *' : '' }}
        </label>
        <div class="relative">
            <select
                name="{{ $nameMunicipio }}"
                x-model="munNombre"
                class="form-input"
                :disabled="!depNombre"
            >
                <option value="">— Seleccionar municipio —</option>
                <template x-for="m in municipios" :key="m">
                    <option :value="m" x-text="m"></option>
                </template>
            </select>
            <span
                x-show="cargando"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"
            >
                <i class="fas fa-spinner fa-spin"></i>
            </span>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
function ubicacionSelector(depInicial, munInicial) {
    return {
        depNombre: depInicial ?? '',
        munNombre: munInicial ?? '',
        municipios: [],
        cargando: false,

        async init() {
            if (this.depNombre) {
                await this.cargarMunicipios();
                this.munNombre = munInicial ?? '';
            }
        },

        async onDepChange() {
            this.munNombre = '';
            this.municipios = [];
            await this.cargarMunicipios();
        },

        async cargarMunicipios() {
            if (!this.depNombre) return;
            this.cargando = true;
            try {
                const url = `/api/municipios?departamento=${encodeURIComponent(this.depNombre)}`;
                const res = await fetch(url);
                this.municipios = await res.json();
            } finally {
                this.cargando = false;
            }
        }
    };
}
</script>
@endpush
@endonce
