@extends('layouts.app')
@section('title', 'Nueva Categoría')
@section('page-title', 'Configuración · Nueva Categoría')

@section('content')
<div class="max-w-xl mx-auto">

    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('categorias.index') }}"
           class="w-9 h-9 bg-[#141c2e] border border-[#1e2d47] rounded-xl
                  flex items-center justify-center text-slate-400
                  hover:text-amber-500 hover:border-amber-500/50 transition-colors">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="font-display font-bold text-2xl">Nueva Categoría</h1>
            <p class="text-slate-500 text-sm">Completa los datos de la nueva categoría</p>
        </div>
    </div>

    <form method="POST" action="{{ route('categorias.store') }}">
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
                    Nombre de la Categoría *
                </label>
                <input type="text" name="nombre"
                       value="{{ old('nombre') }}"
                       placeholder="EJ: ELECTRÓNICA, FERRETERÍA, ALIMENTOS..."
                       data-uppercase
                       autofocus
                       class="form-input @error('nombre') border-red-500 @enderror">
                @error('nombre')
                <p class="text-red-400 text-xs mt-1.5 flex items-center gap-1">
                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                </p>
                @enderror
            </div>

            {{-- Descripción --}}
            <div>
                <label class="form-label">
                    Descripción <span class="text-slate-600 normal-case font-normal">(opcional)</span>
                </label>
                <input type="text" name="descripcion"
                       value="{{ old('descripcion') }}"
                       placeholder="BREVE DESCRIPCIÓN DE LA CATEGORÍA"
                       data-uppercase
                       class="form-input @error('descripcion') border-red-500 @enderror">
                @error('descripcion')
                <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tip --}}
            <div class="bg-amber-500/5 border border-amber-500/20 rounded-xl px-4 py-3
                        text-xs text-slate-500 flex items-start gap-2">
                <i class="fas fa-lightbulb text-amber-500 mt-0.5 flex-shrink-0"></i>
                <span>
                    Esta categoría aparecerá en el campo <strong class="text-slate-400">Categoría</strong>
                    del formulario de <strong class="text-slate-400">Nuevo Producto</strong>.
                    Ejemplos: PAPELERÍA, ELECTRÓNICA, SERVICIOS, FERRETERÍA...
                </span>
            </div>

        </div>

        <div class="flex gap-3 mt-4">
            <a href="{{ route('categorias.index') }}"
               class="flex-1 text-center bg-[#141c2e] border border-[#1e2d47]
                      text-slate-400 font-semibold text-sm py-2.5 rounded-xl
                      hover:border-slate-500 transition-colors">
                Cancelar
            </a>
            <button type="submit"
                    class="flex-1 bg-amber-500 hover:bg-amber-600 text-black
                           font-bold text-sm py-2.5 rounded-xl transition-colors">
                <i class="fas fa-save mr-2"></i> Guardar Categoría
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