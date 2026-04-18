@extends('layouts.app')
@section('title', 'Inventario')
@section('page-title', 'Inventario')

@section('content')

{{-- Encabezado --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-display font-bold text-2xl">Inventario</h1>
        <p class="text-slate-500 text-sm mt-1">Gestiona tus productos y stock</p>
    </div>
    <a href="{{ route('inventario.create') }}"
       class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600
              text-black font-semibold px-5 py-2.5 rounded-xl transition-colors">
        <i class="fas fa-plus"></i> Nuevo Producto
    </a>
</div>

{{-- KPIs --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
    <div class="card p-5">
        <div class="flex items-center justify-between mb-2">
            <div class="text-xs text-slate-500 uppercase tracking-wider">Total Productos</div>
            <div class="w-8 h-8 bg-blue-500/10 rounded-lg flex items-center justify-center text-blue-400">
                <i class="fas fa-boxes text-sm"></i>
            </div>
        </div>
        <div class="font-display font-bold text-2xl">{{ $totalProductos }}</div>
    </div>
    <div class="card p-5 cursor-pointer
                hover:border-amber-500/50 transition-colors"
         onclick="document.querySelector('[name=stock]').value='bajo'; document.querySelector('#filtros').submit()">
        <div class="flex items-center justify-between mb-2">
            <div class="text-xs text-slate-500 uppercase tracking-wider">Bajo Stock</div>
            <div class="w-8 h-8 bg-amber-500/10 rounded-lg flex items-center justify-center text-amber-500">
                <i class="fas fa-exclamation-triangle text-sm"></i>
            </div>
        </div>
        <div class="font-display font-bold text-2xl text-amber-500">{{ $bajoStock }}</div>
        <div class="text-xs text-slate-500 mt-1">Clic para filtrar</div>
    </div>
    <div class="card p-5">
        <div class="flex items-center justify-between mb-2">
            <div class="text-xs text-slate-500 uppercase tracking-wider">Sin Stock</div>
            <div class="w-8 h-8 bg-red-500/10 rounded-lg flex items-center justify-center text-red-400">
                <i class="fas fa-times-circle text-sm"></i>
            </div>
        </div>
        <div class="font-display font-bold text-2xl text-red-400">{{ $sinStock }}</div>
    </div>
</div>

{{-- Filtros --}}
<form id="filtros" method="GET" action="{{ route('inventario.index') }}"
      class="card p-4 mb-5">
    <input type="hidden" name="stock" value="{{ request('stock') }}">
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1 relative">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
            <input type="text" name="buscar" value="{{ request('buscar') }}"
                   placeholder="Buscar por nombre, código..."
                   class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl
                          pl-9 pr-4 py-2.5 text-sm text-slate-200 placeholder-slate-600
                          focus:outline-none focus:border-amber-500">
        </div>
        <select name="categoria_id"
                class="form-input text-slate-300 focus:outline-none focus:border-amber-500">
            <option value="">Todas las categorías</option>
            @foreach($categorias as $cat)
            <option value="{{ $cat->id }}" {{ request('categoria_id')==$cat->id ? 'selected':'' }}>
                {{ $cat->nombre }}
            </option>
            @endforeach
        </select>
        <select name="estado"
                class="form-input text-slate-300 focus:outline-none focus:border-amber-500">
            <option value="">Todos</option>
            <option value="activo"    {{ request('estado')=='activo'    ? 'selected':'' }}>Activos</option>
            <option value="inactivo"  {{ request('estado')=='inactivo'  ? 'selected':'' }}>Inactivos</option>
            <option value="archivado" {{ request('estado')=='archivado' ? 'selected':'' }}>Archivados ({{ $archivados }})</option>
        </select>
        <button type="submit"
                class="bg-amber-500 hover:bg-amber-600 text-black font-semibold
                       px-5 py-2.5 rounded-xl transition-colors whitespace-nowrap">
            <i class="fas fa-filter mr-2"></i>Filtrar
        </button>
        @if(request()->hasAny(['buscar','categoria_id','estado','stock']))
        <a href="{{ route('inventario.index') }}"
           class="bg-[#1a2235] border border-[#1e2d47] hover:border-red-500/50
                  text-slate-400 hover:text-red-400 px-4 py-2.5 rounded-xl
                  transition-colors text-sm flex items-center gap-2">
            <i class="fas fa-times"></i> Limpiar
        </a>
        @endif
    </div>
</form>

{{-- Tabla --}}
<div class="card overflow-hidden">
    {{-- Formulario bulk --}}
    <form id="bulk-form" method="POST" action="{{ route('inventario.bulk-delete') }}">
        @csrf @method('DELETE')
    </form>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-[#1e2d47]">
                    <th class="w-10 px-4 py-3">
                        <input type="checkbox" class="bulk-select-all w-4 h-4 rounded border-[#2d3f5c]
                               bg-[#1a2235] accent-amber-500 cursor-pointer">
                    </th>
                    <th class="table-th">Producto</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden md:table-cell">Categoría</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Stock</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden sm:table-cell">Precio Venta</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden lg:table-cell">Estado</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-5 py-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productos as $producto)
                <tr class="table-row">
                    <td class="px-4 py-4">
                        <input type="checkbox" class="bulk-item w-4 h-4 rounded border-[#2d3f5c]
                               bg-[#1a2235] accent-amber-500 cursor-pointer" value="{{ $producto->id }}">
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center
                                        font-bold text-xs text-white flex-shrink-0
                                        {{ $producto->es_servicio
                                           ? 'bg-gradient-to-br from-purple-500 to-pink-600'
                                           : 'bg-gradient-to-br from-amber-500 to-orange-600' }}">
                                <i class="fas {{ $producto->es_servicio ? 'fa-concierge-bell' : 'fa-box' }}"></i>
                            </div>
                            <div>
                                <div class="text-sm font-semibold">{{ $producto->nombre }}</div>
                                <div class="text-xs text-slate-500 font-mono">{{ $producto->codigo }}</div>
                            </div>
                        </div>
                    </td>

                    <td class="px-3 py-4 hidden md:table-cell">
                        <span class="text-xs bg-[#1a2235] px-2.5 py-1 rounded-full text-slate-400">
                            {{ $producto->categoria->nombre ?? '—' }}
                        </span>
                    </td>

                    <td class="px-3 py-4 text-right">
                        @if($producto->es_servicio)
                            <span class="text-xs text-slate-500">Servicio</span>
                        @else
                            <div class="font-display font-bold text-base
                                {{ $producto->bajo_stock ? 'text-red-400' : 'text-slate-200' }}">
                                {{ number_format($producto->stock_actual, 0) }}
                            </div>
                            <div class="text-xs text-slate-600">
                                mín {{ number_format($producto->stock_minimo, 0) }}
                            </div>
                            @if($producto->bajo_stock)
                            <div class="text-[10px] text-red-400 font-semibold">⚠ Bajo stock</div>
                            @endif
                        @endif
                    </td>

                    <td class="px-3 py-4 text-right hidden sm:table-cell">
                        <div class="text-sm font-semibold">
                            ${{ number_format($producto->precio_venta, 0, ',', '.') }}
                        </div>
                        <div class="text-xs text-slate-500">IVA {{ $producto->iva_pct }}%</div>
                    </td>

                    <td class="px-3 py-4 hidden lg:table-cell">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full
                            {{ $producto->activo
                               ? 'bg-emerald-500/10 text-emerald-500'
                               : 'bg-red-500/10 text-red-400' }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            {{ $producto->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>

                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @if($soloArchivados)
                                <form method="POST" action="{{ route('inventario.restore', $producto->id) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" title="Restaurar"
                                            class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                                   flex items-center justify-center text-slate-400
                                                   hover:text-green-400 hover:border-green-500/50 transition-colors">
                                        <i class="fas fa-undo text-xs"></i>
                                    </button>
                                </form>
                            @else
                            <a href="{{ route('inventario.show', $producto) }}" title="Ver"
                               class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                      flex items-center justify-center text-slate-400
                                      hover:text-blue-400 hover:border-blue-500/50 transition-colors">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            <a href="{{ route('inventario.edit', $producto) }}" title="Editar"
                               class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                      flex items-center justify-center text-slate-400
                                      hover:text-amber-500 hover:border-amber-500/50 transition-colors">
                                <i class="fas fa-pen text-xs"></i>
                            </a>
                            <form method="POST" action="{{ route('inventario.destroy', $producto) }}"
                                  onsubmit="return confirm('¿Eliminar {{ $producto->nombre }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Eliminar"
                                        class="w-8 h-8 bg-[#1a2235] border border-[#1e2d47] rounded-lg
                                               flex items-center justify-center text-slate-400
                                               hover:text-red-400 hover:border-red-500/50 transition-colors">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <x-empty-state
                    icon="fa-boxes"
                    title="No hay productos en el inventario"
                    subtitle="Crea productos o servicios para agregarlos a tus facturas y cotizaciones."
                    href="{{ route('inventario.create') }}"
                    label="Nuevo Producto"
                    :colspan="7" />
                @endforelse
            </tbody>
        </table>
    </div>

    @if($productos->hasPages())
    <div class="px-5 py-4 border-t border-[#1e2d47]">
        {{ $productos->links() }}
    </div>
    @endif
</div>

<div id="bulk-bar"
     class="hidden fixed bottom-16 left-0 right-0 z-[95]
            bg-[#0d1526]/97 backdrop-blur border-t border-amber-500/20
            px-4 py-3 flex items-center gap-3 lg:left-64">
    <div class="w-8 h-8 bg-amber-500/10 border border-amber-500/20 rounded-lg
                flex items-center justify-center flex-shrink-0">
        <i class="fas fa-check-square text-amber-400 text-sm"></i>
    </div>
    <span id="bulk-count" class="text-sm font-medium text-slate-200 flex-1">0 seleccionados</span>
    <button onclick="submitBulkAction('delete')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-red-500/10 border border-red-500/30
                   hover:bg-red-500/20 text-red-400 rounded-xl text-sm transition-colors">
        <i class="fas fa-trash text-xs"></i> Eliminar
    </button>
    <button onclick="clearBulkSelection()"
            class="inline-flex items-center gap-2 px-4 py-2 bg-[#1a2235] border border-[#1e2d47]
                   hover:border-slate-500 text-slate-400 rounded-xl text-sm transition-colors">
        <i class="fas fa-times text-xs"></i> Cancelar
    </button>
</div>
@endsection