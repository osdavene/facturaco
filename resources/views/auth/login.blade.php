<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FacturaCO — Iniciar Sesión</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0b0f1a] text-slate-200 font-sans min-h-screen flex">

    {{-- Panel izquierdo decorativo --}}
    <div class="hidden lg:flex lg:w-1/2 bg-[#111827] border-r border-[#1e2d47]
                flex-col justify-between p-12 relative overflow-hidden">

        {{-- Fondo decorativo --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-32 -left-32 w-96 h-96 bg-amber-500/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-blue-500/5 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2
                        w-64 h-64 bg-amber-500/3 rounded-full blur-2xl"></div>
        </div>

        {{-- Logo --}}
        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-16">
                <div class="w-12 h-12 bg-amber-500 rounded-2xl flex items-center justify-center
                            font-display font-black text-black text-xl">FC</div>
                <div>
                    <div class="font-display font-black text-2xl text-white">
                        Factura<span class="text-amber-500">CO</span>
                    </div>
                    <div class="text-xs text-slate-500">Sistema Empresarial</div>
                </div>
            </div>

            <h2 class="font-display font-bold text-4xl text-white leading-tight mb-4">
                Gestiona tu<br>
                <span class="text-amber-500">negocio</span> con<br>
                confianza
            </h2>
            <p class="text-slate-400 text-base leading-relaxed">
                Facturación electrónica, inventario, clientes,
                reportes y más — todo en un solo lugar.
            </p>
        </div>

        {{-- Features --}}
        <div class="relative z-10 space-y-4">
            @foreach([
                ['fa-file-invoice',    'amber',   'Facturación Electrónica', 'Cumple con la DIAN fácilmente'],
                ['fa-boxes',           'blue',    'Control de Inventario',   'Stock en tiempo real'],
                ['fa-chart-line',      'emerald', 'Reportes y Analytics',    'Toma decisiones con datos'],
                ['fa-file-alt',        'purple',  'Cotizaciones',            'Convierte en factura con un clic'],
            ] as [$icon, $color, $title, $desc])
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-{{ $color }}-500/10 border border-{{ $color }}-500/20
                            rounded-xl flex items-center justify-center
                            text-{{ $color }}-500 flex-shrink-0">
                    <i class="fas {{ $icon }} text-sm"></i>
                </div>
                <div>
                    <div class="text-sm font-semibold text-white">{{ $title }}</div>
                    <div class="text-xs text-slate-500">{{ $desc }}</div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Footer --}}
        <div class="relative z-10 text-xs text-slate-600">
            FacturaCO © {{ now()->year }} · Hecho en Colombia 🇨🇴
        </div>
    </div>

    {{-- Panel derecho — formulario --}}
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 lg:p-12">
        <div class="w-full max-w-md">

            {{-- Logo móvil --}}
            <div class="flex items-center gap-3 mb-10 lg:hidden">
                <div class="w-10 h-10 bg-amber-500 rounded-xl flex items-center justify-center
                            font-display font-black text-black text-lg">FC</div>
                <div class="font-display font-black text-xl text-white">
                    Factura<span class="text-amber-500">CO</span>
                </div>
            </div>

            <h1 class="font-display font-bold text-3xl mb-2">Iniciar Sesión</h1>
            <p class="text-slate-400 text-sm mb-8">Ingresa tus credenciales para continuar</p>

            @if($errors->any())
            <div class="bg-red-500/10 border border-red-500/30 text-red-400
                        rounded-xl px-5 py-4 mb-6 flex items-start gap-3">
                <i class="fas fa-exclamation-circle mt-0.5"></i>
                <div class="text-sm">{{ $errors->first() }}</div>
            </div>
            @endif

            @if(session('status'))
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400
                        rounded-xl px-5 py-4 mb-6 text-sm">
                {{ session('status') }}
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wider">
                        Correo Electrónico
                    </label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2
                                  text-slate-500 text-sm"></i>
                        <input type="email" name="email" value="{{ old('email') }}"
                               autofocus autocomplete="email"
                               placeholder="tu@correo.com"
                               class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl
                                      pl-11 pr-4 py-3 text-sm placeholder-slate-600
                                      focus:outline-none focus:border-amber-500 focus:ring-1
                                      focus:ring-amber-500/30 transition-all
                                      @error('email') border-red-500 @enderror"
                               style="color:#e2e8f0">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                            Contraseña
                        </label>
                        @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                           class="text-xs text-amber-500 hover:text-amber-400 transition-colors">
                            ¿Olvidaste tu contraseña?
                        </a>
                        @endif
                    </div>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2
                                  text-slate-500 text-sm"></i>
                        <input type="password" name="password" id="password"
                               autocomplete="current-password"
                               placeholder="••••••••"
                               class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl
                                      pl-11 pr-12 py-3 text-sm placeholder-slate-600
                                      focus:outline-none focus:border-amber-500 focus:ring-1
                                      focus:ring-amber-500/30 transition-all
                                      @error('password') border-red-500 @enderror"
                               style="color:#e2e8f0">
                        <button type="button" onclick="togglePass()"
                                class="absolute right-4 top-1/2 -translate-y-1/2
                                       text-slate-500 hover:text-slate-300 transition-colors">
                            <i class="fas fa-eye text-sm" id="pass-icon"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" name="remember" id="remember"
                           class="w-4 h-4 accent-amber-500 rounded"
                           {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember" class="text-sm text-slate-400 cursor-pointer">
                        Mantener sesión iniciada
                    </label>
                </div>

                <button type="submit"
                        class="w-full py-3 bg-amber-500 hover:bg-amber-600 text-black
                               font-bold rounded-xl transition-all flex items-center
                               justify-center gap-2 text-sm shadow-lg shadow-amber-500/20
                               hover:shadow-amber-500/30">
                    <i class="fas fa-sign-in-alt"></i>
                    Iniciar Sesión
                </button>
            </form>

            <div class="mt-8 pt-8 border-t border-[#1e2d47] text-center">
                <p class="text-xs text-slate-600">
                    FacturaCO · Sistema de Gestión Empresarial
                </p>
            </div>
        </div>
    </div>

    <script>
    function togglePass() {
        const input = document.getElementById('password');
        const icon  = document.getElementById('pass-icon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
    </script>
</body>
</html>