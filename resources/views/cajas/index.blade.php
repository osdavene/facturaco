@extends('layouts.app')
@section('title', 'Control de Caja y Turnos')
@section('page-title', 'Punto de Venta · Control de Caja')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto" x-data="cajaManager()">

    {{-- ── HEADER & BOTONES ACCIÓN ────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-display font-bold text-2xl text-slate-100 flex items-center gap-2.5">
                <i class="fas fa-cash-register text-amber-500"></i>
                <span>Control de Caja y Turnos</span>
            </h1>
            <p class="text-slate-400 text-sm mt-0.5">
                Arqueos de caja, bases iniciales, movimientos de efectivo y cierres de turno (Reporte Z).
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('pos.index') }}"
               class="btn-secondary flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all">
                <i class="fas fa-calculator"></i>
                <span>Ir al POS</span>
            </a>

            <template x-if="!turno">
                <button @click="abrirModalApertura()"
                        class="bg-amber-500 hover:bg-amber-600 text-black font-bold px-4 py-2.5 rounded-xl text-sm transition-all shadow-lg shadow-amber-500/20 flex items-center gap-2">
                    <i class="fas fa-key"></i>
                    <span>Abrir Turno de Caja</span>
                </button>
            </template>

            <template x-if="turno">
                <div class="flex items-center gap-2">
                    <button @click="abrirModalMovimiento()"
                            class="bg-[#1a2235] hover:bg-[#222f47] border border-[#1e2d47] text-slate-200 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all flex items-center gap-2">
                        <i class="fas fa-exchange-alt text-amber-500"></i>
                        <span>Entrada / Salida</span>
                    </button>
                    <button @click="abrirModalCierre()"
                            class="bg-red-500 hover:bg-red-600 text-white font-bold px-4 py-2.5 rounded-xl text-sm transition-all shadow-lg shadow-red-500/20 flex items-center gap-2">
                        <i class="fas fa-lock"></i>
                        <span>Cerrar Caja (Arqueo)</span>
                    </button>
                </div>
            </template>
        </div>
    </div>

    {{-- ── TARJETA TURNO ACTIVO SI EXISTE ─────────────── --}}
    @if($turnoActivo)
    <div class="bg-gradient-to-r from-amber-500/10 via-[#141c2e] to-[#141c2e] border border-amber-500/30 rounded-2xl p-5 shadow-xl relative overflow-hidden">
        <div class="absolute -right-8 -top-8 w-36 h-36 bg-amber-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 relative z-10">
            <div>
                <div class="flex items-center gap-2.5 mb-1.5">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                        Turno #{{ $turnoActivo->id }} Abierto
                    </span>
                    <span class="text-xs text-slate-400">
                        Abierto por <strong class="text-slate-200">{{ $turnoActivo->usuario->name }}</strong> el {{ $turnoActivo->fecha_apertura->format('d/m/Y H:i') }}
                    </span>
                </div>
                <h3 class="text-xl font-bold text-white">
                    {{ $turnoActivo->caja->nombre ?? 'Caja Principal' }}
                </h3>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 w-full lg:w-auto">
                <div class="bg-[#111827]/80 border border-[#1e2d47] rounded-xl p-3">
                    <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Base Inicial</p>
                    <p class="text-base font-bold text-slate-200 mt-0.5">${{ number_format($turnoActivo->monto_apertura, 0, ',', '.') }}</p>
                </div>
                <div class="bg-[#111827]/80 border border-[#1e2d47] rounded-xl p-3">
                    <p class="text-[11px] uppercase tracking-wider text-emerald-400 font-semibold">Ventas Efectivo</p>
                    <p class="text-base font-bold text-emerald-400 mt-0.5">${{ number_format($turnoActivo->total_ventas_efectivo, 0, ',', '.') }}</p>
                </div>
                <div class="bg-[#111827]/80 border border-[#1e2d47] rounded-xl p-3">
                    <p class="text-[11px] uppercase tracking-wider text-blue-400 font-semibold">Total Ventas POS</p>
                    <p class="text-base font-bold text-blue-400 mt-0.5">${{ number_format($turnoActivo->total_ventas, 0, ',', '.') }}</p>
                </div>
                <div class="bg-[#111827]/80 border border-amber-500/30 bg-amber-500/5 rounded-xl p-3">
                    <p class="text-[11px] uppercase tracking-wider text-amber-400 font-semibold">Efectivo en Caja</p>
                    <p class="text-base font-bold text-amber-400 mt-0.5">${{ number_format($turnoActivo->monto_cierre_esperado, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── TABLA HISTORIAL DE TURNOS ───────────────────── --}}
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-[#1e2d47] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h3 class="font-display font-bold text-base text-slate-200">
                Historial de Turnos y Arqueos
            </h3>

            {{-- Filtros --}}
            <form method="GET" action="{{ route('cajas.index') }}" class="flex items-center gap-2">
                <select name="estado" onchange="this.form.submit()"
                        class="bg-[#1a2235] border border-[#1e2d47] rounded-lg px-3 py-1.5 text-xs text-slate-200 focus:outline-none focus:border-amber-500">
                    <option value="">Todos los estados</option>
                    <option value="abierto" {{ request('estado') === 'abierto' ? 'selected' : '' }}>Abiertos</option>
                    <option value="cerrado" {{ request('estado') === 'cerrado' ? 'selected' : '' }}>Cerrados</option>
                </select>
                <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" onchange="this.form.submit()"
                       class="bg-[#1a2235] border border-[#1e2d47] rounded-lg px-2.5 py-1.5 text-xs text-slate-200 focus:outline-none focus:border-amber-500">
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs uppercase bg-[#111827] text-slate-400 border-b border-[#1e2d47]">
                    <tr>
                        <th class="px-4 py-3">Turno</th>
                        <th class="px-4 py-3">Cajero</th>
                        <th class="px-4 py-3">Apertura</th>
                        <th class="px-4 py-3">Cierre</th>
                        <th class="px-4 py-3 text-right">Base</th>
                        <th class="px-4 py-3 text-right">Ventas Totales</th>
                        <th class="px-4 py-3 text-right">Efectivo Esperado</th>
                        <th class="px-4 py-3 text-right">Efectivo Real</th>
                        <th class="px-4 py-3 text-center">Diferencia</th>
                        <th class="px-4 py-3 text-center">Estado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1e2d47]">
                    @forelse($turnos as $t)
                    <tr class="hover:bg-[#1a2235]/50 transition-colors">
                        <td class="px-4 py-3 font-bold text-slate-200">
                            #{{ $t->id }}
                        </td>
                        <td class="px-4 py-3 font-medium text-slate-300">
                            {{ $t->usuario->name ?? 'Usuario' }}
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-400">
                            {{ $t->fecha_apertura ? $t->fecha_apertura->format('d/m/Y H:i') : '-' }}
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-400">
                            {{ $t->fecha_cierre ? $t->fecha_cierre->format('d/m/Y H:i') : 'En curso…' }}
                        </td>
                        <td class="px-4 py-3 text-right text-slate-300">
                            ${{ number_format($t->monto_apertura, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-emerald-400">
                            ${{ number_format($t->total_ventas, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right text-slate-300">
                            ${{ number_format($t->monto_cierre_esperado, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right font-medium text-slate-200">
                            {{ $t->monto_cierre_real !== null ? '$' . number_format($t->monto_cierre_real, 0, ',', '.') : '-' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($t->estado === 'cerrado')
                                @if($t->diferencia > 0)
                                    <span class="badge bg-emerald-500/20 text-emerald-400 text-xs">
                                        +${{ number_format($t->diferencia, 0, ',', '.') }}
                                    </span>
                                @elseif($t->diferencia < 0)
                                    <span class="badge bg-red-500/20 text-red-400 text-xs">
                                        -${{ number_format(abs($t->diferencia), 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="badge bg-slate-500/20 text-slate-300 text-xs">
                                        $0 (Exacto)
                                    </span>
                                @endif
                            @else
                                <span class="text-slate-500 text-xs">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($t->estado === 'abierto')
                                <span class="badge bg-emerald-500/20 text-emerald-400 text-xs">Abierto</span>
                            @else
                                <span class="badge bg-slate-500/20 text-slate-400 text-xs">Cerrado</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('cajas.cierre_ticket', $t->id) }}" target="_blank"
                               class="text-xs bg-[#1a2235] hover:bg-amber-500 hover:text-black text-amber-400 font-semibold px-2.5 py-1.5 rounded-lg border border-[#1e2d47] transition-colors inline-flex items-center gap-1.5"
                               title="Imprimir Reporte Z">
                                <i class="fas fa-print"></i>
                                <span>Reporte Z</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-8 text-slate-500">
                            <i class="fas fa-inbox text-3xl mb-2 block text-slate-600"></i>
                            No hay turnos registrados con los filtros seleccionados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($turnos->hasPages())
        <div class="p-4 border-t border-[#1e2d47]">
            {{ $turnos->links() }}
        </div>
        @endif
    </div>

    {{-- ── MODAL APERTURA DE CAJA ──────────────────────── --}}
    <div x-show="modalApertura" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div class="bg-[#141c2e] border border-amber-500/30 rounded-2xl w-full max-w-md p-6 shadow-2xl relative">
            <h3 class="text-lg font-bold text-white mb-1 flex items-center gap-2">
                <i class="fas fa-key text-amber-500"></i>
                <span>Apertura de Turno de Caja</span>
            </h3>
            <p class="text-xs text-slate-400 mb-4">
                Ingresa la base inicial en efectivo con la que iniciarás las ventas en el turno.
            </p>

            {{-- Info cajero --}}
            <div class="bg-[#111827] border border-[#1e2d47] rounded-xl p-3 mb-4 space-y-1 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-400">Cajero responsable:</span>
                    <span class="font-bold text-slate-200">{{ auth()->user()->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Terminal:</span>
                    <span class="font-bold text-slate-200">Caja Principal</span>
                </div>
            </div>

            <form @submit.prevent="guardarApertura()">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">
                            Base Inicial en Efectivo ($)
                        </label>
                        <input type="number" step="1000" min="0" x-model="formApertura.monto_apertura" required
                               placeholder="Ej: 100000"
                               class="w-full bg-[#1a2235] border border-amber-500/50 rounded-xl px-4 py-3 text-xl font-bold text-amber-400 text-center focus:outline-none focus:border-amber-500">
                        
                        <div class="grid grid-cols-4 gap-1.5 mt-2">
                            <button type="button" @click="formApertura.monto_apertura = 50000"
                                    class="bg-[#1a2235] hover:bg-[#222f47] border border-[#1e2d47] text-slate-300 py-1.5 rounded-lg text-[11px] font-semibold transition-all">
                                $50.000
                            </button>
                            <button type="button" @click="formApertura.monto_apertura = 100000"
                                    class="bg-[#1a2235] hover:bg-[#222f47] border border-[#1e2d47] text-slate-300 py-1.5 rounded-lg text-[11px] font-semibold transition-all">
                                $100.000
                            </button>
                            <button type="button" @click="formApertura.monto_apertura = 200000"
                                    class="bg-[#1a2235] hover:bg-[#222f47] border border-[#1e2d47] text-slate-300 py-1.5 rounded-lg text-[11px] font-semibold transition-all">
                                $200.000
                            </button>
                            <button type="button" @click="formApertura.monto_apertura = 500000"
                                    class="bg-[#1a2235] hover:bg-[#222f47] border border-[#1e2d47] text-slate-300 py-1.5 rounded-lg text-[11px] font-semibold transition-all">
                                $500.000
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">
                            Observaciones (Opcional)
                        </label>
                        <input type="text" x-model="formApertura.observaciones" placeholder="Ej: Turno mañana"
                               class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2 text-sm text-slate-200 focus:outline-none focus:border-amber-500">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <button type="button" @click="modalApertura = false"
                            class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-400 hover:text-white transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" :disabled="guardando"
                            class="bg-amber-500 hover:bg-amber-600 text-black font-bold px-5 py-2.5 rounded-xl text-sm transition-all flex items-center gap-2">
                        <i class="fas fa-check"></i>
                        <span x-text="guardando ? 'Abriendo…' : 'Abrir Turno'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── MODAL MOVIMIENTO (ENTRADA / SALIDA) ─────────── --}}
    <div x-show="modalMovimiento" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl w-full max-w-md p-6 shadow-2xl relative">
            <h3 class="text-lg font-bold text-white mb-1 flex items-center gap-2">
                <i class="fas fa-exchange-alt text-amber-500"></i>
                <span>Movimiento de Efectivo</span>
            </h3>
            <p class="text-xs text-slate-400 mb-4">
                Registra una entrada extra o salida menor de dinero de la caja registradora.
            </p>

            <form @submit.prevent="guardarMovimiento()">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">
                            Tipo de Movimiento
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" value="salida" x-model="formMovimiento.tipo" class="hidden">
                                <div :class="formMovimiento.tipo === 'salida' ? 'bg-red-500/20 border-red-500 text-red-400' : 'bg-[#1a2235] border-[#1e2d47] text-slate-400'"
                                     class="border rounded-xl p-3 text-center font-bold text-sm transition-all flex items-center justify-center gap-2">
                                    <i class="fas fa-arrow-circle-up"></i>
                                    <span>Salida / Retiro</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" value="entrada" x-model="formMovimiento.tipo" class="hidden">
                                <div :class="formMovimiento.tipo === 'entrada' ? 'bg-emerald-500/20 border-emerald-500 text-emerald-400' : 'bg-[#1a2235] border-[#1e2d47] text-slate-400'"
                                     class="border rounded-xl p-3 text-center font-bold text-sm transition-all flex items-center justify-center gap-2">
                                    <i class="fas fa-arrow-circle-down"></i>
                                    <span>Entrada Extra</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">
                            Monto ($)
                        </label>
                        <input type="number" step="100" min="1" x-model="formMovimiento.monto" required
                               placeholder="Ej: 20000"
                               class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5 text-base font-bold text-white focus:outline-none focus:border-amber-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">
                            Motivo / Concepto
                        </label>
                        <input type="text" x-model="formMovimiento.motivo" required
                               placeholder="Ej: Compra de insumos / Retiro de cambio"
                               class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-amber-500">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <button type="button" @click="modalMovimiento = false"
                            class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-400 hover:text-white transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" :disabled="guardando"
                            class="bg-amber-500 hover:bg-amber-600 text-black font-bold px-5 py-2.5 rounded-xl text-sm transition-all flex items-center gap-2">
                        <i class="fas fa-save"></i>
                        <span x-text="guardando ? 'Guardando…' : 'Registrar Movimiento'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── MODAL CIERRE DE CAJA / ARQUEO ───────────────── --}}
    <div x-show="modalCierre" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl w-full max-w-lg p-6 shadow-2xl relative">
            <h3 class="text-lg font-bold text-white mb-1 flex items-center gap-2">
                <i class="fas fa-lock text-red-500"></i>
                <span>Arqueo y Cierre de Caja</span>
            </h3>
            <p class="text-xs text-slate-400 mb-4">
                Cuenta el efectivo físico en la gaveta e ingrésalo para calcular si el turno cuadra con exactitud.
            </p>

            <form @submit.prevent="guardarCierre()">
                <div class="space-y-4">
                    {{-- Resumen del turno --}}
                    <div class="bg-[#111827] border border-[#1e2d47] rounded-xl p-3.5 space-y-2 text-xs">
                        <div class="flex justify-between text-slate-400">
                            <span>(+) Base inicial:</span>
                            <span class="font-medium text-slate-200" x-text="'$' + formatoMoneda(turno?.monto_apertura)"></span>
                        </div>
                        <div class="flex justify-between text-emerald-400">
                            <span>(+) Ventas efectivo:</span>
                            <span class="font-medium" x-text="'$' + formatoMoneda(turno?.total_ventas_efectivo)"></span>
                        </div>
                        <div class="flex justify-between text-slate-400">
                            <span>(+) Entradas menores:</span>
                            <span class="font-medium text-slate-200" x-text="'$' + formatoMoneda(turno?.total_entradas)"></span>
                        </div>
                        <div class="flex justify-between text-red-400">
                            <span>(-) Salidas / Retiros:</span>
                            <span class="font-medium" x-text="'-$' + formatoMoneda(turno?.total_salidas)"></span>
                        </div>
                        <div class="border-t border-[#1e2d47] pt-2 flex justify-between font-bold text-sm text-amber-400">
                            <span>(=) Efectivo Esperado:</span>
                            <span x-text="'$' + formatoMoneda(turno?.monto_cierre_esperado)"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">
                            Efectivo Real Contado en Caja ($)
                        </label>
                        <input type="number" step="100" min="0" x-model="formCierre.monto_cierre_real" required
                               placeholder="Total de billetes y monedas contados"
                               class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-3 text-lg font-bold text-white focus:outline-none focus:border-amber-500">
                    </div>

                    {{-- Diferencia calculada en vivo --}}
                    <div x-show="formCierre.monto_cierre_real !== ''"
                         class="p-3 rounded-xl border text-center font-bold text-sm"
                         :class="calcularDiferencia() >= 0 ? (calcularDiferencia() === 0 ? 'bg-slate-800 border-slate-700 text-slate-300' : 'bg-emerald-500/20 border-emerald-500/40 text-emerald-400') : 'bg-red-500/20 border-red-500/40 text-red-400'">
                        <span x-text="calcularDiferencia() > 0 ? 'Sobrante en caja: +$' + formatoMoneda(calcularDiferencia()) : (calcularDiferencia() < 0 ? 'Faltante en caja: -$' + formatoMoneda(Math.abs(calcularDiferencia())) : '¡Caja cuadrada con exactitud ($0)!')"></span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">
                            Observaciones de Cierre
                        </label>
                        <textarea x-model="formCierre.observaciones" rows="2" placeholder="Notas sobre el cierre o novedades del turno…"
                                  class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2 text-sm text-slate-200 focus:outline-none focus:border-amber-500"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <button type="button" @click="modalCierre = false"
                            class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-400 hover:text-white transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" :disabled="guardando"
                            class="bg-red-500 hover:bg-red-600 text-white font-bold px-5 py-2.5 rounded-xl text-sm transition-all flex items-center gap-2">
                        <i class="fas fa-lock"></i>
                        <span x-text="guardando ? 'Cerrando…' : 'Confirmar Cierre de Caja'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function cajaManager() {
    return {
        turno: @json($turnoActivo),
        modalApertura: false,
        modalMovimiento: false,
        modalCierre: false,
        guardando: false,

        formApertura: {
            monto_apertura: 100000,
            observaciones: ''
        },
        formMovimiento: {
            tipo: 'salida',
            monto: '',
            motivo: ''
        },
        formCierre: {
            monto_cierre_real: '',
            observaciones: ''
        },

        formatoMoneda(val) {
            if (val === null || val === undefined) return '0';
            return Math.round(Number(val)).toLocaleString('es-CO');
        },

        calcularDiferencia() {
            if (this.formCierre.monto_cierre_real === '') return 0;
            const real = Number(this.formCierre.monto_cierre_real) || 0;
            const esp = Number(this.turno?.monto_cierre_esperado) || 0;
            return real - esp;
        },

        abrirModalApertura() {
            this.modalApertura = true;
        },

        abrirModalMovimiento() {
            this.formMovimiento = { tipo: 'salida', monto: '', motivo: '' };
            this.modalMovimiento = true;
        },

        abrirModalCierre() {
            this.formCierre.monto_cierre_real = this.turno?.monto_cierre_esperado || '';
            this.formCierre.observaciones = '';
            this.modalCierre = true;
        },

        async guardarApertura() {
            this.guardando = true;
            try {
                const res = await fetch('{{ route('cajas.abrir') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(this.formApertura)
                });
                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Error al abrir turno');
                }
            } catch (e) {
                alert('Error de conexión al servidor');
            } finally {
                this.guardando = false;
            }
        },

        async guardarMovimiento() {
            this.guardando = true;
            try {
                const res = await fetch('{{ route('cajas.movimiento') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(this.formMovimiento)
                });
                const data = await res.json();
                if (data.success) {
                    this.turno = data.turno;
                    this.modalMovimiento = false;
                    window.toast?.('Movimiento registrado correctamente', 'success');
                    window.location.reload();
                } else {
                    alert(data.message || 'Error al registrar movimiento');
                }
            } catch (e) {
                alert('Error de conexión al servidor');
            } finally {
                this.guardando = false;
            }
        },

        async guardarCierre() {
            this.guardando = true;
            try {
                const res = await fetch('{{ route('cajas.cerrar') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(this.formCierre)
                });
                const data = await res.json();
                if (data.success) {
                    if (data.ticket_url) {
                        window.open(data.ticket_url, '_blank');
                    }
                    window.location.reload();
                } else {
                    alert(data.message || 'Error al cerrar turno');
                }
            } catch (e) {
                alert('Error de conexión al servidor');
            } finally {
                this.guardando = false;
            }
        }
    };
}
</script>
@endsection
