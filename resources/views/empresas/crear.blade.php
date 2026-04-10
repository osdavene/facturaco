<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Empresa — FacturaCO</title>
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
            <p class="text-slate-500 text-sm">Configura tu empresa para comenzar</p>
        </div>

        {{-- Errores --}}
        @if($errors->any())
        <div class="bg-red-500/10 border border-red-500/30 text-red-400
                    rounded-xl px-5 py-4 mb-5">
            <ul class="text-sm space-y-1">
                @foreach($errors->all() as $error)
                <li><i class="fas fa-exclamation-circle mr-2"></i>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('empresas.store') }}"
              class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-6 space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Razón social --}}
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">
                        Razón Social <span class="text-amber-500">*</span>
                    </label>
                    <input type="text" name="razon_social"
                           value="{{ old('razon_social') }}"
                           class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                                  text-sm text-slate-200 placeholder-slate-600
                                  focus:outline-none focus:border-amber-500 transition-colors"
                           placeholder="MI EMPRESA S.A.S"
                           required>
                </div>

                {{-- Nombre comercial --}}
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">
                        Nombre Comercial
                    </label>
                    <input type="text" name="nombre_comercial"
                           value="{{ old('nombre_comercial') }}"
                           class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                                  text-sm text-slate-200 placeholder-slate-600
                                  focus:outline-none focus:border-amber-500 transition-colors"
                           placeholder="Nombre visible en facturas (opcional)">
                </div>

                {{-- NIT --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">
                        NIT <span class="text-amber-500">*</span>
                    </label>
                    <input type="text" name="nit"
                           value="{{ old('nit') }}"
                           class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                                  text-sm text-slate-200 placeholder-slate-600
                                  focus:outline-none focus:border-amber-500 transition-colors"
                           placeholder="900000000"
                           required>
                </div>

                {{-- Dígito de verificación --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">
                        Dígito Verificación
                    </label>
                    <input type="text" name="digito_verificacion"
                           value="{{ old('digito_verificacion') }}"
                           maxlength="1"
                           class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                                  text-sm text-slate-200 placeholder-slate-600
                                  focus:outline-none focus:border-amber-500 transition-colors"
                           placeholder="0">
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">
                        Email
                    </label>
                    <input type="email" name="email"
                           value="{{ old('email') }}"
                           class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                                  text-sm text-slate-200 placeholder-slate-600
                                  focus:outline-none focus:border-amber-500 transition-colors"
                           placeholder="empresa@correo.com">
                </div>

                {{-- Teléfono --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">
                        Teléfono
                    </label>
                    <input type="text" name="telefono"
                           value="{{ old('telefono') }}"
                           class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                                  text-sm text-slate-200 placeholder-slate-600
                                  focus:outline-none focus:border-amber-500 transition-colors"
                           placeholder="601 0000000">
                </div>

                {{-- Ciudad --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">
                        Municipio
                    </label>
                    <input type="text" name="municipio"
                           value="{{ old('municipio') }}"
                           class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                                  text-sm text-slate-200 placeholder-slate-600
                                  focus:outline-none focus:border-amber-500 transition-colors"
                           placeholder="Bogotá D.C.">
                </div>

                {{-- Prefijo --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">
                        Prefijo de Factura
                    </label>
                    <input type="text" name="prefijo_factura"
                           value="{{ old('prefijo_factura', 'FE') }}"
                           maxlength="10"
                           class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                                  text-sm text-slate-200 placeholder-slate-600
                                  focus:outline-none focus:border-amber-500 transition-colors"
                           placeholder="FE">
                </div>

                {{-- Moneda --}}
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">
                        Moneda
                    </label>
                    <select name="moneda"
                            class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5
                                   text-sm text-slate-200 focus:outline-none focus:border-amber-500 transition-colors">
                        <option value="COP" {{ old('moneda', 'COP') === 'COP' ? 'selected' : '' }}>
                            COP — Peso Colombiano
                        </option>
                        <option value="USD" {{ old('moneda') === 'USD' ? 'selected' : '' }}>
                            USD — Dólar
                        </option>
                    </select>
                </div>
            </div>

            <button type="submit"
                    class="w-full py-3 bg-amber-500 hover:bg-amber-600 text-black
                           font-bold rounded-xl transition-colors text-sm">
                <i class="fas fa-building mr-2"></i> Crear Empresa y Continuar
            </button>
        </form>

        {{-- Volver --}}
        @if(auth()->user()->empresas()->count() > 0)
        <div class="text-center mt-4">
            <a href="{{ route('empresas.seleccionar') }}"
               class="text-xs text-slate-600 hover:text-slate-400 transition-colors">
                <i class="fas fa-arrow-left mr-1"></i> Volver al selector
            </a>
        </div>
        @endif

    </div>

</body>
</html>
