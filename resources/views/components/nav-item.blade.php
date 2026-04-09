@props(['href' => '#', 'icon' => '', 'active' => false, 'badge' => null])

<a href="{{ $href }}"
   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm mb-0.5 transition-all relative
          {{ $active
              ? 'bg-amber-500/10 text-amber-500 font-medium'
              : 'text-slate-500 hover:bg-[#1a2235] hover:text-slate-200' }}">

    @if($active)
        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-3/5
                     bg-amber-500 rounded-r"></span>
    @endif

    <i class="fas {{ $icon }} w-4 text-center text-[15px]"></i>
    <span class="flex-1">{{ $slot }}</span>

    @if($badge)
        <span class="bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">
            {{ $badge }}
        </span>
    @endif
</a>