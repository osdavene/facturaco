@extends('layouts.app')
@section('title', 'Nuevo Usuario')
@section('page-title', 'Usuarios · Nuevo')

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
            <h1 class="font-display font-bold text-2xl">Nuevo Usuario</h1>
            <p class="text-slate-500 text-sm">Crea un usuario y asígnale un rol</p>
        </div>
    </div>

    <form method="POST" action="{{ route('usuarios.store') }}">
        @csrf

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
                           value="{{ old('name') }}"
                           placeholder="NOMBRE DEL USUARIO"
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
                           value="{{ old('email') }}"
                           placeholder="usuario@empresa.com"
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
                Contraseña
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">
                        Contraseña * <span class="text-slate-600">(mín. 8 caracteres)</span>
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
                        Confirmar Contraseña *
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
            <div class="mt-3">
                <div class="flex gap-1 mb-1">
                    <div class="h-1 flex-1 rounded-full bg-[#1e2d47]" id="bar1"></div>
                    <div class="h-1 flex-1 rounded-full bg-[#1e2d47]" id="bar2"></div>
                    <div class="h-1 flex-1 rounded-full bg-[#1e2d47]" id="bar3"></div>
                    <div class="h-1 flex-1 rounded-full bg-[#1e2d47]" id="bar4"></div>
                </div>
                <div class="text-xs text-slate-600" id="pass-label"></div>
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
            $rolesConfig = [
                'super-admin'  => ['#f59e0b', 'fa-crown',        'Acceso total al sistema'],
                'admin'        => ['#3b82f6', 'fa-user-shield',  'Gestión completa excepto configuración'],
                'vendedor'     => ['#10b981', 'fa-cash-register','Facturas, clientes y cotizaciones'],
                'bodeguero'    => ['#8b5cf6', 'fa-warehouse',    'Inventario y órdenes de compra'],
                'contador'     => ['#06b6d4', 'fa-calculator',   'Reportes, facturas y contabilidad'],
                'solo-lectura' => ['#64748b', 'fa-eye',          'Solo puede consultar información'],
            ];
            $rolSeleccionado = old('rol', '');
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="roles-grid">
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

        {{-- SECCIÓN 4: Acceso a empresas (si el grupo tiene más de una) --}}
        @if($empresasGrupo->count() > 1)
        <div class="card p-6 mb-6">
            <h2 class="font-display font-bold text-base mb-1 flex items-center gap-2">
                <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center
                             text-black text-xs font-black">4</span>
                Acceso a empresas
            </h2>
            <p class="text-xs text-slate-500 mb-4">Selecciona en cuál(es) empresa(s) del grupo puede trabajar este usuario.</p>

            <div class="space-y-2">
                @foreach($empresasGrupo as $emp)
                <label class="flex items-center gap-3 bg-[#1a2235] border border-[#1e2d47] rounded-xl
                              px-4 py-3 cursor-pointer hover:border-amber-500/40 transition-colors
                              has-[:checked]:border-amber-500/50 has-[:checked]:bg-amber-500/5">
                    <input type="checkbox" name="empresa_ids[]" value="{{ $emp->id }}"
                           {{ (!old('empresa_ids') || in_array($emp->id, old('empresa_ids', []))) && $emp->id == session('empresa_activa_id') ? 'checked' : '' }}
                           class="w-4 h-4 accent-amber-500">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-slate-200">{{ $emp->razon_social }}</p>
                        @if($emp->esFilial())
                            <p class="text-xs text-slate-600">Filial · NIT: {{ $emp->nit }}</p>
                        @else
                            <p class="text-xs text-violet-400/70">Empresa matriz · NIT: {{ $emp->nit }}</p>
                        @endif
                    </div>
                </label>
                @endforeach
            </div>
            @error('empresa_ids')
                <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
            @enderror
        </div>
        @else
        {{-- Si solo hay una empresa en el grupo, enviarla oculta --}}
        <input type="hidden" name="empresa_ids[]" value="{{ $empresasGrupo->first()->id }}">
        @endif

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('usuarios.index') }}"
               class="px-6 py-2.5 bg-[#1a2235] border border-[#1e2d47] rounded-xl
                      text-slate-400 hover:text-slate-200 text-sm transition-colors">
                Cancelar
            </a>
            <button type="submit"
                    class="px-8 py-2.5 bg-amber-500 hover:bg-amber-600 text-black
                           font-semibold rounded-xl transition-colors flex items-center gap-2">
                <i class="fas fa-save"></i> Crear Usuario
            </button>
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

// ── Fortaleza contraseña ──────────────────────
document.getElementById('password').addEventListener('input', function() {
    const val    = this.value;
    const bars   = [1,2,3,4].map(i => document.getElementById('bar'+i));
    const label  = document.getElementById('pass-label');
    const colors = ['#ef4444','#f97316','#eab308','#10b981'];
    const labels = ['Muy débil','Débil','Moderada','Fuerte'];

    let score = 0;
    if (val.length >= 8)           score++;
    if (/[A-Z]/.test(val))         score++;
    if (/[0-9]/.test(val))         score++;
    if (/[^A-Za-z0-9]/.test(val))  score++;

    bars.forEach((b, i) => {
        b.style.background = i < score ? colors[score-1] : '#1e2d47';
    });

    label.textContent = val.length > 0 ? 'Fortaleza: ' + (labels[score-1] || 'Muy débil') : '';
    label.style.color = score > 0 ? colors[score-1] : '#475569';
});

// ── Selección de rol con estilos visuales ─────
document.querySelectorAll('.rol-radio').forEach(radio => {
    radio.addEventListener('change', function() {
        // Reset todos
        document.querySelectorAll('.rol-label').forEach(label => {
            const card  = label.querySelector('.rol-card');
            const check = label.querySelector('.check-icon');
            card.style.borderColor  = '#1e2d47';
            card.style.background   = 'transparent';
            check.style.display     = 'none';
        });

        // Activar seleccionado
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