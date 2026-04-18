<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FacturaCO — Nueva Contraseña</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0b0f1a] text-slate-200 font-sans min-h-screen flex items-center justify-center p-6">

    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <div class="mb-6">
                <img src="/img/logo-dark.png" alt="FacturaCO" class="h-14 w-auto object-contain mx-auto">
            </div>
            <h1 class="font-display font-bold text-2xl mb-1">Nueva Contraseña</h1>
            <p class="text-slate-400 text-sm">Elige una contraseña segura para tu cuenta</p>
        </div>

        <div class="card p-8">

            @if($errors->any())
            <div class="bg-red-500/10 border border-red-500/30 text-red-400
                        rounded-xl px-4 py-3 mb-5 text-sm">
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div>
                    <label class="form-label">
                        Correo Electrónico
                    </label>
                    <input type="email" name="email"
                           value="{{ old('email', $request->email) }}"
                           autocomplete="email"
                           class="form-input"
                           style="color:#e2e8f0">
                </div>

                <div>
                    <label class="form-label">
                        Nueva Contraseña
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="password"
                               autocomplete="new-password"
                               placeholder="••••••••"
                               class="form-input pr-10"
                               style="color:#e2e8f0">
                        <button type="button" onclick="togglePass('password','icon1')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500">
                            <i class="fas fa-eye text-sm" id="icon1"></i>
                        </button>
                    </div>
                    <div class="flex gap-1 mt-2">
                        @for($i=1;$i<=4;$i++)
                        <div class="h-1 flex-1 rounded-full bg-[#1e2d47]" id="bar{{$i}}"></div>
                        @endfor
                    </div>
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
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500">
                            <i class="fas fa-eye text-sm" id="icon2"></i>
                        </button>
                    </div>
                </div>

                <button type="submit"
                        class="w-full py-3 bg-amber-500 hover:bg-amber-600 text-black
                               font-bold rounded-xl transition-colors flex items-center
                               justify-center gap-2 text-sm mt-2">
                    <i class="fas fa-key"></i> Restablecer Contraseña
                </button>
            </form>
        </div>
    </div>

    <script>
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
        let score = 0;
        if (val.length >= 8)           score++;
        if (/[A-Z]/.test(val))         score++;
        if (/[0-9]/.test(val))         score++;
        if (/[^A-Za-z0-9]/.test(val))  score++;
        [1,2,3,4].forEach(i => {
            document.getElementById('bar'+i).style.background =
                i <= score ? colors[score-1] : '#1e2d47';
        });
    });
    </script>
</body>
</html>