@extends('layouts.app')
@section('title', 'Impuestos / DIAN')
@section('page-title', 'Impuestos / DIAN')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-display font-bold text-2xl">Impuestos / DIAN</h1>
        <p class="text-slate-500 text-sm mt-1">
            Resumen tributario del período:
            <span class="text-amber-500 font-semibold">
                {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }}
                al {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}
            </span>
        </p>
    </div>
    <a href="{{ route('impuestos.pdf', request()->query()) }}" target="_blank"
       class="inline-flex items-center gap-2 bg-red-500/10 border border-red-500/30
              text-red-400 hover:bg-red-500/20 px-5 py-2.5 rounded-xl transition-colors text-sm">
        <i class="fas fa-file-pdf"></i> Exportar PDF
    </a>
</div>

{{-- FILTROS DE PERÍODO --}}
<form method="GET" action="{{ route('impuestos.index') }}"
      class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5 mb-6" id="form-filtros">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Tipo período --}}
        <div>
            <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wider">
                Tipo de Período
            </label>
            <div class="flex gap-1">
                @foreach(['bimestral'=>'Bimestral','mensual'=>'Mensual','anual'=>'Anual'] as $val=>$label)
                <button type="button"
                        onclick="setPeriodo('{{ $val }}')"
                        id="btn-{{ $val }}"
                        class="flex-1 py-2 text-xs font-semibold rounded-lg transition-colors
                               {{ $periodoTipo==$val
                                  ? 'bg-amber-500 text-black'
                                  : 'bg-[#1a2235] border border-[#1e2d47] text-slate-400 hover:text-slate-200' }}">
                    {{ $label }}
                </button>
                @endforeach
            </div>
            <input type="hidden" name="periodo" id="input-periodo" value="{{ $periodoTipo }}">
        </div>

        {{-- Año --}}
        <div>
            <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wider">
                Año
            </label>
            <select name="anio"
                    class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                           text-sm focus:outline-none focus:border-amber-500"
                    style="color:#e2e8f0">
                @foreach($aniosDisponibles as $a)
                <option value="{{ $a }}" {{ $anio==$a?'selected':'' }}>{{ $a }}</option>
                @endforeach
                <option value="{{ now()->year }}" {{ $anio==now()->year?'selected':'' }}>
                    {{ now()->year }}
                </option>
            </select>
        </div>

        {{-- Bimestre / Mes --}}
        <div id="campo-bimestre" class="{{ $periodoTipo=='anual' ? 'hidden':'' }}">
            <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wider"
                   id="label-periodo">
                {{ $periodoTipo=='mensual' ? 'Mes' : 'Bimestre' }}
            </label>
            {{-- Bimestre --}}
            <select name="bimestre" id="select-bimestre"
                    class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                           text-sm focus:outline-none focus:border-amber-500
                           {{ $periodoTipo=='mensual' ? 'hidden':'' }}"
                    style="color:#e2e8f0">
                @foreach([1=>'1° Ene-Feb',2=>'2° Mar-Abr',3=>'3° May-Jun',
                           4=>'4° Jul-Ago',5=>'5° Sep-Oct',6=>'6° Nov-Dic'] as $b=>$bl)
                <option value="{{ $b }}" {{ $bimestre==$b?'selected':'' }}>{{ $bl }}</option>
                @endforeach
            </select>
            {{-- Mes --}}
            <select name="mes" id="select-mes"
                    class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                           text-sm focus:outline-none focus:border-amber-500
                           {{ $periodoTipo!='mensual' ? 'hidden':'' }}"
                    style="color:#e2e8f0">
                @foreach([1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
                           7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',
                           11=>'Noviembre',12=>'Diciembre'] as $m=>$ml)
                <option value="{{ $m }}" {{ request('mes',now()->month)==$m?'selected':'' }}>
                    {{ $ml }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="flex items-end">
            <button type="submit"
                    class="w-full bg-amber-500 hover:bg-amber-600 text-black font-semibold
                           px-5 py-2.5 rounded-xl transition-colors flex items-center justify-center gap-2">
                <i class="fas fa-search"></i> Consultar
            </button>
        </div>
    </div>
</form>

{{-- KPIs PRINCIPALES --}}
<div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5">
        <div class="flex items-center justify-between mb-3">
            <div class="text-xs text-slate-500 uppercase tracking-wider">Total Ventas</div>
            <div class="w-8 h-8 bg-emerald-500/10 rounded-lg flex items-center justify-center text-emerald-500">
                <i class="fas fa-dollar-sign text-sm"></i>
            </div>
        </div>
        <div class="font-display font-bold text-2xl text-emerald-500">
            ${{ number_format($resumen['total_ventas'], 0, ',', '.') }}
        </div>
        <div class="text-xs text-slate-500 mt-1">{{ $resumen['num_facturas'] }} facturas</div>
    </div>

    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5">
        <div class="flex items-center justify-between mb-3">
            <div class="text-xs text-slate-500 uppercase tracking-wider">Base Gravable</div>
            <div class="w-8 h-8 bg-blue-500/10 rounded-lg flex items-center justify-center text-blue-400">
                <i class="fas fa-calculator text-sm"></i>
            </div>
        </div>
        <div class="font-display font-bold text-2xl text-blue-400">
            ${{ number_format($resumen['base_gravable'], 0, ',', '.') }}
        </div>
        <div class="text-xs text-slate-500 mt-1">Subtotal sin impuestos</div>
    </div>

    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-5 col-span-2 lg:col-span-1">
        <div class="flex items-center justify-between mb-3">
            <div class="text-xs text-slate-500 uppercase tracking-wider">IVA Generado</div>
            <div class="w-8 h-8 bg-amber-500/10 rounded-lg flex items-center justify-center text-amber-500">
                <i class="fas fa-percent text-sm"></i>
            </div>
        </div>
        <div class="font-display font-bold text-2xl text-amber-500">
            ${{ number_format($resumen['total_iva'], 0, ',', '.') }}
        </div>
        <div class="text-xs text-slate-500 mt-1">IVA por pagar / compensar</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">

    {{-- Retenciones --}}
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-6">
        <h3 class="font-display font-bold text-base mb-5 flex items-center gap-2">
            <i class="fas fa-hand-holding-usd text-amber-500 text-sm"></i>
            Retenciones en la Fuente
        </h3>
        <div class="space-y-4">
            <div class="flex items-center justify-between p-3 bg-[#1a2235] rounded-xl">
                <div>
                    <div class="text-sm font-semibold" style="color:#e2e8f0">ReteFuente</div>
                    <div class="text-xs text-slate-500 mt-0.5">Retención en la fuente aplicada</div>
                </div>
                <div class="text-right">
                    <div class="font-display font-bold text-lg text-orange-400">
                        ${{ number_format($resumen['total_rete'], 0, ',', '.') }}
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-between p-3 bg-[#1a2235] rounded-xl">
                <div>
                    <div class="text-sm font-semibold" style="color:#e2e8f0">ReteICA</div>
                    <div class="text-xs text-slate-500 mt-0.5">Retención industria y comercio</div>
                </div>
                <div class="text-right">
                    <div class="font-display font-bold text-lg text-purple-400">
                        ${{ number_format($resumen['total_reteica'], 0, ',', '.') }}
                    </div>
                </div>
            </div>
            <div class="border-t border-[#1e2d47] pt-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-slate-400">Total Retenciones</span>
                    <span class="font-display font-bold text-xl text-red-400">
                        ${{ number_format($resumen['total_rete'] + $resumen['total_reteica'], 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- IVA por tasa --}}
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-6">
        <h3 class="font-display font-bold text-base mb-5 flex items-center gap-2">
            <i class="fas fa-percent text-amber-500 text-sm"></i>
            IVA por Tarifa
        </h3>
        @if($ivaPorTasa->count())
        <div class="space-y-3">
            @foreach($ivaPorTasa as $tasa)
            <div class="p-3 bg-[#1a2235] rounded-xl">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold
                                     {{ $tasa->iva_pct == 19 ? 'bg-amber-500/10 text-amber-500' :
                                        ($tasa->iva_pct == 5 ? 'bg-blue-500/10 text-blue-400' :
                                         'bg-slate-500/10 text-slate-400') }}">
                            {{ $tasa->iva_pct }}%
                        </span>
                        <div>
                            <div class="text-sm font-semibold" style="color:#e2e8f0">
                                Tarifa {{ $tasa->iva_pct }}%
                            </div>
                            <div class="text-xs text-slate-500">
                                {{ number_format($tasa->num_items) }} ítems
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-amber-500">
                            ${{ number_format($tasa->iva, 0, ',', '.') }}
                        </div>
                        <div class="text-xs text-slate-500">
                            Base: ${{ number_format($tasa->base, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
                @php $pct = $resumen['total_iva'] > 0 ? ($tasa->iva / $resumen['total_iva']) * 100 : 0; @endphp
                <div class="w-full bg-[#141c2e] rounded-full h-1">
                    <div class="h-1 rounded-full bg-amber-500" style="width:{{ $pct }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-8 text-slate-500 text-sm">
            No hay datos en el período seleccionado
        </div>
        @endif
    </div>
</div>

{{-- Resumen por mes --}}
@if($ventasPorMes->count())
<div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-[#1e2d47]">
        <h3 class="font-display font-bold text-base">Detalle por Mes</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-[#1e2d47]">
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-5 py-3">Mes</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Facturas</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Base Gravable</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">IVA</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden md:table-cell">ReteFuente</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-5 py-3">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ventasPorMes as $mes)
                @php
                    $nombreMes = \Carbon\Carbon::createFromFormat('Y-m', $mes->mes)
                                    ->locale('es')->monthName;
                @endphp
                <tr class="border-b border-[#1e2d47]/50 hover:bg-[#1a2235]/50 transition-colors">
                    <td class="px-5 py-3 text-sm font-semibold capitalize" style="color:#e2e8f0">
                        {{ $nombreMes }}
                    </td>
                    <td class="px-3 py-3 text-right text-sm text-slate-400">
                        {{ $mes->num_facturas }}
                    </td>
                    <td class="px-3 py-3 text-right text-sm" style="color:#e2e8f0">
                        ${{ number_format($mes->base, 0, ',', '.') }}
                    </td>
                    <td class="px-3 py-3 text-right text-sm text-amber-500 font-semibold">
                        ${{ number_format($mes->iva, 0, ',', '.') }}
                    </td>
                    <td class="px-3 py-3 text-right text-sm text-orange-400 hidden md:table-cell">
                        ${{ number_format($mes->retefuente, 0, ',', '.') }}
                    </td>
                    <td class="px-5 py-3 text-right text-sm font-bold text-emerald-500">
                        ${{ number_format($mes->total, 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-[#1e2d47] bg-[#1a2235]">
                    <td class="px-5 py-3 text-sm font-bold" style="color:#e2e8f0">TOTALES</td>
                    <td class="px-3 py-3 text-right text-sm font-semibold" style="color:#e2e8f0">
                        {{ $resumen['num_facturas'] }}
                    </td>
                    <td class="px-3 py-3 text-right text-sm font-semibold" style="color:#e2e8f0">
                        ${{ number_format($resumen['base_gravable'], 0, ',', '.') }}
                    </td>
                    <td class="px-3 py-3 text-right text-sm font-bold text-amber-500">
                        ${{ number_format($resumen['total_iva'], 0, ',', '.') }}
                    </td>
                    <td class="px-3 py-3 text-right text-sm font-semibold text-orange-400 hidden md:table-cell">
                        ${{ number_format($resumen['total_rete'], 0, ',', '.') }}
                    </td>
                    <td class="px-5 py-3 text-right text-sm font-bold text-emerald-500">
                        ${{ number_format($resumen['total_ventas'], 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endif

{{-- Top clientes IVA --}}
@if($topClientesIva->count())
<div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-[#1e2d47]">
        <h3 class="font-display font-bold text-base">Top Clientes — IVA Generado</h3>
        <p class="text-xs text-slate-500 mt-0.5">Clientes que más IVA generaron en el período</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-[#1e2d47]">
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-5 py-3">#</th>
                    <th class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Cliente</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3 hidden sm:table-cell">Facturas</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 py-3">Base</th>
                    <th class="text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-5 py-3">IVA</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topClientesIva as $i => $cli)
                <tr class="border-b border-[#1e2d47]/50 hover:bg-[#1a2235]/50 transition-colors">
                    <td class="px-5 py-3">
                        <span class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-bold inline-flex
                                     {{ $i===0 ? 'bg-amber-500 text-black' : 'bg-[#1a2235] text-slate-400' }}">
                            {{ $i + 1 }}
                        </span>
                    </td>
                    <td class="px-3 py-3 text-sm font-medium" style="color:#e2e8f0">
                        {{ $cli->cliente_nombre }}
                    </td>
                    <td class="px-3 py-3 text-right text-sm text-slate-400 hidden sm:table-cell">
                        {{ $cli->facturas }}
                    </td>
                    <td class="px-3 py-3 text-right text-sm text-slate-400">
                        ${{ number_format($cli->base, 0, ',', '.') }}
                    </td>
                    <td class="px-5 py-3 text-right text-sm font-bold text-amber-500">
                        ${{ number_format($cli->iva, 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Guía DIAN --}}
<div class="bg-blue-500/5 border border-blue-500/20 rounded-2xl p-6">
    <h3 class="font-display font-bold text-base mb-4 flex items-center gap-2">
        <i class="fas fa-info-circle text-blue-400"></i>
        Guía de Fechas DIAN — {{ now()->year }}
    </h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach([
            ['1° Bimestre', 'Ene-Feb', 'Marzo'],
            ['2° Bimestre', 'Mar-Abr', 'Mayo'],
            ['3° Bimestre', 'May-Jun', 'Julio'],
            ['4° Bimestre', 'Jul-Ago', 'Septiembre'],
            ['5° Bimestre', 'Sep-Oct', 'Noviembre'],
            ['6° Bimestre', 'Nov-Dic', 'Enero '.( now()->year + 1)],
        ] as [$bim, $meses, $declarar])
        <div class="bg-[#141c2e] border border-[#1e2d47] rounded-xl p-3">
            <div class="text-xs font-bold text-blue-400 mb-1">{{ $bim }}</div>
            <div class="text-sm font-semibold" style="color:#e2e8f0">{{ $meses }}</div>
            <div class="text-xs text-slate-500 mt-1">
                <i class="fas fa-calendar-check mr-1"></i>
                Declarar en: {{ $declarar }}
            </div>
        </div>
        @endforeach
    </div>
    <p class="text-xs text-slate-500 mt-4">
        * Las fechas exactas varían según el último dígito del NIT. Consulta el calendario tributario en
        <span class="text-blue-400">dian.gov.co</span>
    </p>
</div>

@endsection

@push('scripts')
<script>
function setPeriodo(tipo) {
    document.getElementById('input-periodo').value = tipo;

    // Actualizar botones
    ['bimestral','mensual','anual'].forEach(t => {
        const btn = document.getElementById('btn-'+t);
        if (t === tipo) {
            btn.className = btn.className.replace(
                'bg-[#1a2235] border border-[#1e2d47] text-slate-400 hover:text-slate-200',
                'bg-amber-500 text-black'
            );
        } else {
            btn.className = btn.className.replace(
                'bg-amber-500 text-black',
                'bg-[#1a2235] border border-[#1e2d47] text-slate-400 hover:text-slate-200'
            );
        }
    });

    // Mostrar/ocultar selectores
    const campoBim = document.getElementById('campo-bimestre');
    const label    = document.getElementById('label-periodo');
    const selBim   = document.getElementById('select-bimestre');
    const selMes   = document.getElementById('select-mes');

    if (tipo === 'anual') {
        campoBim.classList.add('hidden');
    } else {
        campoBim.classList.remove('hidden');
        if (tipo === 'mensual') {
            label.textContent = 'Mes';
            selBim.classList.add('hidden');
            selMes.classList.remove('hidden');
        } else {
            label.textContent = 'Bimestre';
            selBim.classList.remove('hidden');
            selMes.classList.add('hidden');
        }
    }
}
</script>
@endpush