@extends('layouts.app')
@section('title', 'Certificados Laborales')
@section('page-title', 'Nómina · Certificados Laborales')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display font-bold text-2xl">Certificados y Cartas Laborales</h1>
            <p class="text-slate-500 text-sm mt-0.5">Expide certificaciones laborales oficiales con membrete y firma en PDF</p>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-xl px-5 py-3 flex items-center gap-3 text-sm">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-5 py-3 flex items-center gap-3 text-sm">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif

    <div class="card p-6">
        <form method="POST" action="{{ route('nomina.certificados.generar') }}" target="_blank" class="space-y-5" id="certForm">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="form-label">Empleado <span class="text-amber-500">*</span></label>
                    <select name="empleado_id" required class="form-input text-sm">
                        <option value="">-- Seleccionar empleado --</option>
                        @foreach($empleados as $e)
                        <option value="{{ $e->id }}" @selected(request('empleado_id') == $e->id)>
                            {{ $e->apellidos }} {{ $e->nombres }} — {{ $e->cargo ?: 'Empleado' }} ({{ $e->tipo_documento }} {{ $e->numero_documento }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Destinatario de la Carta</label>
                    <input type="text" name="destinatario" value="A QUIEN PUEDA INTERESAR"
                           placeholder="Ej. Banco de Bogotá / Embajada / A QUIEN PUEDA INTERESAR"
                           class="form-input text-sm">
                </div>

                <div>
                    <label class="form-label">¿Incluir Salario en la Carta?</label>
                    <select name="incluir_salario" class="form-input text-sm">
                        <option value="1" selected>Sí, incluir salario actual devengado</option>
                        <option value="0">No, solo constancia de cargo y antigüedad</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">¿Incluir Manual de Funciones?</label>
                    <select name="incluir_funciones" class="form-input text-sm">
                        <option value="0" selected>No, solo datos del cargo</option>
                        <option value="1">Sí, detallar funciones y responsabilidades desempeñadas</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="form-label">Párrafo Adicional / Observaciones (Opcional)</label>
                    <textarea name="observaciones" rows="2"
                              placeholder="Ej. El presente certificado se expide a solicitud del interesado para trámites de crédito..."
                              class="form-input text-sm"></textarea>
                </div>
            </div>

            <div class="pt-3 border-t border-[#1e2d47] flex items-center justify-end gap-3">
                <button type="submit"
                        class="bg-amber-500 hover:bg-amber-600 text-black font-bold text-sm px-6 py-2.5 rounded-xl transition-all shadow-lg shadow-amber-500/20">
                    <i class="fas fa-print mr-1.5"></i> Vista Previa e Imprimir PDF
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
