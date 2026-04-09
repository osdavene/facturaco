@props(['label'])
<div class="mb-2">
    <div class="text-[10px] font-semibold tracking-widest text-slate-600
                uppercase px-3 pb-2 pt-3">
        {{ $label }}
    </div>
    {{ $slot }}
</div>