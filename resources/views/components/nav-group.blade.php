@props([
    'label',
    'icon'   => 'fa-folder',
    'color'  => 'amber',
    'active' => false,
    'badge'  => null,
    'id'     => null,
])

@php
    $id = $id ?? 'nav-group-' . \Illuminate\Support\Str::slug($label);
    $colorClasses = match($color) {
        'sky'     => 'text-sky-400 bg-sky-500/10 border-sky-500/20',
        'violet'  => 'text-violet-400 bg-violet-500/10 border-violet-500/20',
        'emerald' => 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20',
        'pink'    => 'text-pink-400 bg-pink-500/10 border-pink-500/20',
        'slate'   => 'text-slate-400 bg-slate-500/10 border-slate-500/20',
        default   => 'text-amber-400 bg-amber-500/10 border-amber-500/20',
    };
@endphp

<div class="nav-accordion-group mb-1" data-group-id="{{ $id }}">
    <button type="button"
            onclick="toggleNavGroup('{{ $id }}')"
            aria-expanded="{{ $active ? 'true' : 'false' }}"
            class="w-full flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-sm font-medium transition-all group select-none
                   {{ $active
                       ? 'bg-[#162034] text-slate-100 border border-[#1e2d47]'
                       : 'text-slate-400 hover:bg-[#151d2e] hover:text-slate-200 border border-transparent' }}">

        {{-- Icono con badge redondeado --}}
        <div class="w-7 h-7 rounded-lg border flex items-center justify-center flex-shrink-0 transition-transform group-hover:scale-105 {{ $colorClasses }}">
            <i class="fas {{ $icon }} text-xs"></i>
        </div>

        {{-- Texto del grupo --}}
        <span class="flex-1 text-left truncate text-xs sm:text-[13px] font-semibold tracking-tight">{{ $label }}</span>

        {{-- Badge opcional --}}
        @if($badge)
            <span class="bg-amber-500/20 border border-amber-500/30 text-amber-400 text-[10px] font-bold px-1.5 py-0.5 rounded-md">
                {{ $badge }}
            </span>
        @endif

        {{-- Flecha Chevron giratoria --}}
        <i class="fas fa-chevron-right text-[10px] text-slate-500 transition-transform duration-200 group-hover:text-slate-300 {{ $active ? 'rotate-90 text-amber-500' : '' }}"
           id="chevron-{{ $id }}"></i>
    </button>

    {{-- Submenú colapsable --}}
    <div id="content-{{ $id }}"
         class="pl-4 pr-1 mt-1 border-l-2 border-[#1e2d47]/60 ml-5 space-y-0.5 {{ $active ? '' : 'hidden' }}">
        {{ $slot }}
    </div>
</div>
