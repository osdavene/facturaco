@extends('layouts.app')
@section('title', 'Reporte de Inventario')
@section('page-title', 'Reportes · Inventario')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-display font-bold text-2xl">Reporte de Inventario</h1>
        <p class="text-slate-500 text-sm mt-1">Estado actual del inventario</p>
    </div>
    <div class="flex gap-2 flex-wrap">
        <a href="{{ route('reportes.inventario.pdf', request()->query()) }}"
           target="_blank"
           class="inline-flex items-center gap-2 bg-red-500/10 border border-red-500/30
                  text-red-400 hover:bg-red-500/20 px-4 py-2.5 rounded-xl transition-colors text-sm">
            <i class="fas fa-file-pdf"></i> PDF
        </a>
        <a href="{{ route('reportes.inventario.excel') }}"
           class="inline-flex items-center gap-2 bg-emerald-500/10 border border-emerald-500/30
                  text-emerald-400 hover:bg-emerald-500/20 px-4 py-2.5 rounded-xl transition-colors text-sm">
            <i class="fas fa-file-excel"></i> Excel
        </a>
        <a href="{{ route('reportes.index') }}"
           class="inline-flex items-center gap-2 bg-[#1a2235] border border-[#1e2d47]
                  text-slate-400 hover:text-slate-200 px-4 py-2.5 rounded-xl transition-colors text-sm">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>

{{-- Filtros --}}
<form method="GET" action="{{ route('reportes.inventario') }}"
      class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-4 mb-5">
    <div class="flex flex-col sm:flex-row gap-3">
        <select name="filtro"
                class="bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                       text-sm focus:outline-none focus:border-amber-500"
                style="color:#e2e8f0">
            <option value="todos"      {{ $filtro=='todos'      ? 'selected':'' }}>Todos los productos</option>
            <option value="bajo_stock" {{ $filtro=='bajo_stock' ? 'selected':'' }}>Bajo stock</option>
            <option value="sin_stock"  {{ $filtro=='sin_stock'  ? 'selected':'' }}>Sin stock</option>
        </select>
        <select name="categoria_id"
                class="bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                       text-sm focus:outline-none focus:border-amber-500"
                style="color:#e2e8f0">
            <option value="">Todas las categorías</option>
            @foreach($categorias as $cat)
            <option value="{{ $cat->id }}" {{ $categoria==$cat->id ? 'selected':'' }}>
                {{ $cat->nombre }}
            </option>
            @endforeach
        </select>
        <button type="submit"
                class="bg-amber-500 hover:bg-amber-600 text-black font-semibold
                       px-5 py-2.5 rounded-xl transition-colors">
            <i class="fas fa-filter mr-1"></i> Filtrar
        </button>
    </div>
</form>

{{-- KPIs --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5">
        <div class="text-xs text-slate-500 uppercase tracking-wider mb-2">Total Productos</div>
        <div class="font-display font-bold text-xl">{{ $productos->count() }}</div>
    </div>
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5">
        <div class="text-xs text-slate-500 uppercase tracking-wider mb-2">Bajo Stock</div>
        <div class="font-display font-bold text-xl text-amber-500">
            {{ $productos->where('es_servicio', false)->filter(fn($p) => $p->bajo_stock)->count() }}
        </div>
    </div>
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5">
        <div class="text-xs text-slate-500 uppercase tracking-wider mb-2">Sin Stock</div>
        <div class="font-display font-bold text-xl text-red-400">
            {{ $productos->where('es_servicio', false)->where('stock_actual', 0)->count() }}
        </div>
    </div>
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5">
        <div class="text-xs text-slate-500 uppercase tracking-wider mb-2">Valor Inventario</div>
        <div class="font-display font-bold text-xl text-emerald-500">
            ${{ number_format($valorInventario, 0, ',', '.') }}
        </div>
    </div>
</div>

{{-- Tabla --}}
<div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-[#1e2d47]">
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-5 py-3">Producto</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden md:table-cell">Categoría</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Stock Actual</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden sm:table-cell">Mínimo</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden lg:table-cell">P. Compra</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden lg:table-cell">P. Venta</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-5 py-3 hidden md:table-cell">Valor Stock</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productos as $producto)
                <tr class="border-b border-[#1e2d47]/50 hover:bg-[#1a2235]/50 transition-colors">
                    <td class="px-5 py-3">
                        <div class="text-sm font-semibold" style="color:#e2e8f0">{{ $producto->nombre }}</div>
                        <div class="text-xs text-slate-500 font-mono">{{ $producto->codigo }}</div>
                    </td>
                    <td class="px-3 py-3 hidden md:table-cell">
                        <span class="text-xs bg-[#1a2235] px-2 py-0.5 rounded-full text-slate-400">
                            {{ $producto->categoria->nombre ?? '—' }}
                        </span>
                    </td>
                    <td class="px-3 py-3 text-right">
                        @if($producto->es_servicio)
                        <span class="text-xs text-slate-500">Servicio</span>
                        @else
                        <span class="font-semibold text-sm
                            {{ $producto->bajo_stock ? 'text-red-400' : '' }}"
                            style="{{ !$producto->bajo_stock ? 'color:#e2e8f0' : '' }}">
                            {{ number_format($producto->stock_actual, 0) }}
                        </span>
                        @endif
                    </td>
                    <td class="px-3 py-3 text-right text-sm text-slate-500 hidden sm:table-cell">
                        {{ $producto->es_servicio ? '—' : number_format($producto->stock_minimo, 0) }}
                    </td>
                    <td class="px-3 py-3 text-right text-sm text-slate-400 hidden lg:table-cell">
                        ${{ number_format($producto->precio_compra, 0, ',', '.') }}
                    </td>
                    <td class="px-3 py-3 text-right text-sm font-semibold hidden lg:table-cell"
                        style="color:#e2e8f0">
                        ${{ number_format($producto->precio_venta, 0, ',', '.') }}
                    </td>
                    <td class="px-5 py-3 text-right text-sm text-emerald-500 hidden md:table-cell">
                        @if(!$producto->es_servicio)
                        ${{ number_format($producto->stock_actual * $producto->precio_compra, 0, ',', '.') }}
                        @else —
                        @endif
                    </td>
                    <td class="px-3 py-3">
                        @if($producto->es_servicio)
                        <span class="text-xs bg-purple-500/10 text-purple-400 px-2 py-0.5 rounded-full">Servicio</span>
                        @elseif($producto->stock_actual == 0)
                        <span class="text-xs bg-red-500/10 text-red-400 px-2 py-0.5 rounded-full">Sin stock</span>
                        @elseif($producto->bajo_stock)
                        <span class="text-xs bg-amber-500/10 text-amber-500 px-2 py-0.5 rounded-full">Bajo stock</span>
                        @else
                        <span class="text-xs bg-emerald-500/10 text-emerald-500 px-2 py-0.5 rounded-full">OK</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-12 text-center text-slate-500">
                        No hay productos con los filtros seleccionados
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection