<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center gap-2 bg-[#1a2235] border border-[#1e2d47] hover:border-slate-500 text-slate-300 hover:text-white font-medium text-sm px-5 py-2.5 rounded-xl transition-colors focus:outline-none disabled:opacity-50 cursor-pointer']) }}>
    {{ $slot }}
</button>
