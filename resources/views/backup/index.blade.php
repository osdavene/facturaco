@extends('layouts.app')
@section('title', 'Copia de Seguridad y Backup')
@section('page-title', 'Administración · Backup de Empresa')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-12">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-display font-bold text-2xl">Copia de Seguridad y Respaldo</h1>
            <p class="text-slate-500 text-sm mt-0.5">
                Exporta y descarga toda la información de
                <strong class="text-amber-500">{{ $empresa->razon_social ?? 'tu empresa' }}</strong>.
                Solo tus datos empresariales, aislados y 100% seguros.
            </p>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-xl px-5 py-3 flex items-center gap-3 text-sm">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    {{-- ═══════════════════════════════════════════
         OPCIÓN A — SQL & JSON COMPLETOS
    ════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        {{-- SQL Script --}}
        <div class="card p-6 flex flex-col justify-between space-y-4 border-blue-500/20 bg-gradient-to-br from-[#111827] to-[#141c2e]">
            <div class="space-y-2">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-500/10 border border-blue-500/20 rounded-xl flex items-center justify-center text-blue-400">
                        <i class="fas fa-file-code text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-200 text-base">Script SQL Nativo (.sql)</h3>
                        <span class="text-xs text-slate-500">PostgreSQL / MySQL</span>
                    </div>
                </div>
                <p class="text-xs text-slate-400 leading-relaxed pt-1">
                    Genera un archivo con sentencias <code class="text-blue-400 font-mono">INSERT INTO</code> estructuradas exclusivamente con los datos y transacciones de tu empresa.
                </p>
            </div>
            <a href="{{ route('backup.sql') }}"
               class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-blue-500/20">
                <i class="fas fa-download text-xs"></i> Descargar Script SQL (.sql)
            </a>
        </div>

        {{-- JSON Completo --}}
        <div class="card p-6 flex flex-col justify-between space-y-4 border-emerald-500/20 bg-gradient-to-br from-[#111827] to-[#141c2e]">
            <div class="space-y-2">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-emerald-500/10 border border-emerald-500/20 rounded-xl flex items-center justify-center text-emerald-400">
                        <i class="fas fa-database text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-200 text-base">Estructura JSON Completa (.json)</h3>
                        <span class="text-xs text-slate-500">Universal & Portátil</span>
                    </div>
                </div>
                <p class="text-xs text-slate-400 leading-relaxed pt-1">
                    Exporta todas las tablas y catálogos en un archivo JSON unificado con codificación UTF-8, ideal para integraciones externas y archivo histórico.
                </p>
            </div>
            <a href="{{ route('backup.json') }}"
               class="inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-emerald-500/20">
                <i class="fas fa-download text-xs"></i> Descargar Archivo JSON (.json)
            </a>
        </div>

    </div>

    {{-- Resumen de Registros por Módulo --}}
    <div class="card p-5 space-y-3">
        <h3 class="font-bold text-slate-300 text-xs uppercase tracking-wider">
            Total de Registros Respaldados en tu Empresa:
        </h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2">
            @foreach($tablas as $tabla => $nombre)
            <div class="bg-[#1a2235] rounded-xl px-3 py-2 text-center border border-[#1e2d47]/60">
                <div class="text-base font-mono font-bold text-amber-400">{{ number_format($conteos[$tabla]) }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5 leading-tight truncate" title="{{ $nombre }}">{{ $nombre }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ═══════════════════════════════════════════
         OPCIÓN C — CSV / ZIP SELECTIVO
    ════════════════════════════════════════════ --}}
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-[#1e2d47] flex items-center gap-3">
            <div class="w-10 h-10 bg-amber-500/10 border border-amber-500/20 rounded-xl
                        flex items-center justify-center text-amber-400">
                <i class="fas fa-file-excel text-lg"></i>
            </div>
            <div>
                <div class="font-semibold text-slate-200">Backup Personalizado — Archivos CSV en ZIP (Excel)</div>
                <div class="text-xs text-slate-500">Selecciona módulos específicos y rangos de fechas</div>
            </div>
        </div>

        <form method="POST" action="{{ route('backup.csv') }}" class="px-6 py-5 space-y-5">
            @csrf

            {{-- Rango de fechas --}}
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wider">
                    Filtro por Rango de Fechas <span class="text-slate-500 normal-case font-normal">(aplica a facturas, notas, cotizaciones, compras, kárdex y asientos)</span>
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
                        Módulos a Incluir en el Archivo ZIP:
                    </label>
                    <div class="flex gap-2 text-xs">
                        <button type="button" onclick="marcarTodos(true)"
                                class="text-amber-400 hover:text-amber-300 font-semibold transition-colors">
                            Seleccionar todos
                        </button>
                        <span class="text-slate-700">·</span>
                        <button type="button" onclick="marcarTodos(false)"
                                class="text-slate-500 hover:text-slate-400 transition-colors">
                            Quitar todos
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                    @foreach($tablas as $tabla => $nombre)
                    <label class="flex items-center gap-3 bg-[#1a2235] border border-[#1e2d47] rounded-xl
                                  px-4 py-2.5 cursor-pointer hover:border-amber-500/30 transition-colors
                                  has-[:checked]:border-amber-500/50 has-[:checked]:bg-amber-500/5">
                        <input type="checkbox" name="tablas[]" value="{{ $tabla }}"
                               class="w-4 h-4 accent-amber-500" checked>
                        <div class="flex-1 min-w-0">
                            <div class="text-xs font-semibold text-slate-300 truncate" title="{{ $nombre }}">{{ $nombre }}</div>
                            <div class="text-[10px] text-slate-500 font-mono">{{ number_format($conteos[$tabla]) }} registros</div>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="pt-2">
                <button type="submit"
                        class="flex items-center gap-2 bg-amber-500 hover:bg-amber-600
                               text-black font-bold text-sm px-6 py-2.5 rounded-xl transition-all shadow-lg shadow-amber-500/20">
                    <i class="fas fa-file-archive text-xs"></i> Generar y Descargar Archivo ZIP
                </button>
            </div>
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