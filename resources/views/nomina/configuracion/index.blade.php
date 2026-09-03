@extends('layouts.app')
@section('title', 'Parámetros de Nómina')
@section('page-title', 'Nómina · Configuración')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display font-bold text-2xl">Parámetros Laborales y Legales</h1>
            <p class="text-slate-500 text-sm mt-0.5">Configura el Salario Mínimo Legal Vigente y topes aplicables para Colombia</p>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-xl px-5 py-3 flex items-center gap-3 text-sm">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <div class="card p-6">
        <form method="POST" action="{{ route('nomina.configuracion.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <h3 class="font-bold text-slate-200 text-sm border-b border-[#1e2d47] pb-2">
                    Valores Legales del Año Vigente
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Salario Mínimo Mensual (SMMLV) <span class="text-amber-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-2.5 text-slate-500 font-mono text-sm">$</span>
                            <input type="number" step="100" min="1" name="smmlv" value="{{ old('smmlv', $smmlv) }}"
                                   required class="form-input pl-8 font-mono">
                        </div>
                        <span class="text-[11px] text-slate-500 mt-1 block">Base para deducciones de ley y topes</span>
                    </div>

                    <div>
                        <label class="form-label">Auxilio de Transporte Mensual <span class="text-amber-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-2.5 text-slate-500 font-mono text-sm">$</span>
                            <input type="number" step="100" min="0" name="auxilio_transporte" value="{{ old('auxilio_transporte', $auxTrans) }}"
                                   required class="form-input pl-8 font-mono">
                        </div>
                        <span class="text-[11px] text-slate-500 mt-1 block">Aplica a empleados con hasta 2 SMMLV</span>
                    </div>

                    <div>
                        <label class="form-label">Valor Unidad de Valor Tributario (UVT) <span class="text-amber-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-2.5 text-slate-500 font-mono text-sm">$</span>
                            <input type="number" step="10" min="1" name="uvt" value="{{ old('uvt', $uvt) }}"
                                   required class="form-input pl-8 font-mono">
                        </div>
                        <span class="text-[11px] text-slate-500 mt-1 block">Para cálculo de Retención en la Fuente</span>
                    </div>
                </div>

                <div class="pt-4 border-t border-[#1e2d47]">
                    <h3 class="font-bold text-slate-200 text-sm mb-3">Exoneración de Aportes Patronales</h3>
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="exoneracion_parafiscales_114_1" value="1"
                               @checked($exoneracion == '1')
                               class="rounded border-[#1e2d47] text-amber-500 focus:ring-amber-500 mt-1">
                        <div>
                            <span class="font-medium text-slate-200 text-sm block">Aplica Exoneración Art. 114-1 E.T. (Ley 1607 / 1819)</span>
                            <span class="text-xs text-slate-500 block mt-0.5">
                                Exonera a la empresa de pagar aportes patronales a Salud (8.5%), SENA (2%) e ICBF (3%) por trabajadores que devenguen menos de 10 SMMLV.
                            </span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="pt-4 border-t border-[#1e2d47] flex justify-end">
                <button type="submit"
                        class="bg-amber-500 hover:bg-amber-600 text-black font-bold text-sm px-6 py-2.5 rounded-xl transition-all shadow-lg shadow-amber-500/20">
                    <i class="fas fa-save mr-1.5"></i> Guardar Parámetros
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
