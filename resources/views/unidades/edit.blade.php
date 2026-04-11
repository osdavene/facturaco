@extends('layouts.app')
@section('title', 'Editar Unidad de Medida')
@section('page-title', 'Configuración · Editar Unidad de Medida')

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
            <h1 class="font-display font-bold text-2xl">Editar Unidad de Medida</h1>
            <p class="text-slate-500 text-sm">{{ $unidad->nombre }} ({{ $unidad->abreviatura }})</p>
        </div>
    </div>

    <form method="POST" action="{{ route('unidades.update', $unidad) }}">
        @csrf @method('PUT')

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
                       value="{{ old('nombre', $unidad->nombre) }}"
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
                       value="{{ old('abreviatura', $unidad->abreviatura) }}"
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
            </div>

            {{-- Estado --}}
            <div class="p-4 bg-[#1a2235] rounded-xl border border-[#1e2d47]">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="activo" value="0">
                    <input type="checkbox" name="activo" value="1"
                           class="w-4 h-4 accent-amber-500"
                           {{ old('activo', $unidad->activo) ? 'checked' : '' }}>
                    <div>
                        <div class="text-sm font-semibold text-slate-300">Unidad activa</div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            Solo las unidades activas aparecen al crear productos
                        </div>
                    </div>
                </label>
            </div>

            {{-- Productos asociados --}}
            @if($unidad->productos_count > 0)
            <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl px-4 py-3
                        text-xs text-blue-400 flex items-center gap-2">
                <i class="fas fa-info-circle flex-shrink-0"></i>
                Esta unidad tiene <strong>{{ $unidad->productos_count }} producto(s)</strong> asociado(s).
                No se puede eliminar mientras tenga productos.
            </div>
            @endif

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
                <i class="fas fa-save mr-2"></i> Actualizar Unidad
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