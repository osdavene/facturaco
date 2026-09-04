@props([
    'href'   => '#',
    'active' => false,
    'badge'  => null,
    'badgeColor' => 'bg-red-500 text-white',
    'icon'   => null,
])

<a href="{{ $href }}"
   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-all group relative
          {{ $active
              ? 'bg-amber-500/15 text-amber-400 font-semibold shadow-sm border border-amber-500/30'
              : 'text-slate-400 hover:text-slate-200 hover:bg-[#151e30]' }}">

    {{-- Punto indicador o icono pequeño --}}
    @if($icon)
        <i class="fas {{ $icon }} text-[11px] w-3.5 text-center {{ $active ? 'text-amber-400' : 'text-slate-500 group-hover:text-slate-400' }}"></i>
    @else
        <span class="w-1.5 h-1.5 rounded-full transition-all {{ $active ? 'bg-amber-400 scale-125' : 'bg-slate-600 group-hover:bg-slate-400' }}"></span>
    @endif

    <span class="flex-1 truncate leading-tight">{{ $slot }}</span>

    @if($badge)
        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full {{ $badgeColor }}">
            {{ $badge }}
        </span>
    @endif
</a>
