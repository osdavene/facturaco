<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagar Factura {{ $factura->numero }} — {{ $empresa->nombre_comercial ?: $empresa->razon_social }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Script oficial de Wompi Widget --}}
    <script type="text/javascript" src="https://checkout.wompi.co/widget.js"></script>
</head>
<body class="bg-[#0b0f1a] text-slate-200 font-sans min-h-screen flex flex-col justify-between antialiased selection:bg-amber-500 selection:text-black">

    {{-- HEADER PÚBLICO --}}
    <header class="border-b border-[#1e2d47] bg-[#111827]/90 backdrop-blur-md sticky top-0 z-30">
        <div class="max-w-4xl mx-auto px-4 py-3.5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if($empresa->logo_url)
                    <img src="{{ $empresa->logo_url }}" class="h-9 max-w-[140px] object-contain" alt="{{ $empresa->razon_social }}">
                @else
                    <div class="w-9 h-9 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center font-black text-amber-500 text-lg">
                        {{ substr($empresa->razon_social, 0, 1) }}
                    </div>
                @endif
                <div>
                    <h1 class="font-display font-bold text-sm sm:text-base text-white truncate max-w-[200px] sm:max-w-xs">
                        {{ $empresa->nombre_comercial ?: $empresa->razon_social }}
                    </h1>
                    <p class="text-[11px] text-slate-400">NIT: {{ $empresa->nit_formateado ?? $empresa->nit }}</p>
                </div>
            </div>

            <div>
                <a href="{{ route('facturas.pdf', $factura) }}" target="_blank"
                   class="bg-[#1a2235] hover:bg-[#222f47] border border-[#1e2d47] text-slate-200 text-xs font-semibold px-3 py-2 rounded-xl transition-all inline-flex items-center gap-1.5">
                    <i class="fas fa-file-pdf text-red-400"></i>
                    <span class="hidden sm:inline">Descargar</span> PDF
                </a>
            </div>
        </div>
    </header>

    {{-- CONTENIDO PRINCIPAL --}}
    <main class="max-w-4xl mx-auto px-4 py-8 w-full flex-1">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">

            {{-- COLUMNA IZQUIERDA: DETALLE FACTURA --}}
            <div class="md:col-span-7 space-y-5">
                <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-6 shadow-xl relative overflow-hidden">
                    <div class="flex items-center justify-between gap-2 border-b border-[#1e2d47] pb-4 mb-4">
                        <div>
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Comprobante de Venta</span>
                            <h2 class="text-xl font-display font-bold text-white mt-0.5">
                                Factura #{{ $factura->numero }}
                            </h2>
                        </div>
                        <div>
                            @if($factura->estado === 'pagada')
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center gap-1.5">
                                    <i class="fas fa-check-circle"></i> Pagada
                                </span>
                            @elseif($factura->estado === 'vencida')
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-500/20 text-red-400 border border-red-500/30 flex items-center gap-1.5">
                                    <i class="fas fa-clock"></i> Vencida
                                </span>
                            @else
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center gap-1.5">
                                    <i class="fas fa-exclamation-circle"></i> Pendiente
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Datos del cliente y fechas --}}
                    <div class="grid grid-cols-2 gap-4 text-xs mb-5">
                        <div>
                            <p class="text-slate-500 uppercase tracking-wider text-[10px] font-semibold">Facturado a</p>
                            <p class="font-bold text-slate-200 mt-0.5 text-sm">{{ $factura->cliente_nombre }}</p>
                            <p class="text-slate-400">{{ $factura->cliente_documento }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 uppercase tracking-wider text-[10px] font-semibold">Fecha de Emisión</p>
                            <p class="text-slate-200 mt-0.5">{{ $factura->fecha_emision ? $factura->fecha_emision->format('d/m/Y') : '-' }}</p>
                            <p class="text-slate-500 uppercase tracking-wider text-[10px] font-semibold mt-2">Vencimiento</p>
                            <p class="{{ $factura->estado === 'vencida' ? 'text-red-400 font-bold' : 'text-slate-200' }}">
                                {{ $factura->fecha_vencimiento ? $factura->fecha_vencimiento->format('d/m/Y') : 'Inmediato' }}
                            </p>
                        </div>
                    </div>

                    {{-- Lista de Ítems --}}
                    <div class="border-t border-[#1e2d47] pt-4">
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Detalle de Productos / Servicios</p>
                        <div class="space-y-2">
                            @foreach($factura->items as $it)
                            <div class="flex items-center justify-between text-xs py-1.5 border-b border-[#1e2d47]/50 last:border-0">
                                <div class="pr-2">
                                    <p class="font-medium text-slate-200">{{ $it->descripcion }}</p>
                                    <p class="text-[11px] text-slate-500">Cant: {{ rtrim(rtrim(number_format($it->cantidad, 2), '0'), '.') }} x ${{ number_format($it->precio_unitario, 0, ',', '.') }}</p>
                                </div>
                                <div class="text-right font-bold text-slate-200 whitespace-nowrap">
                                    ${{ number_format($it->total, 0, ',', '.') }}
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- COLUMNA DERECHA: RESUMEN DE PAGO & BOTÓN WOMPI / PSE / NEQUI --}}
            <div class="md:col-span-5 space-y-4">
                <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl p-6 shadow-xl sticky top-20">
                    <h3 class="text-sm font-bold text-slate-300 uppercase tracking-wider mb-4">
                        Resumen de la Cuenta
                    </h3>

                    <div class="space-y-2.5 text-xs">
                        <div class="flex justify-between text-slate-400">
                            <span>Subtotal:</span>
                            <span class="text-slate-200 font-semibold">${{ number_format($factura->subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if($factura->descuento > 0)
                        <div class="flex justify-between text-emerald-400">
                            <span>Descuento:</span>
                            <span class="font-semibold">-${{ number_format($factura->descuento, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if($factura->iva > 0)
                        <div class="flex justify-between text-slate-400">
                            <span>IVA:</span>
                            <span class="text-slate-200 font-semibold">${{ number_format($factura->iva, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        <div class="border-t border-[#1e2d47] pt-2 flex justify-between text-sm text-slate-200 font-bold">
                            <span>Total Factura:</span>
                            <span>${{ number_format($factura->total, 0, ',', '.') }}</span>
                        </div>
                        @if($factura->total_pagado > 0)
                        <div class="flex justify-between text-emerald-400 text-xs">
                            <span>Abonado / Pagado:</span>
                            <span>-${{ number_format($factura->total_pagado, 0, ',', '.') }}</span>
                        </div>
                        @endif
                    </div>

                    {{-- Saldo a pagar destacado --}}
                    <div class="mt-5 p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-center">
                        <span class="text-[11px] font-bold text-amber-400 uppercase tracking-wider block">Saldo Pendiente a Pagar</span>
                        <span class="text-2xl sm:text-3xl font-black text-white font-display mt-1 block">
                            ${{ number_format($factura->saldo_pendiente, 0, ',', '.') }} <span class="text-xs font-normal text-slate-400">COP</span>
                        </span>
                    </div>

                    {{-- Botón de pago en línea --}}
                    <div class="mt-5">
                        @if($factura->estado === 'pagada' || $factura->saldo_pendiente <= 0)
                            <div class="p-3.5 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-center text-emerald-400 font-bold text-sm flex items-center justify-center gap-2">
                                <i class="fas fa-check-circle text-lg"></i>
                                <span>Esta factura ya se encuentra pagada</span>
                            </div>
                        @elseif(!empty($empresa->wompi_public_key))
                            {{-- Formulario oficial Wompi Widget --}}
                            <form action="https://checkout.wompi.co/p/" method="GET" class="w-full">
                                <input type="hidden" name="public-key" value="{{ $empresa->wompi_public_key }}" />
                                <input type="hidden" name="currency" value="COP" />
                                <input type="hidden" name="amount-in-cents" value="{{ $montoEnCentavos }}" />
                                <input type="hidden" name="reference" value="{{ $wompiReferencia }}" />
                                @if(!empty($wompiSignature))
                                    <input type="hidden" name="signature:integrity" value="{{ $wompiSignature }}" />
                                @endif
                                <input type="hidden" name="customer-data:email" value="{{ $factura->cliente_email }}" />
                                <input type="hidden" name="customer-data:full-name" value="{{ $factura->cliente_nombre }}" />
                                <input type="hidden" name="redirect-url" value="{{ route('factura.pago_publico', $factura->token_pago) }}" />

                                <button type="submit"
                                        class="w-full bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-black font-extrabold py-3.5 px-4 rounded-xl text-sm transition-all shadow-lg shadow-amber-500/20 flex items-center justify-center gap-2 transform active:scale-95">
                                    <i class="fas fa-credit-card"></i>
                                    <span>Pagar con PSE / Nequi / Tarjeta</span>
                                </button>
                            </form>
                            <div class="flex items-center justify-center gap-3 mt-3 text-slate-400 text-xs">
                                <span><i class="fas fa-lock text-emerald-400 mr-1"></i>Pago 100% Seguro</span>
                                <span>•</span>
                                <span>Pasarela Wompi</span>
                            </div>
                        @else
                            <div class="p-3.5 bg-[#111827] border border-[#1e2d47] rounded-xl text-center text-xs text-slate-400 space-y-1">
                                <p class="font-semibold text-slate-300">Medios de pago disponibles:</p>
                                <p>Transferencia bancaria, efectivo o consignación directa a la empresa.</p>
                                @if($empresa->telefono)
                                    <a href="https://api.whatsapp.com/send?phone=57{{ preg_replace('/\D/', '', $empresa->telefono) }}&text=Hola,%20acabo%20de%20ver%20mi%20factura%20{{ $factura->numero }}"
                                       target="_blank"
                                       class="inline-flex items-center gap-1.5 text-emerald-400 hover:underline font-semibold mt-1">
                                        <i class="fab fa-whatsapp"></i> Contactar por WhatsApp
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </main>

    {{-- FOOTER --}}
    <footer class="border-t border-[#1e2d47] bg-[#111827] py-4 text-center text-xs text-slate-500">
        <p>Facturación electrónica y gestión empresarial con <strong class="text-amber-500">FacturaCO</strong></p>
    </footer>

</body>
</html>
