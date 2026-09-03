<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liquidación de Prestaciones — {{ $empleado->nombre_completo }}</title>
    @vite(['resources/css/app.css'])
    <style>
        body { background: #fff; color: #1a202c; font-family: 'DM Sans', sans-serif; font-size: 13px; }
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 0; }
            .page { max-width: 100%; padding: 10mm; }
        }
        .tabla-print td, .tabla-print th {
            padding: 6px 10px;
            border: 1px solid #e5e7eb;
        }
    </style>
</head>
<body class="bg-white text-gray-900">

<div class="no-print bg-gray-100 border-b border-gray-200 px-6 py-3 flex items-center justify-between">
    <a href="{{ route('nomina.liquidacion-definitiva.index', ['empleado_id' => $empleado->id]) }}"
       class="text-sm text-gray-600 hover:text-gray-900 flex items-center gap-2">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
    <button onclick="window.print()"
            class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600
                   text-black font-semibold px-5 py-2 rounded-xl text-sm transition-colors">
        <i class="fas fa-print"></i> Imprimir Liquidación
    </button>
</div>

<div class="page max-w-3xl mx-auto px-8 py-8 space-y-6">

    {{-- Encabezado --}}
    <div class="flex items-start justify-between border-b-2 border-amber-500 pb-4">
        <div>
            <h1 class="text-xl font-black text-gray-900">{{ $empresa->nombre_comercial ?: $empresa->razon_social }}</h1>
            <p class="text-xs text-gray-500">NIT: {{ $empresa->nit }}-{{ $empresa->digito_verificacion }} · {{ $empresa->direccion }}</p>
        </div>
        <div class="text-right">
            <span class="text-xs uppercase font-bold text-gray-500 block">DOCUMENTO</span>
            <span class="text-base font-extrabold text-amber-600">LIQUIDACIÓN DE CONTRATO</span>
            <span class="text-xs text-gray-500 block mt-0.5">Fecha: {{ date('d/m/Y') }}</span>
        </div>
    </div>

    {{-- Datos del Trabajador --}}
    <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
        <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2 border-b border-gray-200 pb-1">
            Información del Trabajador
        </h3>
        <div class="grid grid-cols-2 gap-y-2 text-xs">
            <div><span class="text-gray-500">Nombre:</span> <strong class="text-gray-800">{{ $empleado->nombre_completo }}</strong></div>
            <div><span class="text-gray-500">Documento:</span> <strong class="text-gray-800">{{ $empleado->tipo_documento }} {{ $empleado->numero_documento }}</strong></div>
            <div><span class="text-gray-500">Cargo:</span> <strong class="text-gray-800">{{ $empleado->cargo ?: 'General' }}</strong></div>
            <div><span class="text-gray-500">Tipo Contrato:</span> <strong class="text-gray-800 uppercase">{{ $empleado->tipo_contrato }}</strong></div>
            <div><span class="text-gray-500">Fecha Ingreso:</span> <strong class="text-gray-800">{{ $calculo['fecha_ingreso']->format('d/m/Y') }}</strong></div>
            <div><span class="text-gray-500">Fecha Retiro:</span> <strong class="text-gray-800">{{ $calculo['fecha_retiro']->format('d/m/Y') }}</strong></div>
            <div><span class="text-gray-500">Salario Básico:</span> <strong class="text-gray-800">${{ number_format($calculo['salario_base'], 0, ',', '.') }}</strong></div>
            <div><span class="text-gray-500">Base Liquidación:</span> <strong class="text-gray-800">${{ number_format($calculo['base_prestaciones'], 0, ',', '.') }}</strong></div>
        </div>
    </div>

    {{-- Tabla de Liquidación --}}
    <div>
        <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Liquidación de Conceptos y Prestaciones Sociales</h3>
        <table class="w-full text-xs tabla-print">
            <thead class="bg-gray-100 font-bold text-gray-700">
                <tr>
                    <th class="text-left">Concepto</th>
                    <th class="text-center">Días</th>
                    <th class="text-right">Base</th>
                    <th class="text-right">Total a Pagar</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Cesantías</td>
                    <td class="text-center">{{ $calculo['dias_cesantias'] }}</td>
                    <td class="text-right">${{ number_format($calculo['base_prestaciones'], 0, ',', '.') }}</td>
                    <td class="text-right font-semibold">${{ number_format($calculo['valor_cesantias'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Intereses sobre Cesantías (12% anual)</td>
                    <td class="text-center">{{ $calculo['dias_cesantias'] }}</td>
                    <td class="text-right">${{ number_format($calculo['valor_cesantias'], 0, ',', '.') }}</td>
                    <td class="text-right font-semibold">${{ number_format($calculo['valor_intereses'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Prima de Servicios</td>
                    <td class="text-center">{{ $calculo['dias_prima'] }}</td>
                    <td class="text-right">${{ number_format($calculo['base_prestaciones'], 0, ',', '.') }}</td>
                    <td class="text-right font-semibold">${{ number_format($calculo['valor_prima'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Vacaciones Compensadas</td>
                    <td class="text-center">{{ $calculo['dias_vacaciones'] }}</td>
                    <td class="text-right">${{ number_format($calculo['salario_base'], 0, ',', '.') }}</td>
                    <td class="text-right font-semibold">${{ number_format($calculo['valor_vacaciones'], 0, ',', '.') }}</td>
                </tr>
                @if($calculo['valor_indemnizacion'] > 0)
                <tr>
                    <td>Indemnización por Despido (Art. 64 CST)</td>
                    <td class="text-center">—</td>
                    <td class="text-right">${{ number_format($calculo['salario_base'], 0, ',', '.') }}</td>
                    <td class="text-right font-semibold">${{ number_format($calculo['valor_indemnizacion'], 0, ',', '.') }}</td>
                </tr>
                @endif
            </tbody>
            <tfoot class="bg-gray-100 font-bold text-gray-900">
                <tr>
                    <td colspan="3" class="text-right uppercase">Total Liquidación Definitiva:</td>
                    <td class="text-right text-sm text-amber-600">${{ number_format($calculo['neto_pagar'], 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Paz y Salvo --}}
    <p class="text-[11px] text-gray-600 leading-relaxed text-justify">
        El suscrito trabajador declara que ha recibido a entera satisfacción de la empresa <strong>{{ $empresa->razon_social }}</strong>
        la suma de <strong>${{ number_format($calculo['neto_pagar'], 0, ',', '.') }} COP</strong> por concepto de la liquidación final
        de sus prestaciones sociales, salarios y demás acreencias laborales derivadas del contrato de trabajo, quedando a paz y salvo
        por todo concepto legal o convencional.
    </p>

    {{-- Firmas --}}
    <div class="grid grid-cols-2 gap-12 pt-10 text-xs">
        <div class="border-t border-gray-400 text-center pt-2">
            <strong>{{ $empresa->razon_social }}</strong><br>
            <span class="text-gray-500">Empleador / Representante Legal</span>
        </div>
        <div class="border-t border-gray-400 text-center pt-2">
            <strong>{{ $empleado->nombre_completo }}</strong><br>
            <span class="text-gray-500">{{ $empleado->tipo_documento }}: {{ $empleado->numero_documento }}</span><br>
            <span class="text-gray-400">Firma del Trabajador</span>
        </div>
    </div>

</div>

</body>
</html>
