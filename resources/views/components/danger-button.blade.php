<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 bg-red-500/10 border border-red-500/30 hover:bg-red-500/20 text-red-400 hover:text-red-300 font-semibold text-sm px-4 py-2 rounded-xl transition-colors focus:outline-none disabled:opacity-50 cursor-pointer']) }}>
    {{ $slot }}
</button>
