<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Empresa — FacturaCO</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0b0f1a] text-slate-200 min-h-screen flex flex-col items-center justify-center p-4">

    <div class="w-full max-w-lg">

        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="font-display font-black text-3xl text-white mb-1">
                Factura<span class="text-amber-500">CO</span>
            </div>
            <p class="text-slate-500 text-sm">Selecciona la empresa con la que deseas trabajar</p>
        </div>

        {{-- Lista de empresas --}}
        <div class="space-y-3 mb-6">
            @foreach($empresas as $emp)
            <form method="POST" action="{{ route('empresas.elegir', $emp->id) }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-4 bg-[#141c2e] hover:bg-[#1a2235]
                               border border-[#1e2d47] hover:border-amber-500/50
                               rounded-2xl p-5 text-left transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/20
                                flex items-center justify-center flex-shrink-0 overflow-hidden">
                        @if($emp->logo)
                            <img src="{{ Storage::url($emp->logo) }}"
                                 class="w-12 h-12 object-contain" alt="">
                        @else
                            <span class="font-display font-black text-amber-500 text-lg">
                                {{ strtoupper(substr($emp->razon_social, 0, 2)) }}
                            </span>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-display font-bold text-white group-hover:text-amber-400 transition-colors truncate">
                            {{ $emp->nombre_comercial ?: $emp->razon_social }}
                        </div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            NIT: {{ $emp->nit_formateado }}
                            @if($emp->municipio) · {{ $emp->municipio }} @endif
                        </div>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-[10px] px-2 py-0.5 rounded-full
                                         {{ $emp->pivot->rol === 'admin' ? 'bg-amber-500/15 text-amber-400' : 'bg-slate-700 text-slate-400' }}">
                                {{ $emp->pivot->rol === 'admin' ? 'Administrador' : 'Operador' }}
                            </span>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-slate-600 group-hover:text-amber-500
                               transition-colors flex-shrink-0"></i>
                </button>
            </form>
            @endforeach
        </div>

        {{-- Crear nueva empresa --}}
        <div class="text-center">
            <a href="{{ route('empresas.crear') }}"
               class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-amber-400 transition-colors">
                <i class="fas fa-plus-circle"></i>
                Crear nueva empresa
            </a>
        </div>

        {{-- Logout --}}
        <div class="text-center mt-4">
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit"
                        class="text-xs text-slate-600 hover:text-red-400 transition-colors">
                    <i class="fas fa-sign-out-alt mr-1"></i> Cerrar sesión
                </button>
            </form>
        </div>
    </div>

</body>
</html>
