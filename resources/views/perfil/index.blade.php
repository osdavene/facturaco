@extends('layouts.app')
@section('title', 'Mi Perfil')
@section('page-title', 'Mi Perfil')

@section('content')
<div class="max-w-4xl mx-auto">

    @if(session('success'))
    <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400
                rounded-xl px-5 py-3 mb-5 flex items-center gap-3">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-500/10 border border-red-500/30 text-red-400
                rounded-xl px-5 py-3 mb-5 flex items-center gap-3">
        <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
    </div>
    @endif

    {{-- Header perfil --}}
    <div class="card p-6 mb-4">
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">

            {{-- Avatar --}}
            <div class="relative flex-shrink-0">
                <img src="{{ $usuario->avatar_url }}"
                     id="preview-avatar"
                     class="w-24 h-24 rounded-2xl object-cover border-2 border-[#1e2d47]"
                     alt="{{ $usuario->name }}">

                {{-- Overlay editar --}}
                <label for="input-avatar"
                       class="absolute inset-0 rounded-2xl bg-black/50 flex items-center
                              justify-center opacity-0 hover:opacity-100 cursor-pointer
                              transition-opacity">
                    <i class="fas fa-camera text-white text-lg"></i>
                </label>

                {{-- Badge rol --}}
                <div class="absolute -bottom-2 -right-2 w-6 h-6 rounded-lg
                            bg-amber-500 flex items-center justify-center">
                    <i class="fas fa-crown text-black text-[10px]"></i>
                </div>
            </div>

            {{-- Info --}}
            <div class="flex-1 text-center sm:text-left">
                <h1 class="font-display font-bold text-2xl" style="color:#e2e8f0">
                    {{ $usuario->name }}
                </h1>
                <p class="text-slate-400 text-sm mt-1">{{ $usuario->email }}</p>
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mt-3">
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold
                                 px-3 py-1 rounded-full bg-amber-500/10 text-amber-500">
                        <i class="fas fa-shield-alt text-xs"></i>
                        {{ ucfirst(str_replace('-',' ', $usuario->getRoleNames()->first() ?? 'sin rol')) }}
                    </span>
                    @if($usuario->cargo)
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold
                                 px-3 py-1 rounded-full bg-blue-500/10 text-blue-400">
                        <i class="fas fa-briefcase text-xs"></i>
                        {{ $usuario->cargo }}
                    </span>
                    @endif
                    <span class="inline-flex items-center gap-1.5 text-xs
                                 px-3 py-1 rounded-full
                                 {{ $usuario->activo ? 'bg-emerald-500/10 text-emerald-500' : 'bg-red-500/10 text-red-400' }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                        {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
                <p class="text-xs text-slate-600 mt-2">
                    Miembro desde {{ $usuario->created_at->format('d/m/Y') }}
                    · {{ $usuario->created_at->diffForHumans() }}
                </p>
            </div>

            {{-- Acciones avatar --}}
            <div class="flex flex-col gap-2">
                <form method="POST" action="{{ route('perfil.avatar') }}"
                      enctype="multipart/form-data" id="form-avatar">
                    @csrf
                    <input type="file" id="input-avatar" name="avatar"
                           accept="image/*" class="hidden"
                           onchange="previewAvatar(this)">
                    <button type="button" onclick="document.getElementById('input-avatar').click()"
                            class="w-full inline-flex items-center justify-center gap-2
                                   bg-[#1a2235] border border-[#1e2d47] hover:border-amber-500/50
                                   text-slate-400 hover:text-amber-500 px-4 py-2 rounded-xl
                                   transition-colors text-sm">
                        <i class="fas fa-camera"></i> Cambiar foto
                    </button>
                </form>

                @if($usuario->avatar)
                <form method="POST" action="{{ route('perfil.avatar.delete') }}"
                      onsubmit="return confirm('¿Eliminar foto de perfil?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2
                                   bg-red-500/10 border border-red-500/30 text-red-400
                                   hover:bg-red-500/20 px-4 py-2 rounded-xl
                                   transition-colors text-sm">
                        <i class="fas fa-trash"></i> Quitar foto
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 card p-1.5 mb-4"
         id="tabs-container">
        @foreach([
            ['tab' => 'info',     'icon' => 'fa-user',     'label' => 'Información'],
            ['tab' => 'password', 'icon' => 'fa-lock',     'label' => 'Contraseña'],
            ['tab' => 'actividad','icon' => 'fa-clock',    'label' => 'Actividad'],
        ] as $t)
        <button onclick="setTab('{{ $t['tab'] }}')"
                id="tab-btn-{{ $t['tab'] }}"
                class="flex-1 flex items-center justify-center gap-2 py-2.5 px-4
                       rounded-xl text-sm font-semibold transition-all">
            <i class="fas {{ $t['icon'] }} text-xs"></i>
            <span class="hidden sm:inline">{{ $t['label'] }}</span>
        </button>
        @endforeach
    </div>

    {{-- TAB: Información --}}
    <div id="tab-info" class="tab-content">
        <form method="POST" action="{{ route('perfil.update') }}">
            @csrf @method('PUT')
            <div class="card p-6">
                <h2 class="font-display font-bold text-base mb-5 flex items-center gap-2">
                    <i class="fas fa-user text-amber-500 text-sm"></i>
                    Datos Personales
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
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
                    <div>
                        <label class="form-label">
                            Teléfono
                        </label>
                        <input type="text" name="telefono"
                               value="{{ old('telefono', $usuario->telefono) }}"
                               placeholder="300 000 0000"
                               class="form-input"
                               style="color:#e2e8f0">
                    </div>
                    <div>
                        <label class="form-label">
                            Cargo / Posición
                        </label>
                        <input type="text" name="cargo"
                               value="{{ old('cargo', $usuario->cargo) }}"
                               placeholder="VENDEDOR, CONTADOR..."
                               data-uppercase
                               class="form-input"
                               style="color:#e2e8f0">
                    </div>
                    <div>
                        <label class="form-label">
                            Rol del Sistema
                        </label>
                        <div class="form-input text-slate-500 flex items-center gap-2">
                            <i class="fas fa-shield-alt text-xs text-amber-500"></i>
                            {{ ucfirst(str_replace('-',' ', $usuario->getRoleNames()->first() ?? 'Sin rol')) }}
                            <span class="text-xs text-slate-600 ml-auto">(solo admin puede cambiar)</span>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end mt-5">
                    <button type="submit"
                            class="px-8 py-2.5 bg-amber-500 hover:bg-amber-600 text-black
                                   font-bold rounded-xl transition-colors flex items-center gap-2">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- TAB: Contraseña --}}
    <div id="tab-password" class="tab-content hidden">
        <form method="POST" action="{{ route('perfil.password') }}">
            @csrf @method('PUT')
            <div class="card p-6">
                <h2 class="font-display font-bold text-base mb-5 flex items-center gap-2">
                    <i class="fas fa-lock text-amber-500 text-sm"></i>
                    Cambiar Contraseña
                </h2>
                <div class="space-y-4 max-w-md">
                    <div>
                        <label class="form-label">
                            Contraseña Actual *
                        </label>
                        <div class="relative">
                            <input type="password" name="password_actual" id="pass-actual"
                                   placeholder="••••••••"
                                   class="form-input pr-10
                                          focus:outline-none focus:border-amber-500
                                          @error('password_actual') border-red-500 @enderror"
                                   style="color:#e2e8f0">
                            <button type="button" onclick="togglePass('pass-actual','icon-actual')"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300">
                                <i class="fas fa-eye text-sm" id="icon-actual"></i>
                            </button>
                        </div>
                        @error('password_actual')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label">
                            Nueva Contraseña *
                        </label>
                        <div class="relative">
                            <input type="password" name="password" id="pass-nueva"
                                   placeholder="••••••••"
                                   class="form-input pr-10
                                          focus:outline-none focus:border-amber-500
                                          @error('password') border-red-500 @enderror"
                                   style="color:#e2e8f0">
                            <button type="button" onclick="togglePass('pass-nueva','icon-nueva')"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300">
                                <i class="fas fa-eye text-sm" id="icon-nueva"></i>
                            </button>
                        </div>
                        {{-- Fortaleza --}}
                        <div class="flex gap-1 mt-2">
                            @for($i=1;$i<=4;$i++)
                            <div class="h-1 flex-1 rounded-full bg-[#1e2d47]" id="pbar{{$i}}"></div>
                            @endfor
                        </div>
                        <div class="text-xs text-slate-600 mt-1" id="plabel"></div>
                        @error('password')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label">
                            Confirmar Nueva Contraseña *
                        </label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="pass-confirm"
                                   placeholder="••••••••"
                                   class="form-input pr-10
                                          focus:outline-none focus:border-amber-500"
                                   style="color:#e2e8f0">
                            <button type="button" onclick="togglePass('pass-confirm','icon-confirm')"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300">
                                <i class="fas fa-eye text-sm" id="icon-confirm"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Requisitos --}}
                    <div class="bg-[#1a2235] border border-[#1e2d47] rounded-xl p-4">
                        <div class="text-xs font-semibold text-slate-400 mb-2">Requisitos:</div>
                        <div class="space-y-1.5" id="requisitos">
                            @foreach([
                                ['req-len',    'Mínimo 8 caracteres'],
                                ['req-upper',  'Al menos una mayúscula'],
                                ['req-num',    'Al menos un número'],
                            ] as [$id, $label])
                            <div class="flex items-center gap-2 text-xs text-slate-500" id="{{ $id }}">
                                <i class="fas fa-circle text-[8px]"></i>
                                {{ $label }}
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-5">
                    <button type="submit"
                            class="px-8 py-2.5 bg-amber-500 hover:bg-amber-600 text-black
                                   font-bold rounded-xl transition-colors flex items-center gap-2">
                        <i class="fas fa-key"></i> Actualizar Contraseña
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- TAB: Actividad --}}
    <div id="tab-actividad" class="tab-content hidden">
        <div class="card p-6">
            <h2 class="font-display font-bold text-base mb-5 flex items-center gap-2">
                <i class="fas fa-clock text-amber-500 text-sm"></i>
                Resumen de Actividad
            </h2>

            @php
                $facturas   = \App\Models\Factura::where('user_id', $usuario->id)->count();
                $cotizaciones = \App\Models\Cotizacion::where('user_id', $usuario->id)->count();
                $remisiones = \App\Models\Remision::where('user_id', $usuario->id)->count();
                $recibos    = \App\Models\ReciboCaja::where('user_id', $usuario->id)->count();
                $ordenes    = \App\Models\OrdenCompra::where('user_id', $usuario->id)->count();

                $totalFacturado = \App\Models\Factura::where('user_id', $usuario->id)
                                    ->where('estado','!=','anulada')->sum('total');
            @endphp

            {{-- Stats --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
                @foreach([
                    ['Facturas Creadas',    $facturas,      'fa-file-invoice',     'amber'],
                    ['Cotizaciones',        $cotizaciones,  'fa-file-alt',         'blue'],
                    ['Remisiones',          $remisiones,    'fa-receipt',          'purple'],
                    ['Recibos de Caja',     $recibos,       'fa-hand-holding-usd', 'emerald'],
                    ['Órdenes de Compra',   $ordenes,       'fa-shopping-cart',    'cyan'],
                    ['Total Facturado',     '$'.number_format($totalFacturado,0,',','.'),
                                           'fa-dollar-sign', 'orange'],
                ] as [$label, $valor, $icon, $color])
                <div class="bg-[#1a2235] border border-[#1e2d47] rounded-xl p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-7 h-7 bg-{{ $color }}-500/10 rounded-lg
                                    flex items-center justify-center
                                    text-{{ $color }}-{{ $color=='slate'?'400':'500' }}">
                            <i class="fas {{ $icon }} text-xs"></i>
                        </div>
                        <div class="text-xs text-slate-500">{{ $label }}</div>
                    </div>
                    <div class="font-display font-bold text-xl" style="color:#e2e8f0">
                        {{ $valor }}
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Últimas facturas del usuario --}}
            @php
                $ultimasFacturas = \App\Models\Factura::where('user_id', $usuario->id)
                                    ->orderByDesc('created_at')->limit(5)->get();
            @endphp
            @if($ultimasFacturas->count())
            <h3 class="text-sm font-semibold text-slate-400 mb-3">
                Últimas facturas creadas
            </h3>
            <div class="space-y-2">
                @foreach($ultimasFacturas as $f)
                <a href="{{ route('facturas.show', $f) }}"
                   class="flex items-center gap-3 bg-[#1a2235] border border-[#1e2d47]
                          rounded-xl p-3 hover:border-amber-500/50 transition-colors">
                    <div class="w-8 h-8 bg-amber-500/10 rounded-lg flex items-center
                                justify-center text-amber-500 flex-shrink-0">
                        <i class="fas fa-file-invoice text-xs"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-mono font-semibold text-amber-500">
                            {{ $f->numero }}
                        </div>
                        <div class="text-xs text-slate-500 truncate">
                            {{ $f->cliente_nombre }}
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <div class="text-sm font-semibold" style="color:#e2e8f0">
                            ${{ number_format($f->total, 0, ',', '.') }}
                        </div>
                        <div class="text-xs text-slate-500">
                            {{ $f->created_at->diffForHumans() }}
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            @endif

        </div>
    </div>

</div>

@push('scripts')
<script>
// ── Tabs ──────────────────────────────────────
function setTab(tab) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.getElementById('tab-' + tab).classList.remove('hidden');

    document.querySelectorAll('[id^="tab-btn-"]').forEach(btn => {
        btn.className = btn.className
            .replace('bg-[#1a2235] text-amber-500 shadow-sm', '')
            .replace('bg-amber-500/10 text-amber-500', '')
            + ' text-slate-400';
        btn.style.background = '';
    });

    const activeBtn = document.getElementById('tab-btn-' + tab);
    activeBtn.className = activeBtn.className.replace('text-slate-400', '');
    activeBtn.style.background = '#f59e0b18';
    activeBtn.style.color = '#f59e0b';
}

// Activar tab inicial
const tabInicial = '{{ session("tab", "info") }}';
setTab(tabInicial);

// ── Mayúsculas ────────────────────────────────
document.querySelectorAll('[data-uppercase]').forEach(el => {
    el.addEventListener('input', function() {
        const pos = this.selectionStart;
        this.value = this.value.toUpperCase();
        this.setSelectionRange(pos, pos);
    });
});

// ── Mostrar/ocultar contraseña ────────────────
function togglePass(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    if (!input) return;
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// ── Fortaleza contraseña ──────────────────────
const passNueva = document.getElementById('pass-nueva');
if (passNueva) {
    passNueva.addEventListener('input', function() {
        const val    = this.value;
        const colors = ['#ef4444','#f97316','#eab308','#10b981'];
        const labels = ['Muy débil','Débil','Moderada','Fuerte'];

        let score = 0;
        if (val.length >= 8)           score++;
        if (/[A-Z]/.test(val))         score++;
        if (/[0-9]/.test(val))         score++;
        if (/[^A-Za-z0-9]/.test(val))  score++;

        [1,2,3,4].forEach(i => {
            const b = document.getElementById('pbar'+i);
            if (b) b.style.background = i <= score ? colors[score-1] : '#1e2d47';
        });

        const lbl = document.getElementById('plabel');
        if (lbl) {
            lbl.textContent = val.length > 0 ? 'Fortaleza: ' + (labels[score-1]||'Muy débil') : '';
            lbl.style.color = score > 0 ? colors[score-1] : '#475569';
        }

        // Requisitos
        setReq('req-len',   val.length >= 8);
        setReq('req-upper', /[A-Z]/.test(val));
        setReq('req-num',   /[0-9]/.test(val));
    });
}

function setReq(id, ok) {
    const el = document.getElementById(id);
    if (!el) return;
    if (ok) {
        el.className = 'flex items-center gap-2 text-xs text-emerald-500';
        el.querySelector('i').className = 'fas fa-check-circle text-[10px]';
    } else {
        el.className = 'flex items-center gap-2 text-xs text-slate-500';
        el.querySelector('i').className = 'fas fa-circle text-[8px]';
    }
}

// ── Preview avatar ────────────────────────────
function previewAvatar(input) {
    const file = input.files[0];
    if (!file) return;

    // Validar tamaño (2MB)
    if (file.size > 2 * 1024 * 1024) {
        alert('La imagen no puede superar 2MB');
        input.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('preview-avatar').src = e.target.result;
        // Auto-submit
        document.getElementById('form-avatar').submit();
    };
    reader.readAsDataURL(file);
}
</script>
@endpush
@endsection