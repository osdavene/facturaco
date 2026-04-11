@props([
    'icon'     => 'fa-inbox',
    'title'    => 'Sin resultados',
    'subtitle' => null,
    'href'     => null,
    'label'    => 'Crear el primero',
    'colspan'  => 6,
    'table'    => true,
])

@php $inner = true; @endphp

@if($table)
<tr>
    <td colspan="{{ $colspan }}" class="py-20 px-5">
@else
<div class="py-20 px-5">
@endif

        <div class="flex flex-col items-center gap-4 max-w-xs mx-auto text-center">

            {{-- Icono con glow sutil --}}
            <div class="relative">
                <div class="w-20 h-20 rounded-2xl flex items-center justify-center
                            bg-gradient-to-br from-[#1a2235] to-[#141c2e]
                            border border-[#1e2d47] shadow-lg">
                    <i class="fas {{ $icon }} text-3xl text-slate-600"></i>
                </div>
                {{-- Punto decorativo --}}
                <div class="absolute -top-1 -right-1 w-3 h-3 bg-amber-500/20
                            border border-amber-500/30 rounded-full"></div>
            </div>

            {{-- Textos --}}
            <div>
                <p class="font-display font-bold text-slate-400 text-base">{{ $title }}</p>
                @if($subtitle)
                <p class="text-slate-600 text-xs mt-1 leading-relaxed">{{ $subtitle }}</p>
                @endif
            </div>

            {{-- CTA --}}
            @if($href)
            <a href="{{ $href }}"
               class="inline-flex items-center gap-2 px-5 py-2.5
                      bg-amber-500/10 hover:bg-amber-500/20
                      border border-amber-500/30 hover:border-amber-500/50
                      text-amber-500 font-semibold text-sm rounded-xl
                      transition-all duration-200">
                <i class="fas fa-plus text-xs"></i>
                {{ $label }}
            </a>
            @endif

        </div>

@if($table)
    </td>
</tr>
@else
</div>
@endif
