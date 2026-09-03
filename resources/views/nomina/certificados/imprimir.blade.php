<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificado Laboral — {{ $empleado->nombre_completo }}</title>
    @vite(['resources/css/app.css'])
    <style>
        body { background: #fff; color: #1e293b; font-family: 'Times New Roman', Times, serif; font-size: 15px; line-height: 1.8; }
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 0; }
            .page { max-width: 100%; padding: 25mm 20mm; }
        }
    </style>
</head>
<body class="bg-white text-gray-900">

<div class="no-print bg-gray-100 border-b border-gray-200 px-6 py-3 flex items-center justify-between font-sans text-xs">
    <button onclick="window.close()" class="text-gray-600 hover:text-gray-900 flex items-center gap-1.5">
        <i class="fas fa-arrow-left"></i> Cerrar
    </button>
    <button onclick="window.print()"
            class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-black font-bold px-5 py-2 rounded-xl text-sm transition-colors">
        <i class="fas fa-print"></i> Imprimir Certificado
    </button>
</div>

<div class="page max-w-2xl mx-auto px-10 py-12 space-y-8">

    {{-- Membrete --}}
    <div class="text-center border-b-2 border-gray-800 pb-6 font-sans">
        <h1 class="text-2xl font-black uppercase tracking-wider text-gray-900">
            {{ $empresa->nombre_comercial ?: $empresa->razon_social }}
        </h1>
        <p class="text-xs text-gray-600 mt-1">
            NIT: {{ $empresa->nit }}-{{ $empresa->digito_verificacion }} · {{ $empresa->direccion ?: 'Colombia' }}
            @if($empresa->telefono) · Tel: {{ $empresa->telefono }} @endif
            @if($empresa->email) · Email: {{ $empresa->email }} @endif
        </p>
    </div>

    {{-- Título y Destinatario --}}
    <div class="space-y-6 pt-4">
        <div class="text-center">
            <h2 class="text-lg font-bold uppercase tracking-widest text-gray-800">EL SUSCRITO REPRESENTANTE LEGAL / TALENTO HUMANO</h2>
            <p class="text-base font-bold uppercase tracking-wider mt-1">CERTIFICA:</p>
        </div>

        <p class="text-justify indent-8">
            Que el(la) señor(a) <strong>{{ strtoupper($empleado->nombre_completo) }}</strong>, identificado(a) con
            <strong>{{ $empleado->tipo_documento }} No. {{ number_format((float)$empleado->numero_documento, 0, ',', '.') }}</strong>,
            @if($empleado->activo)
                labora para nuestra empresa desde el día <strong>{{ $empleado->fecha_ingreso ? $empleado->fecha_ingreso->locale('es')->isoFormat('D [de] MMMM [de] YYYY') : 'su ingreso' }}</strong>
                hasta la fecha actual,
            @else
                laboró para nuestra empresa desde el día <strong>{{ $empleado->fecha_ingreso ? $empleado->fecha_ingreso->locale('es')->isoFormat('D [de] MMMM [de] YYYY') : 'su ingreso' }}</strong>
                hasta el día <strong>{{ $empleado->fecha_retiro ? $empleado->fecha_retiro->locale('es')->isoFormat('D [de] MMMM [de] YYYY') : date('d/m/Y') }}</strong>,
            @endif
            desempeñando el cargo de <strong>{{ strtoupper($empleado->cargo ?: 'EMPLEADO GENERAL') }}</strong>
            mediante un contrato de trabajo a <strong>TÉRMINO {{ strtoupper($empleado->tipo_contrato ?: 'INDEFINIDO') }}</strong>.
        </p>

        @if($incluirSalario)
        <p class="text-justify indent-8">
            Para el desempeño de sus labores devenga una asignación salarial mensual de
            <strong>${{ number_format($empleado->salario_base, 0, ',', '.') }} COP</strong>
            (Tipo de salario: {{ ucfirst($empleado->tipo_salario ?: 'ordinario') }}).
        </p>
        @endif

        @if($observaciones)
        <p class="text-justify indent-8">
            {{ $observaciones }}
        </p>
        @endif

        <p class="text-justify indent-8">
            El presente certificado se expide a solicitud del interesado con destino a: <strong>{{ strtoupper($destinatario) }}</strong>,
            en {{ $empresa->ciudad ?: 'la ciudad' }}, a los <strong>{{ $fecha->locale('es')->isoFormat('D [días del mes de] MMMM [de] YYYY') }}</strong>.
        </p>
    </div>

    {{-- Firma --}}
    <div class="pt-16 font-sans">
        <div class="w-64 border-t border-gray-800 text-center pt-2">
            <strong class="text-sm block text-gray-900">{{ $empresa->representante_legal ?: $empresa->razon_social }}</strong>
            <span class="text-xs text-gray-600 block">Representante Legal / Gerencia</span>
            <span class="text-xs text-gray-500 block">{{ $empresa->nombre_comercial ?: $empresa->razon_social }}</span>
        </div>
    </div>

</div>

</body>
</html>
