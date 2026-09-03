@extends('backoffice.layout')

@section('title', 'Planes & Paquetes SaaS')

@section('content')
<div class="space-y-6" x-data="planesManager()">

    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-display font-black text-2xl text-white flex items-center gap-3">
                <div class="w-10 h-10 bg-amber-500/10 rounded-xl flex items-center justify-center border border-amber-500/20 text-amber-500">
                    <i class="fas fa-cubes-stacked text-lg"></i>
                </div>
                Planes & Paquetes de Facturación
            </h1>
            <p class="text-slate-400 text-sm mt-1">
                Crea y administra los planes de suscripción y paquetes de facturas DIAN para tus clientes.
            </p>
        </div>
        <div>
            <button @click="abrirCrear()"
                    class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-black font-semibold text-sm rounded-xl transition-colors flex items-center gap-2 shadow-lg shadow-amber-500/20">
                <i class="fas fa-plus text-xs"></i>
                <span>Crear Nuevo Plan</span>
            </button>
        </div>
    </div>

    {{-- Alertas --}}
    @if(session('success'))
    <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-xl px-5 py-4 text-sm flex items-center gap-3">
        <i class="fas fa-check-circle text-base"></i>
        <div>{{ session('success') }}</div>
    </div>
    @endif

    {{-- Tarjetas de Planes --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        @forelse($planes as $plan)
        <div class="card p-6 flex flex-col justify-between relative border {{ $plan->destacado ? 'border-amber-500/40 shadow-xl shadow-amber-500/5' : 'border-[#1e2d47]' }} hover:border-slate-600 transition-all group">
            
            @if($plan->destacado)
            <div class="absolute -top-3 right-4 bg-amber-500 text-black text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-full shadow">
                Recomendado
            </div>
            @endif

            <div>
                {{-- Header tarjeta --}}
                <div class="flex items-center justify-between mb-3">
                    <span class="w-8 h-8 rounded-lg bg-{{ $plan->color }}-500/10 flex items-center justify-center text-{{ $plan->color }}-400">
                        <i class="fas fa-tag text-xs"></i>
                    </span>
                    <span class="text-xs px-2.5 py-1 rounded-full {{ $plan->activo ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-slate-700 text-slate-400' }}">
                        {{ $plan->activo ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>

                <h3 class="font-display font-bold text-lg text-white group-hover:text-amber-400 transition-colors">
                    {{ $plan->nombre }}
                </h3>
                <p class="text-xs text-slate-400 mt-1 min-h-[32px] line-clamp-2">
                    {{ $plan->descripcion ?: 'Sin descripción' }}
                </p>

                {{-- Precio y Duración --}}
                <div class="my-4 pb-4 border-b border-[#1e2d47]">
                    <div class="flex items-baseline gap-1.5 flex-wrap">
                        <span class="font-display font-black text-3xl text-white">{{ $plan->precio_formateado }}</span>
                        <span class="text-xs text-slate-400">
                            COP / {{ $plan->duracion_meses == 1 ? '1 mes completo' : ($plan->duracion_meses == 12 ? '1 año' : $plan->duracion_meses . ' meses') }}
                        </span>
                    </div>
                </div>

                {{-- Características y límites --}}
                <ul class="space-y-2.5 text-xs text-slate-300">
                    <li class="flex items-center gap-2">
                        <i class="fas fa-file-invoice text-amber-500 w-4 text-center"></i>
                        <span class="font-semibold text-white">{{ $plan->limite_facturas_texto }}</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fas fa-users text-blue-400 w-4 text-center"></i>
                        <span>{{ $plan->limite_usuarios ? number_format($plan->limite_usuarios).' usuarios' : 'Usuarios ilimitados' }}</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fas fa-boxes-stacked text-emerald-400 w-4 text-center"></i>
                        <span>{{ $plan->limite_productos ? number_format($plan->limite_productos).' productos' : 'Productos ilimitados' }}</span>
                    </li>
                    <li class="flex items-center gap-2 text-{{ $plan->soporta_dian ? 'emerald-400' : 'slate-500' }}">
                        <i class="fas {{ $plan->soporta_dian ? 'fa-check-circle' : 'fa-times-circle' }} w-4 text-center"></i>
                        <span>Facturación DIAN / CUFE</span>
                    </li>
                    <li class="flex items-center gap-2 text-{{ $plan->soporta_pos ? 'emerald-400' : 'slate-500' }}">
                        <i class="fas {{ $plan->soporta_pos ? 'fa-check-circle' : 'fa-times-circle' }} w-4 text-center"></i>
                        <span>Punto de Venta (POS)</span>
                    </li>
                    <li class="flex items-center gap-2 text-{{ $plan->soporta_nomina ? 'emerald-400' : 'slate-500' }}">
                        <i class="fas {{ $plan->soporta_nomina ? 'fa-check-circle' : 'fa-times-circle' }} w-4 text-center"></i>
                        <span>Módulo de Nómina</span>
                    </li>
                </ul>
            </div>

            {{-- Footer tarjeta --}}
            <div class="mt-6 pt-4 border-t border-[#1e2d47] flex items-center justify-between">
                <span class="text-[11px] text-slate-500 flex items-center gap-1.5">
                    <i class="fas fa-building text-slate-600"></i>
                    <strong>{{ $plan->empresas_count }}</strong> {{ $plan->empresas_count == 1 ? 'empresa' : 'empresas' }}
                </span>

                <div class="flex items-center gap-1.5">
                    <button @click="abrirEditar({{ json_encode($plan) }})"
                            class="w-7 h-7 bg-[#1a2235] hover:bg-[#24314d] text-slate-300 rounded-lg flex items-center justify-center transition-colors text-xs"
                            title="Editar plan">
                        <i class="fas fa-pen"></i>
                    </button>
                    @if($plan->empresas_count == 0)
                    <form method="POST" action="{{ route('backoffice.planes.destroy', $plan) }}"
                          onsubmit="return confirm('¿Seguro que deseas eliminar este plan?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="w-7 h-7 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg flex items-center justify-center transition-colors text-xs"
                                title="Eliminar plan">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                    @endif
                </div>
            </div>

        </div>
        @empty
        <div class="col-span-4 card p-12 text-center text-slate-500">
            <i class="fas fa-cubes-stacked text-3xl mb-3 text-slate-600"></i>
            <p>No has creado ningún plan todavía.</p>
        </div>
        @endforelse
    </div>

    {{-- MODAL CREAR / EDITAR PLAN --}}
    <div x-show="modal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
         @click.self="modal = false">
        <div class="bg-[#111827] border border-[#1e2d47] rounded-2xl w-full max-w-lg p-6 shadow-2xl slide-up max-h-[90vh] overflow-y-auto">
            
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-[#1e2d47]">
                <h3 class="font-display font-black text-lg text-white" x-text="editando ? 'Editar Plan' : 'Crear Nuevo Plan'"></h3>
                <button @click="modal = false" class="text-slate-500 hover:text-white w-8 h-8 flex items-center justify-center rounded-lg hover:bg-[#1a2235]">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form :action="formUrl" method="POST" class="space-y-4">
                @csrf
                <template x-if="editando">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="form-label">Nombre del Plan *</label>
                    <input type="text" name="nombre" x-model="form.nombre" required placeholder="Ej. Plan Emprendedor 100 Facturas" class="form-input">
                </div>

                <div>
                    <label class="form-label">Descripción</label>
                    <input type="text" name="descripcion" x-model="form.descripcion" placeholder="Ej. Ideal para pequeñas empresas" class="form-input">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label">Precio (COP) *</label>
                        <input type="number" name="precio" x-model="form.precio" required min="0" step="1000" placeholder="39000" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Duración Período *</label>
                        <select name="duracion_meses" x-model="form.duracion_meses" class="form-input">
                            <option value="1">1 Mes Completo</option>
                            <option value="3">3 Meses (Trimestre)</option>
                            <option value="6">6 Meses (Semestre)</option>
                            <option value="12">12 Meses (1 Año)</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Límite Facturas</label>
                        <input type="number" name="limite_facturas_mes" x-model="form.limite_facturas_mes" min="1" placeholder="Vacio = Ilimitadas" class="form-input">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Límite Usuarios</label>
                        <input type="number" name="limite_usuarios" x-model="form.limite_usuarios" min="1" placeholder="Vacio = Ilimitados" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Color de Tarjeta</label>
                        <select name="color" x-model="form.color" class="form-input">
                            <option value="amber">Dorado / Amber</option>
                            <option value="blue">Azul</option>
                            <option value="emerald">Verde Esmeralda</option>
                            <option value="purple">Morado / Púrpura</option>
                            <option value="rose">Rosa / Rojo</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-2.5 pt-2 border-t border-[#1e2d47]">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Módulos Incluidos</p>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2.5 text-xs text-slate-300 cursor-pointer">
                            <input type="checkbox" name="soporta_dian" value="1" x-model="form.soporta_dian" class="rounded border-[#1e2d47] text-amber-500 focus:ring-amber-500 bg-[#1a2235]">
                            <span>Facturación DIAN</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-xs text-slate-300 cursor-pointer">
                            <input type="checkbox" name="soporta_pos" value="1" x-model="form.soporta_pos" class="rounded border-[#1e2d47] text-amber-500 focus:ring-amber-500 bg-[#1a2235]">
                            <span>Punto de Venta POS</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-xs text-slate-300 cursor-pointer">
                            <input type="checkbox" name="soporta_nomina" value="1" x-model="form.soporta_nomina" class="rounded border-[#1e2d47] text-amber-500 focus:ring-amber-500 bg-[#1a2235]">
                            <span>Nómina Electrónica</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-xs text-slate-300 cursor-pointer">
                            <input type="checkbox" name="destacado" value="1" x-model="form.destacado" class="rounded border-[#1e2d47] text-amber-500 focus:ring-amber-500 bg-[#1a2235]">
                            <span>Marcar como Destacado</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-[#1e2d47]">
                    <button type="button" @click="modal = false"
                            class="px-5 py-2.5 bg-[#1a2235] border border-[#1e2d47] rounded-xl text-sm text-slate-400 hover:text-white transition-colors">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-black font-semibold text-sm rounded-xl transition-colors flex items-center gap-2">
                        <i class="fas fa-save text-xs"></i>
                        <span x-text="editando ? 'Actualizar Plan' : 'Guardar Plan'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function planesManager() {
    return {
        modal: false,
        editando: false,
        formUrl: '{{ route('backoffice.planes.store') }}',
        form: {
            id: null,
            nombre: '',
            descripcion: '',
            precio: 39000,
            duracion_meses: 1,
            limite_facturas_mes: 100,
            limite_usuarios: 2,
            limite_productos: '',
            color: 'amber',
            soporta_dian: true,
            soporta_pos: true,
            soporta_nomina: false,
            soporta_contabilidad: false,
            destacado: false,
            activo: true,
        },
        abrirCrear() {
            this.editando = false;
            this.formUrl = '{{ route('backoffice.planes.store') }}';
            this.form = {
                id: null,
                nombre: '',
                descripcion: '',
                precio: 39000,
                duracion_meses: 1,
                limite_facturas_mes: 100,
                limite_usuarios: 2,
                limite_productos: '',
                color: 'amber',
                soporta_dian: true,
                soporta_pos: true,
                soporta_nomina: false,
                soporta_contabilidad: false,
                destacado: false,
                activo: true,
            };
            this.modal = true;
        },
        abrirEditar(plan) {
            this.editando = true;
            this.formUrl = '{{ url('backoffice/planes') }}/' + plan.id;
            this.form = {
                id: plan.id,
                nombre: plan.nombre,
                descripcion: plan.descripcion || '',
                precio: plan.precio,
                duracion_meses: plan.duracion_meses || 1,
                limite_facturas_mes: plan.limite_facturas_mes,
                limite_usuarios: plan.limite_usuarios,
                limite_productos: plan.limite_productos || '',
                color: plan.color || 'amber',
                soporta_dian: Boolean(plan.soporta_dian),
                soporta_pos: Boolean(plan.soporta_pos),
                soporta_nomina: Boolean(plan.soporta_nomina),
                soporta_contabilidad: Boolean(plan.soporta_contabilidad),
                destacado: Boolean(plan.destacado),
                activo: Boolean(plan.activo),
            };
            this.modal = true;
        }
    };
}
</script>
@endsection
