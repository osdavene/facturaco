@extends('layouts.app')
@section('title', 'Editar Usuario')
@section('page-title', 'Usuarios · Editar')

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('usuarios.index') }}"
           class="w-9 h-9 bg-[#141c2e] border border-[#1e2d47] rounded-xl
                  flex items-center justify-center text-slate-400
                  hover:text-amber-500 hover:border-amber-500/50 transition-colors">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="font-display font-bold text-2xl">Editar Usuario</h1>
            <p class="text-slate-500 text-sm uppercase">{{ $usuario->name }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('usuarios.update', $usuario) }}">
        @csrf @method('PUT')

        {{-- SECCIÓN 1 --}}
        <div class="card p-6 mb-4">
            <h2 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center
                             text-black text-xs font-black">1</span>
                Datos del Usuario
            </h2>
            <div class="space-y-4">
                <div>
                    <label class="form-label">
                        Nombre Completo *
                    </label>
                    <input type="text" name="name"
                           value="{{ old('name', $usuario->name) }}"
                           data-uppercase
                           class="form-input @error('name') border-red-500 @enderror"
                           style="color:#e2e8f0">
                    @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">
                        Correo Electrónico *
                    </label>
                    <input type="email" name="email"
                           value="{{ old('email', $usuario->email) }}"
                           class="form-input @error('email') border-red-500 @enderror"
                           style="color:#e2e8f0">
                    @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- SECCIÓN 2 --}}
        <div class="card p-6 mb-4">
            <h2 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center
                             text-black text-xs font-black">2</span>
                Cambiar Contraseña
                <span class="text-xs text-slate-500 font-normal">(dejar vacío para mantener la actual)</span>
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">
                        Nueva Contraseña
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="password"
                               placeholder="••••••••"
                               class="form-input pr-10
                                      @error('password') border-red-500 @enderror"
                               style="color:#e2e8f0">
                        <button type="button" onclick="togglePass('password')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500
                                       hover:text-slate-300 transition-colors">
                            <i class="fas fa-eye text-sm" id="icon-password"></i>
                        </button>
                    </div>
                    @error('password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">
                        Confirmar Contraseña
                    </label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               placeholder="••••••••"
                               class="form-input pr-10"
                               style="color:#e2e8f0">
                        <button type="button" onclick="togglePass('password_confirmation')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500
                                       hover:text-slate-300 transition-colors">
                            <i class="fas fa-eye text-sm" id="icon-password_confirmation"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECCIÓN 3: Rol --}}
        <div class="card p-6 mb-6">
            <h2 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center
                             text-black text-xs font-black">3</span>
                Rol y Permisos
            </h2>

            @php
            $rolActual   = $usuario->getRoleNames()->first() ?? '';
            $rolesConfig = [
                'super-admin'  => ['#f59e0b', 'fa-crown',        'Acceso total al sistema'],
                'admin'        => ['#3b82f6', 'fa-user-shield',  'Gestión completa excepto configuración'],
                'vendedor'     => ['#10b981', 'fa-cash-register','Facturas, clientes y cotizaciones'],
                'bodeguero'    => ['#8b5cf6', 'fa-warehouse',    'Inventario y órdenes de compra'],
                'contador'     => ['#06b6d4', 'fa-calculator',   'Reportes, facturas y contabilidad'],
                'solo-lectura' => ['#64748b', 'fa-eye',          'Solo puede consultar información'],
            ];
            $rolSeleccionado = old('rol', $rolActual);
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($roles as $rol)
                @php
                    $cfg      = $rolesConfig[$rol->name] ?? ['#64748b','fa-user','Rol del sistema'];
                    $color    = $cfg[0];
                    $icono    = $cfg[1];
                    $desc     = $cfg[2];
                    $selected = $rolSeleccionado === $rol->name;
                @endphp
                <label class="cursor-pointer block rol-label" data-color="{{ $color }}">
                    <input type="radio" name="rol" value="{{ $rol->name }}"
                           class="sr-only rol-radio"
                           {{ $selected ? 'checked' : '' }}>
                    <div class="rol-card border-2 rounded-xl p-4 transition-all duration-200"
                         style="border-color: {{ $selected ? $color : '#1e2d47' }};
                                background: {{ $selected ? $color.'18' : 'transparent' }};">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                                 style="background:{{ $color }}18; color:{{ $color }}">
                                <i class="fas {{ $icono }} text-sm"></i>
                            </div>
                            <div class="font-semibold text-sm text-slate-200 capitalize flex-1">
                                {{ str_replace('-', ' ', $rol->name) }}
                            </div>
                            <i class="fas fa-check-circle text-sm check-icon"
                               style="color:{{ $color }}; display:{{ $selected ? 'block':'none' }}"></i>
                        </div>
                        <div class="text-xs text-slate-500">{{ $desc }}</div>
                    </div>
                </label>
                @endforeach
            </div>
            @error('rol') <p class="text-red-400 text-xs mt-2">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-between">
            @if($usuario->id !== auth()->id())
            <form method="POST" action="{{ route('usuarios.destroy', $usuario) }}"
                  onsubmit="return confirm('¿Eliminar al usuario {{ $usuario->name }}?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="px-5 py-2.5 bg-red-500/10 border border-red-500/30
                               text-red-400 hover:bg-red-500/20 rounded-xl text-sm
                               flex items-center gap-2 transition-colors">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </form>
            @else
            <div></div>
            @endif

            <div class="flex gap-3">
                <a href="{{ route('usuarios.index') }}"
                   class="px-6 py-2.5 bg-[#1a2235] border border-[#1e2d47] rounded-xl
                          text-slate-400 hover:text-slate-200 text-sm transition-colors">
                    Cancelar
                </a>
                <button type="submit"
                        class="px-8 py-2.5 bg-amber-500 hover:bg-amber-600 text-black
                               font-semibold rounded-xl transition-colors flex items-center gap-2">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
// ── Mayúsculas ────────────────────────────────
document.querySelectorAll('[data-uppercase]').forEach(el => {
    el.addEventListener('input', function() {
        const pos = this.selectionStart;
        this.value = this.value.toUpperCase();
        this.setSelectionRange(pos, pos);
    });
});

// ── Mostrar/ocultar contraseña ────────────────
function togglePass(id) {
    const input = document.getElementById(id);
    const icon  = document.getElementById('icon-' + id);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// ── Selección de rol con estilos visuales ─────
document.querySelectorAll('.rol-radio').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.rol-label').forEach(label => {
            const card  = label.querySelector('.rol-card');
            const check = label.querySelector('.check-icon');
            card.style.borderColor = '#1e2d47';
            card.style.background  = 'transparent';
            check.style.display    = 'none';
        });

        const label = this.closest('.rol-label');
        const card  = label.querySelector('.rol-card');
        const check = label.querySelector('.check-icon');
        const color = label.dataset.color;

        card.style.borderColor = color;
        card.style.background  = color + '18';
        check.style.display    = 'block';
    });
});
</script>
@endpush
@endsection