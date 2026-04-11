@extends('layouts.app')
@section('title', 'Enviar Factura')
@section('page-title', 'Facturación · Enviar por Email')

@section('content')
<div class="max-w-xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('facturas.show', $factura) }}"
           class="w-9 h-9 bg-[#141c2e] border border-[#1e2d47] rounded-xl
                  flex items-center justify-center text-slate-400
                  hover:text-amber-500 hover:border-amber-500/50 transition-colors">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="font-display font-bold text-2xl">Enviar Factura</h1>
            <p class="text-slate-500 text-sm">{{ $factura->numero }} — {{ $factura->cliente_nombre }}</p>
        </div>
    </div>

    @if(session('error'))
    <div class="bg-red-500/10 border border-red-500/30 text-red-400
                rounded-xl px-5 py-3 mb-5 flex items-center gap-3">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif

    <form method="POST" action="{{ route('facturas.enviar', $factura) }}">
        @csrf

        <div class="card p-6 space-y-5">

            @if($errors->any())
            <div class="bg-red-500/10 border border-red-500/30 text-red-400
                        rounded-xl px-4 py-3 flex items-center gap-3 text-sm">
                <i class="fas fa-exclamation-circle flex-shrink-0"></i>
                {{ $errors->first() }}
            </div>
            @endif

            {{-- Resumen de la factura --}}
            <div class="bg-[#1a2235] border border-[#1e2d47] rounded-xl p-4">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">
                    Resumen de la Factura
                </div>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <div class="text-xs text-slate-600">Número</div>
                        <div class="font-mono font-bold text-amber-400">{{ $factura->numero }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-600">Total</div>
                        <div class="font-bold text-emerald-400">
                            ${{ number_format($factura->total, 0, ',', '.') }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-600">Cliente</div>
                        <div class="text-slate-300 truncate">{{ $factura->cliente_nombre }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-600">Vencimiento</div>
                        <div class="text-slate-300">{{ $factura->fecha_vencimiento->format('d/m/Y') }}</div>
                    </div>
                </div>
            </div>

            {{-- Correo destinatario --}}
            <div>
                <label class="form-label">
                    Correo del destinatario *
                </label>
                <input type="email" name="email"
                       value="{{ old('email', $factura->cliente_email) }}"
                       placeholder="cliente@empresa.com"
                       autofocus
                       class="form-input @error('email') border-red-500 @enderror">
                @error('email')
                <p class="text-red-400 text-xs mt-1.5 flex items-center gap-1">
                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                </p>
                @enderror
                @if($factura->cliente_email)
                <p class="text-xs text-slate-600 mt-1">
                    <i class="fas fa-info-circle text-amber-500/70"></i>
                    Se precargó el email registrado del cliente.
                </p>
                @endif
            </div>

            {{-- Mensaje personalizado --}}
            <div>
                <label class="form-label">
                    Mensaje personalizado
                    <span class="text-slate-600 normal-case font-normal">(opcional)</span>
                </label>
                <textarea name="mensaje" rows="4"
                          placeholder="Ej: Adjunto encontrará la factura correspondiente al mes de abril. Quedo atento a cualquier inquietud..."
                          class="form-input resize-none
                                 focus:outline-none focus:border-amber-500 transition-colors">{{ old('mensaje') }}</textarea>
                <p class="text-xs text-slate-600 mt-1">Máximo 500 caracteres. Este texto aparece en el cuerpo del correo.</p>
            </div>

            {{-- Info de lo que se envía --}}
            <div class="bg-amber-500/5 border border-amber-500/20 rounded-xl px-4 py-3
                        flex items-start gap-2 text-xs text-slate-500">
                <i class="fas fa-paper-plane text-amber-500 mt-0.5 flex-shrink-0"></i>
                <div>
                    Se enviará un correo con el <strong class="text-slate-400">PDF de la factura adjunto</strong>
                    y el resumen con número, fecha, total y estado.
                    @if($factura->estado === 'borrador')
                    <br><span class="text-amber-400 font-semibold">La factura pasará a estado "Emitida" al enviarse.</span>
                    @endif
                </div>
            </div>

        </div>

        <div class="flex gap-3 mt-4">
            <a href="{{ route('facturas.show', $factura) }}"
               class="flex-1 text-center bg-[#141c2e] border border-[#1e2d47]
                      text-slate-400 font-semibold text-sm py-2.5 rounded-xl
                      hover:border-slate-500 transition-colors">
                Cancelar
            </a>
            <button type="submit"
                    class="flex-1 bg-amber-500 hover:bg-amber-600 text-black
                           font-bold text-sm py-2.5 rounded-xl transition-colors">
                <i class="fas fa-paper-plane mr-2"></i> Enviar Factura
            </button>
        </div>
    </form>

</div>
@endsection