@extends('layouts.app')
@section('title', 'Detalle Producto')
@section('page-title', 'Inventario · Detalle')

@section('content')
<div class="max-w-5xl mx-auto">

    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('inventario.index') }}"
               class="w-9 h-9 bg-[#141c2e] border border-[#1e2d47] rounded-xl
                      flex items-center justify-center text-slate-400
                      hover:text-amber-500 hover:border-amber-500/50 transition-colors">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="font-display font-bold text-2xl uppercase">{{ $inventario->nombre }}</h1>
                <p class="text-slate-500 text-sm font-mono">{{ $inventario->codigo }}</p>
            </div>
        </div>
        <a href="{{ route('inventario.edit', $inventario) }}"
           class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600
                  text-black font-semibold px-5 py-2.5 rounded-xl transition-colors">
            <i class="fas fa-pen"></i> Editar
        </a>
    </div>

    @if(session('success'))
    <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400
                rounded-xl px-5 py-3 mb-5 flex items-center gap-3">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

        <div class="lg:col-span-1 space-y-4">
            <div class="card p-6 text-center">
                <div class="w-20 h-20 rounded-2xl mx-auto mb-4 flex items-center justify-center text-3xl
                            {{ $inventario->es_servicio
                               ? 'bg-gradient-to-br from-purple-500 to-pink-600'
                               : 'bg-gradient-to-br from-amber-500 to-orange-600' }}">
                    <i class="fas {{ $inventario->es_servicio ? 'fa-concierge-bell' : 'fa-box' }} text-white"></i>
                </div>
                <div class="font-display font-bold text-lg mb-1 uppercase">{{ $inventario->nombre }}</div>
                <div class="text-slate-500 text-sm mb-3">
                    {{ $inventario->es_servicio ? 'Servicio' : 'Producto' }}
                    · {{ $inventario->categoria->nombre ?? 'Sin categoría' }}
                </div>
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full
                    {{ $inventario->activo ? 'bg-emerald-500/10 text-emerald-500' : 'bg-red-500/10 text-red-400' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                    {{ $inventario->activo ? 'Activo' : 'Inactivo' }}
                </span>
            </div>

            @if(!$inventario->es_servicio)
            <div class="card p-5">
                <h3 class="font-display font-bold text-sm mb-4 flex items-center gap-2">
                    <i class="fas fa-warehouse text-amber-500"></i> Stock
                </h3>
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="bg-[#1a2235] rounded-xl p-3">
                        <div class="font-display font-bold text-xl
                            {{ $inventario->bajo_stock ? 'text-red-400' : 'text-emerald-500' }}">
                            {{ number_format($inventario->stock_actual, 0) }}
                        </div>
                        <div class="text-xs text-slate-500 mt-1">Actual</div>
                    </div>
                    <div class="bg-[#1a2235] rounded-xl p-3">
                        <div class="font-display font-bold text-xl text-amber-500">
                            {{ number_format($inventario->stock_minimo, 0) }}
                        </div>
                        <div class="text-xs text-slate-500 mt-1">Mínimo</div>
                    </div>
                    <div class="bg-[#1a2235] rounded-xl p-3">
                        <div class="font-display font-bold text-xl text-blue-400">
                            {{ number_format($inventario->stock_maximo, 0) }}
                        </div>
                        <div class="text-xs text-slate-500 mt-1">Máximo</div>
                    </div>
                </div>
                @if($inventario->ubicacion)
                <div class="mt-3 flex items-center gap-2 text-sm text-slate-400 uppercase">
                    <i class="fas fa-map-pin text-slate-600"></i> {{ $inventario->ubicacion }}
                </div>
                @endif
            </div>
            @endif
        </div>

        <div class="lg:col-span-2 space-y-4">

            <div class="card p-6">
                <h3 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                    <i class="fas fa-dollar-sign text-amber-500 text-sm"></i> Precios
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach([
                        ['Compra',    $inventario->precio_compra,  'slate'],
                        ['Venta',     $inventario->precio_venta,   'emerald'],
                        ['Mayorista', $inventario->precio_venta2,  'blue'],
                        ['Especial',  $inventario->precio_venta3,  'purple'],
                    ] as [$label, $precio, $color])
                    <div class="bg-[#1a2235] rounded-xl p-4">
                        <div class="text-xs text-slate-500 mb-1">{{ $label }}</div>
                        <div class="font-display font-bold text-lg
                            text-{{ $color }}-{{ $color=='slate' ? '300' : '400' }}">
                            ${{ number_format($precio, 0, ',', '.') }}
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="mt-3 text-xs text-slate-500">
                    IVA: {{ $inventario->iva_pct }}%
                    {{ $inventario->incluye_iva ? '(incluido en precio)' : '(no incluido)' }}
                </div>
            </div>

            @if(!$inventario->es_servicio)
            <div class="card p-6">
                <h3 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                    <i class="fas fa-sliders-h text-amber-500 text-sm"></i> Ajustar Stock
                </h3>
                <form method="POST" action="{{ route('inventario.ajustar', $inventario) }}">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-3">
                        <div>
                            <label class="form-label">Tipo</label>
                            <select name="tipo"
                                    class="form-input">
                                <option value="entrada">➕ Entrada</option>
                                <option value="salida">➖ Salida</option>
                                <option value="ajuste">🔧 Ajuste directo</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Cantidad</label>
                            <input type="text" inputmode="decimal"
                                   name="cantidad" placeholder="0"
                                   class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Referencia</label>
                            <input type="text" name="referencia"
                                   placeholder="OC-001, ETC."
                                   style="text-transform:uppercase"
                                   class="form-input">
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <input type="text" name="motivo"
                               placeholder="MOTIVO DEL AJUSTE *"
                               style="text-transform:uppercase"
                               class="flex-1 form-input uppercase
                                      focus:outline-none focus:border-amber-500">
                        <button type="submit"
                                class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-black
                                       font-semibold rounded-xl transition-colors whitespace-nowrap">
                            <i class="fas fa-check mr-1"></i> Aplicar
                        </button>
                    </div>
                </form>
            </div>
            @endif
        </div>
    </div>

    @if(!$inventario->es_servicio && $movimientos->count() > 0)
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-[#1e2d47]">
            <div class="font-display font-bold text-base">Últimos Movimientos</div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-[#1e2d47]">
                        <th class="table-th">Tipo</th>
                        <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Cantidad</th>
                        <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden sm:table-cell">Stock Ant.</th>
                        <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden sm:table-cell">Stock Nuevo</th>
                        <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Motivo</th>
                        <th class="table-th hidden md:table-cell">Usuario / Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($movimientos as $mov)
                    <tr class="border-b border-[#1e2d47]/50">
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full
                                {{ $mov->tipo=='entrada' ? 'bg-emerald-500/10 text-emerald-500'
                                  :($mov->tipo=='salida'  ? 'bg-red-500/10 text-red-400'
                                  :'bg-blue-500/10 text-blue-400') }}">
                                {{ ucfirst($mov->tipo) }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-right font-mono text-sm font-semibold
                            {{ $mov->tipo=='entrada' ? 'text-emerald-500' : 'text-red-400' }}">
                            {{ $mov->tipo=='entrada' ? '+' : '-' }}{{ number_format($mov->cantidad, 0) }}
                        </td>
                        <td class="px-3 py-3 text-right text-sm text-slate-500 hidden sm:table-cell">
                            {{ number_format($mov->stock_anterior, 0) }}
                        </td>
                        <td class="px-3 py-3 text-right text-sm font-semibold hidden sm:table-cell">
                            {{ number_format($mov->stock_nuevo, 0) }}
                        </td>
                        <td class="px-3 py-3 text-sm text-slate-400 uppercase">
                            {{ $mov->motivo }}
                            @if($mov->referencia)
                            <span class="text-xs text-slate-600 ml-1">({{ $mov->referencia }})</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 hidden md:table-cell">
                            <div class="text-xs text-slate-400">{{ $mov->usuario->name ?? '—' }}</div>
                            <div class="text-xs text-slate-600">{{ $mov->created_at->format('d/m/Y H:i') }}</div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <x-activity-log :model="$inventario" />

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