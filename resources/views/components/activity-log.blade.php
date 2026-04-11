@props(['model'])

@php
    $activities = $model->activities()->with('causer')->latest()->limit(20)->get();

    $fieldLabels = [
        'estado'           => 'Estado',
        'total'            => 'Total',
        'total_pagado'     => 'Total pagado',
        'fecha_vencimiento'=> 'Fecha vencimiento',
        'observaciones'    => 'Observaciones',
        'forma_pago'       => 'Forma de pago',
        'nombres'          => 'Nombres',
        'apellidos'        => 'Apellidos',
        'razon_social'     => 'Razón social',
        'nombre_contacto'  => 'Contacto',
        'email'            => 'Email',
        'celular'          => 'Celular',
        'telefono'         => 'Teléfono',
        'activo'           => 'Estado activo',
        'plazo_pago'       => 'Plazo de pago',
        'cupo_credito'     => 'Cupo de crédito',
        'regimen'          => 'Régimen',
        'direccion'        => 'Dirección',
        'nombre'           => 'Nombre',
        'codigo'           => 'Código',
        'precio_venta'     => 'Precio venta',
        'precio_compra'    => 'Precio compra',
        'stock_actual'     => 'Stock actual',
        'stock_minimo'     => 'Stock mínimo',
        'iva_pct'          => 'IVA %',
        'lugar_entrega'    => 'Lugar entrega',
        'transportador'    => 'Transportador',
        'guia'             => 'Guía',
        'fecha_esperada'   => 'Fecha esperada',
        'notas_recepcion'  => 'Notas recepción',
    ];

    $eventIcons = [
        'created' => ['icon' => 'fa-plus-circle',  'color' => 'emerald'],
        'updated' => ['icon' => 'fa-pen',           'color' => 'amber'],
        'deleted' => ['icon' => 'fa-trash',         'color' => 'red'],
    ];
@endphp

@if($activities->isNotEmpty())
<div class="card p-6">
    <h3 class="font-display font-bold text-base mb-5 flex items-center gap-2">
        <i class="fas fa-history text-amber-500 text-sm"></i>
        Historial de Cambios
        <span class="ml-auto text-xs font-normal text-slate-500">{{ $activities->count() }} registro{{ $activities->count() !== 1 ? 's' : '' }}</span>
    </h3>

    <div class="relative">
        {{-- Línea vertical --}}
        <div class="absolute left-4 top-0 bottom-0 w-px bg-[#1e2d47]"></div>

        <div class="space-y-4">
            @foreach($activities as $activity)
            @php
                $ev     = $activity->event ?? 'updated';
                $cfg    = $eventIcons[$ev] ?? $eventIcons['updated'];
                $causer = $activity->causer;
                $initials = $causer ? strtoupper(substr($causer->name, 0, 2)) : 'SY';
                $old  = $activity->properties->get('old',        []);
                $new  = $activity->properties->get('attributes', []);
            @endphp
            <div class="flex gap-4 pl-1">
                {{-- Icono evento --}}
                <div class="relative z-10 w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0
                            bg-{{ $cfg['color'] }}-500/10 border border-{{ $cfg['color'] }}-500/30">
                    <i class="fas {{ $cfg['icon'] }} text-{{ $cfg['color'] }}-400 text-[10px]"></i>
                </div>

                <div class="flex-1 min-w-0 pt-0.5">
                    {{-- Cabecera --}}
                    <div class="flex items-center flex-wrap gap-x-2 gap-y-0.5 mb-1">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-200">
                            <span class="w-5 h-5 rounded-full bg-amber-500/10 border border-amber-500/20
                                         flex items-center justify-center text-[8px] font-bold text-amber-400">
                                {{ $initials }}
                            </span>
                            {{ $causer?->name ?? 'Sistema' }}
                        </span>
                        <span class="text-xs text-slate-400">{{ $activity->description }}</span>
                        <span class="text-[11px] text-slate-600 ml-auto whitespace-nowrap">
                            {{ $activity->created_at->diffForHumans() }}
                        </span>
                    </div>

                    {{-- Cambios de campos (solo en updated) --}}
                    @if($ev === 'updated' && count($old) > 0)
                    <div class="bg-[#0d1421] border border-[#1e2d47] rounded-lg p-2.5 space-y-1.5 mt-1">
                        @foreach($old as $field => $oldVal)
                        @php
                            $newVal  = $new[$field] ?? null;
                            $label   = $fieldLabels[$field] ?? $field;
                            $oldDisp = is_bool($oldVal) ? ($oldVal ? 'Sí' : 'No') : (is_null($oldVal) ? '—' : $oldVal);
                            $newDisp = is_bool($newVal) ? ($newVal ? 'Sí' : 'No') : (is_null($newVal) ? '—' : $newVal);
                        @endphp
                        <div class="flex items-start gap-2 text-[11px]">
                            <span class="text-slate-500 min-w-[90px] flex-shrink-0">{{ $label }}:</span>
                            <span class="text-red-400/80 line-through break-all">{{ $oldDisp }}</span>
                            <i class="fas fa-arrow-right text-[9px] text-slate-600 flex-shrink-0 mt-0.5"></i>
                            <span class="text-emerald-400 break-all">{{ $newDisp }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
