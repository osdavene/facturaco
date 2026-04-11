@extends('layouts.app')
@section('title', 'Backup')
@section('page-title', 'Administración · Backup')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">


    {{-- Header --}}
    <div>
        <h1 class="font-display font-bold text-2xl">Backup de Base de Datos</h1>
        <p class="text-slate-500 text-sm mt-0.5">
            Exporta y respalda la información de
            <strong class="text-amber-500">{{ $empresa->razon_social ?? 'tu empresa' }}</strong>
            y sus filiales. Solo tus datos, sin información de otras empresas.
        </p>
    </div>

    {{-- ═══════════════════════════════════════════
         OPCIÓN A — JSON COMPLETO
    ════════════════════════════════════════════ --}}
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-[#1e2d47] flex items-center justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-500/10 border border-emerald-500/20 rounded-xl
                            flex items-center justify-center text-emerald-400">
                    <i class="fas fa-database"></i>
                </div>
                <div>
                    <div class="font-semibold text-slate-200">Backup Completo — JSON</div>
                    <div class="text-xs text-slate-500">Todas las tablas · Formato legible · Recomendado</div>
                </div>
            </div>
            <a href="{{ route('backup.json') }}"
               class="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600
                      text-white font-bold text-sm px-4 py-2.5 rounded-xl transition-colors">
                <i class="fas fa-download text-xs"></i> Descargar JSON
            </a>
        </div>
        <div class="px-6 py-4">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2">
                @foreach($tablas as $tabla => $nombre)
                <div class="bg-[#1a2235] rounded-xl px-3 py-2.5 text-center">
                    <div class="text-lg font-bold text-emerald-400">{{ number_format($conteos[$tabla]) }}</div>
                    <div class="text-xs text-slate-500 mt-0.5 leading-tight">{{ $nombre }}</div>
                </div>
                @endforeach
            </div>
            <p class="text-xs text-slate-600 mt-3 flex items-center gap-1.5">
                <i class="fas fa-info-circle text-amber-500"></i>
                El archivo JSON incluye todos los registros de todas las tablas. Puede abrirse con cualquier editor de texto.
            </p>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════
         OPCIÓN B — CSV SELECTIVO
    ════════════════════════════════════════════ --}}
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-[#1e2d47] flex items-center gap-3">
            <div class="w-10 h-10 bg-amber-500/10 border border-amber-500/20 rounded-xl
                        flex items-center justify-center text-amber-400">
                <i class="fas fa-file-csv"></i>
            </div>
            <div>
                <div class="font-semibold text-slate-200">Backup Selectivo — CSV / ZIP</div>
                <div class="text-xs text-slate-500">Elige módulos y rango de fechas · Compatible con Excel</div>
            </div>
        </div>

        <form method="POST" action="{{ route('backup.csv') }}" class="px-6 py-5 space-y-5">
            @csrf

            {{-- Rango de fechas --}}
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wider">
                    Rango de fechas <span class="text-slate-600 normal-case font-normal">(aplica a: facturas, cotizaciones, órdenes, recibos, remisiones)</span>
                </label>
                <div class="flex gap-3 flex-wrap">
                    <input type="date" name="fecha_desde"
                           class="bg-[#1a2235] border border-[#1e2d47] rounded-xl px-3 py-2
                                  text-sm text-slate-200 focus:outline-none focus:border-amber-500 transition-colors">
                    <span class="text-slate-500 self-center text-sm">hasta</span>
                    <input type="date" name="fecha_hasta"
                           class="bg-[#1a2235] border border-[#1e2d47] rounded-xl px-3 py-2
                                  text-sm text-slate-200 focus:outline-none focus:border-amber-500 transition-colors">
                </div>
            </div>

            {{-- Selección de módulos --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        Módulos a exportar
                    </label>
                    <div class="flex gap-2">
                        <button type="button" onclick="marcarTodos(true)"
                                class="text-xs text-amber-400 hover:text-amber-300 transition-colors">
                            Seleccionar todo
                        </button>
                        <span class="text-slate-700">·</span>
                        <button type="button" onclick="marcarTodos(false)"
                                class="text-xs text-slate-500 hover:text-slate-400 transition-colors">
                            Quitar todo
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                    @foreach($tablas as $tabla => $nombre)
                    <label class="flex items-center gap-3 bg-[#1a2235] border border-[#1e2d47] rounded-xl
                                  px-4 py-3 cursor-pointer hover:border-amber-500/30 transition-colors
                                  has-[:checked]:border-amber-500/50 has-[:checked]:bg-amber-500/5">
                        <input type="checkbox" name="tablas[]" value="{{ $tabla }}"
                               class="w-4 h-4 accent-amber-500" checked>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-semibold text-slate-300">{{ $nombre }}</div>
                            <div class="text-xs text-slate-600">{{ number_format($conteos[$tabla]) }} registros</div>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            <button type="submit"
                    class="flex items-center gap-2 bg-amber-500 hover:bg-amber-600
                           text-black font-bold text-sm px-5 py-2.5 rounded-xl transition-colors">
                <i class="fas fa-file-archive text-xs"></i> Generar y Descargar ZIP
            </button>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
function marcarTodos(estado) {
    document.querySelectorAll('input[name="tablas[]"]').forEach(cb => cb.checked = estado);
}
</script>
@endpush