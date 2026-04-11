@extends('backoffice.layout')
@section('title', 'Backup de plataforma')

@section('content')

<div class="mb-8">
    <h1 class="font-display font-black text-2xl">Backup de plataforma</h1>
    <p class="text-slate-500 text-sm mt-1">
        Exporta <strong class="text-slate-300">toda</strong> la base de datos —
        todas las empresas, usuarios y transacciones.
    </p>
</div>

<div class="max-w-3xl mx-auto space-y-5">

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
            <div class="bg-[#1a2235] rounded-xl px-3 py-2.5 text-center">
                <div class="text-base font-bold text-amber-400">{{ number_format($conteos[$tabla]) }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5 leading-tight">{{ $nombre }}</div>
            </div>
            @endforeach
        </div>

        <div class="flex items-center gap-2 text-xs text-slate-500 border-t border-[#1e2d47] pt-3 mt-2">
            <i class="fas fa-sigma text-amber-500"></i>
            Total de registros en la plataforma:
            <span class="text-slate-200 font-bold">{{ number_format($totalRegistros) }}</span>
        </div>
    </div>

    {{-- Descarga SQL --}}
    <div class="card overflow-hidden">
        <div class="px-6 py-5 flex items-start justify-between gap-6 flex-wrap">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-emerald-500/10 border border-emerald-500/20 rounded-xl
                            flex items-center justify-center text-emerald-400 flex-shrink-0 mt-0.5">
                    <i class="fas fa-file-code text-lg"></i>
                </div>
                <div>
                    <p class="font-semibold text-slate-200 text-base">Backup completo — SQL</p>
                    <p class="text-sm text-slate-500 mt-0.5">
                        Genera sentencias <code class="bg-[#1a2235] px-1.5 py-0.5 rounded text-emerald-400 text-xs">INSERT INTO</code>
                        para todas las tablas y empresas.<br>
                        Compatible con PostgreSQL. Descarga directa al navegador.
                    </p>
                    <div class="mt-3 flex items-start gap-2 text-xs text-slate-600">
                        <i class="fas fa-triangle-exclamation text-amber-500 mt-0.5 flex-shrink-0"></i>
                        <span>Al restaurar, este archivo <strong class="text-slate-400">reemplaza todos los datos</strong> existentes.
                        Úsalo solo para migrar a un servidor nuevo o como respaldo de emergencia.</span>
                    </div>
                </div>
            </div>
            <a href="{{ route('backoffice.backup.descargar') }}"
               class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600
                      text-black font-bold text-sm px-5 py-2.5 rounded-xl transition-colors flex-shrink-0">
                <i class="fas fa-download text-xs"></i>Descargar .sql
            </a>
        </div>

        {{-- Preview SQL --}}
        <div class="px-6 pb-5">
            <div class="bg-[#1a2235] rounded-xl px-5 py-4 font-mono text-xs text-slate-500 leading-relaxed">
                <span class="text-slate-600">-- BACKUP COMPLETO — FacturaCO (BackOffice)</span><br>
                <span class="text-slate-600">-- Fecha: {{ now()->format('d/m/Y H:i:s') }}</span><br>
                <span class="text-slate-600">-- ────────────────────────────────────────</span><br>
                <span class="text-emerald-400">INSERT INTO</span> <span class="text-amber-400">"empresa"</span>
                <span class="text-slate-400">("id", "razon_social", "nit", ...)</span><br>
                <span class="text-emerald-400">VALUES</span> <span class="text-slate-400">(1, 'Empresa S.A.S.', '900...', ...);</span><br>
                <span class="text-slate-600">-- ... todas las tablas, todas las empresas</span>
            </div>
        </div>
    </div>

    {{-- Aviso --}}
    <div class="bg-amber-500/5 border border-amber-500/20 rounded-xl px-5 py-4 flex items-start gap-3">
        <i class="fas fa-shield-halved text-amber-500 mt-0.5 flex-shrink-0"></i>
        <div class="text-sm text-amber-500/80">
            <strong class="text-amber-400">Seguridad:</strong>
            Guarda este archivo en un lugar seguro. Contiene datos de todas las empresas clientes
            de la plataforma, incluyendo información de usuarios y transacciones.
        </div>
    </div>

</div>

@endsection
