<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 bg-amber-500 hover:bg-amber-600 text-black font-bold text-sm px-5 py-2.5 rounded-xl transition-all shadow-md shadow-amber-500/10 hover:shadow-amber-500/20 focus:outline-none disabled:opacity-50 cursor-pointer']) }}>
    {{ $slot }}
</button>
