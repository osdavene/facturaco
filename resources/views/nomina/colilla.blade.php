<!DOCTYPE html>
@php $temaActual = 'light'; @endphp
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colilla de Pago — {{ $liquidacion->empleado->nombre_completo }}</title>
    @vite(['resources/css/app.css'])
    <style>
        body { background: #fff; color: #1a202c; font-family: 'DM Sans', sans-serif; }

        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 0; }
            .page { max-width: 100%; padding: 12mm; }
        }

        .tabla-colilla td, .tabla-colilla th {
            padding: 5px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 0.8rem;
        }
        .tabla-colilla th {
            text-align: left;
            color: #6b7280;
            font-weight: 600;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .tabla-colilla tr:last-child td { border-bottom: none; }
        .col-devengado  { background: #f0fdf4; }
        .col-deduccion  { background: #fef2f2; }
        .col-total      { background: #fffbeb; }
    </style>
</head>
<body class="bg-white text-gray-900 font-sans">

{{-- Acciones imprimir --}}
<div class="no-print bg-gray-100 border-b border-gray-200 px-6 py-3 flex items-center justify-between">
    <a href="{{ route('nomina.show', $nomina) }}"
       class="text-sm text-gray-600 hover:text-gray-900 flex items-center gap-2">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
    <button onclick="window.print()"
            class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600
                   text-black font-semibold px-5 py-2 rounded-xl text-sm transition-colors">
        <i class="fas fa-print"></i> Imprimir
    </button>
</div>

<div class="page max-w-2xl mx-auto px-6 py-8">

    {{-- Encabezado empresa --}}
    <div class="flex items-start justify-between mb-6 pb-4 border-b-2 border-amber-500">
        <div>
            <div class="text-xl font-black text-gray-900">
                {{ $empresa->nombre_comercial ?: $empresa->razon_social }}
            </div>
            <div class="text-xs text-gray-500 mt-0.5">NIT: {{ $empresa->nit }}-{{ $empresa->digito_verificacion }}</div>
            @if($empresa->municipio)
            <div class="text-xs text-gray-500">{{ $empresa->municipio }}</div>
            @endif
        </div>
        <div class="text-right">
            <div class="text-lg font-black text-amber-600">COLILLA DE PAGO</div>
            <div class="text-xs text-gray-500 mt-0.5">{{ $nomina->nombre }}</div>
            <div class="text-xs text-gray-500">
                {{ $nomina->periodo_inicio->format('d/m/Y') }} — {{ $nomina->periodo_fin->format('d/m/Y') }}
            </div>
        </div>
    </div>

    {{-- Datos empleado --}}
    <div class="bg-gray-50 rounded-xl p-4 mb-5">
        <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
            <div>
                <span class="text-gray-500 text-xs uppercase tracking-wider">Empleado</span>
                <div class="font-bold">{{ $liquidacion->empleado->nombre_completo }}</div>
            </div>
            <div>
                <span class="text-gray-500 text-xs uppercase tracking-wider">Documento</span>
                <div class="font-semibold">{{ $liquidacion->empleado->tipo_documento }}: {{ $liquidacion->empleado->numero_documento }}</div>
            </div>
            <div>
                <span class="text-gray-500 text-xs uppercase tracking-wider">Cargo</span>
                <div>{{ $liquidacion->empleado->cargo }}</div>
            </div>
            <div>
                <span class="text-gray-500 text-xs uppercase tracking-wider">Tipo de Contrato</span>
                <div>{{ $liquidacion->empleado->tipo_contrato_label }}</div>
            </div>
            <div>
                <span class="text-gray-500 text-xs uppercase tracking-wider">Salario Base</span>
                <div class="font-semibold">${{ number_format($liquidacion->empleado->salario_base, 0, ',', '.') }}</div>
            </div>
            <div>
                <span class="text-gray-500 text-xs uppercase tracking-wider">Días Trabajados</span>
                <div>{{ $liquidacion->dias_trabajados }}</div>
            </div>
            @if($liquidacion->empleado->eps)
            <div>
                <span class="text-gray-500 text-xs uppercase tracking-wider">EPS</span>
                <div>{{ $liquidacion->empleado->eps }}</div>
            </div>
            @endif
            @if($liquidacion->empleado->afp)
            <div>
                <span class="text-gray-500 text-xs uppercase tracking-wider">Fondo Pensiones</span>
                <div>{{ $liquidacion->empleado->afp }}</div>
            </div>
            @endif
        </div>
    </div>

    {{-- Tabla devengados y deducciones --}}
    <div class="grid grid-cols-2 gap-4 mb-5">

        {{-- DEVENGADOS --}}
        <div>
            <div class="text-xs font-black text-emerald-700 uppercase tracking-wider mb-2 px-2">
                <i class="fas fa-plus-circle mr-1"></i>Devengados
            </div>
            <table class="tabla-colilla w-full rounded-lg overflow-hidden col-devengado">
                <tbody>
                    <tr>
                        <td class="text-gray-600">Salario básico</td>
                        <td class="text-right font-semibold">${{ number_format($liquidacion->salario_basico, 0, ',', '.') }}</td>
                    </tr>
                    @if($liquidacion->auxilio_transporte > 0)
                    <tr>
                        <td class="text-gray-600">Auxilio transporte</td>
                        <td class="text-right font-semibold">${{ number_format($liquidacion->auxilio_transporte, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if($liquidacion->valor_horas_extras > 0)
                    <tr>
                        <td class="text-gray-600">Horas extras</td>
                        <td class="text-right font-semibold">${{ number_format($liquidacion->valor_horas_extras, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if($liquidacion->comisiones > 0)
                    <tr>
                        <td class="text-gray-600">Comisiones</td>
                        <td class="text-right font-semibold">${{ number_format($liquidacion->comisiones, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if($liquidacion->bonificaciones > 0)
                    <tr>
                        <td class="text-gray-600">Bonificaciones</td>
                        <td class="text-right font-semibold">${{ number_format($liquidacion->bonificaciones, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if($liquidacion->otros_devengados > 0)
                    <tr>
                        <td class="text-gray-600">Otros devengados</td>
                        <td class="text-right font-semibold">${{ number_format($liquidacion->otros_devengados, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr class="bg-emerald-100 border-t-2 border-emerald-300">
                        <td class="font-black text-emerald-800">TOTAL DEVENGADO</td>
                        <td class="text-right font-black text-emerald-800">${{ number_format($liquidacion->total_devengado, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- DEDUCCIONES --}}
        <div>
            <div class="text-xs font-black text-red-700 uppercase tracking-wider mb-2 px-2">
                <i class="fas fa-minus-circle mr-1"></i>Deducciones
            </div>
            <table class="tabla-colilla w-full rounded-lg overflow-hidden col-deduccion">
                <tbody>
                    <tr>
                        <td class="text-gray-600">Salud (4%)</td>
                        <td class="text-right font-semibold">${{ number_format($liquidacion->deduccion_salud, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="text-gray-600">Pensión (4%)</td>
                        <td class="text-right font-semibold">${{ number_format($liquidacion->deduccion_pension, 0, ',', '.') }}</td>
                    </tr>
                    @if($liquidacion->fondo_solidaridad > 0)
                    <tr>
                        <td class="text-gray-600">Fondo solidaridad</td>
                        <td class="text-right font-semibold">${{ number_format($liquidacion->fondo_solidaridad, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if($liquidacion->retencion_fuente > 0)
                    <tr>
                        <td class="text-gray-600">Retención en fuente</td>
                        <td class="text-right font-semibold">${{ number_format($liquidacion->retencion_fuente, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if($liquidacion->otras_deducciones > 0)
                    <tr>
                        <td class="text-gray-600">Otras deducciones</td>
                        <td class="text-right font-semibold">${{ number_format($liquidacion->otras_deducciones, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr class="bg-red-100 border-t-2 border-red-300">
                        <td class="font-black text-red-800">TOTAL DEDUCIDO</td>
                        <td class="text-right font-black text-red-800">${{ number_format($liquidacion->total_deducciones, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- NETO --}}
    <div class="bg-amber-50 border-2 border-amber-400 rounded-xl p-4 mb-5">
        <div class="flex justify-between items-center">
            <div>
                <div class="text-sm text-amber-700 font-semibold uppercase tracking-wider">Neto a Pagar</div>
                @if($nomina->fecha_pago)
                <div class="text-xs text-amber-600">Fecha de pago: {{ $nomina->fecha_pago->format('d/m/Y') }}</div>
                @endif
                @if($liquidacion->empleado->banco)
                <div class="text-xs text-amber-600 mt-0.5">
                    {{ $liquidacion->empleado->banco }}
                    @if($liquidacion->empleado->tipo_cuenta) — {{ ucfirst($liquidacion->empleado->tipo_cuenta) }} @endif
                    @if($liquidacion->empleado->numero_cuenta) Cta: {{ $liquidacion->empleado->numero_cuenta }} @endif
                </div>
                @endif
            </div>
            <div class="text-3xl font-black text-amber-700">
                ${{ number_format($liquidacion->neto_pagar, 0, ',', '.') }}
            </div>
        </div>
    </div>

    {{-- Prestaciones sociales acumuladas --}}
    <div class="mb-5">
        <div class="text-xs font-black text-blue-700 uppercase tracking-wider mb-2 px-1">
            <i class="fas fa-piggy-bank mr-1"></i>Prestaciones Sociales Acumuladas (este período)
        </div>
        <div class="grid grid-cols-4 gap-2">
            @foreach([
                ['Cesantías',       $liquidacion->acumulado_cesantias,              'blue'],
                ['Int. Cesantías',  $liquidacion->acumulado_intereses_cesantias,    'indigo'],
                ['Prima',           $liquidacion->acumulado_prima,                  'purple'],
                ['Vacaciones',      $liquidacion->acumulado_vacaciones,             'cyan'],
            ] as [$lbl, $val, $col])
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-2 text-center">
                <div class="text-[9px] text-blue-600 font-semibold uppercase mb-0.5">{{ $lbl }}</div>
                <div class="text-xs font-bold text-blue-800">${{ number_format($val, 0, ',', '.') }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Firma --}}
    <div class="border-t border-gray-200 pt-5 grid grid-cols-2 gap-8 mt-6">
        <div class="text-center">
            <div class="border-t-2 border-gray-400 pt-2 mt-10 text-xs text-gray-500">
                Empleador
            </div>
            <div class="text-xs text-gray-700 font-semibold">{{ $empresa->razon_social }}</div>
        </div>
        <div class="text-center">
            <div class="border-t-2 border-gray-400 pt-2 mt-10 text-xs text-gray-500">
                Empleado
            </div>
            <div class="text-xs text-gray-700 font-semibold">{{ $liquidacion->empleado->nombre_completo }}</div>
            <div class="text-[10px] text-gray-500">{{ $liquidacion->empleado->tipo_documento }}: {{ $liquidacion->empleado->numero_documento }}</div>
        </div>
    </div>

    <div class="text-center mt-4 text-[10px] text-gray-400">
        Generado por FacturaCO · {{ now()->format('d/m/Y H:i') }}
    </div>
</div>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</body>
</html>
