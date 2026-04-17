@extends('backoffice.layout')
@section('title', 'Nueva empresa')

@section('content')

<div class="mb-8">
    <a href="{{ route('backoffice.empresas') }}"
       class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-200 text-sm transition-colors mb-4">
        <i class="fas fa-arrow-left text-xs"></i>Volver a empresas
    </a>
    <h1 class="font-display font-black text-2xl">Nueva empresa</h1>
    <p class="text-slate-500 text-sm mt-1">Crea una empresa cliente o filial de un grupo existente</p>
</div>

<div class="max-w-2xl mx-auto">
    <form method="POST" action="{{ route('backoffice.empresas.store') }}">
        @csrf

        <div class="card p-6 space-y-5 mb-4">

            <h2 class="font-display font-bold text-base flex items-center gap-2">
                <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center text-black text-xs font-black">1</span>
                Datos de la empresa
            </h2>

            <div>
                <label class="form-label">Razón social *</label>
                <input type="text" name="razon_social" value="{{ old('razon_social') }}" required
                       placeholder="Empresa S.A.S."
                       class="form-input @error('razon_social') border-red-500 @enderror">
                @error('razon_social')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">NIT *</label>
                    <input type="text" name="nit" value="{{ old('nit') }}" required
                           placeholder="900000000"
                           class="form-input @error('nit') border-red-500 @enderror">
                    @error('nit')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" value="{{ old('telefono') }}"
                           placeholder="601 000 0000"
                           class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           placeholder="contacto@empresa.com"
                           class="form-input">
                </div>
                <x-ubicacion
                    :departamento="old('departamento')"
                    :municipio="old('municipio')"
                />
            </div>
        </div>

        <div class="card p-6 mb-5">

            <h2 class="font-display font-bold text-base flex items-center gap-2 mb-4">
                <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center text-black text-xs font-black">2</span>
                Jerarquía del grupo
            </h2>

            <label class="form-label">
                ¿Es filial de otra empresa?
            </label>
            <select name="empresa_padre_id"
                    class="form-input">
                <option value="">— No, es empresa matriz independiente —</option>
                @foreach($matrices as $m)
                    <option value="{{ $m->id }}"
                        {{ old('empresa_padre_id', request('padre')) == $m->id ? 'selected' : '' }}>
                        {{ $m->razon_social }}
                    </option>
                @endforeach
            </select>
            <p class="text-xs text-slate-600 mt-2">
                Si seleccionas una matriz, los admins de esa empresa tendrán acceso automático a esta filial.
            </p>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('backoffice.empresas') }}"
               class="px-6 py-2.5 bg-[#1a2235] border border-[#1e2d47] rounded-xl
                      text-slate-400 hover:text-slate-200 text-sm transition-colors">
                Cancelar
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600
                           text-black font-semibold text-sm px-6 py-2.5 rounded-xl transition-colors">
                <i class="fas fa-check text-xs"></i>Crear empresa
            </button>
        </div>
    </form>
</div>

@endsection
