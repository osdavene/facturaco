@extends('backoffice.layout')
@section('title', 'Backup y Restauración de Plataforma')

@section('content')

<div class="mb-8">
    <h1 class="font-display font-black text-2xl">Backup y Restauración de Plataforma</h1>
    <p class="text-slate-500 text-sm mt-1">
        Exporta y restaura <strong class="text-slate-300">toda</strong> la base de datos central —
        todas las empresas, usuarios, contabilidad, nómina y transacciones.
    </p>
</div>

@if(session('success'))
<div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-xl px-5 py-3 mb-5 flex items-center gap-3 text-sm">
    <i class="fas fa-check-circle text-lg"></i> {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-5 py-3 mb-5 flex items-center gap-3 text-sm">
    <i class="fas fa-exclamation-triangle text-lg"></i> {{ session('error') }}
</div>
@endif

@if($errors->any())
<div class="bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-5 py-3 mb-5 text-sm space-y-1">
    @foreach($errors->all() as $err)
        <div><i class="fas fa-times-circle mr-1"></i> {{ $err }}</div>
    @endforeach
</div>
@endif

<div class="max-w-4xl mx-auto space-y-6">

    {{-- Resumen de registros --}}
    <div class="card p-6">
        <h2 class="font-display font-bold text-base flex items-center gap-2 mb-4">
            <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center text-black text-xs font-black">
                <i class="fas fa-database text-[10px]"></i>
            </span>
            Estado actual de la base de datos
        </h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2 mb-4">
            @foreach($tablas as $tabla => $nombre)
            <div class="bg-[#1a2235] rounded-xl px-3 py-2 text-center border border-[#1e2d47]/60">
                <div class="text-base font-mono font-bold text-amber-400">{{ number_format($conteos[$tabla]) }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5 leading-tight truncate" title="{{ $nombre }}">{{ $nombre }}</div>
            </div>
            @endforeach
        </div>

        <div class="flex items-center gap-2 text-xs text-slate-500 border-t border-[#1e2d47] pt-3 mt-2">
            <i class="fas fa-calculator text-amber-500"></i>
            Total de registros en la plataforma:
            <span class="text-slate-200 font-bold font-mono">{{ number_format($totalRegistros) }}</span>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════
         SECCIÓN 1: EXPORTACIÓN COMPLETA
    ════════════════════════════════════════════ --}}
    <div class="card overflow-hidden border-emerald-500/20">
        <div class="px-6 py-5 flex items-start justify-between gap-6 flex-wrap">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-emerald-500/10 border border-emerald-500/20 rounded-xl
                            flex items-center justify-center text-emerald-400 flex-shrink-0 mt-0.5">
                    <i class="fas fa-file-code text-lg"></i>
                </div>
                <div>
                    <p class="font-semibold text-slate-200 text-base">Exportar Base de Datos Completa (SQL)</p>
                    <p class="text-sm text-slate-400 mt-0.5">
                        Genera un script con sentencias <code class="bg-[#1a2235] px-1.5 py-0.5 rounded text-emerald-400 text-xs">INSERT INTO</code>
                        para todas las empresas, usuarios, nóminas, contabilidad y facturación.
                    </p>
                </div>
            </div>
            <a href="{{ route('backoffice.backup.descargar') }}"
               class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600
                      text-black font-bold text-sm px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-emerald-500/20 flex-shrink-0">
                <i class="fas fa-download text-xs"></i> Descargar Script .sql
            </a>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════
         SECCIÓN 2: IMPORTACIÓN / RESTAURACIÓN
    ════════════════════════════════════════════ --}}
    <div class="card overflow-hidden border-blue-500/20">
        <div class="px-6 py-4 border-b border-[#1e2d47] flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-500/10 border border-blue-500/20 rounded-xl flex items-center justify-center text-blue-400">
                    <i class="fas fa-file-import text-base"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-200 text-base">Importar / Restaurar Base de Datos (SQL)</h3>
                    <p class="text-xs text-slate-500">Ejecuta sentencias SQL desde un archivo de respaldo</p>
                </div>
            </div>
            <span class="badge bg-blue-500/10 text-blue-400 text-xs px-2.5 py-1">SuperAdmin</span>
        </div>

        <form method="POST" action="{{ route('backoffice.backup.importar') }}" enctype="multipart/form-data" class="p-6 space-y-4"
              onsubmit="return confirm('⚠️ ADVERTENCIA CRÍTICA: ¿Estás seguro de importar este archivo SQL? Esta acción ejecutará las sentencias sobre la base de datos de producción.')">
            @csrf

            <div class="space-y-2">
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
                    Seleccionar Archivo SQL de Respaldo (.sql) <span class="text-amber-500">*</span>
                </label>
                <input type="file" name="archivo_sql" accept=".sql,.txt" required
                       class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-3 text-sm text-slate-200
                              file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold
                              file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer focus:outline-none">
                <p class="text-[11px] text-slate-500">Tamaño máximo permitido: 50 MB. El archivo se ejecuta dentro de una transacción segura.</p>
            </div>

            <div class="bg-amber-500/5 border border-amber-500/20 rounded-xl p-4 text-xs text-amber-300/80 leading-relaxed flex items-start gap-2.5">
                <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5 flex-shrink-0"></i>
                <div>
                    <strong class="text-amber-400">Recomendación de seguridad:</strong>
                    Antes de restaurar una copia de seguridad externa, descarga un respaldo de la base de datos actual con el botón verde superior.
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm px-6 py-2.5 rounded-xl transition-all shadow-lg shadow-blue-500/20">
                    <i class="fas fa-upload text-xs"></i> Iniciar Importación de Base de Datos
                </button>
            </div>
        </form>
    </div>

</div>

@endsection
