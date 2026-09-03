@extends('layouts.app')
@section('title', 'Nuevo Asiento Contable')
@section('page-title', 'Contabilidad · Nuevo Asiento')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('contabilidad.libro-diario.index') }}"
               class="w-9 h-9 bg-[#141c2e] border border-[#1e2d47] rounded-xl
                      flex items-center justify-center text-slate-400
                      hover:text-amber-500 hover:border-amber-500/50 transition-colors">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="font-display font-bold text-2xl text-slate-100">Nuevo Asiento Manual</h1>
                <p class="text-xs text-slate-500 mt-0.5">Registra comprobantes de diario, ajustes contables, nómina o pagos directos</p>
            </div>
        </div>
    </div>

    @if(session('error'))
    <div class="bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-5 py-3 flex items-center gap-3 text-sm">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif

    <form method="POST" action="{{ route('contabilidad.libro-diario.store') }}" id="asientoForm" class="space-y-6">
        @csrf

        {{-- Cabecera --}}
        <div class="card p-5">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Fecha del Asiento <span class="text-amber-500">*</span></label>
                    <input type="date" name="fecha" value="{{ old('fecha', today()->format('Y-m-d')) }}"
                           required class="form-input">
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Descripción General / Concepto <span class="text-amber-500">*</span></label>
                    <input type="text" name="descripcion" value="{{ old('descripcion') }}"
                           placeholder="Ej. Pago arriendo local del mes / Ajuste depreciación / Aporte capital"
                           required class="form-input">
                </div>
            </div>
        </div>

        {{-- Líneas del Asiento --}}
        <div class="card p-5 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-200 text-sm">Movimientos Contables (Partida Doble)</h3>
                    <p class="text-xs text-slate-500">Cada asiento debe estar cuadrado (Total Débitos = Total Créditos)</p>
                </div>
                <button type="button" onclick="agregarLinea()"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-400 hover:text-amber-300
                               bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/30 px-3 py-1.5 rounded-xl transition-colors">
                    <i class="fas fa-plus"></i> Añadir Línea
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="tablaLineas">
                    <thead>
                        <tr class="border-b border-[#1e2d47] text-xs text-slate-500 uppercase tracking-wider">
                            <th class="pb-3 text-left w-2/5">Cuenta PUC</th>
                            <th class="pb-3 text-left w-1/4">Detalle / Referencia</th>
                            <th class="pb-3 text-right w-1/6">Débito (COP)</th>
                            <th class="pb-3 text-right w-1/6">Crédito (COP)</th>
                            <th class="pb-3 w-10"></th>
                        </tr>
                    </thead>
                    <tbody id="lineasContainer" class="divide-y divide-[#1e2d47]">
                        {{-- Líneas dinámicas --}}
                    </tbody>
                </table>
            </div>

            {{-- Totales y Cuadre --}}
            <div class="bg-[#141c2e] border border-[#1e2d47] rounded-xl p-4 mt-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-center sm:text-right">
                    <div>
                        <span class="text-xs text-slate-500 uppercase tracking-wider block">Total Débito</span>
                        <span class="font-mono font-bold text-lg text-slate-200" id="txtTotalDebito">$0</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500 uppercase tracking-wider block">Total Crédito</span>
                        <span class="font-mono font-bold text-lg text-slate-200" id="txtTotalCredito">$0</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500 uppercase tracking-wider block">Diferencia</span>
                        <span class="font-mono font-bold text-lg text-emerald-400" id="txtDiferencia">$0</span>
                    </div>
                </div>

                <div class="mt-3 pt-3 border-t border-[#1e2d47] flex items-center justify-between flex-wrap gap-2 text-xs">
                    <div id="badgeCuadre" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        <i class="fas fa-check-circle"></i> Asiento Cuadrado
                    </div>
                    <span class="text-slate-500">Mínimo 2 cuentas con valores para registrar.</span>
                </div>
            </div>
        </div>

        {{-- Botones de Acción --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('contabilidad.libro-diario.index') }}"
               class="px-5 py-2.5 rounded-xl border border-[#1e2d47] text-slate-400 hover:text-white text-sm font-semibold transition-colors">
                Cancelar
            </a>
            <button type="submit" id="btnGuardar"
                    class="px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-black text-sm font-bold shadow-lg shadow-amber-500/20 transition-all">
                <i class="fas fa-save mr-1.5"></i> Guardar Asiento
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const cuentas = @json($cuentas);
    let indexLinea = 0;

    function formatNumber(num) {
        return new Intl.NumberFormat('es-CO').format(num);
    }

    function agregarLinea(cuentaId = '', desc = '', deb = 0, cred = 0) {
        const tbody = document.getElementById('lineasContainer');
        const tr = document.createElement('tr');
        tr.id = `linea_${indexLinea}`;
        tr.className = "hover:bg-[#1a2235]/40 transition-colors";

        let optionsHtml = '<option value="">-- Seleccionar cuenta PUC --</option>';
        cuentas.forEach(c => {
            const selected = (c.id == cuentaId) ? 'selected' : '';
            optionsHtml += `<option value="${c.id}" ${selected}>${c.codigo} - ${c.nombre} (${c.naturaleza.toUpperCase()})</option>`;
        });

        tr.innerHTML = `
            <td class="py-2.5 pr-2">
                <select name="lineas[${indexLinea}][cuenta_id]" required class="form-input text-xs py-2">
                    ${optionsHtml}
                </select>
            </td>
            <td class="py-2.5 px-2">
                <input type="text" name="lineas[${indexLinea}][descripcion]" value="${desc}"
                       placeholder="Detalle de línea..." class="form-input text-xs py-2">
            </td>
            <td class="py-2.5 px-2">
                <input type="number" step="0.01" min="0" name="lineas[${indexLinea}][debito]" value="${deb || ''}"
                       placeholder="0" oninput="calcularTotales()" class="form-input text-xs py-2 text-right font-mono inp-debito">
            </td>
            <td class="py-2.5 px-2">
                <input type="number" step="0.01" min="0" name="lineas[${indexLinea}][credito]" value="${cred || ''}"
                       placeholder="0" oninput="calcularTotales()" class="form-input text-xs py-2 text-right font-mono inp-credito">
            </td>
            <td class="py-2.5 pl-2 text-center">
                <button type="button" onclick="eliminarLinea(${indexLinea})"
                        class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-500 hover:text-red-400 hover:bg-red-500/10 transition-colors">
                    <i class="fas fa-trash-alt text-xs"></i>
                </button>
            </td>
        `;

        tbody.appendChild(tr);
        indexLinea++;
        calcularTotales();
    }

    function eliminarLinea(idx) {
        const tr = document.getElementById(`linea_${idx}`);
        if (tr) {
            tr.remove();
            calcularTotales();
        }
    }

    function calcularTotales() {
        let totalDeb = 0;
        let totalCred = 0;

        document.querySelectorAll('.inp-debito').forEach(inp => {
            const val = parseFloat(inp.value) || 0;
            totalDeb += val;
        });

        document.querySelectorAll('.inp-credito').forEach(inp => {
            const val = parseFloat(inp.value) || 0;
            totalCred += val;
        });

        const dif = Math.abs(totalDeb - totalCred);

        document.getElementById('txtTotalDebito').innerText = '$' + formatNumber(totalDeb);
        document.getElementById('txtTotalCredito').innerText = '$' + formatNumber(totalCred);
        document.getElementById('txtDiferencia').innerText = '$' + formatNumber(dif);

        const badge = document.getElementById('badgeCuadre');
        const btn = document.getElementById('btnGuardar');

        if (dif < 0.01 && totalDeb > 0) {
            badge.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20";
            badge.innerHTML = '<i class="fas fa-check-circle"></i> Asiento Cuadrado (Partida Doble OK)';
            btn.disabled = false;
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            badge.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20";
            badge.innerHTML = `<i class="fas fa-exclamation-triangle"></i> Descuadrado por $${formatNumber(dif)}`;
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }

    // Inicializar con 2 líneas por defecto
    document.addEventListener('DOMContentLoaded', () => {
        agregarLinea();
        agregarLinea();
    });
</script>
@endpush
@endsection
