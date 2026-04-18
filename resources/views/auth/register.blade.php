<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FacturaCO — Registro</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0b0f1a] text-slate-200 font-sans min-h-screen flex items-center justify-center p-6">

    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <div class="mb-4">
                <img src="/img/logo-dark.png" alt="FacturaCO" class="h-14 w-auto object-contain">
            </div>
            <h1 class="font-display font-bold text-2xl mb-1">Crear Cuenta</h1>
            <p class="text-slate-400 text-sm">Completa los datos para registrarte</p>
        </div>

        <div class="card p-8">

            @if($errors->any())
            <div class="bg-red-500/10 border border-red-500/30 text-red-400
                        rounded-xl px-4 py-3 mb-5 text-sm">
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="form-label">
                        Nombre Completo
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           autofocus autocomplete="name"
                           data-uppercase
                           placeholder="TU NOMBRE"
                           class="form-input"
                           style="color:#e2e8f0">
                    @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">
                        Correo Electrónico
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           autocomplete="email"
                           placeholder="tu@correo.com"
                           class="form-input"
                           style="color:#e2e8f0">
                    @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">
                        Contraseña
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="password"
                               autocomplete="new-password"
                               placeholder="••••••••"
                               class="form-input pr-10"
                               style="color:#e2e8f0">
                        <button type="button" onclick="togglePass('password','icon1')"
                                class="absolute right-3 top-1/2 -translate-y-1/2
                                       text-slate-500 hover:text-slate-300 transition-colors">
                            <i class="fas fa-eye text-sm" id="icon1"></i>
                        </button>
                    </div>
                    {{-- Fortaleza --}}
                    <div class="flex gap-1 mt-2">
                        @for($i=1;$i<=4;$i++)
                        <div class="h-1 flex-1 rounded-full bg-[#1e2d47]" id="bar{{$i}}"></div>
                        @endfor
                    </div>
                    <div class="text-xs text-slate-600 mt-1" id="pass-label"></div>
                    @error('password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">
                        Confirmar Contraseña
                    </label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="password2"
                               autocomplete="new-password"
                               placeholder="••••••••"
                               class="form-input pr-10"
                               style="color:#e2e8f0">
                        <button type="button" onclick="togglePass('password2','icon2')"
                                class="absolute right-3 top-1/2 -translate-y-1/2
                                       text-slate-500 hover:text-slate-300 transition-colors">
                            <i class="fas fa-eye text-sm" id="icon2"></i>
                        </button>
                    </div>
                </div>

                <button type="submit"
                        class="w-full py-3 bg-amber-500 hover:bg-amber-600 text-black
                               font-bold rounded-xl transition-colors flex items-center
                               justify-center gap-2 text-sm mt-2">
                    <i class="fas fa-user-plus"></i> Crear Cuenta
                </button>

                <p class="text-center text-sm text-slate-500 mt-4">
                    ¿Ya tienes cuenta?
                    <a href="{{ route('login') }}" class="text-amber-500 hover:underline">
                        Iniciar sesión
                    </a>
                </p>
            </form>
        </div>
    </div>

    <script>
    document.querySelectorAll('[data-uppercase]').forEach(el => {
        el.addEventListener('input', function() {
            const pos = this.selectionStart;
            this.value = this.value.toUpperCase();
            this.setSelectionRange(pos, pos);
        });
    });

    function togglePass(id, iconId) {
        const input = document.getElementById(id);
        const icon  = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    document.getElementById('password').addEventListener('input', function() {
        const val    = this.value;
        const colors = ['#ef4444','#f97316','#eab308','#10b981'];
        const labels = ['Muy débil','Débil','Moderada','Fuerte'];
        let score = 0;
        if (val.length >= 8)           score++;
        if (/[A-Z]/.test(val))         score++;
        if (/[0-9]/.test(val))         score++;
        if (/[^A-Za-z0-9]/.test(val))  score++;
        [1,2,3,4].forEach(i => {
            const b = document.getElementById('bar'+i);
            b.style.background = i <= score ? colors[score-1] : '#1e2d47';
        });
        const label = document.getElementById('pass-label');
        label.textContent = val.length > 0 ? 'Fortaleza: ' + (labels[score-1]||'Muy débil') : '';
        label.style.color = score > 0 ? colors[score-1] : '#475569';
    });
    </script>
</body>
</html>