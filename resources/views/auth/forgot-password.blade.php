<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FacturaCO — Recuperar Contraseña</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0b0f1a] text-slate-200 font-sans min-h-screen flex items-center justify-center p-6">

    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-3 mb-6">
                <div class="w-12 h-12 bg-amber-500 rounded-2xl flex items-center justify-center
                            font-display font-black text-black text-xl">FC</div>
                <div class="font-display font-black text-2xl text-white">
                    Factura<span class="text-amber-500">CO</span>
                </div>
            </div>
            <div class="w-16 h-16 bg-amber-500/10 border border-amber-500/20 rounded-2xl
                        flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-lock text-amber-500 text-2xl"></i>
            </div>
            <h1 class="font-display font-bold text-2xl mb-2">¿Olvidaste tu contraseña?</h1>
            <p class="text-slate-400 text-sm leading-relaxed">
                Escribe tu correo y te enviaremos un enlace para restablecerla.
            </p>
        </div>

        <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-8">

            @if(session('status'))
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400
                        rounded-xl px-4 py-3 mb-5 flex items-center gap-2 text-sm">
                <i class="fas fa-check-circle"></i> {{ session('status') }}
            </div>
            @endif

            @if($errors->any())
            <div class="bg-red-500/10 border border-red-500/30 text-red-400
                        rounded-xl px-4 py-3 mb-5 text-sm">
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
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
                                      focus:outline-none focus:border-amber-500"
                               style="color:#e2e8f0">
                    </div>
                </div>
                <button type="submit"
                        class="w-full py-3 bg-amber-500 hover:bg-amber-600 text-black
                               font-bold rounded-xl transition-colors flex items-center
                               justify-center gap-2 text-sm">
                    <i class="fas fa-paper-plane"></i> Enviar Enlace
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}"
                   class="text-sm text-slate-400 hover:text-amber-500 transition-colors flex
                          items-center justify-center gap-2">
                    <i class="fas fa-arrow-left text-xs"></i> Volver al login
                </a>
            </div>
        </div>
    </div>
</body>
</html>