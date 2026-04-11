@extends('layouts.app')
@section('title', 'Nueva Unidad de Medida')
@section('page-title', 'Configuración · Nueva Unidad de Medida')

@section('content')
<div class="max-w-xl mx-auto">

    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('unidades.index') }}"
           class="w-9 h-9 bg-[#141c2e] border border-[#1e2d47] rounded-xl
                  flex items-center justify-center text-slate-400
                  hover:text-amber-500 hover:border-amber-500/50 transition-colors">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="font-display font-bold text-2xl">Nueva Unidad de Medida</h1>
            <p class="text-slate-500 text-sm">Define cómo se miden tus productos</p>
        </div>
    </div>

    <form method="POST" action="{{ route('unidades.store') }}">
        @csrf

        <div class="card p-6 space-y-5">

            @if($errors->any())
            <div class="bg-red-500/10 border border-red-500/30 text-red-400
                        rounded-xl px-4 py-3 flex items-center gap-3 text-sm">
                <i class="fas fa-exclamation-circle flex-shrink-0"></i>
                {{ $errors->first() }}
            </div>
            @endif

            {{-- Nombre --}}
            <div>
                <label class="form-label">
                    Nombre de la Unidad *
                </label>
                <input type="text" name="nombre"
                       value="{{ old('nombre') }}"
                       placeholder="EJ: UNIDAD, KILOGRAMO, LITRO, METRO..."
                       data-uppercase
                       autofocus
                       class="form-input @error('nombre') border-red-500 @enderror">
                @error('nombre')
                <p class="text-red-400 text-xs mt-1.5 flex items-center gap-1">
                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                </p>
                @enderror
            </div>

            {{-- Abreviatura --}}
            <div>
                <label class="form-label">
                    Abreviatura *
                    <span class="text-slate-600 normal-case font-normal">(máx. 10 caracteres)</span>
                </label>
                <input type="text" name="abreviatura"
                       value="{{ old('abreviatura') }}"
                       placeholder="EJ: UN, KG, LT, MT, M2..."
                       data-uppercase
                       maxlength="10"
                       class="form-input font-mono tracking-widest
                              focus:outline-none focus:border-amber-500 transition-colors
                              @error('abreviatura') border-red-500 @enderror">
                @error('abreviatura')
                <p class="text-red-400 text-xs mt-1.5 flex items-center gap-1">
                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                </p>
                @enderror
                <p class="text-xs text-slate-600 mt-1">La abreviatura se imprime en las facturas junto al producto.</p>
            </div>

            {{-- Referencia rápida --}}
            <div class="bg-amber-500/5 border border-amber-500/20 rounded-xl px-4 py-3">
                <p class="text-xs text-slate-500 mb-2 font-semibold uppercase tracking-wider">Unidades comunes en Colombia</p>
                <div class="grid grid-cols-4 gap-1.5">
                    @foreach([
                        ['UN','Unidad'],['KG','Kilogramo'],['GR','Gramo'],['LB','Libra'],
                        ['LT','Litro'],['ML','Mililitro'],['GL','Galón'],['MT','Metro'],
                        ['M2','Metro²'],['CJ','Caja'],['BL','Bulto'],['PQ','Paquete'],
                        ['DC','Docena'],['PR','Par'],['SV','Servicio'],['HR','Hora'],
                    ] as [$abr, $nom])
                    <button type="button"
                            onclick="document.querySelector('[name=abreviatura]').value='{{ $abr }}';
                                     document.querySelector('[name=nombre]').value='{{ strtoupper($nom) }}';"
                            class="text-left text-xs bg-[#1a2235] rounded-lg px-2 py-1.5
                                   hover:bg-amber-500/10 hover:border-amber-500/30
                                   border border-transparent transition-colors">
                        <span class="font-bold text-amber-400 font-mono">{{ $abr }}</span>
                        <span class="text-slate-500 ml-1">{{ $nom }}</span>
                    </button>
                    @endforeach
                </div>
                <p class="text-xs text-slate-600 mt-2">Haz clic en una unidad para autocompletar los campos.</p>
            </div>

        </div>

        <div class="flex gap-3 mt-4">
            <a href="{{ route('unidades.index') }}"
               class="flex-1 text-center bg-[#141c2e] border border-[#1e2d47]
                      text-slate-400 font-semibold text-sm py-2.5 rounded-xl
                      hover:border-slate-500 transition-colors">
                Cancelar
            </a>
            <button type="submit"
                    class="flex-1 bg-amber-500 hover:bg-amber-600 text-black
                           font-bold text-sm py-2.5 rounded-xl transition-colors">
                <i class="fas fa-save mr-2"></i> Guardar Unidad
            </button>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-uppercase]').forEach(function(input) {
    input.addEventListener('input', function() {
        const start = this.selectionStart;
        const end   = this.selectionEnd;
        this.value  = this.value.toUpperCase();
        this.setSelectionRange(start, end);
    });
});
</script>
@endpush