<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FacturaCO — Error del servidor</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0b0f1a] text-slate-200 font-sans min-h-screen flex items-center justify-center p-6">
    <div class="text-center max-w-md">
        <div class="font-display font-black text-[8rem] leading-none text-red-500/20 mb-4">
            500
        </div>
        <div class="w-16 h-16 bg-red-500/10 border border-red-500/20 rounded-2xl
                    flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-exclamation-triangle text-red-400 text-2xl"></i>
        </div>
        <h1 class="font-display font-bold text-2xl mb-3">Error del servidor</h1>
        <p class="text-slate-400 text-sm mb-8">
            Algo salió mal. Intenta de nuevo o contacta al administrador.
        </p>
        <a href="{{ route('dashboard') }}"
           class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-black font-semibold
                  rounded-xl transition-colors text-sm inline-flex items-center gap-2">
            <i class="fas fa-chart-line text-xs"></i> Ir al Dashboard
        </a>
    </div>
</body>
</html>