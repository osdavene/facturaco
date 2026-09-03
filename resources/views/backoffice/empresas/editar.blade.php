@extends('backoffice.layout')
@section('title', 'Editar empresa')

@section('content')

<div class="mb-8">
    <a href="{{ route('backoffice.empresas') }}"
       class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-200 text-sm transition-colors mb-4">
        <i class="fas fa-arrow-left text-xs"></i>Volver a empresas
    </a>
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-[#1a2235] border border-[#1e2d47] rounded-xl
                    flex items-center justify-center font-display font-black text-amber-500">
            {{ strtoupper(substr($empresa->razon_social, 0, 2)) }}
        </div>
        <div>
            <h1 class="font-display font-black text-2xl">{{ $empresa->razon_social }}</h1>
            <p class="text-slate-500 text-sm">
                NIT: {{ $empresa->nit }}
                @if($empresa->esFilial())
                    · Filial de <span class="text-amber-500">{{ $empresa->padre->razon_social }}</span>
                @else
                    · <span class="text-amber-500">Empresa matriz</span>
                @endif
            </p>
        </div>
    </div>
</div>

<div class="max-w-2xl mx-auto space-y-5">

    {{-- Datos básicos --}}
    <form method="POST" action="{{ route('backoffice.empresas.update', $empresa) }}">
        @csrf @method('PUT')

        <div class="card p-6 space-y-5">
            <h2 class="font-display font-bold text-base flex items-center gap-2">
                <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center text-black text-xs font-black">1</span>
                Datos de la empresa
            </h2>

            <div>
                <label class="form-label">Razón social *</label>
                <input type="text" name="razon_social" value="{{ old('razon_social', $empresa->razon_social) }}" required
                       class="form-input">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">NIT *</label>
                    <input type="text" name="nit" value="{{ old('nit', $empresa->nit) }}" required
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" value="{{ old('telefono', $empresa->telefono) }}"
                           class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $empresa->email) }}"
                           class="form-input">
                </div>
                <x-ubicacion
                    :departamento="old('departamento', $empresa->departamento)"
                    :municipio="old('municipio', $empresa->municipio)"
                />
            </div>

            <div>
                <label class="form-label">Jerarquía</label>
                <select name="empresa_padre_id"
                        class="form-input">
                    <option value="">— Empresa matriz independiente —</option>
                    @foreach($matrices as $m)
                        <option value="{{ $m->id }}"
                            {{ old('empresa_padre_id', $empresa->empresa_padre_id) == $m->id ? 'selected' : '' }}>
                            {{ $m->razon_social }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Plan y Límites SaaS con cálculo automático de meses completos --}}
        <div class="card p-6 space-y-5 mt-5" x-data="planVencimientoManager({
            planId: '{{ old('plan_id', $empresa->plan_id) }}',
            vencimiento: '{{ old('plan_vencimiento', $empresa->plan_vencimiento?->format('Y-m-d')) }}',
            planes: {{ json_encode($planes) }}
        })">
            <div class="flex items-center justify-between border-b border-[#1e2d47] pb-3">
                <h2 class="font-display font-bold text-base flex items-center gap-2">
                    <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center text-black text-xs font-black">2</span>
                    Plan SaaS y Vigencia Automática
                </h2>
                <span class="text-xs text-amber-400 font-medium" x-show="vencimientoTexto" x-text="vencimientoTexto"></span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Plan Asignado</label>
                    <select name="plan_id" x-model="planId" @change="alCambiarPlan()" class="form-input">
                        <option value="">— Sin Plan (Facturas Ilimitadas) —</option>
                        @foreach($planes as $p)
                            <option value="{{ $p->id }}" {{ old('plan_id', $empresa->plan_id) == $p->id ? 'selected' : '' }}>
                                {{ $p->nombre }} ({{ $p->precio_formateado }} · {{ $p->limite_facturas_texto }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="form-label mb-0">Fecha de Vencimiento (Automática)</label>
                        <span class="text-[11px] text-slate-400" x-show="diasRestantesTexto" x-text="diasRestantesTexto"></span>
                    </div>
                    <input type="date" name="plan_vencimiento" x-model="vencimiento"
                           class="form-input">
                </div>

                {{-- Botones de Acceso Rápido para Vigencia --}}
                <div class="sm:col-span-2 bg-[#1a2235]/60 border border-[#1e2d47] rounded-xl p-3.5 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-300">Fijar Período Automático (Meses Completos):</span>
                        <span class="text-[11px] text-slate-500">Calcula meses de 30 o 31 días automáticamente</span>
                    </div>
                    <div class="flex flex-wrap gap-2 pt-1">
                        <button type="button" @click="fijarMesesDesdeHoy(1)"
                                class="px-3 py-1.5 bg-[#111827] hover:bg-amber-500/10 hover:text-amber-400 border border-[#1e2d47] hover:border-amber-500/30 rounded-lg text-xs font-semibold text-slate-300 transition-colors flex items-center gap-1.5">
                            <i class="fas fa-calendar-day text-amber-500 text-[10px]"></i>
                            <span>1 Mes Completo</span>
                        </button>
                        <button type="button" @click="fijarMesesDesdeHoy(3)"
                                class="px-3 py-1.5 bg-[#111827] hover:bg-amber-500/10 hover:text-amber-400 border border-[#1e2d47] hover:border-amber-500/30 rounded-lg text-xs font-semibold text-slate-300 transition-colors flex items-center gap-1.5">
                            <i class="fas fa-calendar-week text-blue-400 text-[10px]"></i>
                            <span>3 Meses (Trimestre)</span>
                        </button>
                        <button type="button" @click="fijarMesesDesdeHoy(6)"
                                class="px-3 py-1.5 bg-[#111827] hover:bg-amber-500/10 hover:text-amber-400 border border-[#1e2d47] hover:border-amber-500/30 rounded-lg text-xs font-semibold text-slate-300 transition-colors flex items-center gap-1.5">
                            <i class="fas fa-calendar text-emerald-400 text-[10px]"></i>
                            <span>6 Meses (Semestre)</span>
                        </button>
                        <button type="button" @click="fijarMesesDesdeHoy(12)"
                                class="px-3 py-1.5 bg-[#111827] hover:bg-amber-500/10 hover:text-amber-400 border border-[#1e2d47] hover:border-amber-500/30 rounded-lg text-xs font-semibold text-slate-300 transition-colors flex items-center gap-1.5">
                            <i class="fas fa-calendar-star text-purple-400 text-[10px]"></i>
                            <span>1 Año (12 Meses)</span>
                        </button>
                        <button type="button" @click="extenderUnMes()"
                                class="px-3 py-1.5 bg-amber-500/10 text-amber-400 border border-amber-500/30 hover:bg-amber-500/20 rounded-lg text-xs font-semibold transition-colors flex items-center gap-1.5 ml-auto">
                            <i class="fas fa-plus text-[10px]"></i>
                            <span>Renovar (+1 Mes)</span>
                        </button>
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <label class="form-label">Facturas Extra / Recargas Adicionales</label>
                    <input type="number" name="plan_facturas_adicionales"
                           value="{{ old('plan_facturas_adicionales', $empresa->plan_facturas_adicionales ?? 0) }}"
                           min="0" placeholder="0" class="form-input">
                    <p class="text-[11px] text-slate-500 mt-1">Facturas extra adicionales al límite mensual del plan.</p>
                </div>
            </div>
        </div>

        <script>
        function planVencimientoManager(config) {
            return {
                planId: config.planId || '',
                vencimiento: config.vencimiento || '',
                planes: config.planes || [],
                
                get vencimientoTexto() {
                    if (!this.vencimiento) return '';
                    const partes = this.vencimiento.split('-');
                    if (partes.length !== 3) return '';
                    const d = new Date(parseInt(partes[0]), parseInt(partes[1]) - 1, parseInt(partes[2]));
                    return 'Vence: ' + d.toLocaleDateString('es-CO', { day: 'numeric', month: 'short', year: 'numeric' });
                },

                get diasRestantesTexto() {
                    if (!this.vencimiento) return '';
                    const hoy = new Date();
                    hoy.setHours(0, 0, 0, 0);
                    const partes = this.vencimiento.split('-');
                    const target = new Date(parseInt(partes[0]), parseInt(partes[1]) - 1, parseInt(partes[2]));
                    const diff = Math.ceil((target - hoy) / (1000 * 60 * 60 * 24));
                    if (diff < 0) return 'Vencido hace ' + Math.abs(diff) + ' días';
                    if (diff === 0) return 'Vence hoy';
                    return 'Faltan ' + diff + ' días';
                },

                alCambiarPlan() {
                    if (this.planId && !this.vencimiento) {
                        this.fijarMesesDesdeHoy(1);
                    }
                },

                fijarMesesDesdeHoy(meses) {
                    const hoy = new Date();
                    const target = new Date(hoy.getFullYear(), hoy.getMonth() + meses, hoy.getDate());
                    this.vencimiento = this.formatearFecha(target);
                },

                extenderUnMes() {
                    let base = new Date();
                    if (this.vencimiento) {
                        const partes = this.vencimiento.split('-');
                        base = new Date(parseInt(partes[0]), parseInt(partes[1]) - 1, parseInt(partes[2]));
                        if (base < new Date()) {
                            base = new Date(); // Si ya estaba vencido, extender desde hoy
                        }
                    }
                    const target = new Date(base.getFullYear(), base.getMonth() + 1, base.getDate());
                    this.vencimiento = this.formatearFecha(target);
                },

                formatearFecha(d) {
                    const year = d.getFullYear();
                    const month = String(d.getMonth() + 1).padStart(2, '0');
                    const day = String(d.getDate()).padStart(2, '0');
                    return `${year}-${month}-${day}`;
                }
            };
        }
        </script>

        <div class="flex justify-end mt-4">
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600
                           text-black font-semibold text-sm px-6 py-2.5 rounded-xl transition-colors">
                <i class="fas fa-check text-xs"></i>Guardar cambios
            </button>
        </div>
    </form>

    {{-- Admins asignados --}}
    <div class="card p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-display font-bold text-base">Administradores</h2>
            <a href="{{ route('backoffice.empresas.admin.crear', $empresa) }}"
               class="inline-flex items-center gap-1.5 bg-[#1a2235] border border-[#1e2d47]
                      hover:border-emerald-500/50 hover:text-emerald-400
                      text-slate-400 text-xs px-3 py-1.5 rounded-lg transition-colors">
                <i class="fas fa-user-plus text-[10px]"></i>Nuevo admin
            </a>
        </div>

        @if($adminUsuarios->count())
        <div class="space-y-2">
            @foreach($adminUsuarios as $u)
            <div class="flex items-center gap-3 bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-3">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-sm
                            text-white flex-shrink-0 bg-gradient-to-br from-blue-500 to-purple-600">
                    {{ strtoupper(substr($u->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold">{{ $u->name }}</p>
                    <p class="text-xs text-slate-500">{{ $u->email }}</p>
                </div>
                <span class="text-xs px-2 py-0.5 bg-amber-500/10 text-amber-500 rounded-full font-semibold">Admin</span>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-slate-500 text-sm">Sin administradores asignados.</p>
        @endif
    </div>

    {{-- Zona peligrosa --}}
    <div class="bg-[#141c2e] border border-red-900/30 rounded-2xl p-6">
        <h2 class="font-display font-bold text-base text-red-400 mb-2">Zona peligrosa</h2>
        <p class="text-slate-500 text-xs mb-4">
            Eliminar esta empresa es permanente.
            @if(!$empresa->esFilial())Las filiales quedarán como matrices independientes.@endif
        </p>
        <form method="POST" action="{{ route('backoffice.empresas.destroy', $empresa) }}"
              onsubmit="return confirm('¿Seguro que deseas eliminar {{ $empresa->razon_social }}?')">
            @csrf @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center gap-2 border border-red-500/30 text-red-400
                           hover:bg-red-500/10 text-sm px-4 py-2 rounded-xl transition-colors">
                <i class="fas fa-trash text-xs"></i>Eliminar empresa
            </button>
        </form>
    </div>

</div>
@endsection
