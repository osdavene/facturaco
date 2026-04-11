<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FacturaCO — Sesión expirada</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .orb { position: absolute; border-radius: 50%; filter: blur(80px); pointer-events: none; }
        .orb-1 { width: 400px; height: 400px; background: rgba(6,182,212,0.06); top: -100px; right: -100px; animation: drift 8s ease-in-out infinite; }
        .orb-2 { width: 300px; height: 300px; background: rgba(245,158,11,0.04); bottom: -80px; left: -80px; animation: drift 10s ease-in-out infinite reverse; }
        @keyframes drift {
            0%,100% { transform: translate(0,0) scale(1); }
            50% { transform: translate(30px,20px) scale(1.05); }
        }
        .code-glow { text-shadow: 0 0 80px rgba(6,182,212,0.12), 0 0 160px rgba(6,182,212,0.05); }
        .grid-bg {
            background-image:
                linear-gradient(rgba(30,45,71,0.4) 1px, transparent 1px),
                linear-gradient(90deg, rgba(30,45,71,0.4) 1px, transparent 1px);
            background-size: 48px 48px;
        }
    </style>
</head>
<body class="bg-[#0b0f1a] text-slate-200 font-sans min-h-screen flex items-center justify-center p-6 overflow-hidden relative">

    <div class="grid-bg absolute inset-0 opacity-60"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="relative text-center max-w-lg w-full z-10">

        <div class="font-display font-black text-[9rem] sm:text-[11rem] leading-none
                    text-cyan-500/10 code-glow select-none mb-2 tracking-tight">
            419
        </div>

        <div class="w-20 h-20 bg-cyan-500/10 border border-cyan-500/20 rounded-3xl
                    flex items-center justify-center mx-auto -mt-8 mb-6 relative z-10
                    shadow-lg shadow-cyan-500/5">
            <i class="fas fa-clock text-cyan-400 text-3xl"></i>
        </div>

        <h1 class="font-display font-bold text-2xl sm:text-3xl mb-3">
            Sesión expirada
        </h1>
        <p class="text-slate-400 text-sm sm:text-base leading-relaxed mb-8 max-w-sm mx-auto">
            Tu sesión ha caducado por inactividad. Regresa a la página anterior y vuelve a enviar el formulario.
        </p>

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="javascript:history.back()"
               class="inline-flex items-center justify-center gap-2 px-5 py-2.5
                      bg-[#141c2e] border border-[#1e2d47] hover:border-slate-500
                      text-slate-400 hover:text-slate-200 rounded-xl transition-colors text-sm">
                <i class="fas fa-arrow-left text-xs"></i> Volver al formulario
            </a>
            <a href="{{ route('login') }}"
               class="inline-flex items-center justify-center gap-2 px-6 py-2.5
                      bg-amber-500 hover:bg-amber-600 text-black font-semibold
                      rounded-xl transition-colors text-sm">
                <i class="fas fa-sign-in-alt text-xs"></i> Volver a ingresar
            </a>
        </div>

        <div class="mt-12 flex items-center justify-center gap-2 text-slate-700">
            <span class="font-display font-black text-sm">FacturaCO</span>
            <span class="text-xs">· Sesión expirada</span>
        </div>

    </div>
</body>
</html>
